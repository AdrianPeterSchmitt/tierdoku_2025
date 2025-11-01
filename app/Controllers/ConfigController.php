<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use InvalidArgumentException;

/**
 * Configuration Controller
 */
class ConfigController
{
    /**
     * Get current user from session
     */
    private function getCurrentUser(): User
    {
        $user = $_REQUEST['_user'] ?? null;

        if (!$user) {
            http_response_code(401);
            redirect('/login');
            exit;
        }

        return $user;
    }

    /**
     * Display configuration page
     * 
     * @return string
     */
    public function index(): string
    {
        $user = $this->getCurrentUser();

        if (!$user->isAdmin()) {
            http_response_code(403);
            $message = 'Keine Berechtigung für Konfiguration';
            return view('errors/403', compact('message'));
        }

        $envFile = __DIR__ . '/../../.env';
        $config = $this->readEnvFile($envFile);

        return view('config/index', [
            'config' => $config,
            'user' => $user,
        ]);
    }

    /**
     * Update configuration
     * 
     * @return void
     */
    public function update(): void
    {
        $user = $this->getCurrentUser();
        header('Content-Type: application/json');

        if (!$user->isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
            return;
        }

        $envFile = __DIR__ . '/../../.env';
        $backupFile = __DIR__ . '/../../.env.backup';

        try {
            // Create backup before updating
            if (file_exists($envFile)) {
                copy($envFile, $backupFile);
            }

            // Validate and collect parameters
            $params = $this->validateAndCollectParams();

            // Write .env file
            $this->writeEnvFile($envFile, $params);

            echo json_encode([
                'success' => true,
                'message' => 'Konfiguration erfolgreich gespeichert',
                'backup_created' => file_exists($backupFile),
            ]);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Fehler beim Speichern der Konfiguration']);
        }
    }

    /**
     * Read .env file and parse values
     * 
     * @param string $envFile
     * @return array<string, mixed>
     */
    private function readEnvFile(string $envFile): array
    {
        $config = [];
        
        // Default values
        $defaults = [
            'APP_NAME' => 'Tierdokumentation',
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'APP_TIMEZONE' => 'Europe/Berlin',
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => 'localhost',
            'DB_PORT' => '3306',
            'DB_DATABASE' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
            'SESSION_SECURE' => 'false',
            'LOG_LEVEL' => 'error',
            'QR_CODE_SIZE' => '300',
            'QR_CODE_MARGIN' => '10',
            'QR_CODE_ERROR_CORRECTION' => 'High',
            'QR_CODE_ENCODING' => 'UTF-8',
            'PDF_PAPER_SIZE' => 'a4',
            'PDF_PAPER_ORIENTATION' => 'portrait',
            'PDF_QR_CODE_SIZE_MM' => '60',
            'PDF_QR_CODE_PADDING_MM' => '5',
            'PDF_FONT_SIZE_BASE' => '14pt',
            'PDF_FONT_SIZE_HEADER' => '36pt',
            'PDF_LABEL_BORDER_WIDTH' => '3px',
        ];

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            if ($lines === false) {
                return [];
            }
            
            foreach ($lines as $line) {
                $line = trim($line);
                
                // Skip comments
                if (strpos($line, '#') === 0) {
                    continue;
                }
                
                // Parse key=value pairs
                if (strpos($line, '=') !== false) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    
                    // Remove quotes if present
                    $value = trim($value, '"\'');
                    
                    $config[$key] = $value;
                }
            }
        }

        // Merge with defaults
        return array_merge($defaults, $config);
    }

    /**
     * Validate and collect parameters from POST
     * 
     * @return array<string, string>
     */
    private function validateAndCollectParams(): array
    {
        $params = [];

        // App configuration
        $params['APP_NAME'] = trim($_POST['APP_NAME'] ?? 'Tierdokumentation');
        $params['APP_ENV'] = in_array($_POST['APP_ENV'] ?? 'production', ['production', 'local']) 
            ? $_POST['APP_ENV'] 
            : 'production';
        $params['APP_DEBUG'] = isset($_POST['APP_DEBUG']) ? 'true' : 'false';
        $params['APP_TIMEZONE'] = trim($_POST['APP_TIMEZONE'] ?? 'Europe/Berlin');
        
        // Validate timezone
        if (!in_array($params['APP_TIMEZONE'], timezone_identifiers_list())) {
            throw new InvalidArgumentException('Ungültige Zeitzone');
        }

        // Database configuration
        $params['DB_CONNECTION'] = in_array($_POST['DB_CONNECTION'] ?? 'mysql', ['mysql', 'sqlite'])
            ? $_POST['DB_CONNECTION']
            : 'mysql';
        $params['DB_HOST'] = trim($_POST['DB_HOST'] ?? 'localhost');
        $params['DB_PORT'] = (string) ((int) ($_POST['DB_PORT'] ?? 3306));
        
        $params['DB_DATABASE'] = trim($_POST['DB_DATABASE'] ?? '');
        if (empty($params['DB_DATABASE'])) {
            throw new InvalidArgumentException('Datenbank-Name ist erforderlich');
        }

        $params['DB_USERNAME'] = trim($_POST['DB_USERNAME'] ?? '');
        if (empty($params['DB_USERNAME'])) {
            throw new InvalidArgumentException('Datenbank-Benutzername ist erforderlich');
        }

        $params['DB_PASSWORD'] = $_POST['DB_PASSWORD'] ?? '';
        if (empty($params['DB_PASSWORD'])) {
            throw new InvalidArgumentException('Datenbank-Passwort ist erforderlich');
        }

        // Session configuration
        $params['SESSION_SECURE'] = isset($_POST['SESSION_SECURE']) ? 'true' : 'false';
        
        // Logging configuration
        $params['LOG_LEVEL'] = in_array($_POST['LOG_LEVEL'] ?? 'error', ['error', 'warning', 'info', 'debug'])
            ? $_POST['LOG_LEVEL']
            : 'error';

        // QR Code configuration
        $qrSize = (int) ($_POST['QR_CODE_SIZE'] ?? 300);
        if ($qrSize < 100 || $qrSize > 1000) {
            throw new InvalidArgumentException('QR-Code-Größe muss zwischen 100 und 1000 Pixeln liegen');
        }
        $params['QR_CODE_SIZE'] = (string) $qrSize;

        $qrMargin = (int) ($_POST['QR_CODE_MARGIN'] ?? 10);
        if ($qrMargin < 0 || $qrMargin > 50) {
            throw new InvalidArgumentException('QR-Code-Margin muss zwischen 0 und 50 Pixeln liegen');
        }
        $params['QR_CODE_MARGIN'] = (string) $qrMargin;

        $params['QR_CODE_ERROR_CORRECTION'] = in_array(
            $_POST['QR_CODE_ERROR_CORRECTION'] ?? 'High',
            ['Low', 'Medium', 'Quartile', 'High']
        ) ? $_POST['QR_CODE_ERROR_CORRECTION'] : 'High';

        $params['QR_CODE_ENCODING'] = trim($_POST['QR_CODE_ENCODING'] ?? 'UTF-8');

        // PDF Label configuration
        $params['PDF_PAPER_SIZE'] = trim($_POST['PDF_PAPER_SIZE'] ?? 'a4');
        
        $params['PDF_PAPER_ORIENTATION'] = in_array(
            $_POST['PDF_PAPER_ORIENTATION'] ?? 'portrait',
            ['portrait', 'landscape']
        ) ? $_POST['PDF_PAPER_ORIENTATION'] : 'portrait';

        $pdfQrSize = (int) ($_POST['PDF_QR_CODE_SIZE_MM'] ?? 60);
        if ($pdfQrSize < 10 || $pdfQrSize > 200) {
            throw new InvalidArgumentException('PDF QR-Code-Größe muss zwischen 10 und 200 mm liegen');
        }
        $params['PDF_QR_CODE_SIZE_MM'] = (string) $pdfQrSize;

        $pdfQrPadding = (int) ($_POST['PDF_QR_CODE_PADDING_MM'] ?? 5);
        if ($pdfQrPadding < 0 || $pdfQrPadding > 20) {
            throw new InvalidArgumentException('PDF QR-Code-Padding muss zwischen 0 und 20 mm liegen');
        }
        $params['PDF_QR_CODE_PADDING_MM'] = (string) $pdfQrPadding;

        $params['PDF_FONT_SIZE_BASE'] = trim($_POST['PDF_FONT_SIZE_BASE'] ?? '14pt');
        $params['PDF_FONT_SIZE_HEADER'] = trim($_POST['PDF_FONT_SIZE_HEADER'] ?? '36pt');
        $params['PDF_LABEL_BORDER_WIDTH'] = trim($_POST['PDF_LABEL_BORDER_WIDTH'] ?? '3px');

        return $params;
    }

    /**
     * Write .env file
     * 
     * @param string $envFile
     * @param array<string, string> $params
     * @return void
     */
    private function writeEnvFile(string $envFile, array $params): void
    {
        $content = "# Tierdokumentation - Environment Configuration\n";
        $content .= "# Auto-generated/edited by Config Controller\n";
        $content .= "# Last updated: " . date('Y-m-d H:i:s') . "\n\n";

        // App configuration
        $content .= "# Application Configuration\n";
        $content .= "APP_NAME={$params['APP_NAME']}\n";
        $content .= "APP_ENV={$params['APP_ENV']}\n";
        $content .= "APP_DEBUG={$params['APP_DEBUG']}\n";
        $content .= "APP_TIMEZONE={$params['APP_TIMEZONE']}\n\n";

        // Database configuration
        $content .= "# Database Configuration\n";
        $content .= "DB_CONNECTION={$params['DB_CONNECTION']}\n";
        $content .= "DB_HOST={$params['DB_HOST']}\n";
        $content .= "DB_PORT={$params['DB_PORT']}\n";
        $content .= "DB_DATABASE={$params['DB_DATABASE']}\n";
        $content .= "DB_USERNAME={$params['DB_USERNAME']}\n";
        $content .= "DB_PASSWORD={$params['DB_PASSWORD']}\n\n";

        // Session configuration
        $content .= "# Session Configuration\n";
        $content .= "SESSION_SECURE={$params['SESSION_SECURE']}\n\n";

        // Logging configuration
        $content .= "# Logging Configuration\n";
        $content .= "LOG_LEVEL={$params['LOG_LEVEL']}\n\n";

        // QR Code configuration
        $content .= "# QR Code Configuration\n";
        $content .= "QR_CODE_SIZE={$params['QR_CODE_SIZE']}\n";
        $content .= "QR_CODE_MARGIN={$params['QR_CODE_MARGIN']}\n";
        $content .= "QR_CODE_ERROR_CORRECTION={$params['QR_CODE_ERROR_CORRECTION']}\n";
        $content .= "QR_CODE_ENCODING={$params['QR_CODE_ENCODING']}\n\n";

        // PDF Label configuration
        $content .= "# PDF Label Configuration\n";
        $content .= "PDF_PAPER_SIZE={$params['PDF_PAPER_SIZE']}\n";
        $content .= "PDF_PAPER_ORIENTATION={$params['PDF_PAPER_ORIENTATION']}\n";
        $content .= "PDF_QR_CODE_SIZE_MM={$params['PDF_QR_CODE_SIZE_MM']}\n";
        $content .= "PDF_QR_CODE_PADDING_MM={$params['PDF_QR_CODE_PADDING_MM']}\n";
        $content .= "PDF_FONT_SIZE_BASE={$params['PDF_FONT_SIZE_BASE']}\n";
        $content .= "PDF_FONT_SIZE_HEADER={$params['PDF_FONT_SIZE_HEADER']}\n";
        $content .= "PDF_LABEL_BORDER_WIDTH={$params['PDF_LABEL_BORDER_WIDTH']}\n";

        if (file_put_contents($envFile, $content) === false) {
            throw new \Exception('Fehler beim Schreiben der .env Datei');
        }
    }
}


