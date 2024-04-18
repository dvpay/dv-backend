<?php

namespace Database\Seeders;

use App\Enums\PermissionsEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        Permission::firstOrCreate(['name' => PermissionsEnum::TransfersFunds->value]);
        Permission::firstOrCreate(['name' => PermissionsEnum::StopStorePay->value]);
        Permission::firstOrCreate(['name' => PermissionsEnum::ExchangeStop->value]);
    }
}
