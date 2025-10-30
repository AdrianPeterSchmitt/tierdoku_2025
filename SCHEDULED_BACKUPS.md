# 🔄 Regelmäßige GitHub-Backups

Dieses Dokument beschreibt, wie automatische Backups auf GitHub eingerichtet werden.

## 📋 Aktueller Status

✅ **Erste Sicherung erstellt:**
- Repository: [https://github.com/AdrianPeterSchmitt/tierdoku_2025](https://github.com/AdrianPeterSchmitt/tierdoku_2025)
- Commit: 45 Dateien, 4508 Zeilen

---

## 🚀 Manuelle Backups

### Einfachste Methode:
```powershell
.\backup-to-github.ps1
```

### Mit eigener Commit-Message:
```powershell
.\backup-to-github.ps1 -Message "My custom backup message"
```

---

## ⏰ Automatische Backups

### Option 1: Windows Task Scheduler (Empfohlen)

#### Einrichten:

1. **Task Scheduler öffnen:**
   - Windows-Taste → "Task Scheduler" suchen
   - Oder: `Win + R` → `taskschd.msc`

2. **Neue Aufgabe erstellen:**
   - Rechts: "Basic Task Create..." klicken
   - Name: `Tierdoku GitHub Backup`
   - Beschreibung: `Automatisches Backup der Tierdokumentation auf GitHub`
   - Trigger: **Täglich** (oder **Wöchentlich**)
   - Zeit: z.B. 02:00 Uhr

3. **Aktion konfigurieren:**
   - "Start a program" auswählen
   - Program/script: `powershell.exe`
   - Arguments:
     ```
     -File "C:\Cursor_Projekte\Tierdokumentation\backup-to-github.ps1"
     ```
   - Start in: `C:\Cursor_Projekte\Tierdokumentation`

4. **Optionen:**
   - ✅ "Run whether user is logged on or not"
   - ✅ "Run with highest privileges"
   - ✅ "Configure for Windows 10"

5. **Fertig!** Backup läuft automatisch.

---

### Option 2: PowerShell ScheduledJob

```powershell
# Einmalig ausführen (als Administrator)
$trigger = New-JobTrigger -Daily -At 2am
$action = {
    Set-Location "C:\Cursor_Projekte\Tierdokumentation"
    .\backup-to-github.ps1
}
Register-ScheduledJob -Name "TierdokuBackup" -ScriptBlock $action -Trigger $trigger

# Status prüfen
Get-ScheduledJob -Name TierdokuBackup

# Logs anzeigen
Get-Job -Name TierdokuBackup | Get-JobLog
```

---

### Option 3: Git Hooks (Bei jedem Commit)

Automatische Tests vor jedem Push:

1. **Pre-push Hook kopieren:**
   ```powershell
   Copy-Item .git-hooks/pre-push .git/hooks/pre-push
   ```

2. **Ausführbar machen (Linux/Git Bash):**
   ```bash
   chmod +x .git/hooks/pre-push
   ```

3. **Fertig!** Tests laufen automatisch vor jedem Push.

---

## 📅 Backup-Häufigkeit

### Empfohlene Frequenz:

| Art | Häufigkeit | Grund |
|-----|-----------|-------|
| **Aktiv entwickeln** | Täglich 22:00 Uhr | Alle Änderungen sichern |
| **Produktiv** | Wöchentlich Sonntag 02:00 Uhr | Wöchentliche Version |
| **Wartung** | Monatlich | Monatliche Sicherung |

---

## 🔍 Backup-Status prüfen

### Lokaler Status:
```powershell
git status
git log --oneline -5
```

### GitHub Status:
```powershell
git remote -v
git fetch origin
git log origin/main --oneline -5
```

### GitHub Web Interface:
Öffne: https://github.com/AdrianPeterSchmitt/tierdoku_2025

---

## 🛡️ Wichtige Hinweise

### Was wird gesichert:
✅ Alle Source-Code-Dateien  
✅ Konfigurationsdateien  
✅ Tests  
✅ Dokumentation  
✅ Migrations  

### Was wird NICHT gesichert:
❌ `.env` Dateien (sensible Daten)  
❌ `vendor/` (npm/composer install)  
❌ `node_modules/`  
❌ Datenbank-Dateien (.sqlite)  
❌ Log-Dateien  
❌ Build-Artefakte  

### Sicherheit:
- `.env` steht in `.gitignore`
- Keine Passwörter im Repository
- GitHub bietet automatische Backups

---

## 🚨 Troubleshooting

### Problem: "Authentication failed"

**Lösung:**
```powershell
# Personal Access Token erstellen:
# 1. GitHub → Settings → Developer settings → Personal access tokens
# 2. Token erstellen mit "repo" Permission
# 3. Bei Push eingeben

# Oder SSH verwenden:
git remote set-url origin git@github.com:AdrianPeterSchmitt/tierdoku_2025.git
```

### Problem: "Remote rejected"

**Lösung:**
```powershell
# Force push (Vorsicht!):
git push -f origin main

# Oder besser: Pull zuerst
git pull --rebase origin main
git push origin main
```

### Problem: Task läuft nicht

**Lösung:**
```powershell
# Logs prüfen
Get-ScheduledTask -TaskName Tierdoku*

# Manuell ausführen
Start-ScheduledTask -TaskName "Tierdoku GitHub Backup"
```

---

## 📊 Backup-Historie

### Aktuelle Backups:

```powershell
git log --oneline --all
```

### Backup-Metriken:

```powershell
# Commits pro Tag
git log --pretty=format:"%ad" --date=short | sort | uniq -c

# Größe des Repositories
git count-objects -vH
```

---

## 🔗 Links

- **Repository:** https://github.com/AdrianPeterSchmitt/tierdoku_2025
- **GitHub Actions:** https://github.com/AdrianPeterSchmitt/tierdoku_2025/actions
- **Commits:** https://github.com/AdrianPeterSchmitt/tierdoku_2025/commits/main

---

## ✅ Setup Checkliste

- [x] Git Repository initialisiert
- [x] Erstes Commit erstellt
- [x] Auf GitHub gepusht
- [x] .gitignore konfiguriert
- [ ] Windows Task Scheduler eingerichtet
- [ ] Pre-push Hook installiert
- [ ] Backup-Script getestet
- [ ] Dokumentation gelesen

---

**🎉 Viel Erfolg mit deinen Backups!**

