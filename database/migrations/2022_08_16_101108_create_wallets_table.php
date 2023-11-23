<?php

use App\Enums\Blockchain;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('address')->nullable(false);

            $table->enum('blockchain', ['tron', 'bitcoin'])->nullable(false);
            $table->uuid('store_id')->nullable(false);

            $table->foreign('store_id')
                ->references('id')->on('stores')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->boolean('readonly')->nullable(false);
            $table->string('seed')->nullable();
            $table->string('pass_phrase')->nullable();

            $table->timestampsTz();
            $table->softDeletes();
        });

        Schema::create('blockchain_currencies', function (Blueprint $table) {
            $table->string('blockchain_currency_code')->primary();
            $table->string('currency_code')->nullable(false);

            $table->foreign('currency_code')
                ->references('code')->on('currencies')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->enum('blockchain', ['tron', 'bitcoin'])->nullable(false);
            $table->string('contract_address')->nullable(false);
            $table->timestampsTz();
        });

        Schema::create('wallet_balances', function (Blueprint $table) {
            $table->uuid('wallet_id')->nullable(false);

            $table->foreign('wallet_id')
                ->references('id')->on('wallets')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->string('blockchain_currency')->nullable(false);

            $table->foreign('blockchain_currency')
                ->references('blockchain_currency_code')->on('blockchain_currencies')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->decimal('balance', 28, 8)->nullable();
            $table->timestampsTz();

            $table->primary(['wallet_id', 'blockchain_currency']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallet_balances');
        Schema::dropIfExists('blockchain_currencies');
        Schema::dropIfExists('wallets');
    }
};
