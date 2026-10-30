<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Plan section 7: the web app sends an Idempotency-Key with every "Book" press. A double tap
        // or a network retry sends the same key again, and gets the same booking back instead of a
        // second one. Unique per player, so two presses can't both slip through at the same moment.
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('idempotency_key', 100)->nullable()->after('code');
            $table->unique(['user_id', 'idempotency_key']);
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'idempotency_key']);
            $table->dropColumn('idempotency_key');
        });
    }
};
