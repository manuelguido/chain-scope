<?php

namespace App\Http\Controllers;

use App\Services\MockChain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|Response
    {
        $q = (string) $request->query('q', '');
        $detected = MockChain::detect($q);

        return match ($detected['type']) {
            'tx' => redirect()->route('tx.show', ['hash' => $detected['value']]),
            'block' => redirect()->route('block.show', ['height' => $detected['value']]),
            'address' => redirect()->route('address.show', ['address' => $detected['value']]),
            'empty' => redirect()->route('explorer.index'),
            default => Inertia::render('Search/NotFound', ['query' => $q]),
        };
    }
}
