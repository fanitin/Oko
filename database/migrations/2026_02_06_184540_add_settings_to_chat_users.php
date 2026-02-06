<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chat_users', function (Blueprint $table) {
            $table->boolean('is_muted')->default(false)->after('user_id');
            $table->boolean('is_pinned')->default(false)->after('is_muted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_users', function (Blueprint $table) {
            $table->dropColumn('is_muted');
            $table->dropColumn('is_pinned');
        });
    }
};
