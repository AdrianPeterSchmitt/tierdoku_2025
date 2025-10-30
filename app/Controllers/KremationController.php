<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\KremationService;
use App\Services\NotificationService;
use App\Services\QRCodeService;
use App\Services\PDFLabelService;
use App\Models\Kremation;
use App\Models\Standort;
use App\Models\Herkunft;
use App\Models\Tierart;
use InvalidArgumentException;

/**
 * Kremation Controller
 */
class KremationController
{
    public function __construct(
        private KremationService $kremationService,
        private NotificationService $notificationService,
        private QRCodeService $qrCodeService,
        private PDFLabelService $pdfLabelService
    ) {
    }

    /**
     * Get current user from session
     */
    private function getCurrentUser()
    {
        $user = $_REQUEST['_user'] ?? null;

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }

        return $user;
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

        // Apply standort filter (non-admins see only their standort)
        if (!$user->isAdmin()) {
            $query->forStandort($user->standort_id);
        } elseif (!empty($_GET['standort'])) {
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
        $nextNr = Kremation::nextVorgangsNummer($user->standort_id);
        $herkuenfte = Herkunft::all();
        $standorte = Standort::aktiv()->get();
        $tierarten = Tierart::all();

        return view('kremation/index', [
            'kremations' => $kremations,
            'nextNr' => $nextNr,
            'currentPage' => $page,
            'hasMore' => $hasMore,
            'herkuenfte' => $herkuenfte,
            'standorte' => $standorte,
            'tierarten' => $tierarten,
            'user' => $user,
            'search' => $search,
            'herkunftFilter' => $herkunftFilter,
            'statusFilter' => $statusFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
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
            echo json_encode([
                'success' => false,
                'error' => 'Ein Fehler ist aufgetreten.',
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
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!is_array($data)) {
                throw new InvalidArgumentException('Invalid JSON');
            }

            $vorgang = (int) ($data['vorgang'] ?? 0);
            $field = $data['field'] ?? '';
            $value = $data['value'] ?? null;

            if ($vorgang <= 0 || empty($field)) {
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
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!is_array($data)) {
                throw new InvalidArgumentException('Invalid JSON');
            }

            $vorgang = (int) ($data['vorgang'] ?? 0);

            if ($vorgang <= 0) {
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
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);

            if (!is_array($data)) {
                throw new InvalidArgumentException('Invalid JSON');
            }

            $vorgang = (int) ($data['vorgang'] ?? 0);

            if ($vorgang <= 0) {
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
            $vorgang = (int) ($vars['id'] ?? 0);

            if ($vorgang <= 0) {
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
     * @return void
     */
    public function showQRCode(array $vars): void
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
        $qrBase64 = $this->qrCodeService->generateForKremation($kremation, 400);

        // Return as image
        header('Content-Type: text/html; charset=utf-8');
        echo view('kremation/qr-code', [
            'kremation' => $kremation,
            'qrBase64' => $qrBase64,
            'user' => $user,
        ]);
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

        // Generate PDF with QR code
        $pdfContent = $this->pdfLabelService->generateLabelWithQR($kremation, $qrBase64);

        // Send PDF to browser
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="kremation_' . $kremation->vorgangs_id . '.pdf"');
        echo $pdfContent;
    }
}

