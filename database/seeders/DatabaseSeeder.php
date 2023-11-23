<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
                DictionariesSeeder::class,
                RoleSeeder::class,
                RootUserSeeder::class,
                ServicesSeeder::class,
                PermissionSeeder::class,
                NotificationCategorySeeder::class,
                NotificationSeeder::class,
                NotificationTargetSeeder::class
        ]);
    }
}
