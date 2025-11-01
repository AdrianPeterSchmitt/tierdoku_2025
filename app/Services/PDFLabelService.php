<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Kremation;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * PDF Label Service
 *
 * Handles PDF label generation for Kremations
 */
class PDFLabelService
{
    /**
     * Generate PDF label for a Kremation
     *
     * @param Kremation $kremation
     * @return string PDF content as binary string
     */
    public function generateLabel(Kremation $kremation): string
    {
        $html = $this->buildLabelHTML($kremation);

        // Get configuration from .env or use defaults
        $paperSize = $_ENV['PDF_PAPER_SIZE'] ?? 'a4';
        $paperOrientation = $_ENV['PDF_PAPER_ORIENTATION'] ?? 'portrait';
        $fontFamily = 'DejaVu Sans'; // Keep default font

        $options = new Options();
        $options->set('defaultFont', $fontFamily);
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultPaperSize', $paperSize);
        $options->set('defaultPaperOrientation', $paperOrientation);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper($paperSize, $paperOrientation);
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Generate PDF label with embedded QR code
     *
     * @param Kremation $kremation
     * @param string $qrCodeBase64 Base64 encoded QR code image
     * @param string $qrMimeType MIME type of the QR code (image/png or image/svg+xml)
     * @return string PDF content as binary string
     */
    public function generateLabelWithQR(Kremation $kremation, string $qrCodeBase64, string $qrMimeType = 'image/png'): string
    {
        // Get paper size to determine QR code size before building HTML
        $paperSize = strtolower($_ENV['PDF_PAPER_SIZE'] ?? 'a4');
        $isSmallFormat = in_array($paperSize, ['a7', 'a6', 'a5']);
        
        // Get QR code size from .env or use default (adjusted for small formats)
        $qrCodeSizeMm = $_ENV['PDF_QR_CODE_SIZE_MM'] ?? '60';
        if ($isSmallFormat && ($_ENV['PDF_QR_CODE_SIZE_MM'] ?? '60') === '60') {
            $qrCodeSizeMm = '25';
        }
        
        $html = $this->buildLabelHTML($kremation);

        // Replace QR placeholder with actual QR code
        $mimeType = $qrMimeType !== '' ? $qrMimeType : 'image/png';
        
        // Build replacement with proper QR code image
        $replacement = '<img src="data:' . htmlspecialchars($mimeType) . ';base64,' . $qrCodeBase64 . '" style="width: ' . htmlspecialchars($qrCodeSizeMm) . 'mm; height: ' . htmlspecialchars($qrCodeSizeMm) . 'mm; display: block; max-width: 100%; object-fit: contain;">';
        
        // Try multiple replacement strategies
        // Strategy 1: Exact match with the exact size from buildLabelHTML
        $exactPlaceholder = '<div style=\'width: ' . htmlspecialchars($qrCodeSizeMm) . 'mm; height: ' . htmlspecialchars($qrCodeSizeMm) . 'mm; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-size: 8pt; color: #999;\'>
                            Scannen Sie den QR-Code
                            <br>für Details
                        </div>';
        
        if (strpos($html, $exactPlaceholder) !== false) {
            $html = str_replace($exactPlaceholder, $replacement, $html);
        } else {
            // Strategy 2: Regex match - find div with width containing the size
            $pattern = '/<div[^>]*style=[\'"][^\'"]*width:\s*' . preg_quote($qrCodeSizeMm, '/') . 'mm[^\'"]*[\'"][^>]*>.*?Scannen Sie den QR-Code.*?<\/div>/s';
            $html = preg_replace($pattern, $replacement, $html);
            
            // Strategy 3: Fallback - find any div containing "Scannen Sie den QR-Code"
            if (strpos($html, 'Scannen Sie den QR-Code') !== false) {
                $flexiblePattern = '/<div[^>]*>.*?Scannen Sie den QR-Code.*?<\/div>/s';
                $html = preg_replace($flexiblePattern, $replacement, $html, 1); // Replace only first occurrence
            }
        }

        // Get configuration from .env or use defaults
        $paperSize = $_ENV['PDF_PAPER_SIZE'] ?? 'a4';
        $paperOrientation = $_ENV['PDF_PAPER_ORIENTATION'] ?? 'portrait';
        $fontFamily = 'DejaVu Sans'; // Keep default font

        $options = new Options();
        $options->set('defaultFont', $fontFamily);
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultPaperSize', $paperSize);
        $options->set('defaultPaperOrientation', $paperOrientation);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper($paperSize, $paperOrientation);
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Build HTML for the label
     *
     * @param Kremation $kremation
     * @return string
     */
    private function buildLabelHTML(Kremation $kremation): string
    {
        $standort = $kremation->standort->name ?? 'Unbekannt';
        $herkunft = $kremation->herkunft->name ?? 'Unbekannt';
        $eingangsdatum = $kremation->eingangsdatum?->format('d.m.Y') ?? 'N/A';
        $gewicht = number_format($kremation->gewicht, 2, ',', '.');

        // Get PDF configuration from .env or use defaults
        $paperSize = strtolower($_ENV['PDF_PAPER_SIZE'] ?? 'a4');
        $labelBorderWidth = $_ENV['PDF_LABEL_BORDER_WIDTH'] ?? '3px';
        $fontSizeHeader = $_ENV['PDF_FONT_SIZE_HEADER'] ?? '36pt';
        $fontSizeBase = $_ENV['PDF_FONT_SIZE_BASE'] ?? '14pt';
        $qrCodeSizeMm = $_ENV['PDF_QR_CODE_SIZE_MM'] ?? '60';
        $qrCodePaddingMm = $_ENV['PDF_QR_CODE_PADDING_MM'] ?? '5';

        // Adjust sizes based on paper size
        $isSmallFormat = in_array($paperSize, ['a7', 'a6', 'a5']);
        
        if ($isSmallFormat) {
            // Reduce padding and margins for small formats
            $bodyPadding = '5mm';
            $labelPadding = '4mm';
            $headerMarginBottom = '3mm';
            $rowMarginBottom = '2mm';
            $footerMarginTop = '5mm';
            $qrMargin = '3mm';
            
            // Scale down font sizes
            if (empty($_ENV['PDF_FONT_SIZE_HEADER']) || $_ENV['PDF_FONT_SIZE_HEADER'] === '36pt') {
                $fontSizeHeader = '16pt';
            }
            if (empty($_ENV['PDF_FONT_SIZE_BASE']) || $_ENV['PDF_FONT_SIZE_BASE'] === '14pt') {
                $fontSizeBase = '8pt';
            }
            
            // Reduce QR code size for small formats if using default
            if (empty($_ENV['PDF_QR_CODE_SIZE_MM']) || $_ENV['PDF_QR_CODE_SIZE_MM'] === '60') {
                $qrCodeSizeMm = '25';
            }
            if (empty($_ENV['PDF_QR_CODE_PADDING_MM']) || $_ENV['PDF_QR_CODE_PADDING_MM'] === '5') {
                $qrCodePaddingMm = '2';
            }
        } else {
            // Default sizes for A4 and larger
            $bodyPadding = '20mm';
            $labelPadding = '15mm';
            $headerMarginBottom = '10mm';
            $rowMarginBottom = '5mm';
            $footerMarginTop = '15mm';
            $qrMargin = '10mm';
        }

        // Get tier counts
        $tierCounts = [];
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tierart> $tierarten */
        $tierarten = $kremation->tierarten;
        foreach ($tierarten as $tierart) {
            /** @var \App\Models\Tierart $tierart */
            $pivot = $tierart->pivot;
            /** @var mixed $anzahlRaw */
            $anzahlRaw = $pivot->getAttribute('anzahl');
            /** @var int $anzahl */
            $anzahl = is_int($anzahlRaw) ? $anzahlRaw : (int) $anzahlRaw;
            $tierCounts[$tierart->bezeichnung] = $anzahl;
        }

        /** @var array<string, int> $tierCounts */
        $tierartenStr = implode(', ', array_map(
            function ($name, $count) {
                return "$name: $count";
            },
            array_keys($tierCounts),
            array_values($tierCounts)
        ));

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: 'DejaVu Sans', Arial, sans-serif;
                    padding: " . htmlspecialchars($bodyPadding) . ";
                    margin: 0;
                }
                
                .label {
                    width: 100%;
                    border: " . htmlspecialchars($labelBorderWidth) . " solid #000;
                    padding: " . htmlspecialchars($labelPadding) . ";
                    page-break-after: always;
                    box-sizing: border-box;
                }
                
                .label:last-child {
                    page-break-after: auto;
                }
                
                .header {
                    border-bottom: " . ($isSmallFormat ? '2px' : '3px') . " solid #000;
                    padding-bottom: " . ($isSmallFormat ? '3mm' : '10mm') . ";
                    margin-bottom: " . htmlspecialchars($headerMarginBottom) . ";
                    text-align: center;
                }
                
                .header h1 {
                    font-size: " . htmlspecialchars($fontSizeHeader) . ";
                    font-weight: bold;
                    margin-bottom: " . ($isSmallFormat ? '2mm' : '5mm') . ";
                    line-height: 1.2;
                }
                
                .header h2 {
                    font-size: " . ($isSmallFormat ? '12pt' : '24pt') . ";
                    font-weight: bold;
                    line-height: 1.2;
                }
                
                .content {
                    " . ($isSmallFormat ? 'display: block;' : 'display: table;') . "
                    width: 100%;
                }
                
                .row {
                    " . ($isSmallFormat ? 'display: block; margin-bottom: ' . htmlspecialchars($rowMarginBottom) . ';' : 'display: table-row; margin-bottom: ' . htmlspecialchars($rowMarginBottom) . ';') . "
                }
                
                " . ($isSmallFormat ? 
                ".label-cell, .value-cell {
                    display: block;
                    padding: 0.5mm 0;
                    font-size: " . htmlspecialchars($fontSizeBase) . ";
                    line-height: 1.4;
                }
                
                .label-cell {
                    font-weight: bold;
                    margin-bottom: 0.5mm;
                }
                
                .value-cell {
                    border-bottom: 1px dotted #000;
                    padding-bottom: 1mm;
                    margin-bottom: " . htmlspecialchars($rowMarginBottom) . ";
                }"
                :
                ".label-cell, .value-cell {
                    display: table-cell;
                    padding: 3mm 0;
                    font-size: " . htmlspecialchars($fontSizeBase) . ";
                    vertical-align: top;
                    line-height: 1.3;
                }
                
                .label-cell {
                    width: 40%;
                    font-weight: bold;
                }
                
                .value-cell {
                    width: 60%;
                    border-bottom: 1px dotted #000;
                }"
                ) . "
                
                .footer {
                    margin-top: " . htmlspecialchars($footerMarginTop) . ";
                    padding-top: " . ($isSmallFormat ? '3mm' : '10mm') . ";
                    border-top: " . ($isSmallFormat ? '1px' : '2px') . " solid #000;
                    text-align: center;
                    font-size: " . ($isSmallFormat ? '7pt' : '10pt') . ";
                    line-height: 1.2;
                }
                
                .large-text {
                    font-size: " . ($isSmallFormat ? '10pt' : '18pt') . ";
                }
                
                .qr-code {
                    text-align: center;
                    margin: " . htmlspecialchars($qrMargin) . " 0;
                }
                
                .qr-code img {
                    width: " . htmlspecialchars($qrCodeSizeMm) . "mm;
                    height: " . htmlspecialchars($qrCodeSizeMm) . "mm;
                }
            </style>
        </head>
        <body>
            <div class='label'>
                <div class='header'>
                    <h1>animea™ Tierkrematorium</h1>
                    <h2>Kremations-Nr. #{$kremation->vorgangs_id}</h2>
                </div>
                
                " . ($isSmallFormat ? 
                "<div class='content'>
                    <div class='row'>
                        <div class='label-cell'><strong>Standort:</strong></div>
                        <div class='value-cell'>{$standort}</div>
                    </div>
                    <div class='row'>
                        <div class='label-cell'><strong>Eingangsdatum:</strong></div>
                        <div class='value-cell'>{$eingangsdatum}</div>
                    </div>
                    <div class='row'>
                        <div class='label-cell'><strong>Herkunft:</strong></div>
                        <div class='value-cell'>{$herkunft}</div>
                    </div>
                    <div class='row'>
                        <div class='label-cell'><strong>Gewicht:</strong></div>
                        <div class='value-cell large-text'>{$gewicht} kg</div>
                    </div>
                    <div class='row'>
                        <div class='label-cell'><strong>Tierarten:</strong></div>
                        <div class='value-cell'>" . htmlspecialchars($tierartenStr ?: 'N/A') . "</div>
                    </div>
                </div>"
                :
                "<div class='content'>
                    <div class='row'>
                        <div class='label-cell'>Standort:</div>
                        <div class='value-cell'>{$standort}</div>
                    </div>
                    <div class='row'>
                        <div class='label-cell'>Eingangsdatum:</div>
                        <div class='value-cell'>{$eingangsdatum}</div>
                    </div>
                    <div class='row'>
                        <div class='label-cell'>Herkunft:</div>
                        <div class='value-cell'>{$herkunft}</div>
                    </div>
                    <div class='row'>
                        <div class='label-cell'>Gewicht:</div>
                        <div class='value-cell large-text'>{$gewicht} kg</div>
                    </div>
                    <div class='row'>
                        <div class='label-cell'>Tierarten:</div>
                        <div class='value-cell'>" . htmlspecialchars($tierartenStr ?: 'N/A') . "</div>
                    </div>
                </div>"
                ) . "
                
                <div class='qr-code'>
                    <p style='font-size: " . ($isSmallFormat ? '7pt' : '10pt') . "; margin-bottom: " . ($isSmallFormat ? '2mm' : '3mm') . ";'>QR-Code:</p>
                    <div style='display: inline-block; padding: " . htmlspecialchars($qrCodePaddingMm) . "mm; background: #fff; border: " . ($isSmallFormat ? '1px' : '2px') . " solid #000;'>
                        <!-- QR Code placeholder - actual QR code would be embedded as base64 image -->
                        <div style='width: " . htmlspecialchars($qrCodeSizeMm) . "mm; height: " . htmlspecialchars($qrCodeSizeMm) . "mm; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-size: 8pt; color: #999;'>
                            Scannen Sie den QR-Code
                            <br>für Details
                        </div>
                    </div>
                </div>
                
                <div class='footer'>
                    <p>animea™ Tierkrematorium | {$standort}</p>
                    <p>Datum: " . now()->format('d.m.Y H:i') . "</p>
                </div>
            </div>
        </body>
        </html>";
    }
}
