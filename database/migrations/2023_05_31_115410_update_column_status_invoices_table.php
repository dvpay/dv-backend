<?php

use App\Enums\InvoiceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $enums = implode("','", InvoiceStatus::values());

        DB::statement(sprintf("ALTER TABLE invoices MODIFY status ENUM('%s')", $enums));
        DB::statement(sprintf("ALTER TABLE invoice_status_histories MODIFY status ENUM('%s'), MODIFY previous_status ENUM('%s')", $enums, $enums));


    }
};
