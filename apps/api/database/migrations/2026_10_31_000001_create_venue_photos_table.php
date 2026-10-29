<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Venue photos (FR-06). The image itself lives on Cloudinary (ADR-018);
        // we only keep Cloudinary's id for it and build the web addresses from that.
        Schema::create('venue_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->cascadeOnDelete();
            // e.g. "courtku/venues/12/x8sd0fk2lq3n": a random name Cloudinary chose.
            $table->string('public_id')->unique();
            $table->unsignedSmallInteger('width');
            $table->unsignedSmallInteger('height');
            // The first photo is the venue's cover photo.
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['venue_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venue_photos');
    }
};
