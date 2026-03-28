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
        // ============================================
        // Speed Bumps Table
        // ============================================
        Schema::create('speed_bumps', function (Blueprint $table) {
            $table->id();
            
            // Location Data
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            
            // Metadata
            $table->string('source')->default('user'); // osm, google, user, predicted
            $table->string('location')->nullable(); // Human-readable location
            $table->text('description')->nullable();
            
            // Verification & Confidence
            $table->integer('confidence_level')->default(50); // 0-100
            $table->boolean('is_verified')->default(false);
            $table->integer('reports_count')->default(0);
            
            // Relationships
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['latitude', 'longitude']);
            $table->index('source');
            $table->index('confidence_level');
            $table->index('is_verified');
            $table->index('created_at');
        });

        // ============================================
        // Reports Table
        // ============================================
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('speed_bump_id')->constrained('speed_bumps')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Report Data
            $table->string('report_type'); // confirm, false_positive, removed, new
            $table->text('comment')->nullable();
            
            // Location (for new reports)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('report_type');
            $table->index('user_id');
            $table->index('created_at');
        });

        // ============================================
        // Predictions Table (AI-predicted bumps)
        // ============================================
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            
            // Location Data
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            
            // Scoring
            $table->integer('score')->default(0); // 0-100
            
            // Detection Metrics
            $table->integer('vibration_count')->default(0);
            $table->integer('speed_drop_count')->default(0);
            $table->integer('user_count')->default(0);
            
            // Conversion Status
            $table->boolean('is_converted')->default(false);
            $table->foreignId('converted_to_bump_id')->nullable()->constrained('speed_bumps')->onDelete('set null');
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('score');
            $table->index('is_converted');
            $table->index(['latitude', 'longitude']);
        });

        // ============================================
        // User Activities Table
        // ============================================
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            
            // User Reference
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Activity Data
            $table->string('activity_type'); // added_bump, reported_bump, confirmed_bump, detected_event
            $table->text('description')->nullable();
            
            // Polymorphic Relationship (for flexible subject linking)
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('activity_type');
            $table->index('user_id');
            $table->index('created_at');
        });

        // ============================================
        // Device Events Table (motion and speed data)
        // ============================================
        Schema::create('device_events', function (Blueprint $table) {
            $table->id();
            
            // User Reference
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Location Data
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            
            // Motion Data
            $table->decimal('speed', 8, 2)->nullable(); // km/h
            $table->decimal('acceleration_x', 8, 4)->nullable();
            $table->decimal('acceleration_y', 8, 4)->nullable();
            $table->decimal('acceleration_z', 8, 4)->nullable();
            $table->decimal('vibration_magnitude', 8, 4)->nullable();
            
            // Processing Status
            $table->boolean('is_processed')->default(false);
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('is_processed');
            $table->index('user_id');
            $table->index('created_at');
        });

        // ============================================
        // User Settings Table
        // ============================================
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            
            // User Reference (unique - one settings per user)
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            
            // Language & Theme
            $table->string('language')->default('ar'); // ar, en
            $table->string('theme')->default('light'); // light, dark
            
            // Alert Settings
            $table->integer('alert_distance')->default(100); // meters (50, 100, 200)
            
            // Feature Toggles
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('sound_enabled')->default(true);
            $table->boolean('gps_enabled')->default(true);
            $table->boolean('motion_tracking_enabled')->default(true);
            
            // Timestamps
            $table->timestamps();
        });

        // ============================================
        // Road Events Table (for tracking road conditions)
        // ============================================
        Schema::create('road_events', function (Blueprint $table) {
            $table->id();
            
            // Location Data
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            
            // Event Data
            $table->string('event_type'); // accident, congestion, weather, etc.
            $table->text('description')->nullable();
            $table->integer('severity')->default(1); // 1-5
            
            // Relationships
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('speed_bump_id')->nullable()->constrained('speed_bumps')->onDelete('set null');
            
            // Processing Status
            $table->boolean('is_processed')->default(false);
            $table->boolean('has_bump')->default(false);
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('event_type');
            $table->index('is_processed');
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order of creation (respecting foreign keys)
        Schema::dropIfExists('road_events');
        Schema::dropIfExists('user_settings');
        Schema::dropIfExists('device_events');
        Schema::dropIfExists('user_activities');
        Schema::dropIfExists('predictions');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('speed_bumps');
    }
};
