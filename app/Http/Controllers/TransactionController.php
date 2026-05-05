<?php

namespace App\Http\Controllers;

use App\Services\MockChain;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function __invoke(string $hash): Response
    {
        $tx = MockChain::findTransaction($hash);

        return Inertia::render('Transaction/Show', [
            'transaction' => $tx,
            'eth_price' => MockChain::ethPrice(),
        ]);
    }
}
