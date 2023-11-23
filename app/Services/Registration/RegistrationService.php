<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Dto\Models\StoreDto;
use App\Dto\Models\UserDto;
use App\Enums\CurrencySymbol;
use App\Enums\RateSource;
use App\Enums\UserRole;
use App\Services\Store\StoreService;
use App\Services\User\UserService;
use Illuminate\Database\Connection;
use Throwable;

/**
 * RegistrationService
 */
final readonly class RegistrationService
{
    /**
     * @param UserService $userService
     * @param StoreService $storeService
     * @param Connection $db
     */
    public function __construct(
        private UserService  $userService,
        private StoreService $storeService,
        private Connection   $db
    )
    {
    }

    /**
     * @param UserDto $dto
     * @return void
     * @throws Throwable
     */
    public function handle(UserDto $dto): void
    {
        try {
            $this->db->beginTransaction();

            $user = $this->userService->create($dto, UserRole::Admin->value);

            $this->createDefaultStore((string)$user->id);

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();

            throw $e;
        }
    }

    /**
     * @param string $userId
     * @return void
     * @throws Throwable
     */
    private function createDefaultStore(string $userId): void
    {
        $dto = new StoreDto([
            'userId'                => $userId,
            'name'                  => 'First',
            'currencyId'            => CurrencySymbol::USD->value,
            'rateSource'            => RateSource::Binance->value,
            'rateScale'             => config('setting.rate_scale'),
            'invoiceExpirationTime' => config('setting.invoice_lifetime'),
            'addressHoldTime'       => config('setting.store_address_hold_time'),
            'status'                => true,
        ]);

        $this->storeService->create($dto);
    }
}
