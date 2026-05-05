<?php

namespace App\Http\Controllers;

use App\Services\MockChain;
use Inertia\Inertia;
use Inertia\Response;

class AddressController extends Controller
{
    public function __invoke(string $address): Response
    {
        return Inertia::render('Address/Show', [
            'data' => MockChain::addressInfo($address),
            'eth_price' => MockChain::ethPrice(),
        ]);
    }
}
