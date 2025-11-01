<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Tierart Model
 *
 * @property int $tierart_id
 * @property string $bezeichnung
 */
class Tierart extends Model
{
    public $incrementing = true;
    public $timestamps = false;
    protected $table = 'tierart';
    protected $primaryKey = 'tierart_id';

    protected $fillable = [
        'bezeichnung',
    ];

    /**
     * Get kremations for this tierart
     */
    public function kremations(): BelongsToMany
    {
        return $this->belongsToMany(
            Kremation::class,
            'kremation_tiere',
            'tierart_id',
            'kremation_id'
        )->withPivot('anzahl');
    }
}
