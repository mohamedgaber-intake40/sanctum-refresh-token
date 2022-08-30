<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\PersonalAccessToken;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        PersonalAccessToken::query()->chunk(1000,function ($tokensChunk){
           $tokensChunk->each(function ($token){
               $token->update(['expires_at' => $token->expired_at]);
           });
        });
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn('expired_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->timestamp('expired_at')->nullable()->after('last_used_at');
        });
        PersonalAccessToken::query()->chunk(1000,function ($tokensChunk){
            $tokensChunk->each(function ($token){
                $token->update(['expired_at' => $token->expires_at]);
            });
        });
    }
};
