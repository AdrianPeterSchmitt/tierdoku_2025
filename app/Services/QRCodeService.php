<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Kremation;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;

/**
 * QR Code Service
 *
 * Handles QR code generation for Kremations
 */
class QRCodeService
{
    private string $lastMimeType = 'image/png';
    /**
     * Generate QR code for a Kremation
     *
     * @param Kremation $kremation
     * @param int $size
     * @return string Base64 encoded image (PNG or SVG)
     */
    public function generateForKremation(Kremation $kremation, ?int $size = null): string
    {
        $data = $this->buildQRData($kremation);

        // Get configuration from .env or use defaults
        $size = $size ?? (int) ($_ENV['QR_CODE_SIZE'] ?? 300);
        $margin = (int) ($_ENV['QR_CODE_MARGIN'] ?? 10);
        $encoding = $_ENV['QR_CODE_ENCODING'] ?? 'UTF-8';
        $errorCorrection = $_ENV['QR_CODE_ERROR_CORRECTION'] ?? 'High';

        // Map error correction string to enum
        $errorCorrectionLevel = match(strtolower($errorCorrection)) {
            'low' => ErrorCorrectionLevel::Low,
            'medium' => ErrorCorrectionLevel::Medium,
            'quartile' => ErrorCorrectionLevel::Quartile,
            'high' => ErrorCorrectionLevel::High,
            default => ErrorCorrectionLevel::High,
        };

        // Check if GD extension is available for PNG generation
        if (extension_loaded('gd')) {
            $writer = new PngWriter();
            $mimeType = 'image/png';
        } else {
            // Fallback to SVG if GD is not available
            $writer = new SvgWriter();
            $mimeType = 'image/svg+xml';
        }

        $builder = new Builder(
            writer: $writer,
            data: $data,
            encoding: new Encoding($encoding),
            errorCorrectionLevel: $errorCorrectionLevel,
            size: $size,
            margin: $margin
        );

        $result = $builder->build();
        $imageData = base64_encode($result->getString());

        // Store MIME type for later use
        $this->lastMimeType = $mimeType;

        return $imageData;
    }

    /**
     * Get the MIME type of the last generated QR code
     *
     * @return string
     */
    public function getLastMimeType(): string
    {
        return $this->lastMimeType ?? 'image/png';
    }

    /**
     * Save QR code as file
     *
     * @param Kremation $kremation
     * @param string $filepath
     * @param int $size
     * @return bool
     */
    public function saveForKremation(Kremation $kremation, string $filepath, ?int $size = null): bool
    {
        $data = $this->buildQRData($kremation);

        // Get configuration from .env or use defaults
        $size = $size ?? (int) ($_ENV['QR_CODE_SIZE'] ?? 300);
        $margin = (int) ($_ENV['QR_CODE_MARGIN'] ?? 10);
        $encoding = $_ENV['QR_CODE_ENCODING'] ?? 'UTF-8';
        $errorCorrection = $_ENV['QR_CODE_ERROR_CORRECTION'] ?? 'High';

        // Map error correction string to enum
        $errorCorrectionLevel = match(strtolower($errorCorrection)) {
            'low' => ErrorCorrectionLevel::Low,
            'medium' => ErrorCorrectionLevel::Medium,
            'quartile' => ErrorCorrectionLevel::Quartile,
            'high' => ErrorCorrectionLevel::High,
            default => ErrorCorrectionLevel::High,
        };

        // Check if GD extension is available for PNG generation
        if (extension_loaded('gd')) {
            $writer = new PngWriter();
        } else {
            // Fallback to SVG if GD is not available
            $writer = new SvgWriter();
            // Ensure .svg extension if not already set
            if (pathinfo($filepath, PATHINFO_EXTENSION) !== 'svg') {
                $filepath = pathinfo($filepath, PATHINFO_DIRNAME) . '/' . pathinfo($filepath, PATHINFO_FILENAME) . '.svg';
            }
        }

        $builder = new Builder(
            writer: $writer,
            data: $data,
            encoding: new Encoding($encoding),
            errorCorrectionLevel: $errorCorrectionLevel,
            size: $size,
            margin: $margin
        );

        $result = $builder->build();

        // Ensure directory exists
        $dir = dirname($filepath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($filepath, $result->getString()) !== false;
    }

    /**
     * Parse QR code data
     *
     * @param string $qrData
     * @return array<string, mixed>|null
     */
    public function parseQRData(string $qrData): ?array
    {
        $parsed = json_decode($qrData, true);

        if (!is_array($parsed) || !isset($parsed['vorgangs_id'])) {
            return null;
        }

        return $parsed;
    }

    /**
     * Build QR data string for a Kremation
     *
     * @param Kremation $kremation
     * @return string
     */
    private function buildQRData(Kremation $kremation): string
    {
        $data = [
            'vorgangs_id' => $kremation->vorgangs_id,
            'standort' => $kremation->standort->name ?? 'Unbekannt',
            'eingangsdatum' => $kremation->eingangsdatum?->format('Y-m-d'),
            'gewicht' => $kremation->gewicht,
            'timestamp' => now()->format('c'), // ISO 8601 format
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
