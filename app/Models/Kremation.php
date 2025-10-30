<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Kremation Model
 * 
 * @property int $vorgangs_id
 * @property string $eingangsdatum
 * @property float $gewicht
 * @property string|null $einaescherungsdatum
 * @property int $standort_id
 * @property int $herkunft_id
 * @property int|null $created_by
 * @property string|null $deleted_at
 */
class Kremation extends Model
{
    use SoftDeletes;

    protected $table = 'kremation';
    protected $primaryKey = 'vorgangs_id';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'eingangsdatum',
        'gewicht',
        'einaescherungsdatum',
        'standort_id',
        'herkunft_id',
        'created_by',
    ];

    protected $casts = [
        'eingangsdatum' => 'date',
        'gewicht' => 'float',
        'einaescherungsdatum' => 'datetime',
        'standort_id' => 'integer',
        'herkunft_id' => 'integer',
        'created_by' => 'integer',
    ];

    /**
     * Get standort
     */
    public function standort(): BelongsTo
    {
        return $this->belongsTo(Standort::class, 'standort_id', 'standort_id');
    }

    /**
     * Get herkunft
     */
    public function herkunft(): BelongsTo
    {
        return $this->belongsTo(Herkunft::class, 'herkunft_id', 'herkunft_id');
    }

    /**
     * Get creator user
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get tierarten with pivot data
     */
    public function tierarten(): BelongsToMany
    {
        return $this->belongsToMany(
            Tierart::class,
            'kremation_tiere',
            'kremation_id',
            'tierart_id'
        )->withPivot('anzahl');
    }

    /**
     * Scope: Filter by standort
     */
    public function scopeForStandort($query, int $standortId)
    {
        return $query->where('standort_id', $standortId);
    }

    /**
     * Scope: Search by vorgangs_id
     */
    public function scopeSearch($query, string $searchTerm)
    {
        if (is_numeric($searchTerm)) {
            return $query->where('vorgangs_id', (int) $searchTerm);
        }
        return $query->where('vorgangs_id', 0); // No results for non-numeric
    }

    /**
     * Scope: Filter by herkunft
     */
    public function scopeFilterByHerkunft($query, int $herkunftId)
    {
        return $query->where('herkunft_id', $herkunftId);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeFilterByStatus($query, string $status)
    {
        if ($status === 'open') {
            return $query->whereNull('einaescherungsdatum');
        } elseif ($status === 'completed') {
            return $query->whereNotNull('einaescherungsdatum');
        }
        return $query;
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeFilterByDateRange($query, string $from, string $to)
    {
        return $query->whereBetween('eingangsdatum', [$from, $to]);
    }

    /**
     * Get next vorgangs number for a standort
     */
    public static function nextVorgangsNummer(int $standortId): int
    {
        $last = static::forStandort($standortId)
            ->withTrashed()
            ->orderBy('vorgangs_id', 'desc')
            ->first();
        
        return $last ? $last->vorgangs_id + 1 : 1;
    }

    /**
     * Check if kremation is completed
     */
    public function isCompleted(): bool
    {
        return $this->einaescherungsdatum !== null;
    }

    /**
     * Get total animal count
     */
    public function totalAnimals(): int
    {
        return $this->tierarten()->sum('anzahl');
    }
}

