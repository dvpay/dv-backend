<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\RateSource;

return new class extends Migration {
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rate_source')
                ->nullable(false)
                ->default(RateSource::Binance->value);

            $table->string('phone',32)
                ->nullable();

            $table->foreign('rate_source')
                ->references('name')
                ->on('rate_sources');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_rate_source_foreign');
            $table->dropColumn('rate_source');
        });
    }
};