<template>
  <AppLayout>
    <div class="dashboard">

      <!-- Header -->
      <div class="dashboard-header">
        <div>
          <h1 class="dashboard-title">Dashboard</h1>
          <p class="dashboard-subtitle">
            Benvenuto, <strong>{{ auth?.name }}</strong> —
            <span class="role-label" :class="isAdmin ? 'role-admin' : 'role-operator'">
              {{ isAdmin ? 'Amministratore' : 'Operatore' }}
            </span>
          </p>
        </div>
        <div class="header-date">{{ oggi }}</div>
      </div>

      <!-- Stat cards -->
      <div class="stat-grid">
        <div class="stat-card">
          <div class="stat-icon stat-green"><i class="pi pi-download" /></div>
          <div class="stat-body">
            <div class="stat-num">{{ stats.acquisti_totali }}</div>
            <div class="stat-label">Acquisti totali</div>
            <div class="stat-sub">+{{ stats.acquisti_mese }} questo mese</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon stat-blue"><i class="pi pi-upload" /></div>
          <div class="stat-body">
            <div class="stat-num">{{ stats.vendite_totali }}</div>
            <div class="stat-label">Vendite totali</div>
            <div class="stat-sub">+{{ stats.vendite_mese }} questo mese</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon stat-purple"><i class="pi pi-cog" /></div>
          <div class="stat-body">
            <div class="stat-num">{{ stats.produzioni_totali }}</div>
            <div class="stat-label">Produzioni totali</div>
            <div class="stat-sub">+{{ stats.produzioni_mese }} questo mese</div>
          </div>
        </div>
        <div class="stat-card" :class="stats.lotti_scaduti > 0 ? 'stat-danger' : stats.lotti_in_scadenza > 0 ? 'stat-warn' : ''">
          <div class="stat-icon" :class="stats.lotti_scaduti > 0 ? 'stat-red' : stats.lotti_in_scadenza > 0 ? 'stat-orange' : 'stat-green'">
            <i class="pi pi-calendar" />
          </div>
          <div class="stat-body">
            <div class="stat-num">{{ stats.lotti_scaduti + stats.lotti_in_scadenza }}</div>
            <div class="stat-label">Lotti da controllare</div>
            <div class="stat-sub">
              <span v-if="stats.lotti_scaduti > 0" class="text-red">{{ stats.lotti_scaduti }} scaduti</span>
              <span v-if="stats.lotti_scaduti > 0 && stats.lotti_in_scadenza > 0"> · </span>
              <span v-if="stats.lotti_in_scadenza > 0" class="text-orange">{{ stats.lotti_in_scadenza }} in scadenza (30gg)</span>
              <span v-if="stats.lotti_scaduti === 0 && stats.lotti_in_scadenza === 0" class="text-green">Nessuna urgenza</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Expiry alert -->
      <div v-if="lottiInScadenzaDettaglio.length > 0" class="alert-section">
        <div class="alert-header">
          <i class="pi pi-exclamation-triangle alert-icon" />
          <span>Lotti in scadenza nei prossimi 30 giorni</span>
        </div>
        <table class="alert-table">
          <thead>
            <tr>
              <th>Prodotto</th>
              <th>Fornitore</th>
              <th>Lotto</th>
              <th>Q.tà Kg</th>
              <th>Scadenza</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in lottiInScadenzaDettaglio" :key="r.id" :class="isScaduto(r.scadenza) ? 'row-scaduto' : ''">
              <td>{{ r.nome_prodotto }}</td>
              <td>{{ r.acquisto?.fornitore?.ragione_sociale ?? '—' }}</td>
              <td class="mono">{{ r.lotto || r.lotto_esterno || '—' }}</td>
              <td class="right">{{ r.quantita_kg != null ? Number(r.quantita_kg).toFixed(3) : '—' }}</td>
              <td class="center" :class="isScaduto(r.scadenza) ? 'text-red bold' : 'text-orange bold'">
                {{ formatDate(r.scadenza) }}
              </td>
              <td>
                <Link :href="`/acquisti/${r.acquisto_id}/edit`" class="edit-link">Vedi</Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Recent activity -->
      <div class="activity-grid">
        <!-- Recent acquisti -->
        <div class="activity-card">
          <div class="activity-header">
            <span><i class="pi pi-download" /> Ultimi acquisti</span>
            <Link href="/acquisti" class="see-all">Vedi tutti</Link>
          </div>
          <div v-if="ultimiAcquisti.length === 0" class="activity-empty">Nessun acquisto registrato.</div>
          <div v-for="a in ultimiAcquisti" :key="a.id" class="activity-row">
            <div class="activity-main">
              <Link :href="`/acquisti/${a.id}/edit`" class="activity-link">
                {{ a.tipo_documento ?? 'DDT' }} {{ a.numero_documento }}
              </Link>
              <span class="activity-sub">{{ a.fornitore?.ragione_sociale }}</span>
            </div>
            <div class="activity-date">{{ formatDate(a.data_documento) }}</div>
          </div>
        </div>

        <!-- Recent produzioni -->
        <div class="activity-card">
          <div class="activity-header">
            <span><i class="pi pi-cog" /> Ultime produzioni</span>
            <Link href="/produzioni" class="see-all">Vedi tutte</Link>
          </div>
          <div v-if="ultimiProduzioni.length === 0" class="activity-empty">Nessuna produzione registrata.</div>
          <div v-for="p in ultimiProduzioni" :key="p.id" class="activity-row">
            <div class="activity-main">
              <Link :href="`/produzioni/${p.id}/edit`" class="activity-link mono">
                {{ p.lotto_produzione }}
              </Link>
              <span class="activity-sub">{{ p.scheda?.prodotto?.nome }}</span>
            </div>
            <div class="activity-date">{{ formatDate(p.data_produzione) }}</div>
          </div>
        </div>
      </div>

      <!-- Quick links -->
      <div class="quick-links">
        <Link href="/acquisti" class="quick-card">
          <i class="pi pi-download card-icon" />
          <span class="card-label">Acquisti</span>
          <span class="card-desc">Registra DDT e fatture fornitori</span>
        </Link>
        <Link href="/vendite" class="quick-card">
          <i class="pi pi-upload card-icon" />
          <span class="card-label">Vendite</span>
          <span class="card-desc">Gestisci documenti di vendita</span>
        </Link>
        <Link href="/produzioni" class="quick-card">
          <i class="pi pi-cog card-icon" />
          <span class="card-label">Produzioni</span>
          <span class="card-desc">Registra lotti di produzione</span>
        </Link>
        <Link href="/tracciabilita" class="quick-card quick-card-highlight">
          <i class="pi pi-search card-icon" />
          <span class="card-label">Tracciabilità Lotti</span>
          <span class="card-desc">Ricerca forward e reverse per numero lotto</span>
        </Link>
        <Link href="/imballaggi" class="quick-card">
          <i class="pi pi-box card-icon" />
          <span class="card-label">Imballaggi</span>
          <span class="card-desc">Lotti imballaggi e detergenti</span>
        </Link>
        <Link v-if="isAdmin" href="/schede" class="quick-card">
          <i class="pi pi-file-edit card-icon" />
          <span class="card-label">Schede</span>
          <span class="card-desc">Schede di produzione HACCP</span>
        </Link>
        <Link v-if="isAdmin" href="/import" class="quick-card quick-card-secondary">
          <i class="pi pi-database card-icon" />
          <span class="card-label">Import</span>
          <span class="card-desc">Importa dati storici CSV</span>
        </Link>
      </div>

      <div class="info-banner">
        <i class="pi pi-shield" />
        <span>Sistema di tracciabilità alimentare conforme HACCP — Marche International Food S.r.l.</span>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
  stats:                    Object,
  ultimiAcquisti:           Array,
  ultimiProduzioni:         Array,
  lottiInScadenzaDettaglio: Array,
});

const page = usePage();
const auth = computed(() => page.props.auth?.user);
const isAdmin = computed(() => auth.value?.role === 'admin');

const oggi = new Date().toLocaleDateString('it-IT', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

function formatDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function isScaduto(d) {
  return d && new Date(d) < new Date();
}
</script>

<style scoped>
.dashboard { max-width: 1200px; }

/* ── Header ─────────────────────────────────────────────────────── */
.dashboard-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; }
.dashboard-title { font-size: 1.75rem; font-weight: 800; color: #1a3d28; margin: 0 0 0.3rem 0; }
.dashboard-subtitle { font-size: 0.95rem; color: #4b5563; margin: 0; }
.role-label { font-size: 0.72rem; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; padding: 0.15rem 0.5rem; border-radius: 99px; }
.role-admin    { background: #dcfce7; color: #166534; }
.role-operator { background: #fef9c3; color: #854d0e; }
.header-date { font-size: 0.82rem; color: #94a3b8; text-transform: capitalize; }

/* ── Stat cards ─────────────────────────────────────────────────── */
.stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card {
  background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
  padding: 1.25rem; display: flex; gap: 1rem; align-items: center;
  transition: box-shadow 0.15s;
}
.stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.07); }
.stat-card.stat-warn  { border-color: #fbbf24; background: #fffbeb; }
.stat-card.stat-danger { border-color: #f87171; background: #fff5f5; }
.stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
.stat-green  { background: #dcfce7; color: #16a34a; }
.stat-blue   { background: #dbeafe; color: #2563eb; }
.stat-purple { background: #ede9fe; color: #7c3aed; }
.stat-orange { background: #ffedd5; color: #ea580c; }
.stat-red    { background: #fee2e2; color: #dc2626; }
.stat-num   { font-size: 1.75rem; font-weight: 800; color: #1e293b; line-height: 1; }
.stat-label { font-size: 0.8rem; font-weight: 600; color: #64748b; margin-top: 0.15rem; }
.stat-sub   { font-size: 0.75rem; color: #94a3b8; margin-top: 0.1rem; }
.text-red    { color: #dc2626; }
.text-orange { color: #ea580c; }
.text-green  { color: #16a34a; }

/* ── Expiry alert ───────────────────────────────────────────────── */
.alert-section { background: #fffbeb; border: 1px solid #fbbf24; border-radius: 8px; margin-bottom: 1.5rem; overflow: hidden; }
.alert-header { display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; background: #fef3c7; font-size: 0.85rem; font-weight: 600; color: #92400e; }
.alert-icon { color: #d97706; }
.alert-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
.alert-table th { padding: 0.5rem 1rem; text-align: left; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #92400e; border-bottom: 1px solid #fde68a; }
.alert-table td { padding: 0.45rem 1rem; border-bottom: 1px solid #fef3c7; }
.alert-table tr:last-child td { border-bottom: none; }
.alert-table .row-scaduto td { background: #fff5f5; }
.mono  { font-family: monospace; font-size: 0.8rem; }
.right { text-align: right; }
.center { text-align: center; }
.bold  { font-weight: 700; }
.edit-link { color: #2a6941; font-size: 0.78rem; text-decoration: none; font-weight: 600; }
.edit-link:hover { text-decoration: underline; }

/* ── Activity ───────────────────────────────────────────────────── */
.activity-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
.activity-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
.activity-header { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; border-bottom: 1px solid #f1f5f9; font-size: 0.82rem; font-weight: 700; color: #374151; }
.activity-header i { margin-right: 0.3rem; color: #2a6941; }
.see-all { font-size: 0.75rem; color: #2a6941; text-decoration: none; font-weight: 600; }
.see-all:hover { text-decoration: underline; }
.activity-empty { padding: 1.25rem 1rem; font-size: 0.82rem; color: #94a3b8; text-align: center; }
.activity-row { display: flex; justify-content: space-between; align-items: center; padding: 0.55rem 1rem; border-bottom: 1px solid #f8fafc; }
.activity-row:last-child { border-bottom: none; }
.activity-main { display: flex; flex-direction: column; gap: 0.1rem; min-width: 0; }
.activity-link { font-size: 0.85rem; font-weight: 600; color: #2a6941; text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.activity-link:hover { text-decoration: underline; }
.activity-sub { font-size: 0.75rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.activity-date { font-size: 0.75rem; color: #94a3b8; flex-shrink: 0; margin-left: 0.75rem; }

/* ── Quick links ────────────────────────────────────────────────── */
.quick-links { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.quick-card {
  background: #fff; border: 1px solid #dde8dd; border-radius: 10px; padding: 1.1rem 1rem;
  display: flex; flex-direction: column; gap: 0.3rem; text-decoration: none;
  transition: border-color 0.15s, box-shadow 0.15s, transform 0.12s;
}
.quick-card:hover { border-color: #2a6941; box-shadow: 0 4px 16px rgba(42,105,65,.12); transform: translateY(-2px); }
.quick-card-secondary { border-style: dashed; }
.quick-card-highlight { border-color: #2a6941; background: #f0faf2; }
.card-icon { font-size: 1.4rem; color: #2a6941; margin-bottom: 0.2rem; }
.card-label { font-size: 0.9rem; font-weight: 700; color: #1a3d28; }
.card-desc  { font-size: 0.75rem; color: #6b7280; line-height: 1.3; }

/* ── Info banner ────────────────────────────────────────────────── */
.info-banner { display: flex; align-items: center; gap: 0.75rem; background: #f0faf2; border: 1px solid #b7dfc4; border-radius: 8px; padding: 0.85rem 1.25rem; font-size: 0.85rem; color: #1c3d28; }
.info-banner i { color: #2a6941; font-size: 1rem; flex-shrink: 0; }

@media (max-width: 900px) {
  .stat-grid { grid-template-columns: repeat(2, 1fr); }
  .activity-grid { grid-template-columns: 1fr; }
}
</style>
