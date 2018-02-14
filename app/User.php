<?php

namespace App;

use Amelia\Monzo\Contracts\HasMonzoCredentials;
use Amelia\Monzo\MonzoCredentials;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;

/**
 * User model.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $webhook
 *
 * @property string $monzo_pot_id
 * @property string $monzo_user_id
 * @property string $monzo_account_id
 * @property string $monzo_user_token
 * @property string $monzo_webhook_token
 * @property string $monzo_access_token
 * @property string $monzo_refresh_token
 */
class User extends Authenticatable implements HasMonzoCredentials
{
    use Notifiable, MonzoCredentials;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'pot_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'email',
        'password',
        'remember_token',
        'monzo_access_token',
        'monzo_refresh_token',
        'monzo_webhook_token',
    ];

    /**
     * An array of attributes to encrypt.
     *
     * @var array
     */
    protected $encrypted = [
        'monzo_pot_id',
        'monzo_user_id',
        'monzo_account_id',
        'monzo_access_token',
        'monzo_refresh_token',
        'monzo_webhook_token',
    ];

    /**
     * Boot this model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $model) {
            if ($model->getAttribute('monzo_user_token') === null) {
                $token = Str::upper(str_random(16));

                $model->setAttribute('monzo_user_token', $token);
            }

            if ($model->getAttribute('monzo_webhook_token') === null) {
                $token = Str::upper(str_random(32));

                $model->setAttribute('monzo_webhook_token', $token);
            }
        });
    }

    /**
     * Get the route key name for this model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'monzo_user_token';
    }

    /**
     * Get a webhook URL for a user.
     *
     * @return string
     */
    public function getWebhookAttribute()
    {
        return route('monzo.webhook', [
            $this->monzo_user_token,
            $this->monzo_webhook_token,
        ]);
    }

    /**
     * Register a user's webhook.
     *
     * @return void
     */
    public function registerWebhook()
    {
        /** @var \Amelia\Monzo\Monzo $monzo */
        $monzo = app('monzo')->as($this);

        $webhook = $monzo->webhooks()->where('url', $this->webhook)->first();

        if ($webhook === null) {
            $monzo->registerWebhook($this->webhook);
        }
    }

    /**
     * Register a user's webhook.
     *
     * @return void
     */
    public function deleteWebhook()
    {
        /** @var \Amelia\Monzo\Monzo $monzo */
        $monzo = app('monzo')->as($this);

        $webhook = $monzo->webhooks()->where('url', $this->webhook)->first();

        if ($webhook !== null) {
            $monzo->deleteWebhook($webhook->id);
        }
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $attribute = parent::getAttribute($key);

        if (in_array($key, $this->encrypted, true)) {
            return decrypt($key);
        }

        return $attribute;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string $key
     * @param  mixed $value
     * @return $this|\Illuminate\Foundation\Auth\User
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encrypted, true)) {
            $value = encrypt($value);
        }

        return parent::setAttribute($key, $value);
    }
}
