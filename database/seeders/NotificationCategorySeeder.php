<?php

namespace Database\Seeders;

use App\Enums\NotificationCategoryType;
use App\Models\Notification\NotificationCategory as NotificationCategoryModel;
use Illuminate\Database\Seeder;

class NotificationCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = NotificationCategoryType::cases();

        foreach ($categories as $category) {
            NotificationCategoryModel::firstOrCreate(['slug' => $category->value,],
                    [
                            'name'      => $category->name,
                            'is_active' => true
                    ]);
        }
    }
}
