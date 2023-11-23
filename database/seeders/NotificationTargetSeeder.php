<?php

namespace Database\Seeders;

use App\Models\Notification\NotificationTarget;
use Illuminate\Database\Seeder;

class NotificationTargetSeeder extends Seeder
{
    public function run(): void
    {
        $notifications = ['telegram', 'mail'];

        foreach ($notifications as $notification) {
            NotificationTarget::firstOrCreate(['slug' => $notification]);
        }
    }
}
