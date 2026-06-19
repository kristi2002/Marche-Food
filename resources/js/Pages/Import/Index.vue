<template>
  <AppLayout>
    <div class="page-header">
      <h1 class="page-title">Import Dati Storici</h1>
    </div>
    <p class="desc">Importa acquisti e vendite storiche da file CSV (separatore: punto e virgola).</p>

    <div class="cards">
      <!-- ACQUISTI -->
      <div class="import-card">
        <div class="card-icon-wrap acquisti"><i class="pi pi-download" /></div>
        <h2 class="card-title">Acquisti</h2>
        <p class="card-desc">Importa DDT/fatture fornitore con righe articolo e lotti.</p>
        <div class="template-row">
          <a href="/import/template-acquisti" class="template-link"><i class="pi pi-file-export" /> Scarica template CSV</a>
        </div>
        <form @submit.prevent="submitAcquisti" class="upload-form">
          <FileUpload
            mode="basic"
            name="file"
            accept=".csv,.txt"
            :auto="false"
            choose-label="Scegli file CSV"
            @select="onSelectAcquisti"
          />
          <Button type="submit" label="Importa Acquisti" icon="pi pi-upload" :loading="formAcquisti.processing" :disabled="!fileAcquisti" class="mt-2" />
        </form>
      </div>

      <!-- VENDITE -->
      <div class="import-card">
        <div class="card-icon-wrap vendite"><i class="pi pi-upload" /></div>
        <h2 class="card-title">Vendite</h2>
        <p class="card-desc">Importa DDT/fatture cliente con righe articolo e lotti.</p>
        <div class="template-row">
          <a href="/import/template-vendite" class="template-link"><i class="pi pi-file-export" /> Scarica template CSV</a>
        </div>
        <form @submit.prevent="submitVendite" class="upload-form">
          <FileUpload
            mode="basic"
            name="file"
            accept=".csv,.txt"
            :auto="false"
            choose-label="Scegli file CSV"
            @select="onSelectVendite"
          />
          <Button type="submit" label="Importa Vendite" icon="pi pi-upload" :loading="formVendite.processing" :disabled="!fileVendite" class="mt-2" />
        </form>
      </div>
    </div>

    <!-- INSTRUCTIONS -->
    <div class="instructions">
      <h3 class="instr-title">Formato CSV Acquisti</h3>
      <pre class="csv-example">fornitore_codice;numero_documento;data_documento;tipo_documento;nome_prodotto;quantita_kg;quantita_pz;lotto;lotto_esterno;scadenza;data_in;note_documento
FOR001;DDT/2024/001;01/01/2024;DDT;Tonno al naturale;100.000;;A2024001;;31/12/2025;01/01/2024;</pre>

      <h3 class="instr-title mt-4">Formato CSV Vendite</h3>
      <pre class="csv-example">cliente_codice;numero_documento;data_documento;tipo_documento;nome_prodotto;pezzatura_gr;quantita_kg;quantita_pz;lotto;lotto_esterno;scadenza;note_documento
CLI001;DDT/2024/001;01/01/2024;DDT;Tonno all'olio 800g;800;50.000;62;LP2024-001;;31/12/2025;</pre>

      <ul class="instr-list">
        <li>Date nel formato <strong>DD/MM/YYYY</strong> o <strong>YYYY-MM-DD</strong></li>
        <li>Separatore decimale: <strong>punto</strong> o <strong>virgola</strong></li>
        <li>Righe multiple con stesso documento/fornitore vengono raggruppate in un unico acquisto</li>
        <li>Il codice fornitore/cliente deve esistere nell'anagrafica</li>
      </ul>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import FileUpload from 'primevue/fileupload';

const fileAcquisti = ref(null);
const fileVendite  = ref(null);
const formAcquisti = useForm({ file: null });
const formVendite  = useForm({ file: null });

function onSelectAcquisti(e) { fileAcquisti.value = e.files[0]; }
function onSelectVendite(e)  { fileVendite.value  = e.files[0]; }

function submitAcquisti() {
  formAcquisti.file = fileAcquisti.value;
  formAcquisti.post('/import/acquisti', { forceFormData: true });
}

function submitVendite() {
  formVendite.file = fileVendite.value;
  formVendite.post('/import/vendite', { forceFormData: true });
}
</script>

<style scoped>
.page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:0.5rem; }
.page-title { font-size:1.5rem; font-weight:700; color:#1e293b; margin:0; }
.desc { color:#64748b; margin:0 0 1.5rem 0; font-size:0.875rem; }
.cards { display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2rem; }
.import-card { background:#fff; border-radius:8px; border:1px solid #e2e8f0; padding:1.5rem; }
.card-icon-wrap { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; margin-bottom:1rem; font-size:1.5rem; }
.card-icon-wrap.acquisti { background:#dbeafe; color:#1d4ed8; }
.card-icon-wrap.vendite  { background:#dcfce7; color:#15803d; }
.card-title { font-size:1.1rem; font-weight:700; color:#1e293b; margin:0 0 0.5rem 0; }
.card-desc { color:#64748b; font-size:0.875rem; margin:0 0 1rem 0; }
.template-row { margin-bottom:1rem; }
.template-link { color:#1d4ed8; font-size:0.875rem; text-decoration:none; display:inline-flex; align-items:center; gap:0.3rem; }
.template-link:hover { text-decoration:underline; }
.upload-form { display:flex; flex-direction:column; gap:0.5rem; }
.mt-2 { margin-top:0.5rem; }
.instructions { background:#fff; border-radius:8px; border:1px solid #e2e8f0; padding:1.5rem; }
.instr-title { font-size:0.9rem; font-weight:700; color:#374151; margin:0 0 0.5rem 0; }
.mt-4 { margin-top:1.25rem; }
.csv-example { background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:0.75rem 1rem; font-size:0.78rem; overflow-x:auto; white-space:pre; margin:0 0 0.5rem 0; }
.instr-list { margin:1rem 0 0 0; padding-left:1.25rem; color:#475569; font-size:0.875rem; display:flex; flex-direction:column; gap:0.3rem; }
</style>
