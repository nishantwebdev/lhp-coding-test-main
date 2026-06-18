<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $appends = ['remaining_seats'];

    protected $casts = [
        'payload' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function newUniqueId(): string
    {
        return (string) Str::uuid();
    }

    /**
     * The path to the images directory.
     */
    public const IMAGE_PATH = 'storage/images/events/';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the images for the event with their full URLs.
     */
    public function getImagesWithUrl(): array
    {
        $images = $this->payload['images'] ?? [];

        return array_map(fn ($image) => asset(self::IMAGE_PATH.$image), $images);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function getRemainingSeatsAttribute(): int
    {
        $capacity = (int) ($this->payload['venue']['capacity'] ?? 0);

        $attendeeCount = array_key_exists('attendees_count', $this->attributes)
            ? $this->attendees_count
            : $this->attendees()->count();

        return max(0, $capacity - $attendeeCount);
    }
}
