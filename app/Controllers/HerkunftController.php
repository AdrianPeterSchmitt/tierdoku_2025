<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Herkunft;
use App\Models\Standort;
use App\Models\User;
use Respect\Validation\Validator as v;

/**
 * Herkunft Controller
 */
class HerkunftController
{
    /**
     * Display herkunft index
     * 
     * @return string
     */
    public function index(): string
    {
        /** @var User $user */
        $user = $_REQUEST['_user'] ?? null;
        $standorte = Standort::orderBy('name')->get();

        $currentStandortId = null;
        if ($user && !$user->isAdmin()) {
            $currentStandortId = $user->standort_id;
        } else {
            $currentStandortId = isset($_GET['standort_id']) && $_GET['standort_id'] !== ''
                ? (int) $_GET['standort_id']
                : ($standorte[0]->standort_id ?? null);
        }

        $query = Herkunft::orderBy('name', 'asc');
        if ($currentStandortId) {
            $query->where('standort_id', $currentStandortId);
        }
        $herkuenfte = $query->get();
        
        // Calculate usage counts dynamically (like StandortController does)
        $herkuenfte = $herkuenfte->map(function ($herkunft) {
            $verwendungenCount = $herkunft->kremations()->count();
            $herkunft->verwendungen_count = $verwendungenCount;
            return $herkunft;
        })->sortByDesc('verwendungen_count')->values();

        return view('herkunft/index', [
            'herkuenfte' => $herkuenfte,
            'standorte' => $standorte,
            'currentStandortId' => $currentStandortId,
            'user' => $user,
        ]);
    }

    /**
     * Get herkunft data for editing
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
        $herkunft = Herkunft::with('standort')->find($id);

        if (!$herkunft) {
            http_response_code(404);
            return (string) json_encode(['success' => false, 'error' => 'Herkunft nicht gefunden']);
        }

        return (string) json_encode([
            'success' => true,
            'herkunft' => [
                'herkunft_id' => $herkunft->herkunft_id,
                'name' => $herkunft->name,
                'standort_id' => $herkunft->standort_id,
            ],
        ]);
    }

    public function store(): string
    {
        /** @var User $user */
        $user = $_REQUEST['_user'] ?? null;
        if (!$user || !$user->isAdmin() && !$user->isManager()) {
            http_response_code(403);
            return json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
        }

        $name = trim($_POST['name'] ?? '');
        $standortId = (int) ($_POST['standort_id'] ?? 0);

        $validator = v::key('name', v::stringType()->length(2, 255))
            ->key('standort_id', v::intType()->min(1));
        try {
            $validator->assert(['name' => $name, 'standort_id' => $standortId]);
        } catch (\Throwable $e) {
            http_response_code(422);
            return (string) json_encode(['success' => false, 'error' => 'Ungültige Eingaben']);
        }

        // unique per standort
        $exists = Herkunft::where('standort_id', $standortId)->where('name', $name)->exists();
        if ($exists) {
            http_response_code(409);
            return (string) json_encode(['success' => false, 'error' => 'Herkunft existiert bereits am Standort']);
        }

        $h = new Herkunft([
            'name' => $name,
            'standort_id' => $standortId,
            'verwendungen_count' => 0,
        ]);
        $h->save();

        return (string) json_encode(['success' => true, 'id' => $h->herkunft_id]);
    }

    /**
     * Update a herkunft
     * 
     * @param array<string, mixed> $vars
     * @return string
     */
    public function update(array $vars): string
    {
        /** @var User $user */
        $user = $_REQUEST['_user'] ?? null;
        if (!$user || !$user->isAdmin() && !$user->isManager()) {
            http_response_code(403);
            return (string) json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
        }

        $id = (int) ($vars['id'] ?? 0);
        $h = Herkunft::find($id);
        if (!$h) {
            http_response_code(404);
            return (string) json_encode(['success' => false, 'error' => 'Nicht gefunden']);
        }

        $name = trim($_POST['name'] ?? $h->name);
        $standortId = (int) ($_POST['standort_id'] ?? $h->standort_id);

        $exists = Herkunft::where('standort_id', $standortId)
            ->where('name', $name)
            ->where('herkunft_id', '!=', $h->herkunft_id)
            ->exists();
        if ($exists) {
            http_response_code(409);
            return (string) json_encode(['success' => false, 'error' => 'Herkunft existiert bereits am Standort']);
        }

        $h->name = $name;
        $h->standort_id = $standortId;
        $h->save();

        return (string) json_encode(['success' => true]);
    }

    /**
     * Delete a herkunft
     * 
     * @param array<string, mixed> $vars
     * @return string
     */
    public function delete(array $vars): string
    {
        /** @var User $user */
        $user = $_REQUEST['_user'] ?? null;
        if (!$user || !$user->isAdmin() && !$user->isManager()) {
            http_response_code(403);
            return (string) json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
        }

        $id = (int) ($vars['id'] ?? 0);
        $h = Herkunft::find($id);
        if (!$h) {
            http_response_code(404);
            return (string) json_encode(['success' => false, 'error' => 'Nicht gefunden']);
        }

        // Check if herkunft is actually used (dynamic count)
        $actualCount = $h->kremations()->count();
        if ($actualCount > 0) {
            http_response_code(409);
            return (string) json_encode(['success' => false, 'error' => 'Herkunft in Verwendung']);
        }

        $h->delete();
        return (string) json_encode(['success' => true]);
    }
}


