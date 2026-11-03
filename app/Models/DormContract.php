<?php

namespace App\Models;

use Database\Factories\DormContractFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DormContract extends Model
{
    /** @use HasFactory<DormContractFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'bed_slot_id',
        'monthly_rate',
        'contract_start',
        'contract_end',
        'security_deposit',
        'total_paid',
        'lease_status',
    ];

    protected function casts(): array
    {
        return [
            'monthly_rate' => 'decimal:2',
            'contract_start' => 'date',
            'contract_end' => 'date',
            'security_deposit' => 'decimal:2',
            'total_paid' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function bedSlot(): BelongsTo
    {
        return $this->belongsTo(BedSlot::class);
    }
}
