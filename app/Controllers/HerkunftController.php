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

        $query = Herkunft::orderBy('verwendungen_count', 'desc')->orderBy('name', 'asc');
        if ($currentStandortId) {
            $query->where('standort_id', $currentStandortId);
        }
        $herkuenfte = $query->get();

        return view('herkunft/index', [
            'herkuenfte' => $herkuenfte,
            'standorte' => $standorte,
            'currentStandortId' => $currentStandortId,
            'user' => $user,
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
            return json_encode(['success' => false, 'error' => 'Ungültige Eingaben']);
        }

        // unique per standort
        $exists = Herkunft::where('standort_id', $standortId)->where('name', $name)->exists();
        if ($exists) {
            http_response_code(409);
            return json_encode(['success' => false, 'error' => 'Herkunft existiert bereits am Standort']);
        }

        $h = new Herkunft([
            'name' => $name,
            'standort_id' => $standortId,
            'verwendungen_count' => 0,
        ]);
        $h->save();

        return json_encode(['success' => true, 'id' => $h->herkunft_id]);
    }

    public function update(int $id): string
    {
        /** @var User $user */
        $user = $_REQUEST['_user'] ?? null;
        if (!$user || !$user->isAdmin() && !$user->isManager()) {
            http_response_code(403);
            return json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
        }

        $h = Herkunft::find($id);
        if (!$h) {
            http_response_code(404);
            return json_encode(['success' => false, 'error' => 'Nicht gefunden']);
        }

        $name = trim($_POST['name'] ?? $h->name);
        $standortId = (int) ($_POST['standort_id'] ?? $h->standort_id);

        $exists = Herkunft::where('standort_id', $standortId)
            ->where('name', $name)
            ->where('herkunft_id', '!=', $h->herkunft_id)
            ->exists();
        if ($exists) {
            http_response_code(409);
            return json_encode(['success' => false, 'error' => 'Herkunft existiert bereits am Standort']);
        }

        $h->name = $name;
        $h->standort_id = $standortId;
        $h->save();

        return json_encode(['success' => true]);
    }

    public function delete(int $id): string
    {
        /** @var User $user */
        $user = $_REQUEST['_user'] ?? null;
        if (!$user || !$user->isAdmin() && !$user->isManager()) {
            http_response_code(403);
            return json_encode(['success' => false, 'error' => 'Keine Berechtigung']);
        }

        $h = Herkunft::find($id);
        if (!$h) {
            http_response_code(404);
            return json_encode(['success' => false, 'error' => 'Nicht gefunden']);
        }

        if ($h->verwendungen_count > 0) {
            http_response_code(409);
            return json_encode(['success' => false, 'error' => 'Herkunft in Verwendung']);
        }

        $h->delete();
        return json_encode(['success' => true]);
    }
}


