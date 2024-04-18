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
        Schema::create('transfers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('uuid', 36);
            $table->unsignedBigInteger('user_id')->index('transfers_user_id_foreign');
            $table->string('kind')->default('transferFromAddress')->index();
            $table->string('currency_id')->index('transfers_currency_id_foreign');
            $table->string('status');
            $table->string('address_from');
            $table->string('address_to');
            $table->timestamps();
            $table->decimal('amount', 28, 8);
            $table->decimal('amount_usd', 28);
            $table->string('message')->nullable();

            $table->index(['uuid', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transfers');
    }
};
