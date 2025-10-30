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

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultPaperSize', 'a4');
        $options->set('defaultPaperOrientation', 'portrait');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('a4', 'portrait');
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
        
        // Get tier counts
        $tierCounts = [];
        foreach ($kremation->tierarten as $tierart) {
            $tierCounts[$tierart->bezeichnung] = $tierart->pivot->anzahl;
        }

        $tierarten = implode(', ', array_map(
            fn($name, $count) => "$name: $count",
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
                    padding: 20mm;
                }
                
                .label {
                    width: 100%;
                    border: 3px solid #000;
                    padding: 15mm;
                    page-break-after: always;
                }
                
                .label:last-child {
                    page-break-after: auto;
                }
                
                .header {
                    border-bottom: 3px solid #000;
                    padding-bottom: 10mm;
                    margin-bottom: 10mm;
                    text-align: center;
                }
                
                .header h1 {
                    font-size: 36pt;
                    font-weight: bold;
                    margin-bottom: 5mm;
                }
                
                .header h2 {
                    font-size: 24pt;
                    font-weight: bold;
                }
                
                .content {
                    display: table;
                    width: 100%;
                }
                
                .row {
                    display: table-row;
                    margin-bottom: 5mm;
                }
                
                .label-cell, .value-cell {
                    display: table-cell;
                    padding: 3mm 0;
                    font-size: 14pt;
                    vertical-align: top;
                }
                
                .label-cell {
                    width: 40%;
                    font-weight: bold;
                }
                
                .value-cell {
                    width: 60%;
                    border-bottom: 1px dotted #000;
                }
                
                .footer {
                    margin-top: 15mm;
                    padding-top: 10mm;
                    border-top: 2px solid #000;
                    text-align: center;
                    font-size: 10pt;
                }
                
                .large-text {
                    font-size: 18pt;
                }
                
                .qr-code {
                    text-align: center;
                    margin: 10mm 0;
                }
                
                .qr-code img {
                    width: 80mm;
                    height: 80mm;
                }
            </style>
        </head>
        <body>
            <div class='label'>
                <div class='header'>
                    <h1>ANIMEA Tierkrematorium</h1>
                    <h2>Kremations-Nr. #{$kremation->vorgangs_id}</h2>
                </div>
                
                <div class='content'>
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
                        <div class='value-cell'>" . ($tierarten ?: 'N/A') . "</div>
                    </div>
                </div>
                
                <div class='qr-code'>
                    <p style='font-size: 10pt; margin-bottom: 3mm;'>QR-Code:</p>
                    <div style='display: inline-block; padding: 5mm; background: #fff; border: 2px solid #000;'>
                        <!-- QR Code placeholder - actual QR code would be embedded as base64 image -->
                        <div style='width: 60mm; height: 60mm; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-size: 8pt; color: #999;'>
                            Scannen Sie den QR-Code
                            <br>für Details
                        </div>
                    </div>
                </div>
                
                <div class='footer'>
                    <p>ANIMEA Tierkrematorium | {$standort}</p>
                    <p>Datum: " . now()->format('d.m.Y H:i') . "</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Generate PDF label with embedded QR code
     *
     * @param Kremation $kremation
     * @param string $qrCodeBase64 Base64 encoded QR code image
     * @return string PDF content as binary string
     */
    public function generateLabelWithQR(Kremation $kremation, string $qrCodeBase64): string
    {
        $html = $this->buildLabelHTML($kremation);

        // Replace QR placeholder with actual QR code
        $html = str_replace(
            '<div style=\'width: 60mm; height: 60mm; border: 1px solid #ccc; display: flex; align-items: center; justify-content: center; font-size: 8pt; color: #999;\'>
                            Scannen Sie den QR-Code
                            <br>für Details
                        </div>',
            '<img src="data:image/png;base64,' . $qrCodeBase64 . '" style="width: 60mm; height: 60mm;">',
            $html
        );

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultPaperSize', 'a4');
        $options->set('defaultPaperOrientation', 'portrait');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}


