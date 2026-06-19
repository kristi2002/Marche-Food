-- =============================================================================
-- Marche International Food S.R.L. — Database Schema (PostgreSQL 18)
-- Sistema di tracciabilità alimentare HACCP
-- =============================================================================

-- ---------------------------------------------------------------------------
-- ANAGRAFICA BASE
-- ---------------------------------------------------------------------------

CREATE TABLE unita_misura (
    id          BIGSERIAL PRIMARY KEY,
    codice      VARCHAR(20)  NOT NULL UNIQUE,
    descrizione VARCHAR(100),
    tipo        VARCHAR(5)   CHECK (tipo IN ('kg', 'lt', 'n'))
);

CREATE TABLE flussi_produzione (
    id        BIGSERIAL PRIMARY KEY,
    numero    INTEGER      NOT NULL,
    nome      VARCHAR(100) NOT NULL,
    controllo VARCHAR(100),     -- punto di controllo CCP (es. "Temp. Sonda > 75°C")
    misura    VARCHAR(50)       -- etichetta del valore (es. "Temperatura:", "Tempo:")
);

CREATE TABLE fornitori (
    id                   BIGSERIAL PRIMARY KEY,
    codice               VARCHAR(20)  UNIQUE,
    ragione_sociale      VARCHAR(200) NOT NULL,
    tipo                 VARCHAR(30)  NOT NULL
                             CHECK (tipo IN ('alimentare','imballaggio_primario','detergente_secondario')),
    piva                 VARCHAR(20),
    indirizzo            TEXT,
    email                VARCHAR(100),
    telefono             VARCHAR(30),
    haccp_certificato    BOOLEAN      NOT NULL DEFAULT FALSE,
    haccp_scadenza       DATE,
    certificazioni_note  TEXT,
    moca_certificato     BOOLEAN      NOT NULL DEFAULT FALSE,
    moca_numero          VARCHAR(50),
    attivo               BOOLEAN      NOT NULL DEFAULT TRUE,
    note                 TEXT,
    created_at           TIMESTAMPTZ  DEFAULT NOW(),
    updated_at           TIMESTAMPTZ  DEFAULT NOW()
);

CREATE TABLE clienti (
    id               BIGSERIAL PRIMARY KEY,
    codice_cliente   VARCHAR(20)  NOT NULL UNIQUE,
    ragione_sociale  VARCHAR(200) NOT NULL,
    piva             VARCHAR(20),
    indirizzo        TEXT,
    email            VARCHAR(100),
    telefono         VARCHAR(30),
    attivo           BOOLEAN      NOT NULL DEFAULT TRUE,
    note             TEXT,
    created_at       TIMESTAMPTZ  DEFAULT NOW(),
    updated_at       TIMESTAMPTZ  DEFAULT NOW()
);

CREATE TABLE prodotti (
    id               BIGSERIAL PRIMARY KEY,
    codice_prodotto  VARCHAR(20)  NOT NULL UNIQUE,
    nome             VARCHAR(200) NOT NULL,
    pezzatura_valore NUMERIC(10,3),
    pezzatura_um     VARCHAR(10),
    um_id            BIGINT REFERENCES unita_misura(id),
    attivo           BOOLEAN      NOT NULL DEFAULT TRUE,
    note             TEXT,
    created_at       TIMESTAMPTZ  DEFAULT NOW(),
    updated_at       TIMESTAMPTZ  DEFAULT NOW()
);

CREATE TABLE materie_prime (
    id        BIGSERIAL PRIMARY KEY,
    codice    INTEGER UNIQUE,
    nome      VARCHAR(200) NOT NULL,
    um_id     BIGINT REFERENCES unita_misura(id),
    created_at TIMESTAMPTZ DEFAULT NOW(),
    updated_at TIMESTAMPTZ DEFAULT NOW()
);

-- Quali materie prime possono entrare in quali prodotti
CREATE TABLE destinazione_ingredienti (
    id                BIGSERIAL PRIMARY KEY,
    prodotto_id       BIGINT NOT NULL REFERENCES prodotti(id),
    materia_prima_id  BIGINT NOT NULL REFERENCES materie_prime(id),
    UNIQUE (prodotto_id, materia_prima_id)
);

-- ---------------------------------------------------------------------------
-- SCREEN 1 — ALIMENTI: ACQUISTI
-- ---------------------------------------------------------------------------

CREATE TABLE acquisti (
    id               BIGSERIAL PRIMARY KEY,
    fornitore_id     BIGINT       NOT NULL REFERENCES fornitori(id),
    numero_documento VARCHAR(50)  NOT NULL,
    data_documento   DATE         NOT NULL,
    tipo_documento   VARCHAR(10)  NOT NULL DEFAULT 'DDT',
    note             TEXT,
    created_at       TIMESTAMPTZ  DEFAULT NOW(),
    updated_at       TIMESTAMPTZ  DEFAULT NOW()
);

CREATE TABLE acquisti_righe (
    id                BIGSERIAL PRIMARY KEY,
    acquisto_id       BIGINT        NOT NULL REFERENCES acquisti(id) ON DELETE CASCADE,
    prodotto_id       BIGINT        REFERENCES prodotti(id),
    nome_prodotto     VARCHAR(200),
    um                VARCHAR(10),
    quantita_pz       NUMERIC(10,3),
    quantita_kg       NUMERIC(10,3) NOT NULL,
    lotto             VARCHAR(100),
    lotto_esterno     VARCHAR(100),
    scadenza          DATE,
    data_in           DATE          NOT NULL,
    data_out          DATE,
    nota_credito_ref  VARCHAR(50),
    created_at        TIMESTAMPTZ   DEFAULT NOW(),
    updated_at        TIMESTAMPTZ   DEFAULT NOW(),
    -- lotto e lotto_esterno sono mutuamente esclusivi
    CONSTRAINT lotto_xor CHECK (NOT (lotto IS NOT NULL AND lotto_esterno IS NOT NULL))
);

-- ---------------------------------------------------------------------------
-- SCREEN 1 — ALIMENTI: VENDITE
-- ---------------------------------------------------------------------------

CREATE TABLE vendite (
    id               BIGSERIAL PRIMARY KEY,
    cliente_id       BIGINT       NOT NULL REFERENCES clienti(id),
    numero_documento VARCHAR(50)  NOT NULL,
    data_documento   DATE         NOT NULL,
    tipo_documento   VARCHAR(5)   NOT NULL CHECK (tipo_documento IN ('DDT','FI','NC')),
    note             TEXT,
    created_at       TIMESTAMPTZ  DEFAULT NOW(),
    updated_at       TIMESTAMPTZ  DEFAULT NOW()
);

CREATE TABLE vendite_righe (
    id            BIGSERIAL PRIMARY KEY,
    vendita_id    BIGINT        NOT NULL REFERENCES vendite(id) ON DELETE CASCADE,
    prodotto_id   BIGINT        REFERENCES prodotti(id),
    nome_prodotto VARCHAR(200),
    pezzatura_gr  NUMERIC(10,3),
    um            VARCHAR(10),
    quantita_pz   NUMERIC(10,3),
    quantita_kg   NUMERIC(10,3) NOT NULL,
    lotto         VARCHAR(100),
    lotto_esterno VARCHAR(100),
    scadenza      DATE,
    created_at    TIMESTAMPTZ   DEFAULT NOW(),
    updated_at    TIMESTAMPTZ   DEFAULT NOW(),
    CONSTRAINT lotto_xor CHECK (NOT (lotto IS NOT NULL AND lotto_esterno IS NOT NULL))
);

CREATE TABLE bolle_reso (
    id               BIGSERIAL PRIMARY KEY,
    vendita_riga_id  BIGINT        NOT NULL REFERENCES vendite_righe(id),
    numero_bolla     VARCHAR(50),
    quantita_pz      NUMERIC(10,3),
    quantita_kg      NUMERIC(10,3) NOT NULL,
    data_reso        DATE          NOT NULL,
    note             TEXT,
    created_at       TIMESTAMPTZ   DEFAULT NOW(),
    updated_at       TIMESTAMPTZ   DEFAULT NOW()
);

CREATE TABLE note_credito (
    id               BIGSERIAL PRIMARY KEY,
    vendita_id       BIGINT        REFERENCES vendite(id),
    bolla_reso_id    BIGINT        REFERENCES bolle_reso(id),
    numero_documento VARCHAR(50)   NOT NULL,
    data_documento   DATE          NOT NULL,
    importo          NUMERIC(12,2),
    note             TEXT,
    created_at       TIMESTAMPTZ   DEFAULT NOW(),
    updated_at       TIMESTAMPTZ   DEFAULT NOW()
);

-- ---------------------------------------------------------------------------
-- SCREEN 2 — IMBALLAGGI
-- ---------------------------------------------------------------------------

CREATE TABLE lotti_imballaggi_primari (
    id              BIGSERIAL PRIMARY KEY,
    fornitore_id    BIGINT        NOT NULL REFERENCES fornitori(id),
    codice_articolo VARCHAR(50),
    componente      VARCHAR(200)  NOT NULL,
    um              VARCHAR(10),
    quantita        NUMERIC(10,3),
    lotto           VARCHAR(100),
    numero_ddt      VARCHAR(50),
    data_in         DATE          NOT NULL,
    data_out        DATE,
    note            TEXT,
    created_at      TIMESTAMPTZ   DEFAULT NOW(),
    updated_at      TIMESTAMPTZ   DEFAULT NOW()
);

CREATE TABLE lotti_detergenti (
    id              BIGSERIAL PRIMARY KEY,
    fornitore_id    BIGINT        NOT NULL REFERENCES fornitori(id),
    codice_articolo VARCHAR(50),
    componente      VARCHAR(200)  NOT NULL,
    um              VARCHAR(10),
    quantita        NUMERIC(10,3),
    lotto           VARCHAR(100),
    scadenza        DATE,
    numero_ddt      VARCHAR(50),
    data_in         DATE          NOT NULL,
    data_out        DATE,
    note            TEXT,
    created_at      TIMESTAMPTZ   DEFAULT NOW(),
    updated_at      TIMESTAMPTZ   DEFAULT NOW()
);

-- ---------------------------------------------------------------------------
-- SCREEN 3 — PRODUZIONE
-- ---------------------------------------------------------------------------

CREATE TABLE schede_produzione (
    id              BIGSERIAL PRIMARY KEY,
    prodotto_id     BIGINT       NOT NULL REFERENCES prodotti(id),
    modello         VARCHAR(20)  NOT NULL,
    revisione       INTEGER      NOT NULL DEFAULT 0,
    data_revisione  DATE         NOT NULL,
    ha_marinatura   BOOLEAN      NOT NULL DEFAULT FALSE,
    attiva          BOOLEAN      NOT NULL DEFAULT TRUE,
    note            TEXT,
    created_at      TIMESTAMPTZ  DEFAULT NOW(),
    updated_at      TIMESTAMPTZ  DEFAULT NOW(),
    UNIQUE (prodotto_id, revisione)
);

CREATE TABLE schede_produzione_flussi (
    id                BIGSERIAL PRIMARY KEY,
    scheda_id         BIGINT       NOT NULL REFERENCES schede_produzione(id) ON DELETE CASCADE,
    flusso_id         BIGINT       NOT NULL REFERENCES flussi_produzione(id),
    ordine            INTEGER      NOT NULL,
    valore_controllo  VARCHAR(100),
    tempo_minuti      INTEGER
);

CREATE TABLE ricette (
    id                BIGSERIAL PRIMARY KEY,
    scheda_id         BIGINT        NOT NULL REFERENCES schede_produzione(id) ON DELETE CASCADE,
    materia_prima_id  BIGINT        NOT NULL REFERENCES materie_prime(id),
    fornitore_id      BIGINT        REFERENCES fornitori(id),
    percentuale       NUMERIC(6,3),
    grammi_per_kg     NUMERIC(8,3),
    um                VARCHAR(10),
    ordine            INTEGER
);

CREATE TABLE ricette_marinature (
    id                BIGSERIAL PRIMARY KEY,
    scheda_id         BIGINT        NOT NULL REFERENCES schede_produzione(id) ON DELETE CASCADE,
    materia_prima_id  BIGINT        NOT NULL REFERENCES materie_prime(id),
    fornitore_id      BIGINT        REFERENCES fornitori(id),
    litri_grammi      NUMERIC(8,3),
    um                VARCHAR(10),
    ordine            INTEGER
);

CREATE TABLE produzioni (
    id                    BIGSERIAL PRIMARY KEY,
    scheda_id             BIGINT        NOT NULL REFERENCES schede_produzione(id),
    lotto_produzione      VARCHAR(100)  NOT NULL UNIQUE,
    data_produzione       DATE          NOT NULL,
    quantita_prodotta_kg  NUMERIC(10,3),
    operatore             VARCHAR(100),
    note                  TEXT,
    created_at            TIMESTAMPTZ   DEFAULT NOW(),
    updated_at            TIMESTAMPTZ   DEFAULT NOW()
);

-- Cuore della tracciabilità HACCP: lega ogni run di produzione ai lotti
-- esatti di acquisto utilizzati per ogni ingrediente.
-- Tracciabilità inversa: acquisti_righe → produzioni_materie_prime
--   → produzioni → vendite_righe → vendite → clienti
CREATE TABLE produzioni_materie_prime (
    id                BIGSERIAL PRIMARY KEY,
    produzione_id     BIGINT        NOT NULL REFERENCES produzioni(id) ON DELETE CASCADE,
    acquisto_riga_id  BIGINT        NOT NULL REFERENCES acquisti_righe(id),
    materia_prima_id  BIGINT        NOT NULL REFERENCES materie_prime(id),
    quantita_kg       NUMERIC(10,3) NOT NULL
);

-- =============================================================================
-- INDICI
-- =============================================================================

CREATE INDEX idx_acquisti_fornitore        ON acquisti(fornitore_id);
CREATE INDEX idx_acquisti_data             ON acquisti(data_documento);
CREATE INDEX idx_acquisti_righe_lotto      ON acquisti_righe(lotto);
CREATE INDEX idx_acquisti_righe_lotto_est  ON acquisti_righe(lotto_esterno);
CREATE INDEX idx_acquisti_righe_data_in    ON acquisti_righe(data_in);
CREATE INDEX idx_vendite_cliente           ON vendite(cliente_id);
CREATE INDEX idx_vendite_data              ON vendite(data_documento);
CREATE INDEX idx_vendite_righe_lotto       ON vendite_righe(lotto);
CREATE INDEX idx_prodotti_codice           ON prodotti(codice_prodotto);
CREATE INDEX idx_produzioni_scheda         ON produzioni(scheda_id);
CREATE INDEX idx_produzioni_lotto          ON produzioni(lotto_produzione);
CREATE INDEX idx_prod_mp_produzione        ON produzioni_materie_prime(produzione_id);
CREATE INDEX idx_prod_mp_acquisto          ON produzioni_materie_prime(acquisto_riga_id);
