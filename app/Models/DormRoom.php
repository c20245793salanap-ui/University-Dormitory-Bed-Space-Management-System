<?php

namespace App\Models;

use Database\Factories\DormRoomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DormRoom extends Model
{
    /** @use HasFactory<DormRoomFactory> */
    use HasFactory;

    protected $fillable = [
        'dorm_building_id',
        'room_number',
    ];

    public function dormBuilding(): BelongsTo
    {
        return $this->belongsTo(DormBuilding::class);
    }

    public function bedSlots(): HasMany
    {
        return $this->hasMany(BedSlot::class);
    }
}
