<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Kremation;
use App\Models\Standort;
use App\Models\Herkunft;
use Illuminate\Database\Eloquent\Collection;

/**
 * Statistics Controller
 */
class StatisticsController
{
    /**
     * Get current user from session
     */
    private function getCurrentUser(): \App\Models\User
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
     * Display statistics page
     * 
     * @return string
     */
    public function index(): string
    {
        $user = $this->getCurrentUser();

        // Date range filter
        $dateFrom = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $_GET['to'] ?? date('Y-m-d');

        // Build base query
        $query = Kremation::with(['standort', 'herkunft', 'tierarten']);

        // Apply standort filter using scope
        $query->forAllowedStandorte($user);
        
        // Admin can also filter by specific standort
        if ($user->isAdmin() && !empty($_GET['standort_id'])) {
            $query->where('standort_id', $_GET['standort_id']);
        }

        // Apply herkunft filter
        if (!empty($_GET['herkunft_id'])) {
            $query->where('herkunft_id', $_GET['herkunft_id']);
        }

        // Apply date filter
        $query->whereBetween('eingangsdatum', [$dateFrom, $dateTo]);

        // Get all kremations
        $kremations = $query->get();

        // Calculate statistics
        $stats = $this->calculateStatistics($kremations);

        // Get standorte for filter: filtered for non-admins
        if ($user->isAdmin()) {
            $standorte = Standort::aktiv()->get();
        } else {
            $standorte = $user->standorte()->where('aktiv', true)->get();
        }

        // Get all herkunfte for filter
        $herkunfteQuery = Herkunft::query();
        if (!$user->isAdmin()) {
            // Non-admins: nur Herkünfte der zugewiesenen Standorte
            $allowedStandortIds = $user->getAllowedStandortIds();
            if (!empty($allowedStandortIds)) {
                $herkunfteQuery->whereIn('standort_id', $allowedStandortIds);
            } else {
                $herkunfteQuery->whereRaw('1 = 0'); // No results if no standorte
            }
        }
        $herkunfte = $herkunfteQuery->orderBy('name')->get();

        return view('statistics/index', [
            'stats' => $stats,
            'standorte' => $standorte,
            'herkunfte' => $herkunfte,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'currentStandort' => $_GET['standort_id'] ?? null,
            'currentHerkunft' => $_GET['herkunft_id'] ?? null,
            'user' => $user,
        ]);
    }

    /**
     * Calculate statistics from kremations
     *
     * @param Collection<int, Kremation> $kremations
     * @return array<string, mixed>
     */
    private function calculateStatistics(Collection $kremations): array
    {
        $totalCount = $kremations->count();
        $completedCount = $kremations->where('einaescherungsdatum', '!=', null)->count();
        $openCount = $totalCount - $completedCount;

        $totalWeight = $kremations->sum('gewicht');
        $averageWeight = $totalCount > 0 ? $totalWeight / $totalCount : 0;

        // Count by standort
        $byStandort = $kremations->groupBy('standort_id')->map(function ($group, $standortId) {
            /** @var Kremation $first */
            $first = $group->first();
            /** @var \App\Models\Standort|null $standort */
            $standort = $first->standort;
            return [
                'name' => $standort->name ?? 'Unbekannt',
                'count' => $group->count(),
                'weight' => $group->sum('gewicht'),
            ];
        })->values();

        // Count by herkunft
        $byHerkunft = $kremations->groupBy('herkunft_id')->map(function ($group, $herkunftId) {
            /** @var Kremation $first */
            $first = $group->first();
            /** @var \App\Models\Herkunft|null $herkunft */
            $herkunft = $first->herkunft;
            return [
                'name' => $herkunft->name ?? 'Unbekannt',
                'count' => $group->count(),
            ];
        })->sortByDesc('count')->take(10)->values();

        // Count by tierart
        $byTierart = [];
        /** @var Kremation $kremationItem */
        foreach ($kremations as $kremationItem) {
            foreach ($kremationItem->tierarten as $tierart) {
                /** @var \App\Models\Tierart $tierart */
                $pivot = $tierart->pivot;
                /** @var \Illuminate\Database\Eloquent\Relations\Pivot $pivot */
                /** @var mixed $anzahlRaw */
                $anzahlRaw = $pivot->getAttribute('anzahl');
                /** @var int $anzahl */
                $anzahl = is_int($anzahlRaw) ? $anzahlRaw : (int) $anzahlRaw;
                $name = $tierart->bezeichnung;
                if (!isset($byTierart[$name])) {
                    $byTierart[$name] = 0;
                }
                $byTierart[$name] += $anzahl;
            }
        }
        arsort($byTierart);
        $byTierart = collect($byTierart)->map(fn($count, $name) => ['name' => $name, 'count' => $count])->values();

        // Timeline data (last 30 days)
        $timeline = [];
        for ($i = 29; $i >= 0; $i--) {
            $timestamp = strtotime("-$i days");
            if ($timestamp === false) {
                continue;
            }
            $date = date('Y-m-d', $timestamp);
            $count = $kremations->filter(fn($k) => $k->eingangsdatum->format('Y-m-d') === $date)->count();
            $formatTimestamp = strtotime($date);
            $timeline[] = ['date' => $formatTimestamp !== false ? date('d.m', $formatTimestamp) : $date, 'count' => $count];
        }

        return [
            'totalCount' => $totalCount,
            'completedCount' => $completedCount,
            'openCount' => $openCount,
            'totalWeight' => $totalWeight,
            'averageWeight' => $averageWeight,
            'byStandort' => $byStandort,
            'byHerkunft' => $byHerkunft,
            'byTierart' => $byTierart,
            'timeline' => $timeline,
        ];
    }
}

