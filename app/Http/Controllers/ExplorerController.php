<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class ExplorerController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Explorer/Index', [
            'stats' => [
                ['label' => 'Latest block', 'value' => '19,842,118', 'change' => '+12s', 'tone' => 'cyan'],
                ['label' => 'Gas tracker', 'value' => '23 gwei', 'change' => '-8.4%', 'tone' => 'emerald'],
                ['label' => 'Transactions', 'value' => '1.34M', 'change' => '+4.8%', 'tone' => 'blue'],
                ['label' => 'Finality', 'value' => '2 epochs', 'change' => 'stable', 'tone' => 'slate'],
            ],
            'networks' => [
                ['name' => 'Ethereum', 'status' => 'Live', 'tps' => '13.2', 'gas' => '23 gwei', 'settlement' => '$5.8B'],
                ['name' => 'Base', 'status' => 'Live', 'tps' => '72.4', 'gas' => '0.04 gwei', 'settlement' => '$843M'],
                ['name' => 'Arbitrum', 'status' => 'Live', 'tps' => '31.8', 'gas' => '0.11 gwei', 'settlement' => '$1.2B'],
                ['name' => 'Polygon', 'status' => 'Live', 'tps' => '44.6', 'gas' => '31 gwei', 'settlement' => '$396M'],
            ],
            'blocks' => [
                ['height' => '19,842,118', 'validator' => 'beaverbuild', 'txs' => 214, 'gasUsed' => '18.7M', 'reward' => '0.052 ETH', 'age' => '12s'],
                ['height' => '19,842,117', 'validator' => 'rsync-builder', 'txs' => 178, 'gasUsed' => '15.1M', 'reward' => '0.044 ETH', 'age' => '24s'],
                ['height' => '19,842,116', 'validator' => 'titan-builder', 'txs' => 246, 'gasUsed' => '21.4M', 'reward' => '0.061 ETH', 'age' => '36s'],
                ['height' => '19,842,115', 'validator' => 'flashbots', 'txs' => 191, 'gasUsed' => '16.2M', 'reward' => '0.039 ETH', 'age' => '48s'],
            ],
            'transactions' => [
                ['hash' => '0xb9a4...7e12', 'method' => 'Swap', 'from' => '0xA1d9...C340', 'to' => 'Uniswap V3', 'value' => '$42,180', 'fee' => '$4.12', 'status' => 'Success'],
                ['hash' => '0x7c18...90af', 'method' => 'Transfer', 'from' => '0x742d...f44e', 'to' => '0xE9f1...0a13', 'value' => '$18,920', 'fee' => '$2.84', 'status' => 'Success'],
                ['hash' => '0x32de...ab88', 'method' => 'Mint', 'from' => '0x4f2c...19d0', 'to' => 'Zora', 'value' => '$126', 'fee' => '$1.37', 'status' => 'Pending'],
                ['hash' => '0xaa09...312d', 'method' => 'Bridge', 'from' => '0xE6c1...73B4', 'to' => 'Base Portal', 'value' => '$9,450', 'fee' => '$5.06', 'status' => 'Success'],
            ],
            'addresses' => [
                ['address' => '0xd8dA...6045', 'label' => 'vitalik.eth', 'balance' => '3,911 ETH', 'netFlow' => '+$2.1M', 'risk' => 'Low'],
                ['address' => '0x28C6...1d60', 'label' => 'Binance hot wallet', 'balance' => '152,840 ETH', 'netFlow' => '-$18.4M', 'risk' => 'Medium'],
                ['address' => '0x742d...f44e', 'label' => 'Whale wallet', 'balance' => '81,450 ETH', 'netFlow' => '+$7.6M', 'risk' => 'Low'],
            ],
        ]);
    }
}