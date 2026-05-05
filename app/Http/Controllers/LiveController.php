<?php

namespace App\Http\Controllers;

use App\Services\MockChain;
use Illuminate\Http\JsonResponse;

class LiveController extends Controller
{
    public function tip(): JsonResponse
    {
        return response()->json([
            'tip' => MockChain::tipHeight(),
            'gas_gwei' => MockChain::gasPriceGwei(),
            'eth_price' => MockChain::ethPrice(),
            'ts' => time(),
        ]);
    }

    public function blocks(): JsonResponse
    {
        return response()->json(['blocks' => MockChain::latestBlocks(8)]);
    }

    public function transactions(): JsonResponse
    {
        return response()->json(['transactions' => MockChain::latestTransactions(10)]);
    }
}
