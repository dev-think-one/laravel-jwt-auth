<?php


namespace JWTAuth\Database;

use Illuminate\Database\Schema\Blueprint;

class MigrationHelper
{
    public static function defaultColumns(Blueprint $table)
    {
        $table->id();
        $table->morphs('tokenable');
        $table->string('name');
        $table->string('jti', 64)->unique();
        $table->unsignedBigInteger('exp');
        $table->json('abilities')->nullable();
        $table->timestamps();
    }
}
