<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
    ];

    public function speedBumps()
    {
        return $this->hasMany(SpeedBump::class, 'created_by');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function activities()
    {
        return $this->hasMany(UserActivity::class);
    }

    public function settings()
    {
        return $this->hasOne(UserSetting::class);
    }

    public function deviceEvents()
    {
        return $this->hasMany(DeviceEvent::class);
    }

    public function getSettingsOrCreate()
    {
        if ($this->settings) {
            return $this->settings;
        }

        $defaults = [
            'language' => 'ar',
            'theme' => 'light',
            'alert_distance' => 100,
            'notifications_enabled' => true,
            'sound_enabled' => true,
            'gps_enabled' => true,
            'motion_tracking_enabled' => true,
        ];

        try {
            // Use relationship query to safely find or create the settings for this user.
            return $this->settings()->firstOrCreate([], $defaults);
        } catch (\Illuminate\Database\QueryException $e) {
            // In case of a race condition creating the settings (unique constraint),
            // reload the relation and return the existing record.
            if (str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), '1062')) {
                $this->unsetRelation('settings');
                return $this->settings()->first();
            }
            throw $e;
        }
    }

    public function logActivity($description, $type = 'info')
    {
        return $this->activities()->create([
            'description' => $description,
            'type' => $type,
        ]);
    }

    public function getTotalBumpsAdded()
    {
        return $this->speedBumps()->count();
    }

    public function getTotalReports()
    {
        return $this->reports()->count();
    }

    public function getVerifiedBumpsCount()
    {
        return $this->speedBumps()->where('is_verified', true)->count();
    }
}
