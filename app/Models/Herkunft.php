<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Herkunft Model
 * 
 * @property int $herkunft_id
 * @property string $name
 * @property int $verwendungen_count
 */
class Herkunft extends Model
{
    protected $table = 'herkunft';
    protected $primaryKey = 'herkunft_id';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'name',
        'standort_id',
        'verwendungen_count',
    ];

    protected $casts = [
        'verwendungen_count' => 'integer',
    ];

    /**
     * Get kremations for this herkunft
     */
    public function kremations(): HasMany
    {
        return $this->hasMany(Kremation::class, 'herkunft_id', 'herkunft_id');
    }

    public function standort(): BelongsTo
    {
        return $this->belongsTo(Standort::class, 'standort_id', 'standort_id');
    }

    /**
     * Update usage count
     */
    public function updateUsageCount(): void
    {
        $this->verwendungen_count = $this->kremations()->count();
        $this->save();
    }
}

