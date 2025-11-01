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
 * @property string $vorgangs_id
 * @property string $eingangsdatum
 * @property float $gewicht
 * @property string|null $einaescherungsdatum
 * @property int $standort_id
 * @property int $herkunft_id
 * @property int|null $created_by
 * @property string|null $deleted_at
 * @property \App\Models\Standort $standort
 * @property \App\Models\Herkunft $herkunft
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tierart> $tierarten
 */
class Kremation extends Model
{
    public $incrementing = false; // String primary key
    public $timestamps = true;

    protected $table = 'kremation';
    protected $primaryKey = 'vorgangs_id';

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
     * Get next vorgangs number for a standort
     */
    /**
     * Get next vorgangs number for a standort
     * Format: {PREFIX}{NUMMER} (e.g. LAU001, SCH002)
     *
     * @param int $standortId
     * @return string
     */
    public static function nextVorgangsNummer(int $standortId): string
    {
        // Get standort
        $standort = Standort::find($standortId);
        if (!$standort) {
            throw new \InvalidArgumentException('Standort nicht gefunden');
        }

        // Get prefix from standort name
        $prefix = $standort->getPrefix();

        // Find all existing vorgangs_ids for this standort (that start with this prefix)
        /** @var \Illuminate\Database\Eloquent\Builder<Kremation> $query */
        $query = static::query();
        $existingIds = $query->where('standort_id', $standortId)
            ->withTrashed()
            ->where('vorgangs_id', 'LIKE', $prefix . '%')
            ->pluck('vorgangs_id')
            ->toArray();

        // Extract numbers and find highest
        $maxNumber = 0;
        foreach ($existingIds as $id) {
            // Extract numeric part (after prefix)
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $id, $matches)) {
                $number = (int) $matches[1];
                if ($number > $maxNumber) {
                    $maxNumber = $number;
                }
            }
        }

        // Next number
        $nextNumber = $maxNumber + 1;

        // Check max (999 per standort)
        if ($nextNumber > 999) {
            throw new \RuntimeException("Maximale Anzahl von 999 Kremationen pro Standort erreicht");
        }

        // Format: PREFIX + 3-digit number
        return sprintf('%s%03d', $prefix, $nextNumber);
    }

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
            'tierart_id',
            'vorgangs_id', // Local key (primary key of this model)
            'tierart_id'   // Related key (primary key of related model)
        )->withPivot('anzahl');
    }

    /**
     * Scope: Filter by standort
     *
     * @param \Illuminate\Database\Eloquent\Builder<Kremation> $query
     * @param int $standortId
     * @return \Illuminate\Database\Eloquent\Builder<Kremation>
     */
    public function scopeForStandort(\Illuminate\Database\Eloquent\Builder $query, int $standortId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('standort_id', $standortId);
    }

    /**
     * Scope: Filter by allowed standorte for user
     *
     * @param \Illuminate\Database\Eloquent\Builder<Kremation> $query
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Builder<Kremation>
     */
    public function scopeForAllowedStandorte(\Illuminate\Database\Eloquent\Builder $query, User $user): \Illuminate\Database\Eloquent\Builder
    {
        if ($user->isAdmin()) {
            return $query; // Kein Filter für Admins
        }

        $allowedIds = $user->getAllowedStandortIds();
        if (empty($allowedIds)) {
            /** @var \Illuminate\Database\Eloquent\Builder<Kremation> */
            return $query->whereRaw('1 = 0'); // Keine Ergebnisse wenn keine Standorte
        }

        /** @var \Illuminate\Database\Eloquent\Builder<Kremation> */
        return $query->whereIn('standort_id', $allowedIds);
    }

    /**
     * Scope: Search by vorgangs_id
     *
     * @param \Illuminate\Database\Eloquent\Builder<Kremation> $query
     * @param string $searchTerm
     * @return \Illuminate\Database\Eloquent\Builder<Kremation>
     */
    public function scopeSearch(\Illuminate\Database\Eloquent\Builder $query, string $searchTerm): \Illuminate\Database\Eloquent\Builder
    {
        // Support both exact match and prefix search
        $searchTerm = trim($searchTerm);

        if (empty($searchTerm)) {
            /** @var \Illuminate\Database\Eloquent\Builder<Kremation> */
            return $query->whereRaw('1 = 0'); // No results for empty search
        }

        // Try exact match first
        $query->where(function ($q) use ($searchTerm) {
            $q->where('vorgangs_id', $searchTerm)
              ->orWhere('vorgangs_id', 'LIKE', $searchTerm . '%');
        });

        /** @var \Illuminate\Database\Eloquent\Builder<Kremation> */
        return $query;
    }

    /**
     * Scope: Filter by herkunft
     *
     * @param \Illuminate\Database\Eloquent\Builder<Kremation> $query
     * @param int $herkunftId
     * @return \Illuminate\Database\Eloquent\Builder<Kremation>
     */
    public function scopeFilterByHerkunft(\Illuminate\Database\Eloquent\Builder $query, int $herkunftId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('herkunft_id', $herkunftId);
    }

    /**
     * Scope: Filter by status
     *
     * @param \Illuminate\Database\Eloquent\Builder<Kremation> $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder<Kremation>
     */
    public function scopeFilterByStatus(\Illuminate\Database\Eloquent\Builder $query, string $status): \Illuminate\Database\Eloquent\Builder
    {
        if ($status === 'open') {
            /** @var \Illuminate\Database\Eloquent\Builder<Kremation> */
            return $query->whereNull('einaescherungsdatum');
        } elseif ($status === 'completed') {
            /** @var \Illuminate\Database\Eloquent\Builder<Kremation> */
            return $query->whereNotNull('einaescherungsdatum');
        }
        return $query;
    }

    /**
     * Scope: Filter by date range
     *
     * @param \Illuminate\Database\Eloquent\Builder<Kremation> $query
     * @param string $from
     * @param string $to
     * @return \Illuminate\Database\Eloquent\Builder<Kremation>
     */
    public function scopeFilterByDateRange(\Illuminate\Database\Eloquent\Builder $query, string $from, string $to): \Illuminate\Database\Eloquent\Builder
    {
        /** @var \Illuminate\Database\Eloquent\Builder<Kremation> */
        return $query->whereBetween('eingangsdatum', [$from, $to]);
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
    use SoftDeletes;
}
