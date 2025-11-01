<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Standort;
use App\Models\Herkunft;
use App\Models\User;
use Respect\Validation\Validator as v;

/**
 * Standort Controller
 */
class StandortController
{
    /**
     * Display standort index
     * 
     * @return string
     */
    public function index(): string
    {
        /** @var User $user */
        $user = $_REQUEST['_user'] ?? null;
        
        if (!$user) {
            http_response_code(401);
            redirect('/login');
            exit;
        }
        
        if (!$user->isAdmin() && !$user->isManager()) {
            http_response_code(403);
            $message = 'Keine Berechtigung für Standort-Verwaltung';
            return view('errors/403', compact('message'));
        }

        $standorte = Standort::orderBy('name', 'asc')->get();

        // Calculate usage counts for each standort
        $standorteWithUsage = $standorte->map(function ($standort) {
            $kremationsCount = $standort->kremations()->count();
            $usersCount = $standort->users()->count();
            $herkunftCount = Herkunft::where('standort_id', $standort->standort_id)->count();
            
            $standort->verwendungen_count = $kremationsCount + $usersCount + $herkunftCount;
            $standort->kremations_count = $kremationsCount;
            $standort->users_count = $usersCount;
            $standort->herkunft_count = $herkunftCount;
            
            return $standort;
        });

        return view('standort/index', [
            'standorte' => $standorteWithUsage,
            'user' => $user,
        ]);
    }

    /**
     * Get standort data for editing
     * 
     * @param array<string, mixed> $vars
     * @return string
     */
    public function edit(array $vars): string
    {
        /** @var User $user */
        $user = $_REQUEST['_user'] ?? null;
        header('Content-Type: application/json');

        if (!$user || (!$user->isAdmin() && !$user->isManager())) {
            http_response_code(403);
            return (string) json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
        }

        $id = (int) ($vars['id'] ?? 0);
        $standort = Standort::find($id);

        if (!$standort) {
            http_response_code(404);
            return (string) json_encode(['success' => false, 'error' => 'Standort nicht gefunden']);
        }

        return (string) json_encode([
            'success' => true,
            'standort' => [
                'standort_id' => $standort->standort_id,
                'name' => $standort->name,
                'aktiv' => $standort->aktiv,
            ],
        ]);
    }

    /**
     * Store a new standort
     * 
     * @return string
     */
    public function store(): string
    {
        /** @var User $user */
        $user = $_REQUEST['_user'] ?? null;
        if (!$user || (!$user->isAdmin() && !$user->isManager())) {
            http_response_code(403);
            return json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
        }

        $name = trim($_POST['name'] ?? '');
        $aktiv = isset($_POST['aktiv']) ? (bool) $_POST['aktiv'] : true;

        $validator = v::key('name', v::stringType()->length(2, 255));
        try {
            $validator->assert(['name' => $name]);
        } catch (\Throwable $e) {
            http_response_code(422);
            return (string) json_encode(['success' => false, 'error' => 'Ungültige Eingaben']);
        }

        // Check if name already exists
        $exists = Standort::where('name', $name)->exists();
        if ($exists) {
            http_response_code(409);
            return (string) json_encode(['success' => false, 'error' => 'Standort existiert bereits']);
        }

        $s = new Standort([
            'name' => $name,
            'aktiv' => $aktiv,
        ]);
        $s->save();

        return (string) json_encode(['success' => true, 'id' => $s->standort_id]);
    }

    /**
     * Update a standort
     * 
     * @param array<string, mixed> $vars
     * @return string
     */
    public function update(array $vars): string
    {
        /** @var User $user */
        $user = $_REQUEST['_user'] ?? null;
        if (!$user || (!$user->isAdmin() && !$user->isManager())) {
            http_response_code(403);
            return (string) json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
        }

        $id = (int) ($vars['id'] ?? 0);
        $s = Standort::find($id);
        if (!$s) {
            http_response_code(404);
            return (string) json_encode(['success' => false, 'error' => 'Nicht gefunden']);
        }

        $name = trim($_POST['name'] ?? $s->name);
        
        // Check if name is being changed
        if ($name !== $s->name) {
            $exists = Standort::where('name', $name)
                ->where('standort_id', '!=', $s->standort_id)
                ->exists();
            if ($exists) {
                http_response_code(409);
                return (string) json_encode(['success' => false, 'error' => 'Standort existiert bereits']);
            }
            $s->name = $name;
        }

        // Update aktiv status if provided
        if (isset($_POST['aktiv'])) {
            $s->aktiv = (bool) $_POST['aktiv'];
        }

        $s->save();

        return (string) json_encode(['success' => true]);
    }

    /**
     * Delete a standort
     * 
     * @param array<string, mixed> $vars
     * @return string
     */
    public function delete(array $vars): string
    {
        /** @var User $user */
        $user = $_REQUEST['_user'] ?? null;
        if (!$user || (!$user->isAdmin() && !$user->isManager())) {
            http_response_code(403);
            return (string) json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
        }

        $id = (int) ($vars['id'] ?? 0);
        $s = Standort::find($id);
        if (!$s) {
            http_response_code(404);
            return (string) json_encode(['success' => false, 'error' => 'Nicht gefunden']);
        }

        // Check if standort is in use
        $hasKremations = $s->kremations()->exists();
        $hasUsers = $s->users()->exists();
        $hasHerkunft = Herkunft::where('standort_id', $s->standort_id)->exists();

        if ($hasKremations || $hasUsers || $hasHerkunft) {
            http_response_code(409);
            $reasons = [];
            if ($hasKremations) $reasons[] = 'Kremationen';
            if ($hasUsers) $reasons[] = 'Benutzer';
            if ($hasHerkunft) $reasons[] = 'Herkünfte';
            return (string) json_encode([
                'success' => false, 
                'error' => 'Standort in Verwendung (' . implode(', ', $reasons) . ')'
            ]);
        }

        $s->delete();
        return (string) json_encode(['success' => true]);
    }
}

