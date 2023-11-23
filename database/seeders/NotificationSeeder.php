<?php

namespace Database\Seeders;

use App\Enums\NotificationCategoryType;
use App\Models\Notification\Notification;
use App\Models\Notification\NotificationCategory;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        foreach (NotificationCategory::all() as $category) {
            $notifications = [];

            if ($category->slug === NotificationCategoryType::Errors->value) {
                $notifications += [
                    'systemErrors'  => 'System Errors',
                    'webhookErrors' => 'Webhook Errors',
                ];
            }

            if ($category->slug === NotificationCategoryType::Events->value) {
                $notifications += [
                    'receivingPayment'        => 'Receiving Payment',
                    'invoiceCreation'         => 'Invoice Creation',
                    'transfers'               => 'Transfers',
                    'sharpExchangeRateChange' => 'Sharp Exchange Rate Change',
                    'webhookSends'            => 'Webhook Sends',
                ];
            }
            if ($category->slug === NotificationCategoryType::Reports->value) {
                $notifications += [
                    'dailyReport'   => 'Daily Report',
                    'weeklyReport'  => 'Weekly Report',
                    'monthlyReport' => 'Monthly Report',
                ];
            }

            foreach ($notifications as $slug => $name) {
                Notification::firstOrCreate(['slug' => $slug], [
                    'name'                     => $name,
                    'notification_category_id' => $category->id,
                    'is_active'                => true,
                ]);
            }
        }
    }
}