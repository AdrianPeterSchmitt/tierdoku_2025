<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Kremation;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

/**
 * QR Code Service
 *
 * Handles QR code generation for Kremations
 */
class QRCodeService
{
    /**
     * Generate QR code for a Kremation
     *
     * @param Kremation $kremation
     * @param int $size
     * @return string Base64 encoded PNG image
     */
    public function generateForKremation(Kremation $kremation, int $size = 300): string
    {
        $data = $this->buildQRData($kremation);

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($data)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size($size)
            ->margin(10)
            ->build();

        return base64_encode($result->getString());
    }

    /**
     * Save QR code as file
     *
     * @param Kremation $kremation
     * @param string $filepath
     * @param int $size
     * @return bool
     */
    public function saveForKremation(Kremation $kremation, string $filepath, int $size = 300): bool
    {
        $data = $this->buildQRData($kremation);

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($data)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size($size)
            ->margin(10)
            ->build();

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
            'timestamp' => now()->toIso8601String(),
        ];

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}


