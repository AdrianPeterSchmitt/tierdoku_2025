<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Kremation;
use App\Models\User;
use App\Models\Standort;
use App\Models\Herkunft;
use App\Models\Tierart;
use Illuminate\Database\Capsule\Manager as Capsule;
use InvalidArgumentException;

/**
 * Kremation Service
 * 
 * Handles business logic for kremations
 */
class KremationService
{
    public function __construct(
        private AuditService $auditService,
        private NotificationService $notificationService
    ) {
    }

    /**
     * Validate kremation data
     * 
     * @param array<string, mixed> $data
     * @return array<string>
     */
    public function validate(array $data): array
    {
        $errors = [];

        // Required fields
        if (empty($data['Standort'] ?? '')) {
            $errors[] = 'Standort ist erforderlich.';
        }
        
        if (empty($data['Herkunft'] ?? '')) {
            $errors[] = 'Herkunft ist erforderlich.';
        }
        
        if (empty($data['Gewicht'] ?? '')) {
            $errors[] = 'Gewicht ist erforderlich.';
        }

        // Gewicht validation
        if (isset($data['Gewicht']) && $data['Gewicht'] !== '') {
            $gewicht = str_replace(',', '.', trim((string) $data['Gewicht']));
            $gewichtFloat = (float) $gewicht;

            if ($gewichtFloat <= 0) {
                $errors[] = 'Gewicht muss größer als 0 sein.';
            }
        }

        // Animal count validation
        $totalAnimals = 
            max(0, (int) ($data['Anzahl_Vogel'] ?? 0)) +
            max(0, (int) ($data['Anzahl_Heimtier'] ?? 0)) +
            max(0, (int) ($data['Anzahl_Katze'] ?? 0)) +
            max(0, (int) ($data['Anzahl_Hund'] ?? 0));

        if ($totalAnimals === 0) {
            $errors[] = 'Mindestens eine Tieranzahl muss > 0 sein.';
        }

        return $errors;
    }

    /**
     * Create a new kremation
     * 
     * @param array<string, mixed> $data
     * @param User $user The user creating the kremation
     * @return Kremation
     */
    public function create(array $data, User $user): Kremation
    {
        $errors = $this->validate($data);

        if (count($errors) > 0) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }

        return Capsule::transaction(function () use ($data, $user) {
            // Get standort_id
            $standort = Standort::where('name', $data['Standort'])->first();

            if (!$standort) {
                throw new InvalidArgumentException('Unbekannter Standort: ' . $data['Standort']);
            }

            $standortId = $standort->standort_id;
            $nextVorgangsNr = Kremation::nextVorgangsNummer($standortId);

            // Get or create herkunft
            $herkunftName = trim($data['Herkunft']);
            $herkunft = Herkunft::where('name', $herkunftName)->first();

            if (!$herkunft) {
                $herkunft = Herkunft::create([
                    'name' => $herkunftName,
                    'verwendungen_count' => 0,
                ]);
                $herkunftId = $herkunft->herkunft_id;
            } else {
                $herkunftId = $herkunft->herkunft_id;
            }

            // Parse gewicht
            $gewicht = (float) str_replace(',', '.', trim((string) $data['Gewicht']));

            // Create kremation
            $kremation = Kremation::create([
                'vorgangs_id' => $nextVorgangsNr,
                'eingangsdatum' => $data['Eingangsdatum'] ?? date('Y-m-d'),
                'gewicht' => $gewicht,
                'standort_id' => $standortId,
                'herkunft_id' => $herkunftId,
                'created_by' => $user->id,
            ]);

            // Add tier counts
            $tierMap = [
                'Anzahl_Vogel' => 'Vogel',
                'Anzahl_Heimtier' => 'Heimtier',
                'Anzahl_Katze' => 'Katze',
                'Anzahl_Hund' => 'Hund',
            ];

            // Sync tierarten using Eloquent relationships
            $syncData = [];
            foreach ($tierMap as $key => $bezeichnung) {
                $anzahl = max(0, (int) ($data[$key] ?? 0));
                
                if ($anzahl > 0) {
                    $tierart = Tierart::where('bezeichnung', $bezeichnung)->first();
                    
                    if ($tierart) {
                        $syncData[$tierart->tierart_id] = ['anzahl' => $anzahl];
                    }
                }
            }
            
            // Sync all tierarten at once
            $kremation->tierarten()->sync($syncData);

            // Audit log
            $this->auditService->log(
                $user,
                'created',
                'kremation',
                $kremation->vorgangs_id,
                null,
                ['vorgangs_id' => $kremation->vorgangs_id, 'gewicht' => $kremation->gewicht]
            );

            // Notify managers/admins
            $this->notifyManagers($kremation, $user, 'Neu erfasst: #' . $kremation->vorgangs_id);

            return $kremation;
        });
    }

    /**
     * Update a field on a kremation
     * 
     * @param Kremation $kremation
     * @param string $field
     * @param mixed $value
     * @param User $user
     * @return bool
     */
    public function update(Kremation $kremation, string $field, mixed $value, User $user): bool
    {
        return Capsule::transaction(function () use ($kremation, $field, $value, $user) {
            $oldValue = $kremation->getAttributes();

            $fieldMap = [
                'Eingangsdatum' => 'eingangsdatum',
                'Gewicht' => 'gewicht',
                'Einaescherungsdatum' => 'einaescherungsdatum',
            ];

            if (isset($fieldMap[$field])) {
                $dbField = $fieldMap[$field];
                
                if ($dbField === 'einaescherungsdatum' && $value !== null && strlen((string) $value) === 10) {
                    $value = $value . ' 00:00:00';
                }

                if ($dbField === 'gewicht') {
                    $value = (float) str_replace(',', '.', trim((string) $value));
                }

                $kremation->$dbField = $value;
                $kremation->save();
            } elseif ($field === 'Herkunft') {
                $herkunftName = trim((string) $value);
                $herkunft = Herkunft::where('name', $herkunftName)->first();

                if (!$herkunft) {
                    $herkunft = Herkunft::create([
                        'name' => $herkunftName,
                        'verwendungen_count' => 0,
                    ]);
                    $herkunftId = $herkunft->herkunft_id;
                } else {
                    $herkunftId = $herkunft->herkunft_id;
                }

                $kremation->herkunft_id = $herkunftId;
                $kremation->save();
            } elseif ($field === 'Standort') {
                $standort = Standort::where('name', $value)->first();

                if (!$standort) {
                    throw new InvalidArgumentException('Unbekannter Standort: ' . $value);
                }

                $kremation->standort_id = $standort->standort_id;
                $kremation->save();
            } else {
                throw new InvalidArgumentException("Feld nicht unterstützt: {$field}");
            }

            // Audit log
            $this->auditService->log(
                $user,
                'updated',
                'kremation',
                $kremation->vorgangs_id,
                [$field => $oldValue[$fieldMap[$field] ?? $field] ?? null],
                [$field => $value]
            );

            return true;
        });
    }

    /**
     * Complete a kremation
     * 
     * @param Kremation $kremation
     * @param User $user
     * @return bool
     */
    public function complete(Kremation $kremation, User $user): bool
    {
        if ($kremation->isCompleted()) {
            return false;
        }

        $kremation->einaescherungsdatum = now();
        $kremation->save();

        // Audit log
        $this->auditService->log(
            $user,
            'completed',
            'kremation',
            $kremation->vorgangs_id,
            null,
            ['einaescherungsdatum' => $kremation->einaescherungsdatum->format('Y-m-d H:i:s')]
        );

        // Notify
        $this->notifyManagers($kremation, $user, 'Abgeschlossen: #' . $kremation->vorgangs_id);

        return true;
    }

    /**
     * Soft delete a kremation
     * 
     * @param Kremation $kremation
     * @param User $user
     * @return bool
     */
    public function softDelete(Kremation $kremation, User $user): bool
    {
        if ($kremation->trashed()) {
            return false;
        }

        $kremation->delete();

        // Audit log
        $this->auditService->log(
            $user,
            'deleted',
            'kremation',
            $kremation->vorgangs_id,
            ['status' => 'active'],
            ['status' => 'deleted']
        );

        // Notify
        $this->notifyManagers($kremation, $user, 'Gelöscht: #' . $kremation->vorgangs_id);

        return true;
    }

    /**
     * Restore a soft-deleted kremation
     * 
     * @param int $id
     * @param User $user
     * @return bool
     */
    public function restore(int $id, User $user): bool
    {
        $kremation = Kremation::onlyTrashed()->find($id);

        if (!$kremation) {
            return false;
        }

        $kremation->restore();

        // Audit log
        $this->auditService->log(
            $user,
            'restored',
            'kremation',
            $kremation->vorgangs_id,
            ['status' => 'deleted'],
            ['status' => 'active']
        );

        return true;
    }

    /**
     * Bulk complete kremations
     * 
     * @param array<int> $ids
     * @param User $user
     * @return int Number of completed kremations
     */
    public function bulkComplete(array $ids, User $user): int
    {
        return Capsule::transaction(function () use ($ids, $user) {
            $count = 0;

            foreach ($ids as $id) {
                $kremation = Kremation::find($id);

                if ($kremation && !$kremation->isCompleted()) {
                    $this->complete($kremation, $user);
                    $count++;
                }
            }

            // Audit log
            $this->auditService->log(
                $user,
                'bulk_completed',
                'kremation',
                0,
                null,
                ['count' => $count, 'ids' => $ids]
            );

            return $count;
        });
    }

    /**
     * Bulk delete kremations
     * 
     * @param array<int> $ids
     * @param User $user
     * @return int Number of deleted kremations
     */
    public function bulkDelete(array $ids, User $user): int
    {
        return Capsule::transaction(function () use ($ids, $user) {
            $count = 0;

            foreach ($ids as $id) {
                $kremation = Kremation::find($id);

                if ($kremation && !$kremation->trashed()) {
                    $this->softDelete($kremation, $user);
                    $count++;
                }
            }

            // Audit log
            $this->auditService->log(
                $user,
                'bulk_deleted',
                'kremation',
                0,
                null,
                ['count' => $count, 'ids' => $ids]
            );

            return $count;
        });
    }

    /**
     * Export kremations to CSV
     * 
     * @param array<string, mixed> $filters
     * @param string $format
     * @return string CSV content
     */
    public function export(array $filters, string $format = 'csv'): string
    {
        $query = Kremation::with(['standort', 'herkunft', 'creator', 'tierarten']);

        // Apply filters
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['herkunft'])) {
            $query->filterByHerkunft((int) $filters['herkunft']);
        }

        if (!empty($filters['status'])) {
            $query->filterByStatus($filters['status']);
        }

        if (!empty($filters['from']) && !empty($filters['to'])) {
            $query->filterByDateRange($filters['from'], $filters['to']);
        }

        $kremations = $query->orderBy('eingangsdatum', 'desc')->get();

        // Build CSV
        $csv = "VorgangsNr,Eingangsdatum,Herkunft,Standort,Gewicht,Einäscherungsdatum,Status,Erstellt von\n";

        foreach ($kremations as $k) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%.2f,%s,%s,%s\n",
                $k->vorgangs_id,
                $k->eingangsdatum->format('Y-m-d'),
                $k->herkunft->name ?? '',
                $k->standort->name ?? '',
                $k->gewicht,
                $k->einaescherungsdatum?->format('Y-m-d H:i:s') ?? '',
                $k->isCompleted() ? 'Abgeschlossen' : 'Offen',
                $k->creator->username ?? 'Unbekannt'
            );
        }

        return $csv;
    }

    /**
     * Notify managers and admins about kremation changes
     */
    private function notifyManagers(Kremation $kremation, User $actor, string $message): void
    {
        $managers = User::whereIn('role', ['admin', 'manager'])
            ->where('id', '!=', $actor->id)
            ->get();

        foreach ($managers as $manager) {
            $this->notificationService->create(
                $manager,
                'info',
                'Kremation Update',
                $message . ' durch ' . $actor->username
            );
        }
    }
}

