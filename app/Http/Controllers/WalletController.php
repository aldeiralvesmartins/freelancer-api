<?php
namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function index()
    {
        $wallet = Wallet::where('user_id', Auth::id())->firstOrFail();

        return response()->json($wallet);
    }

    public function show($id)
    {
        $wallet = Wallet::findOrFail($id);
        $this->authorize('view', $wallet);
        return $wallet;
    }

    // Para este exemplo, não tem store/update/destroy — o saldo é controlado pelo sistema
}
