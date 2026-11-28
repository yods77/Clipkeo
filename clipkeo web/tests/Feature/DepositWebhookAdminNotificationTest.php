<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Notifications\AdminActivityNotification;
use App\Notifications\FinancialStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DepositWebhookAdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.xendit.verification_token' => 'test-xendit-callback-token']);
        Notification::fake();
    }

    public function test_paid_publisher_deposit_notifies_every_admin_and_credits_wallet_once(): void
    {
        $publisher = User::factory()->create([
            'name' => 'Publisher Test',
            'role' => 'publisher',
            'wallet_balance' => 0,
        ]);

        $firstAdmin = User::factory()->create(['role' => 'admin']);
        $secondAdmin = User::factory()->create(['role' => 'admin']);

        $externalId = 'DEP-20261128-TEST';
        $transaction = Transaction::create([
            'user_id' => $publisher->id,
            'type' => Transaction::TYPE_DEPOSIT,
            'amount' => 50000,
            'currency' => 'IDR',
            'status' => 'pending',
            'reference_id' => $externalId,
            'external_ref' => 'xendit-invoice-test',
            'description' => 'Menunggu Pembayaran via Xendit',
        ]);

        $this->sendPaidWebhook($externalId)->assertOk();

        $this->assertSame(50000.0, (float) $publisher->fresh()->wallet_balance);
        $this->assertSame('success', $transaction->fresh()->status);
        $this->assertSame('BANK_TRANSFER', $transaction->fresh()->payment_channel);

        Notification::assertSentTo(
            $firstAdmin,
            AdminActivityNotification::class,
            function (AdminActivityNotification $notification) use ($firstAdmin, $publisher, $transaction) {
                $data = $notification->toArray($firstAdmin);

                return $data['type'] === 'deposit_success'
                    && $data['data_id'] === $transaction->id
                    && $data['reference_id'] === $transaction->reference_id
                    && str_contains($data['message'], $publisher->name)
                    && str_contains($data['message'], '50.000');
            }
        );

        Notification::assertSentTo($secondAdmin, AdminActivityNotification::class);
        Notification::assertSentTo($publisher, FinancialStatusNotification::class);

        // Xendit may retry the same callback. It must not add balance or
        // create another admin notification for an already-successful deposit.
        $this->sendPaidWebhook($externalId)->assertOk();

        $this->assertSame(50000.0, (float) $publisher->fresh()->wallet_balance);
        Notification::assertSentToTimes($firstAdmin, AdminActivityNotification::class, 1);
        Notification::assertSentToTimes($secondAdmin, AdminActivityNotification::class, 1);
    }

    public function test_paid_deposit_does_not_notify_users_who_are_not_admins(): void
    {
        $publisher = User::factory()->create([
            'role' => 'publisher',
            'wallet_balance' => 0,
        ]);
        $clipper = User::factory()->create(['role' => 'clipper']);
        $admin = User::factory()->create(['role' => 'admin']);

        $externalId = 'DEP-20261128-CLIPPER-CHECK';
        Transaction::create([
            'user_id' => $publisher->id,
            'type' => Transaction::TYPE_DEPOSIT,
            'amount' => 25000,
            'currency' => 'IDR',
            'status' => 'pending',
            'reference_id' => $externalId,
            'description' => 'Menunggu Pembayaran via Xendit',
        ]);

        $this->sendPaidWebhook($externalId)->assertOk();

        Notification::assertSentTo($admin, AdminActivityNotification::class);
        Notification::assertNotSentTo($clipper, AdminActivityNotification::class);
    }

    private function sendPaidWebhook(string $externalId)
    {
        return $this->postJson('/webhooks/xendit', [
            'external_id' => $externalId,
            'status' => 'PAID',
            'payment_method' => 'BANK_TRANSFER',
        ], [
            'x-callback-token' => 'test-xendit-callback-token',
        ]);
    }
}
