<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\Standort;
use InvalidArgumentException;

/**
 * User Controller
 */
class UserController
{
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
     * Display user index
     * 
     * @return string
     */
    public function index(): string
    {
        $user = $this->getCurrentUser();

        if (!$user->isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Insufficient permissions']);
            exit;
        }

        $users = User::with('standort')->orderBy('username', 'asc')->get();
        $standorte = Standort::aktiv()->get();

        return view('users/index', [
            'users' => $users,
            'standorte' => $standorte,
            'user' => $user,
        ]);
    }

    /**
     * Store a new user
     * 
     * @return void
     */
    public function store(): void
    {
        $currentUser = $this->getCurrentUser();
        header('Content-Type: application/json');

        if (!$currentUser->isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Insufficient permissions']);
            return;
        }

        try {
            // Validate required fields
            $required = ['username', 'email', 'password', 'role', 'standort_id'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new InvalidArgumentException("Feld '{$field}' ist erforderlich.");
                }
            }

            // Check if username already exists
            if (User::where('username', $_POST['username'])->exists()) {
                throw new InvalidArgumentException('Benutzername bereits vergeben.');
            }

            // Create user
            $user = User::create([
                'username' => $_POST['username'],
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'],
                'password_hash' => password_hash($_POST['password'], PASSWORD_ARGON2ID),
                'role' => $_POST['role'],
                'standort_id' => $_POST['standort_id'],
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Benutzer erfolgreich erstellt.',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                ],
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
                'error' => 'Fehler beim Erstellen des Benutzers.',
            ]);
        }
    }

    /**
     * Update a user
     * 
     * @param array<string, mixed> $vars
     * @return void
     */
    public function update(array $vars): void
    {
        $currentUser = $this->getCurrentUser();
        header('Content-Type: application/json');

        if (!$currentUser->isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Insufficient permissions']);
            return;
        }

        $userId = (int) ($vars['id'] ?? 0);
        $user = User::find($userId);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'Benutzer nicht gefunden']);
            return;
        }

        try {
            // Update fields
            if (isset($_POST['username'])) {
                // Check if username is taken by another user
                if (User::where('username', $_POST['username'])->where('id', '!=', $userId)->exists()) {
                    throw new InvalidArgumentException('Benutzername bereits vergeben.');
                }
                $user->username = $_POST['username'];
            }

            if (isset($_POST['name'])) {
                $user->name = $_POST['name'];
            }

            if (isset($_POST['email'])) {
                $user->email = $_POST['email'];
            }

            if (isset($_POST['password']) && !empty($_POST['password'])) {
                $user->password_hash = password_hash($_POST['password'], PASSWORD_ARGON2ID);
            }

            if (isset($_POST['role'])) {
                $user->role = $_POST['role'];
            }

            if (isset($_POST['standort_id'])) {
                $user->standort_id = $_POST['standort_id'];
            }

            $user->save();

            echo json_encode([
                'success' => true,
                'message' => 'Benutzer erfolgreich aktualisiert.',
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
                'error' => 'Fehler beim Aktualisieren des Benutzers.',
            ]);
        }
    }

    /**
     * Delete a user
     * 
     * @param array<string, mixed> $vars
     * @return void
     */
    public function delete(array $vars): void
    {
        $currentUser = $this->getCurrentUser();
        header('Content-Type: application/json');

        if (!$currentUser->isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Insufficient permissions']);
            return;
        }

        $userId = (int) ($vars['id'] ?? 0);
        $user = User::find($userId);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'Benutzer nicht gefunden']);
            return;
        }

        // Prevent deleting own account
        if ($user->id === $currentUser->id) {
            http_response_code(400);
            echo json_encode(['error' => 'Du kannst deinen eigenen Account nicht löschen.']);
            return;
        }

        try {
            $user->delete();

            echo json_encode([
                'success' => true,
                'message' => 'Benutzer erfolgreich gelöscht.',
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Fehler beim Löschen des Benutzers.',
            ]);
        }
    }
}

