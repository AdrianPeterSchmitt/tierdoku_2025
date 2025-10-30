## Beitragende Richtlinien (2025 Basis-Workflow)

### Branch-Strategie
- `main`: stabile Releases
- `develop`: Integrationsbranch
- Feature/Fixes: `feature/<kurz-name>`, `fix/<kurz-name>`, `chore/<kurz-name>`

### Commit-Stil (Conventional Commits)
- `feat:`, `fix:`, `chore:`, `docs:`, `refactor:`, `test:`, `perf:`
- Deutsch/Englisch erlaubt; kurze, präzise Messages

### Qualitäts-Gates (lokal)
Führe vor Push aus:
```bash
composer format
composer analyse
composer test
```

### Composer-Skripte
```bash
composer serve    # Lokaler Server (http://localhost:8000)
composer migrate  # Migrationen ausführen
composer seed     # Beispieldaten einspielen
composer analyse  # PHPStan (Level 7)
composer format   # Laravel Pint
composer test     # PHPUnit (alle Tests)
composer qa       # Format + Analyse + Unit + Feature
```

### Datenbank
- Migrationen in `database/migrations/`
- Lokale DB: SQLite (`.env`), Prod: MySQL

### ENV-Handhabung
- `.env.example` aktuell halten
- `.env` niemals committen

### Pull Requests
- Ziel: `develop`
- Checkliste: QA grün (Pint/PHPStan/PHPUnit), Migrationen und Doku angepasst
- Screenshots/Notizen bei UI-Änderungen

### Definition of Done
- Tests grün, Linting/Analyse fehlerfrei
- Relevante Doku aktualisiert (`README.md`, `.env.example`, ggf. `cursor.md`)
- Migrations/Seeds enthalten (falls DB-Änderungen)


