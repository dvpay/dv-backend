<?php

declare(strict_types=1);

namespace App\Services\Store;

use App\Dto\Models\StoreDto;
use App\Enums\UserRole;
use App\Models\Store;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class StoreService
{

    /**
     * @throws Throwable
     */
    public function create(StoreDto $dto): Store
    {
        return Store::create([
                'user_id'                 => $dto->userId,
                'name'                    => $dto->name ?? '',
                'currency_id'             => $dto->currencyId,
                'rate_source'             => $dto->rateSource,
                'rate_scale'              => $dto->rateScale,
                'invoice_expiration_time' => $dto->invoiceExpirationTime,
                'address_hold_time'       => $dto->addressHoldTime,
                'status'                  => $dto->status
        ]);
    }

    /**
     * @param  User  $user
     * @return Collection Store model
     */
    public function list(User $user): Collection
    {
        $stores = $user->stores()
                ->withSum('invoicesSuccess', 'amount')
                ->withCount('invoicesSuccess')
                ->get();

        if (!$user->hasRole(UserRole::Admin->value)) {
            return $stores;
        }

        return $user->storesHolder()
                ->withSum('invoicesSuccess', 'amount')
                ->withCount('invoicesSuccess')
                ->get()
                ->merge($stores);
    }

    public function update(StoreDto $dto, Store $store): Store
    {
        $store->update($dto->toSnakeCase());

        return $store;
    }

    /**
     * @throws Throwable
     */
    public function batchUpdateStore(StoreDto $dto, User|Authenticatable $user): void
    {
        Store::where('user_id', $user->id)
                ->update($dto->toSnakeCase());
    }

    /**
     * @param  User  $holder
     * @param  User  $user
     * @return void
     */
    public function attachUserToStoresByHolder(int $holder, User $user): void
    {
        Store::where('user_id', $holder)
                ->lazy()
                ->each(function ($store) use ($user) {
                    $store->users()->attach([$user->id]);
                });
    }

    public function attachUserToStores(array $stores, User $user): void
    {
        $user->stores()->sync($stores);
    }
}