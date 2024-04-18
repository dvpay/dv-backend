<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->unsignedSmallInteger('precision')->default(8);
            $table->boolean('is_fiat')->default(false);
            $table->string('blockchain')->nullable()->comment('If currency is not fiat and have contracts in many blockchains, e.g. (USDT)');
            $table->string('chain')->nullable();
            $table->string('contract_address')->nullable();
            $table->decimal('withdrawal_min_balance', 28, 8)->nullable();
            $table->boolean('has_balance')->default(true);
            $table->timestamps();
            $table->boolean('status')->default(true);
            $table->tinyInteger('sort_order')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('currencies');
    }
};
