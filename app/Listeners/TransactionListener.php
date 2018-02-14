<?php

namespace App\Listeners;

use Amelia\Monzo\Monzo;
use Amelia\Monzo\Models\Transaction;
use Amelia\Monzo\Events\TransactionCreated;

class TransactionListener
{
    /**
     * Schemes to act on.
     *
     * @var array
     */
    protected $schemes = [
        'mastercard',
        'bacs',
        'payport_faster_payments',
        'p2p_payment',
    ];

    /**
     * Monzo API client.
     *
     * @var \Amelia\Monzo\Monzo
     */
    protected $monzo;

    /**
     * Create the event listener.
     *
     * @param \Amelia\Monzo\Monzo $monzo
     */
    public function __construct(Monzo $monzo)
    {
        $this->monzo = $monzo;
    }

    /**
     * Handle the event.
     *
     * @param TransactionCreated $event
     * @return void
     */
    public function handle(TransactionCreated $event)
    {
        if (! $this->applies($event->transaction) || is_null($event->user->monzo_pot_id)) {
            return;
        }

        $change = $this->change($event->transaction->amount);

        if ($change > 0) {
            $this->monzo
                ->as($user = $event->user)
                ->pot($user->monzo_pot_id)
                ->deposit($change);
        }
    }

    /**
     * Get the change from a transaction.
     *
     * @param int $amount
     * @return int
     */
    protected function change(int $amount)
    {
        if ($amount > 0) {
            return 0;
        }

        $modulo = $amount % 100;

        return ($modulo === 0) ? 0 : (100 + $modulo);
    }

    /**
     * Check if this transaction applies.
     *
     * @param \Amelia\Monzo\Models\Transaction $transaction
     * @return bool
     */
    protected function applies(Transaction $transaction)
    {
        return $transaction->currency === 'GBP'
            && in_array($transaction->scheme, $this->schemes, true)
            && $transaction->amount < 0;
    }
}
