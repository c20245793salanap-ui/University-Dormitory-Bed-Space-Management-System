<?php

namespace App\Models;

use Database\Factories\BedSlotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BedSlot extends Model
{
    /** @use HasFactory<BedSlotFactory> */
    use HasFactory;

    protected $fillable = [
        'dorm_room_id',
        'bed_slot',
        'status',
    ];

    public function dormRoom(): BelongsTo
    {
        return $this->belongsTo(DormRoom::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(DormContract::class);
    }
}
