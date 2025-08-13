<?php
namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class WalletController extends Controller
{
    public function index()
    {
        $wallet = Wallet::where('user_id', Auth::id())->firstOrFail();

        return response()->json($wallet);
    }

    public function me(Request $request)
    {
        $wallet = Wallet::where('user_id', Auth::id())->firstOrFail();

        // Calcula valor bloqueado
        $lockedAmount = Transaction::where('wallet_id', $wallet->id)
            ->selectRaw("SUM(CASE WHEN type = 'lock' THEN amount ELSE 0 END) -
                         SUM(CASE WHEN type IN ('release', 'unlock') THEN amount ELSE 0 END) AS locked_amount")
            ->value('locked_amount');

        // Busca últimas 10 transações ordenadas por mais recente
        $transactions = Transaction::where('wallet_id', $wallet->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'balance' => $wallet->balance,
            'locked' => $lockedAmount ?? 0,
            'transactions' => $transactions,
        ]);
    }

    // Para este exemplo, não tem store/update/destroy — o saldo é controlado pelo sistema
}
