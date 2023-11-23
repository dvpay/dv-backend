<?php

use App\Enums\Blockchain;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoice_addresses', function (Blueprint $table) {
            $enums = implode("','", Blockchain::values());
            DB::statement(sprintf("ALTER TABLE invoice_addresses MODIFY blockchain ENUM('%s')", $enums));
        });
    }

};
