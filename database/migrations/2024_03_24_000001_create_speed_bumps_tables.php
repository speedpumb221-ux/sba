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
        // Speed Bumps Table
        Schema::create('speed_bumps', function (Blueprint $table) {
            $table->id();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->enum('source', ['osm', 'google', 'user', 'predicted'])->default('user');
            $table->integer('confidence')->default(50); // 0-100
            $table->integer('reports_count')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['latitude', 'longitude']);
            $table->index('source');
            $table->index('confidence');
        });

        // Reports Table
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('speed_bump_id')->constrained('speed_bumps')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('report_type', ['confirm', 'false_positive', 'removed', 'new'])->default('confirm');
            $table->text('comment')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
            $table->index('report_type');
        });

        // Predictions Table (for AI-predicted bumps)
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('score')->default(0); // Scoring system
            $table->integer('vibration_count')->default(0);
            $table->integer('speed_drop_count')->default(0);
            $table->integer('user_count')->default(0);
            $table->boolean('is_converted')->default(false);
            $table->foreignId('converted_to_bump_id')->nullable()->constrained('speed_bumps')->onDelete('set null');
            $table->timestamps();
            $table->index('score');
            $table->index('is_converted');
        });

        // User Activities Table
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('activity_type', ['added_bump', 'reported_bump', 'confirmed_bump', 'detected_event'])->default('detected_event');
            $table->morphs('subject');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index('activity_type');
        });

        // Device Events Table (for motion and speed data)
        Schema::create('device_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('speed', 8, 2)->nullable(); // km/h
            $table->decimal('acceleration_x', 8, 4)->nullable();
            $table->decimal('acceleration_y', 8, 4)->nullable();
            $table->decimal('acceleration_z', 8, 4)->nullable();
            $table->decimal('vibration_magnitude', 8, 4)->nullable();
            $table->boolean('is_processed')->default(false);
            $table->timestamps();
            $table->index('is_processed');
        });

        // User Settings Table
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->enum('language', ['ar', 'en'])->default('ar');
            $table->enum('theme', ['light', 'dark'])->default('light');
            $table->integer('alert_distance')->default(100); // meters (50, 100, 200)
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('sound_enabled')->default(true);
            $table->boolean('gps_enabled')->default(true);
            $table->boolean('motion_tracking_enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_events');
        Schema::dropIfExists('user_settings');
        Schema::dropIfExists('user_activities');
        Schema::dropIfExists('predictions');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('speed_bumps');
    }
};
