<?php

namespace App\Http\Controllers;

use App\Services\MockChain;
use Inertia\Inertia;
use Inertia\Response;

class BlockController extends Controller
{
    public function __invoke(int $height): Response
    {
        $block = MockChain::block($height);
        $tip = MockChain::tipHeight();

        return Inertia::render('Block/Show', [
            'block' => $block,
            'tip' => $tip,
            'confirmations' => max(0, $tip - $height),
            'eth_price' => MockChain::ethPrice(),
        ]);
    }
}
