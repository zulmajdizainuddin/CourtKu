<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Payment taken at the counter (FR-28, ADR-021): when, how (cash, QR / DuitNow, card) and by whom.
        // Online payments get their own table with M4 (plan section 6, payments).
        Schema::table('bookings', function (Blueprint $table) {
            $table->dateTime('paid_at')->nullable()->after('checked_in_at');
            $table->string('payment_method', 20)->nullable()->after('paid_at');
            $table->foreignId('paid_by')->nullable()->after('payment_method')->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('paid_by');
            $table->dropColumn(['paid_at', 'payment_method']);
        });
    }
};
