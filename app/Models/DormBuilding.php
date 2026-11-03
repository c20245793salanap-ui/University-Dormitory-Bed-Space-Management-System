<?php

namespace App\Models;

use Database\Factories\DormBuildingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DormBuilding extends Model
{
    /** @use HasFactory<DormBuildingFactory> */
    use HasFactory;

    protected $fillable = [
        'building_name',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(DormRoom::class);
    }
}
