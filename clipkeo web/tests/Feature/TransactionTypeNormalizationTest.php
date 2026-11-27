<?php

namespace Tests\Feature;

use App\Models\Transaction;
use Tests\TestCase;

class TransactionTypeNormalizationTest extends TestCase
{
    public function test_legacy_transaction_type_aliases_are_normalized_to_supported_values(): void
    {
        $aliases = [
            'reward' => Transaction::TYPE_BOUNTY_PRIZE,
            'payout' => Transaction::TYPE_BOUNTY_PRIZE,
            'refund' => Transaction::TYPE_BOUNTY_REFUND,
        ];

        foreach ($aliases as $legacyType => $canonicalType) {
            $transaction = new Transaction();
            $transaction->type = $legacyType;

            $this->assertSame($canonicalType, $transaction->getAttributes()['type']);
        }
    }

    public function test_supported_transaction_types_are_preserved(): void
    {
        $supportedTypes = [
            Transaction::TYPE_DEPOSIT,
            Transaction::TYPE_WITHDRAWAL,
            Transaction::TYPE_BOUNTY_HOLD,
            Transaction::TYPE_BOUNTY_PRIZE,
            Transaction::TYPE_BOUNTY_REFUND,
            Transaction::TYPE_PLATFORM_FEE,
        ];

        foreach ($supportedTypes as $type) {
            $transaction = new Transaction();
            $transaction->type = $type;

            $this->assertSame($type, $transaction->getAttributes()['type']);
        }
    }
}
