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
            $table->string('code')->primary();
            $table->string('name')->nullable(false);
            $table->unsignedSmallInteger('precision')->nullable(false)->default(8);
            $table->boolean('is_fiat')->nullable(false)->default(false);
            $table->string('blockchain')->nullable(true)->comment('If currency is not fiat and have contracts in many blockchains, e.g. (USDT)');

            $table->timestamps();
        });

        Schema::create('rate_sources', function (Blueprint $table) {
            $table->string('name')->primary();
            $table->string('uri');
        });

        Schema::create('stores', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->unsignedInteger('user_id')->nullable(false);
            $table->string('name')->nullable(false);
            $table->string('currency')->nullable(false)->comment('Default currency rate');
            $table->string('rate_source')->nullable(false)->comment('Place where get currency rate');

            $table->foreign('currency')
                ->references('code')->on('currencies')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->foreign('rate_source')
                ->references('name')->on('rate_sources')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stores');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('rate_sources');
    }
};
