# Datenbank-Zugriff: Eloquent vs. Capsule/Query Builder

## 🎯 Einfache Erklärung

### **Eloquent = "OOP-Wrapper"** (Modern & Empfohlen)
Eloquent ist wie ein "Dolmetscher" zwischen PHP-Objekten und der Datenbank.

### **Capsule/Query Builder = "Direkter Weg"** (Traditional)
Capsule ist wie SQL-Code mit PHP-Methoden.

---

## 📚 Vergleich mit Beispielen

### Beispiel 1: User mit ID 1 finden

#### ❌ Capsule (Query Builder) - "Alte Methode"
```php
use Illuminate\Database\Capsule\Manager as Capsule;

$user = Capsule::table('users')
    ->where('id', 1)
    ->first();

echo $user->username;
```

**Kommentar:**
- Du schreibst "query" Code
- Musst Tabellennamen kennen
- Keine Objekt-Orientierung
- Fehleranfällig bei Tippfehlern

---

#### ✅ Eloquent (ORM) - "Moderne Methode"
```php
use App\Models\User;

$user = User::find(1);

echo $user->username;
```

**Kommentar:**
- Viele weniger Code
- Nutzt die `User` Model-Klasse
- Type-safe (PHP weiß, was ein User ist)
- Automatische Validierung

---

### Beispiel 2: Einen User erstellen

#### ❌ Capsule - Direkter Weg
```php
Capsule::table('users')->insert([
    'username' => 'max',
    'email' => 'max@example.com',
    'password_hash' => password_hash('secret', PASSWORD_DEFAULT),
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
]);
```

**Probleme:**
- Manuell alle Felder
- `created_at` muss selbst gesetzt werden
- Keine Validierung
- Keine Fehlerprüfung

---

#### ✅ Eloquent - Mit Model
```php
$user = User::create([
    'username' => 'max',
    'email' => 'max@example.com',
    'password_hash' => password_hash('secret', PASSWORD_DEFAULT),
]);
```

**Vorteile:**
- `created_at` und `updated_at` automatisch!
- Validierung im Model
- Fehlerbehandlung
- Sauberer Code

---

### Beispiel 3: User mit seinen Standort laden

#### ❌ Capsule - 2 Separate Queries
```php
$user = Capsule::table('users')
    ->where('id', 1)
    ->first();

$standort = Capsule::table('standort')
    ->where('standort_id', $user->standort_id)
    ->first();

echo $user->username . " arbeitet bei " . $standort->name;
```

**Probleme:**
- 2 Datenbank-Abfragen
- Langsam bei vielen Daten
- Komplexer Code

---

#### ✅ Eloquent - Mit Relationship
```php
$user = User::with('standort')->find(1);

echo $user->username . " arbeitet bei " . $user->standort->name;
```

**Vorteile:**
- Nur 1 optimierte Datenbank-Abfrage
- Auto-Loading mit `with()`
- Code liest sich wie natürliche Sprache
- Kann sogar lazy-loaded werden: `$user->standort->name`

---

## 🔍 Was ist jetzt was?

### **Eloquent Model**
Eine PHP-Klasse, die eine Datenbank-Tabelle repräsentiert.

```php
// app/Models/User.php
class User extends Model
{
    protected $table = 'users';
    // ...
}
```

### **Capsule Manager**
Ein "Query Builder" - du baust SQL-Queries mit PHP-Methoden.

```php
use Illuminate\Database\Capsule\Manager as Capsule;

Capsule::table('users')->get(); // SELECT * FROM users
```

---

## 🎯 Zusammenfassung

| Feature | Capsule/Query Builder | Eloquent ORM |
|---------|---------------------|-------------|
| **Code-Menge** | Viel Code | Wenig Code |
| **Objekt-Orientierung** | ❌ Nein | ✅ Ja |
| **Relationships** | ❌ Manuell | ✅ Automatisch |
| **Timestamps** | ❌ Manuell | ✅ Automatisch |
| **Validierung** | ❌ Manuell | ✅ Im Model |
| **Performance** | ⚠️ N+1 Problem | ✅ Optimiert |
| **Type Safety** | ❌ Weak | ✅ Strong |
| **Geeignet für** | Einfache Queries | Alles |

---

## 📍 In unserer App

### Wir nutzen **PRIMÄR Eloquent**:

```php
// ✅ GUT - In allen Services
$user = User::find(1);
$kremation = Kremation::create([...]);
$standort = Standort::where('name', 'Laudenbach')->first();
```

### Wir nutzen **NUR für Transaktionen Capsule**:

```php
// ✅ OK - Nur für DB-Transaktionen
return Capsule::transaction(function () {
    $kremation = Kremation::create([...]);
    $kremation->tierarten()->sync([...]);
    return $kremation;
});
```

**Warum?**
- `Capsule::transaction()` ist die einzige einfache Methode, um DB-Transaktionen zu wrappen
- Eloquent hat keine native `Transaction::wrap()` Methode
- Transaktionen sind wichtig, damit bei Fehlern nichts kaputtgeht

---

## 💡 Praxis-Beispiel aus unserer App

### Kremation erstellen:

#### Mit Eloquent (so machen wir es jetzt):
```php
public function create(array $data, User $user): Kremation
{
    return Capsule::transaction(function () use ($data, $user) {
        // ✅ Eloquent Models
        $standort = Standort::where('name', $data['Standort'])->first();
        $herkunft = Herkunft::firstOrCreate(['name' => $data['Herkunft']]);
        
        // ✅ Eloquent Create
        $kremation = Kremation::create([
            'eingangsdatum' => $data['Eingangsdatum'],
            'gewicht' => $gewicht,
            'standort_id' => $standort->standort_id,
            'herkunft_id' => $herkunft->herkunft_id,
            'created_by' => $user->id,
        ]);
        
        // ✅ Eloquent Relationship Sync
        $kremation->tierarten()->sync($syncData);
        
        return $kremation;
    });
}
```

**Das ist CLEAN! 🎉**

---

## 🎓 Lern-Tipp

**Eloquent nutzen = Bessere Praxis**

1. **Weniger Code**
2. **Mehr Sicherheit** (SQL Injection Protection)
3. **Weniger Fehler**
4. **Besser lesbar**
5. **Performance-Optimiert**

**Capsule nur wenn nötig:**
- Sehr komplexe Queries mit Subqueries
- DB-Transaktionen
- Migrationen

---

## ✅ Fazit

Unsere App ist **optimal strukturiert**:

- ✅ **90% Eloquent** (modern, sauber, sicher)
- ✅ **10% Capsule** (nur wo nötig: Transaktionen & Migrations)
- ✅ **Models für alle Tabellen** (User, Kremation, Standort, etc.)
- ✅ **Relationships definiert** (belongsTo, hasMany, belongsToMany)

**Das ist BEST PRACTICE! 🏆**


