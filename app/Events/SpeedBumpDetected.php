<?php

namespace App\Events;

use App\Models\SpeedBump;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class SpeedBumpDetected implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    public $bump;

    /**
     * Create a new event instance.
     *
     * @param SpeedBump $bump
     */
    public function __construct(SpeedBump $bump)
    {
        $this->bump = $bump;
    }

    /**
     * The channel the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn()
    {
        return new Channel('speed-bumps');
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->bump->id,
            'latitude' => $this->bump->latitude,
            'longitude' => $this->bump->longitude,
            'confidence_level' => $this->bump->confidence_level ?? null,
            'reports_count' => $this->bump->reports_count ?? 0,
        ];
    }

    public function broadcastAs()
    {
        return 'SpeedBumpDetected';
    }
}
