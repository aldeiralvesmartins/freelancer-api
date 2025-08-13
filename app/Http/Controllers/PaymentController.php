<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Proposal;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function Psy\debug;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'proposal_id' => 'required|exists:proposals,id',
            'amount' => 'required|numeric',
            'fee' => 'nullable|numeric'
        ]);

        $validated['status'] = 'held';

        return Payment::create($validated);
    }

    public function release($id)
    {
        $payment = Payment::findOrFail($id);
        if ($payment->status !== 'held') return response(['error' => 'Pagamento já liberado ou inválido'], 400);

        $freelancerId = $payment->proposal->freelancer_id;
        $wallet = Wallet::firstOrCreate(['user_id' => $freelancerId]);
        $wallet->increment('balance', $payment->amount - $payment->fee);

        $payment->update(['status' => 'released']);

        return $payment;
    }

    public function depositAndLock(Request $request)
    {
        $request->validate([
            'proposalId' => 'required|string|exists:proposals,id',
            'methodId' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $proposal = Proposal::findOrFail($request->proposalId);

        if ($proposal->project->client_id !== Auth::id()) {
            return response()->json(['error' => 'Você não tem permissão para pagar esta proposta.'], 403);
        }

        // Busca a wallet do cliente
        $wallet = Wallet::where('user_id', Auth::id())->firstOrFail();

        // Verifica se saldo disponível é suficiente (balance - bloqueado)
        $lockedAmount = Transaction::where('wallet_id', $wallet->id)
            ->selectRaw("SUM(CASE WHEN type = 'lock' THEN amount ELSE 0 END) -
                         SUM(CASE WHEN type IN ('release', 'unlock') THEN amount ELSE 0 END) AS locked_amount")
            ->value('locked_amount') ?? 0;

        $availableBalance = $wallet->balance - $lockedAmount;

        if ($request->amount > $availableBalance) {
            return response()->json(['error' => 'Saldo insuficiente para realizar o depósito.'], 400);
        }

        DB::beginTransaction();

        try {
            // Cria transação de depósito (entrada)
            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'deposit',
                'amount' => $request->amount,
                'related_id' => $proposal->id,
                'related_type' => 'proposal',
                'description' => "Depósito para proposta {$proposal->id}",
            ]);

            // Cria transação de bloqueio
            Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'lock',
                'amount' => $request->amount,
                'related_id' => $proposal->id,
                'related_type' => 'proposal',
                'description' => "Bloqueio de valor para proposta {$proposal->id}",
            ]);

            // Atualiza saldo da wallet (saldo disponível diminui)
            $wallet->balance -= $request->amount;
            $wallet->save();

            // Atualiza status da proposta para refletir pagamento do depósito (opcional)
            $proposal->deposit_amount = $request->amount;
            $proposal->deposit_status = 'paid';
            $proposal->save();

            DB::commit();

            return response()->json([
                'message' => 'Depósito e bloqueio realizados com sucesso.',
                'balance' => $wallet->balance,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Erro ao processar o depósito: ' . $e->getMessage(),
            ], 500);
        }
    }
}
