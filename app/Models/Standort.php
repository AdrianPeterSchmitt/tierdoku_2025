<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Standort Model
 * 
 * @property int $standort_id
 * @property string $name
 * @property bool $aktiv
 */
class Standort extends Model
{
    protected $table = 'standort';
    protected $primaryKey = 'standort_id';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'name',
        'aktiv',
    ];

    protected $casts = [
        'aktiv' => 'boolean',
    ];

    /**
     * Get kremations for this standort
     */
    public function kremations(): HasMany
    {
        return $this->hasMany(Kremation::class, 'standort_id', 'standort_id');
    }

    /**
     * Get users for this standort
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'standort_id', 'standort_id');
    }

    /**
     * Scope: nur aktive Standorte
     * 
     * @param \Illuminate\Database\Eloquent\Builder<Standort> $query
     * @return \Illuminate\Database\Eloquent\Builder<Standort>
     */
    public function scopeAktiv(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('aktiv', true);
    }
}

