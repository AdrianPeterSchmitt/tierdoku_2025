<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
    public $incrementing = true;
    public $timestamps = true;
    protected $table = 'standort';
    protected $primaryKey = 'standort_id';

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
     * Get users for this standort (many-to-many)
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_standort', 'standort_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Get users for this standort (legacy - kept for backwards compatibility)
     * @deprecated Use users() instead
     */
    public function usersLegacy(): HasMany
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

    /**
     * Get prefix for vorgangs_id (first 3 letters, uppercase)
     *
     * @return string
     */
    public function getPrefix(): string
    {
        $name = trim($this->name);

        if (empty($name)) {
            return 'XXX'; // Fallback for empty names
        }

        // Extract first 3 letters, convert to uppercase
        $prefix = strtoupper(substr($name, 0, 3));

        // Pad with X if name is shorter than 3 characters
        if (strlen($prefix) < 3) {
            $prefix = str_pad($prefix, 3, 'X', STR_PAD_RIGHT);
        }

        return $prefix;
    }
}
