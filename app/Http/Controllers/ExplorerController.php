<?php

namespace App\Http\Controllers;

use App\Services\MockChain;
use Inertia\Inertia;
use Inertia\Response;

class ExplorerController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Explorer/Index', [
            'tip' => MockChain::tipHeight(),
            'gas_gwei' => MockChain::gasPriceGwei(),
            'eth_price' => MockChain::ethPrice(),
            'blocks' => MockChain::latestBlocks(8),
            'transactions' => MockChain::latestTransactions(10),
        ]);
    }
}
