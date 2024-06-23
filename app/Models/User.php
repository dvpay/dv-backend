<?php

namespace App\Models;

use App\Enums\WithdrawalInterval;
use App\Models\Notification\Notification;
use App\Models\Notification\NotificationTarget;
use App\Models\Traits\HasSettings;
use App\Notifications\EmailVerifyNotification;
use App\Notifications\InvoiceCreationNotification;
use App\Notifications\ReceivingPaymentNotification;
use App\Notifications\SharpRateNotification;
use App\Notifications\TransferNotification;
use App\Notifications\WebhookErrorNotification;
use App\Notifications\WebhookSuccessNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


/**
 * User model.
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property bool $is_admin
 * @property null|string $google2fa_secret
 */
class User extends Authenticatable implements MustVerifyEmail, HasLocalePreference
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, HasSettings;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'location',
        'language',
        'rate_source',
        'google2fa_secret',
        'google2fa_status',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'google2fa_status'  => 'boolean'
    ];

    public function sendEmailVerificationNotification()
    {
        $this->notify(new EmailVerifyNotification());
    }

    public function storesHolder(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    /**
     * @return BelongsToMany
     */
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'stores_users');
    }

    public function allStores()
    {
        return $this->stores->merge($this->storesHolder);

    }

    /**
     * @return BelongsToMany
     */
    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany(Notification::class, 'notification_user');
    }

    public function telegram(): BelongsTo
    {
        return $this->belongsTo(TgUser::class, 'id', 'user_id');
    }


    public function preferredLocale()
    {
        return $this->language;
    }


    public function notifyNewInvoice(Invoice $invoice): void
    {
        if ($this->notifications->contains('slug', 'invoiceCreation')) {
            $this->notify((new InvoiceCreationNotification($invoice)));
        }
    }

    public function notifyReceivingPayment(Transaction $transaction): void
    {
        if ($this->notifications->contains('slug', 'receivingPayment')) {
            $this->notify((new ReceivingPaymentNotification($transaction)));
        }
    }

    public function notifyTransfer(Transaction $transaction): void
    {
        if ($this->notifications->contains('slug', 'transfers')) {
            $this->notify((new TransferNotification($transaction)));
        }
    }

    public function notifySharpRate(array $data): void
    {
        if ($this->notifications->contains('slug', 'sharpExchangeRateChange')) {
            $this->notify((new SharpRateNotification($data)));
        }
    }

    public function notifyWebhookSuccess(Invoice $invoice, array $data): void
    {
        if ($this->notifications->contains('slug', 'webhookSends')) {
            $this->notify((new WebhookSuccessNotification($invoice, $data)));
        }
    }

    public function notifyWebhookError(Invoice $invoice, array $data): void
    {
        if ($this->notifications->contains('slug', 'webhookSends')) {
            $this->notify((new WebhookErrorNotification($invoice, $data)));
        }
    }

    public function notificationTarget(): BelongsToMany
    {
        return $this->belongsToMany(NotificationTarget::class, 'notification_user_targets');
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class, 'user_id');
    }

    public function withdrawalWallets()
    {
        return $this->hasMany(WithdrawalWallet::class, 'user_id');
    }

    /*
     * If wanna using setting you need set name and type for model
     * */
    public static function getSettingsDefinitions(): array
    {
        return [
            [
                'name'    => 'withdrawal_interval',
                'cast'    => 'string',
                'default' => 'Never'
            ],
            [
                'name'    => 'withdrawal_min_balance',
                'cast'    => 'integer',
                'default' => null
            ],
            [
                'name' => 'withdrawal_rule_type',
                'cast' => 'string',
                'default' => 'manual',
            ]
        ];
    }

}
