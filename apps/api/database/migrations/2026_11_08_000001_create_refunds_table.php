<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Refunds the owner made in their own Stripe dashboard and recorded in CourtKu (FR-25: recorded
        // manually in the MVP). CourtKu doesn't move the money; it keeps the record (plan section 6).
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained();
            $table->foreignId('venue_id')->constrained();
            $table->unsignedInteger('amount_sen');
            $table->string('reason', 200);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // How much of a payment has been refunded so far, kept up to date under the payment's row lock,
        // so two owners recording at once can't refund more than was paid.
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedInteger('refunded_sen')->default(0)->after('paid_amount_sen');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('refunded_sen');
        });
        Schema::dropIfExists('refunds');
    }
};
