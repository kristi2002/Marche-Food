# Marche International Food — Architettura del Sistema

## Panoramica

Sistema di tracciabilità alimentare HACCP per Marche International Food S.R.L. che sostituisce i fogli Excel. Gestisce l'intera catena: acquisto materie prime → produzione → vendita, con tracciabilità lotto per lotto conforme a HACCP.

## Stack Tecnologico

| Layer | Tecnologia |
|-------|-----------|
| Backend | Laravel 13 (PHP 8.5) |
| Frontend | Vue 3 + Inertia.js v3 |
| UI Components | PrimeVue (tema Aura) |
| Database | PostgreSQL 18 |
| Build | Vite 8 |
| Session | File-based (`SESSION_DRIVER=file`) |

**Pattern architetturale:** Monolite full-stack con Inertia.js come bridge SSR. Non c'è una API separata — i controller Laravel restituiscono componenti Vue via `Inertia::render()` con i dati pre-caricati come props.

## Struttura delle Schermate

### Anagrafica (trasversale)
- **Fornitori** — tre tipologie: `alimentare`, `imballaggio_primario`, `detergente_secondario`. Include certificazioni HACCP e MOCA.
- **Clienti** — anagrafica clienti con codice univoco.
- **Prodotti** — prodotti finiti con codice univoco e pezzatura.
- **Materie Prime** — ingredienti e semi-lavorati usati nelle ricette.
- **Destinazione Ingredienti** — tabella di giunzione: quali materie prime entrano in quali prodotti.

### Screen 1 — Alimenti (SCHEDE_FATTURE_LOTTI_ALIMENTI)
La schermata più usata. Traccia il movimento fisico dei prodotti alimentari.

- **Acquisti** — master-detail: testata (DDT/Fattura fornitore) + righe con lotto, scadenza, data entrata/uscita
- **Vendite** — master-detail: testata (DDT/FI/NC cliente) + righe con pezzatura, lotto
- **Bolle Reso** — reso merce da cliente, collegato a una riga di vendita
- **Note di Credito** — documento contabile emesso a seguito di un reso

### Screen 2 — Imballaggi (SCHEDE_FATTURE_LOTTI_IMBALLAGGI)
- **Imballaggi Primari** — materiali a contatto diretto con l'alimento (MOCA certificati): vaschette, film termosaldante, gas
- **Detergenti** — sanificanti certificati per uso in ambiente alimentare

### Screen 3 — Produzione (SCHEDA_UNICA)
- **Schede di Produzione** — modello + revisione. Ogni scheda contiene ricetta (% ingredienti) e ricetta marinatura (per prodotti marinati). Formato: `MODELLO REV N del DD/MM/YYYY`.
- **Flussi di Lavorazione** — fasi HACCP (ricezione → stoccaggio → lavorazione → confezionamento → spedizione) con punti di controllo CCP e limiti critici.
- **Produzioni** — ogni run di produzione con lotto univoco. Lega la scheda ai lotti specifici di materie prime utilizzati (tracciabilità HACCP).

### Utilità
- **Import Dati Storici** — caricamento CSV (separatore `;`) per migrazione dati 2018/2019 → oggi

## Schema del Database

```
fornitori ────────────────────────────┐
                                      │
fornitori ──► acquisti ──► acquisti_righe ──► produzioni_materie_prime ──► produzioni
                                      │                                         │
clienti ───► vendite ──► vendite_righe                                   schede_produzione
                              │                                                 │
                          bolle_reso                          prodotti ◄─────────┤
                              │                                    │             │
                         note_credito                    destinazione_ingredienti │
                                                                                 ├──► ricette ──► materie_prime
fornitori ──► lotti_imballaggi_primari                                           └──► ricette_marinature
fornitori ──► lotti_detergenti                                       schede_produzione_flussi ──► flussi_produzione
```

### Tabelle Chiave

#### `acquisti_righe` — Lotti in Entrata
| Campo | Tipo | Note |
|-------|------|------|
| `lotto` | VARCHAR(100) | Lotto interno Marche Food |
| `lotto_esterno` | VARCHAR(100) | Lotto del produttore originale |
| `scadenza` | DATE | Scadenza del prodotto |
| `data_in` | DATE | DATA IN-LOTTO: arrivo in struttura |
| `data_out` | DATE | DATA OUT-LOTTO: lotto completamente esaurito |
| `nota_credito_ref` | VARCHAR(50) | Riferimento nota credito se applicabile |

**Regola business:** Se `lotto` è presente, `lotto_esterno` non viene inserito e viceversa. `quantita_kg` è sempre obbligatorio (conversione da pz se necessario).

#### `produzioni_materie_prime` — Core della Tracciabilità HACCP
Questa tabella è il cuore del sistema. Per ogni produzione, registra:
- Quale lotto di materia prima (→ `acquisti_righe`) è stato usato
- Quanti kg sono stati prelevati

Permette la tracciabilità inversa: dato un lotto in uscita, risalire ai fornitori e lotti di provenienza di ogni ingrediente.

## Pattern di Codice Ricorrenti

### Master-Detail Form
Tutte le form con righe (Acquisti, Vendite, Schede) seguono lo stesso pattern:
```js
const form = useForm({ ...testata, righe: [...] })

function submit() {
  const payload = {
    ...form.data(),
    data_documento: form.data_documento.toISOString().slice(0,10),
    righe: form.righe.map(r => ({ ...r, data_in: r.data_in?.toISOString().slice(0,10) }))
  }
  form.transform(() => payload).post('/acquisti')
}
```
Le date vengono serializzate esplicitamente perché `useForm` le mantiene come oggetti JS `Date`.

### Flash Messages
`HandleInertiaRequests.php` condivide `flash.success` e `flash.error` → `AppLayout.vue` le intercetta con `watchEffect` e le mostra via PrimeVue Toast.

### Filtro Tipo Fornitore
I fornitori hanno un campo `tipo` (`alimentare | imballaggio_primario | detergente_secondario`). I controller filtrano per tipo nel dropdown:
- `AcquistoController` → `tipo='alimentare'`
- `ImballaggioController` → `tipo='imballaggio_primario'` o `tipo='detergente_secondario'`

## Struttura File

```
app/
  Http/Controllers/
    AcquistoController.php          # Screen 1 — acquisti
    VenditaController.php           # Screen 1 — vendite
    BollaResoController.php         # Screen 1 — bolle reso
    NotaCreditoController.php       # Screen 1 — note credito
    ImballaggioController.php       # Screen 2 — imballaggi (tabbed, 11 metodi)
    SchedaProduzioneController.php  # Screen 3 — schede
    ProduzioneController.php        # Screen 3 — produzioni
    FlussoProduzioneController.php  # Screen 3 — flussi HACCP
    FornitoreController.php         # Anagrafica
    ClienteController.php           # Anagrafica
    ProdottoController.php          # Anagrafica
    MateriaPrimaController.php      # Anagrafica
    DestinazioneIngredientiController.php
    ImportController.php            # CSV import dati storici

resources/js/
  Layouts/AppLayout.vue             # Shell con sidebar e Toast
  Pages/
    Acquisti/                       # Index + Form (master-detail)
    Vendite/                        # Index + Form (master-detail)
    BolleReso/                      # Index + Form
    NoteCredito/                    # Index + Form
    Imballaggi/                     # Index (Tabs) + Form primari + Form detergenti
    Schede/                         # Index + Form (ricetta + marinatura + flussi)
    Produzioni/                     # Index + Form (tracciabilità lotti)
    Flussi/                         # Index (inline CRUD)
    DestinazioneIngredienti/        # Index (inline)
    Import/                         # Index (upload CSV)
    Fornitori/                      # Index + Form
    Clienti/                        # Index + Form
    Prodotti/                       # Index + Form
    MateriePrime/                   # Index + Form

database/
  migrations/                       # 21 migrazioni in batch 1
  seeders/
    ClienteSeeder.php               # 5 clienti test
    Screen3Seeder.php               # U.M., 13 materie prime, 5 prodotti
```

## Import Dati Storici

Il controller `ImportController` accetta CSV con separatore `;` (non virgola, per compatibilità con nomi prodotto che contengono virgole).

**Acquisti CSV:** `fornitore_codice;numero_documento;data_documento;tipo_documento;nome_prodotto;quantita_kg;quantita_pz;lotto;lotto_esterno;scadenza;data_in;note_documento`

**Vendite CSV:** `cliente_codice;numero_documento;data_documento;tipo_documento;nome_prodotto;pezzatura_gr;quantita_kg;quantita_pz;lotto;lotto_esterno;scadenza;note_documento`

Date supportate: `DD/MM/YYYY` o `YYYY-MM-DD`. Righe con stesso documento vengono raggruppate in un solo acquisto/vendita.

## Note Operative (Windows)

- **Build assets:** Usare sempre `npm run build` (produzione). `npm run dev` con hot-reload non è affidabile su Windows con Vite.
- **Dopo modifiche Vue:** `npm run build` poi hard refresh (`Ctrl+Shift+R`) nel browser per evitare cache su `public/build`.
- **OPcache:** Disabilitato in `php.ini` (ambiente sviluppo).
- **Session:** `SESSION_DRIVER=file` — nessun Redis necessario.
