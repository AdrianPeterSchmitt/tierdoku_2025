<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\KremationService;
use App\Services\QRCodeService;
use App\Services\PDFLabelService;
use App\Models\Kremation;
use App\Models\Standort;
use App\Models\Tierart;
use App\Models\User;
use InvalidArgumentException;

/**
 * Kremation Controller
 */
class KremationController
{
    public function __construct(
        private KremationService $kremationService,
        private QRCodeService $qrCodeService,
        private PDFLabelService $pdfLabelService
    ) {
    }

    /**
     * Display kremation index page
     *
     * @return string
     */
    public function index(): string
    {
        $user = $this->getCurrentUser();

        // Pagination
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 200;
        $offset = ($page - 1) * $limit;

        // Filters
        $search = $_GET['search'] ?? '';
        $herkunftFilter = !empty($_GET['herkunft']) ? (int) $_GET['herkunft'] : null;
        $statusFilter = $_GET['status'] ?? 'all';
        $dateFrom = $_GET['from'] ?? '';
        $dateTo = $_GET['to'] ?? '';

        // Build query
        $query = Kremation::with(['standort', 'herkunft', 'creator', 'tierarten']);

        // Apply standort filter using scope
        $query->forAllowedStandorte($user);

        // Admin can also filter by specific standort
        if ($user->isAdmin() && !empty($_GET['standort'])) {
            $query->forStandort((int) $_GET['standort']);
        }

        // Apply search
        if (!empty($search)) {
            $query->search($search);
        }

        // Apply filters
        if ($herkunftFilter) {
            $query->filterByHerkunft($herkunftFilter);
        }

        if ($statusFilter !== 'all') {
            $query->filterByStatus($statusFilter);
        }

        if ($dateFrom && $dateTo) {
            $query->filterByDateRange($dateFrom, $dateTo);
        }

        // Get results
        $kremations = $query->orderBy('eingangsdatum', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();

        $total = $query->count();
        $hasMore = ($offset + count($kremations)) < $total;

        // Get form data
        // For nextVorgangsNummer, use default standort or first allowed standort
        $defaultStandortId = $user->getDefaultStandortId();
        $nextNr = $defaultStandortId ? Kremation::nextVorgangsNummer($defaultStandortId) : '';

        // Get standorte for form: filtered for non-admins
        if ($user->isAdmin()) {
            $standorte = Standort::aktiv()->get();
        } else {
            $standorte = $user->standorte()->where('aktiv', true)->get();
        }

        // Herkünfte werden jetzt dynamisch via API geladen, nicht mehr initial
        $tierarten = Tierart::all();

        return view('kremation/index', [
            'kremations' => $kremations,
            'nextNr' => $nextNr,
            'currentPage' => $page,
            'hasMore' => $hasMore,
            'standorte' => $standorte,
            'tierarten' => $tierarten,
            'user' => $user,
            'defaultStandortId' => $defaultStandortId,
            'search' => $search,
            'herkunftFilter' => $herkunftFilter,
            'statusFilter' => $statusFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    /**
     * Get updates since a specific timestamp
     *
     * @return void
     */
    public function getUpdates(): void
    {
        $user = $this->getCurrentUser();

        header('Content-Type: application/json');

        try {
            // Get since timestamp from query parameter
            $since = $_GET['since'] ?? null;
            $sinceTimestamp = null;

            if ($since) {
                try {
                    $sinceTimestamp = new \DateTime($since);
                } catch (\Exception $e) {
                    throw new InvalidArgumentException('Invalid since timestamp format');
                }
            }

            // Build query - same as index but filter by updated_at
            $query = Kremation::with(['standort', 'herkunft', 'creator', 'tierarten']);

            // Apply standort filter using scope
            $query->forAllowedStandorte($user);

            // Filter by updated_at or created_at if since is provided
            if ($sinceTimestamp) {
                $query->where(function ($q) use ($sinceTimestamp) {
                    $q->where('updated_at', '>', $sinceTimestamp->format('Y-m-d H:i:s'))
                      ->orWhere('created_at', '>', $sinceTimestamp->format('Y-m-d H:i:s'));
                });
            }

            // Get updates (limit to last 50 to prevent overload)
            $updates = $query->orderBy('updated_at', 'desc')
                ->take(50)
                ->get();

            // Format response
            $formattedUpdates = $updates->map(function ($k) {
                // Create tierarten map
                $tierartenMap = [];
                foreach ($k->tierarten as $ta) {
                    $tierartenMap[$ta->bezeichnung] = $ta->pivot->anzahl ?? 0;
                }

                return [
                    'vorgangs_id' => $k->vorgangs_id,
                    'eingangsdatum' => $k->eingangsdatum->format('Y-m-d'),
                    'herkunft' => $k->herkunft->name ?? '',
                    'standort' => $k->standort->name ?? '',
                    'vogel' => $tierartenMap['Vogel'] ?? 0,
                    'heimtier' => $tierartenMap['Heimtier'] ?? 0,
                    'katze' => $tierartenMap['Katze'] ?? 0,
                    'hund' => $tierartenMap['Hund'] ?? 0,
                    'gewicht' => $k->gewicht,
                    'status' => $k->einaescherungsdatum ? 'Abgeschlossen' : 'Offen',
                    'kremation' => $k->einaescherungsdatum ? $k->einaescherungsdatum->format('d.m.Y H:i') . ' Uhr' : '-',
                    'einaescherungsdatum' => $k->einaescherungsdatum ? $k->einaescherungsdatum->format('Y-m-d H:i:s') : null,
                    'updated_at' => $k->updated_at->format('Y-m-d H:i:s'),
                    'created_at' => $k->created_at->format('Y-m-d H:i:s'),
                ];
            });

            echo json_encode([
                'success' => true,
                'updates' => $formattedUpdates->toArray(),
                'lastUpdate' => now()->format('Y-m-d H:i:s'),
                'count' => $formattedUpdates->count(),
            ]);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            error_log('Kremation getUpdates error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Fehler beim Abrufen der Updates.',
            ]);
        }
    }

    /**
     * Store a new kremation
     *
     * @return void
     */
    public function store(): void
    {
        $user = $this->getCurrentUser();

        header('Content-Type: application/json');

        try {
            $kremation = $this->kremationService->create($_POST, $user);

            echo json_encode([
                'success' => true,
                'message' => "Kremation #{$kremation->vorgangs_id} erfolgreich erstellt.",
                'vorgangs_id' => $kremation->vorgangs_id,
            ]);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            // Log the actual error for debugging
            error_log('Kremation store error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());

            // In development, show actual error message
            $errorMessage = ($_ENV['APP_DEBUG'] ?? false)
                ? $e->getMessage()
                : 'Ein Fehler ist aufgetreten.';

            echo json_encode([
                'success' => false,
                'error' => $errorMessage,
            ]);
        }
    }

    /**
     * Update full kremation (all fields)
     *
     * @param array<string, mixed> $vars
     * @return void
     */
    public function updateFull(array $vars): void
    {
        $user = $this->getCurrentUser();

        header('Content-Type: application/json');

        try {
            $vorgang = (string) ($vars['id'] ?? '');

            if (empty($vorgang)) {
                throw new InvalidArgumentException('Invalid vorgangs_id');
            }

            $kremation = Kremation::find($vorgang);

            if (!$kremation) {
                throw new InvalidArgumentException('Kremation not found');
            }

            // Use $_POST for FormData (POST method works better with FormData in PHP)
            $kremation = $this->kremationService->updateFull($kremation, $_POST, $user);

            echo json_encode([
                'success' => true,
                'message' => "Kremation #{$kremation->vorgangs_id} erfolgreich aktualisiert.",
                'vorgangs_id' => $kremation->vorgangs_id,
            ]);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            error_log('Kremation updateFull error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());

            $errorMessage = ($_ENV['APP_DEBUG'] ?? false)
                ? $e->getMessage()
                : 'Ein Fehler ist aufgetreten.';

            echo json_encode([
                'success' => false,
                'error' => $errorMessage,
            ]);
        }
    }

    /**
     * Update a kremation field
     *
     * @return void
     */
    public function update(): void
    {
        $user = $this->getCurrentUser();

        header('Content-Type: application/json');

        try {
            $input = (string) file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!is_array($data)) {
                throw new InvalidArgumentException('Invalid JSON');
            }

            $vorgang = (string) ($data['vorgang'] ?? '');
            $field = $data['field'] ?? '';
            $value = $data['value'] ?? null;

            if (empty($vorgang) || empty($field)) {
                throw new InvalidArgumentException('Invalid parameters');
            }

            $kremation = Kremation::find($vorgang);

            if (!$kremation) {
                throw new InvalidArgumentException('Kremation not found');
            }

            $this->kremationService->update($kremation, $field, $value, $user);

            echo json_encode([
                'success' => true,
                'status' => $field === 'Einaescherungsdatum' && $value ? 'completed' : 'open',
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Complete a kremation
     *
     * @return void
     */
    public function complete(): void
    {
        $user = $this->getCurrentUser();

        header('Content-Type: application/json');

        try {
            $input = (string) file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!is_array($data)) {
                throw new InvalidArgumentException('Invalid JSON');
            }

            $vorgang = (string) ($data['vorgang'] ?? '');

            if (empty($vorgang)) {
                throw new InvalidArgumentException('Invalid vorgangs_id');
            }

            $kremation = Kremation::find($vorgang);

            if (!$kremation) {
                throw new InvalidArgumentException('Kremation not found');
            }

            $success = $this->kremationService->complete($kremation, $user);

            echo json_encode([
                'success' => $success,
                'status' => 'completed',
                'date' => now()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete a kremation
     *
     * @return void
     */
    public function delete(): void
    {
        $user = $this->getCurrentUser();

        header('Content-Type: application/json');

        try {
            $input = (string) file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!is_array($data)) {
                throw new InvalidArgumentException('Invalid JSON');
            }

            $vorgang = (string) ($data['vorgang'] ?? '');

            if (empty($vorgang)) {
                throw new InvalidArgumentException('Invalid vorgangs_id');
            }

            $kremation = Kremation::find($vorgang);

            if (!$kremation) {
                throw new InvalidArgumentException('Kremation not found');
            }

            $success = $this->kremationService->softDelete($kremation, $user);

            echo json_encode([
                'success' => $success,
                'message' => "Vorgang #{$vorgang} wurde gelöscht.",
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Restore a soft-deleted kremation
     *
     * @param array<string, int> $vars
     * @return void
     */
    public function restore(array $vars): void
    {
        $user = $this->getCurrentUser();

        if (!$user->isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Insufficient permissions']);
            return;
        }

        header('Content-Type: application/json');

        try {
            $vorgang = (string) ($vars['id'] ?? '');

            if (empty($vorgang)) {
                throw new InvalidArgumentException('Invalid vorgangs_id');
            }

            $success = $this->kremationService->restore($vorgang, $user);

            echo json_encode([
                'success' => $success,
                'message' => "Vorgang #{$vorgang} wurde wiederhergestellt.",
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Export kremations as CSV
     *
     * @return void
     */
    public function export(): void
    {
        $user = $this->getCurrentUser();

        $filters = [
            'search' => $_GET['search'] ?? '',
            'herkunft' => $_GET['herkunft'] ?? '',
            'status' => $_GET['status'] ?? 'all',
            'from' => $_GET['from'] ?? '',
            'to' => $_GET['to'] ?? '',
        ];

        $csv = $this->kremationService->export($filters);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="kremationen_' . date('Y-m-d') . '.csv"');
        echo $csv;
    }

    /**
     * Generate QR code for a kremation
     *
     * @param array<string, mixed> $vars
     * @return string
     */
    public function showQRCode(array $vars): string
    {
        try {
            $user = $this->getCurrentUser();
            $vorgangsId = (string) ($vars['id'] ?? '');

            if (empty($vorgangsId)) {
                http_response_code(400);
                return view('errors/404', ['message' => 'Ungültige Vorgangs-ID']);
            }

            $kremation = Kremation::with('standort')->find($vorgangsId);

            if (!$kremation) {
                http_response_code(404);
                return view('errors/404', ['message' => 'Kremation nicht gefunden']);
            }

            // Check permissions
            if (!$user->isAdmin() && !$user->hasStandort($kremation->standort_id)) {
                http_response_code(403);
                return view('errors/403', ['message' => 'Keine Berechtigung']);
            }

            // Generate QR code
            $qrBase64 = $this->qrCodeService->generateForKremation($kremation, 400);
            $qrMimeType = $this->qrCodeService->getLastMimeType();

            // Return view
            return view('kremation/qr-code', [
                'kremation' => $kremation,
                'qrBase64' => $qrBase64,
                'qrMimeType' => $qrMimeType,
                'user' => $user,
            ]);
        } catch (\Throwable $e) {
            // Log error and show debug info if enabled
            error_log('QR Code Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

            http_response_code(500);

            if ($_ENV['APP_DEBUG'] ?? false) {
                return '<pre>QR Code Error: ' . htmlspecialchars($e->getMessage()) . "\n" .
                       'File: ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . "\n" .
                       htmlspecialchars($e->getTraceAsString()) . '</pre>';
            }

            return view('errors/500');
        }
    }

    /**
     * Scan QR code
     *
     * @return string
     */
    public function scanQRCode(): string
    {
        $user = $this->getCurrentUser();

        return view('kremation/scan', [
            'user' => $user,
        ]);
    }

    /**
     * Batch scan QR codes
     *
     * @return string
     */
    public function batchScanQRCode(): string
    {
        $user = $this->getCurrentUser();

        return view('kremation/batch-scan', [
            'user' => $user,
        ]);
    }

    /**
     * Process scanned QR code
     *
     * @return void
     */
    public function processScannedQR(): void
    {
        $user = $this->getCurrentUser();
        header('Content-Type: application/json');

        $qrData = $_POST['qr_data'] ?? '';

        if (empty($qrData)) {
            http_response_code(400);
            echo json_encode(['error' => 'Keine QR-Daten']);
            return;
        }

        // Parse QR data
        $data = $this->qrCodeService->parseQRData($qrData);

        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Ungültige QR-Daten']);
            return;
        }

        // Find kremation
        $kremation = Kremation::find($data['vorgangs_id']);

        if (!$kremation) {
            http_response_code(404);
            echo json_encode(['error' => 'Kremation nicht gefunden']);
            return;
        }

        // Return kremation details
        echo json_encode([
            'success' => true,
            'kremation' => [
                'vorgangs_id' => $kremation->vorgangs_id,
                'standort' => $kremation->standort->name ?? 'Unbekannt',
                'eingangsdatum' => $kremation->eingangsdatum?->format('d.m.Y'),
                'gewicht' => $kremation->gewicht,
                'is_completed' => $kremation->isCompleted(),
                'url' => '/kremation?id=' . $kremation->vorgangs_id,
            ],
        ]);
    }

    /**
     * Generate PDF label for a kremation
     *
     * @param array<string, mixed> $vars
     * @return void
     */
    public function downloadLabel(array $vars): void
    {
        $user = $this->getCurrentUser();
        $vorgangsId = (int) ($vars['id'] ?? 0);

        $kremation = Kremation::find($vorgangsId);

        if (!$kremation) {
            http_response_code(404);
            echo json_encode(['error' => 'Kremation nicht gefunden']);
            return;
        }

        // Check permissions
        if (!$user->isAdmin() && $kremation->standort_id !== $user->standort_id) {
            http_response_code(403);
            echo json_encode(['error' => 'Keine Berechtigung']);
            return;
        }

        // Generate QR code
        $qrBase64 = $this->qrCodeService->generateForKremation($kremation, 200);
        $qrMimeType = $this->qrCodeService->getLastMimeType();

        // Generate PDF with QR code
        $pdfContent = $this->pdfLabelService->generateLabelWithQR($kremation, $qrBase64, $qrMimeType);

        // Send PDF to browser
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="kremation_' . $kremation->vorgangs_id . '.pdf"');
        echo $pdfContent;
    }

    /**
     * Get current user from session
     */
    private function getCurrentUser(): User
    {
        $user = $_REQUEST['_user'] ?? null;

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }

        return $user;
    }
}
