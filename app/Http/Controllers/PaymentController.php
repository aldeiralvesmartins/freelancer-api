<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Proposal;
use App\Models\Wallet;
use Illuminate\Http\Request;

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
}
