# Marche International Food — Sistema Tracciabilità HACCP

Sistema gestionale web per la tracciabilità alimentare conforme HACCP di **Marche International Food S.R.L.** Sostituisce i fogli Excel con un'applicazione Laravel + Vue 3.

## Funzionalità

| Area | Funzione |
|------|----------|
| **Screen 1 — Alimenti** | Acquisti (DDT/Fatture fornitori) con lotti, Vendite clienti, Bolle Reso, Note di Credito |
| **Screen 2 — Imballaggi** | Lotti imballaggi primari (MOCA) e detergenti certificati |
| **Screen 3 — Produzione** | Schede di produzione con ricette, Flussi HACCP, Produzioni con tracciabilità lotti |
| **Anagrafica** | Fornitori, Clienti, Prodotti, Materie Prime, Destinazione Ingredienti |
| **Import** | Migrazione dati storici via CSV |

## Requisiti

- PHP 8.5+
- PostgreSQL 18+
- Node.js 20+
- Composer

## Installazione

```bash
# 1. Installa dipendenze PHP e JS
composer install
npm install

# 2. Configura l'ambiente
cp .env.example .env
php artisan key:generate

# 3. Configura il database in .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=marche_food
DB_USERNAME=postgres
DB_PASSWORD=la_tua_password

# 4. Esegui le migrazioni
php artisan migrate

# 5. (Opzionale) Carica i dati di esempio
php artisan db:seed --class=ClienteSeeder
php artisan db:seed --class=Screen3Seeder

# 6. Compila gli asset frontend
npm run build

# 7. Avvia il server
php artisan serve
```

Aprire il browser su `http://localhost:8000`.

## Sviluppo

```bash
# Dopo ogni modifica ai file Vue/JS
npm run build

# Poi Ctrl+Shift+R nel browser per forzare il refresh degli asset
```

> **Nota Windows:** Usare sempre `npm run build`. Il dev-server con hot-reload non è affidabile su Windows.

## Import Dati Storici

Dalla sezione **Utilità → Import Dati Storici** è possibile caricare i dati degli anni precedenti via CSV.

1. Scaricare il **template CSV** dalla pagina Import
2. Compilarlo rispettando il formato (separatore `;`, date `DD/MM/YYYY`)
3. Caricare il file e cliccare **Importa**

**Formato acquisti:**
```
fornitore_codice;numero_documento;data_documento;tipo_documento;nome_prodotto;quantita_kg;quantita_pz;lotto;lotto_esterno;scadenza;data_in;note_documento
```

**Formato vendite:**
```
cliente_codice;numero_documento;data_documento;tipo_documento;nome_prodotto;pezzatura_gr;quantita_kg;quantita_pz;lotto;lotto_esterno;scadenza;note_documento
```

## Tracciabilità HACCP

Il sistema supporta la tracciabilità bidirezionale dei lotti:

- **Forward:** lotto acquistato → produzione → vendita → cliente
- **Backward:** prodotto venduto → produzione → lotti materie prime → fornitori

Implementata tramite `produzioni_materie_prime`, che collega ogni run di produzione ai lotti specifici di `acquisti_righe` utilizzati.

## Stack Tecnico

- **Backend:** Laravel 13 + PHP 8.5
- **Frontend:** Vue 3 + Inertia.js v3 (no API separata)
- **UI:** PrimeVue (tema Aura)
- **Database:** PostgreSQL 18
- **Build:** Vite

Per i dettagli architetturali vedere [ARCHITECTURE.md](ARCHITECTURE.md).
Per lo schema SQL completo vedere [schema.sql](schema.sql).
