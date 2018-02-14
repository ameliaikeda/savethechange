<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Amelia\Monzo\Models\Transaction;
use Amelia\Monzo\Events\TransactionCreated;
use Illuminate\Routing\Controller as BaseController;

class WebhookController extends BaseController
{
    /**
     * Handle a webhook.
     *
     * @param \App\User $user
     * @param string $token
     * @param \Illuminate\Http\Request $request
     */
    public function handle(User $user, string $token, Request $request)
    {
        $this->token($user, $token);

        $type = $request->input('type');

        if ($type === 'transaction.created') {
            event(new TransactionCreated(new Transaction($request->input('data')), $user));
        } else {
            logger('webhook.unhandled-type', [
                'type' => $type,
            ]);
        }
    }

    /**
     * Get a user by webhook token.
     *
     * @param \App\User $user
     * @param string $token
     * @return void
     */
    protected function token(User $user, string $token)
    {
        // now validate the token.
        if (! hash_equals($user->monzo_webhook_token, $token)) {
            logger('webhook.verification', [
                'sent_token' => $token,
                'user' => $user->id,
            ]);

            abort(404);
        }
    }
}
