<template>
  <AppLayout>
    <div class="page-header">
      <h1 class="page-title">{{ isEdit ? 'Modifica Produzione' : 'Nuova Produzione' }}</h1>
      <div style="display:flex;gap:0.5rem;align-items:center">
        <Link v-if="isEdit" :href="`/produzioni/${props.produzione.id}/print`" target="_blank">
          <Button label="Stampa" icon="pi pi-print" outlined severity="secondary" />
        </Link>
        <Link href="/produzioni"><Button label="Annulla" outlined icon="pi pi-arrow-left" /></Link>
      </div>
    </div>

    <form @submit.prevent="submit">
      <!-- TESTATA -->
      <div class="form-card mb-4">
        <section class="form-section">
          <h2 class="section-title">Dati produzione</h2>
          <div class="form-grid-4">
            <div class="field" style="grid-column:span 2">
              <label>Scheda di Produzione *</label>
              <Select
                v-model="form.scheda_id"
                :options="schede"
                :option-label="s => `${s.modello}.${String(s.revisione).padStart(2,'0')} — ${s.prodotto?.nome}`"
                option-value="id"
                placeholder="Seleziona scheda..."
                :invalid="!!form.errors.scheda_id"
                filter
                fluid
              />
              <small class="error">{{ form.errors.scheda_id }}</small>
            </div>
            <div class="field" style="grid-column:span 2">
              <label>Lotto Produzione *</label>
              <InputText v-model="form.lotto_produzione" :invalid="!!form.errors.lotto_produzione" placeholder="es. LP2024-001" fluid />
              <small class="error">{{ form.errors.lotto_produzione }}</small>
            </div>
            <div class="field">
              <label>Data Produzione *</label>
              <DatePicker v-model="form.data_produzione" date-format="dd/mm/yy" :invalid="!!form.errors.data_produzione" show-button-bar fluid />
              <small class="error">{{ form.errors.data_produzione }}</small>
            </div>
            <div class="field">
              <label>Q.tà Prodotta (Kg)</label>
              <InputNumber v-model="form.quantita_prodotta_kg" :min-fraction-digits="3" :max-fraction-digits="3" fluid />
            </div>
            <div class="field">
              <label>Operatore</label>
              <InputText v-model="form.operatore" fluid />
            </div>
            <div class="field">
              <label>Note</label>
              <InputText v-model="form.note" fluid />
            </div>
          </div>
        </section>
      </div>

      <!-- MATERIE PRIME UTILIZZATE (tracciabilità HACCP) -->
      <div class="form-card">
        <div class="righe-header">
          <div>
            <h2 class="section-title" style="margin:0">Materie prime utilizzate</h2>
            <p class="section-sub">Collegare ogni ingrediente al lotto di acquisto per la tracciabilità HACCP</p>
          </div>
          <Button type="button" label="Aggiungi riga" icon="pi pi-plus" size="small" outlined @click="addRiga" />
        </div>

        <div class="table-wrapper">
          <table class="righe-table">
            <thead>
              <tr>
                <th style="min-width:180px">Materia Prima *</th>
                <th style="min-width:280px">Lotto Acquisto (DDT / Fornitore / Lotto) *</th>
                <th style="width:120px">Q.tà Kg *</th>
                <th style="width:44px"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(r, i) in form.materie_prime" :key="i">
                <td>
                  <Select
                    v-model="r.materia_prima_id"
                    :options="materie"
                    option-label="nome"
                    option-value="id"
                    placeholder="Seleziona..."
                    filter
                    fluid size="small"
                  />
                </td>
                <td>
                  <Select
                    v-model="r.acquisto_riga_id"
                    :options="acquisti_righe"
                    :option-label="rigaLabel"
                    option-value="id"
                    placeholder="Seleziona lotto..."
                    filter
                    fluid size="small"
                    :invalid="!!form.errors[`materie_prime.${i}.acquisto_riga_id`]"
                  />
                </td>
                <td>
                  <InputNumber
                    v-model="r.quantita_kg"
                    :min-fraction-digits="3" :max-fraction-digits="3"
                    :invalid="!!form.errors[`materie_prime.${i}.quantita_kg`]"
                    fluid size="small"
                  />
                </td>
                <td>
                  <Button type="button" icon="pi pi-trash" size="small" text severity="danger" @click="removeRiga(i)" />
                </td>
              </tr>
              <tr v-if="!form.materie_prime.length">
                <td colspan="4" style="text-align:center;color:#94a3b8;padding:1rem">Nessuna materia prima aggiunta.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="form-actions">
          <span class="righe-count">{{ form.materie_prime.length }} riga/righe</span>
          <Button type="submit" :label="isEdit ? 'Salva modifiche' : 'Registra produzione'" icon="pi pi-check" :loading="form.processing" />
        </div>
      </div>
    </form>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';

const props = defineProps({
  produzione: Object,
  schede: Array,
  materie: Array,
  acquisti_righe: Array,
});

const isEdit = computed(() => !!props.produzione);

function rigaLabel(r) {
  const lotto = r.lotto || r.lotto_esterno || '—';
  const fornitore = r.acquisto?.fornitore?.ragione_sociale ?? '?';
  const ddt = r.acquisto?.numero_documento ?? '?';
  return `${ddt} | ${fornitore} | ${r.nome_prodotto} | Lotto: ${lotto}`;
}

function emptyRiga() {
  return { materia_prima_id: null, acquisto_riga_id: null, quantita_kg: null };
}

const form = useForm({
  scheda_id:            props.produzione?.scheda_id            ?? null,
  lotto_produzione:     props.produzione?.lotto_produzione     ?? '',
  data_produzione:      props.produzione?.data_produzione      ? new Date(props.produzione.data_produzione) : null,
  quantita_prodotta_kg: props.produzione?.quantita_prodotta_kg ? Number(props.produzione.quantita_prodotta_kg) : null,
  operatore:            props.produzione?.operatore            ?? '',
  note:                 props.produzione?.note                 ?? '',
  materie_prime: props.produzione?.materie_prime?.length
    ? props.produzione.materie_prime.map(m => ({
        materia_prima_id:  m.materia_prima_id,
        acquisto_riga_id:  m.acquisto_riga_id,
        quantita_kg:       Number(m.quantita_kg),
      }))
    : [],
});

function addRiga() { form.materie_prime.push(emptyRiga()); }
function removeRiga(i) { form.materie_prime.splice(i, 1); }

function submit() {
  const payload = {
    ...form.data(),
    data_produzione: form.data_produzione ? form.data_produzione.toISOString().slice(0, 10) : null,
  };
  if (isEdit.value) {
    form.transform(() => payload).put(`/produzioni/${props.produzione.id}`);
  } else {
    form.transform(() => payload).post('/produzioni');
  }
}
</script>

<style scoped>
.page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; }
.page-title { font-size:1.5rem; font-weight:700; color:#1e293b; margin:0; }
.form-card { background:#fff; border-radius:8px; border:1px solid #e2e8f0; overflow:hidden; }
.mb-4 { margin-bottom:1rem; }
.form-section { padding:1.5rem; }
.section-title { font-size:0.9rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#64748b; margin:0 0 0.25rem 0; }
.section-sub { font-size:0.78rem; color:#94a3b8; margin:0; }
.form-grid-4 { display:grid; grid-template-columns:repeat(4,1fr); gap:1rem; }
.field { display:flex; flex-direction:column; gap:0.3rem; }
.field label { font-size:0.85rem; font-weight:600; color:#374151; }
.error { color:#dc2626; font-size:0.78rem; }
.righe-header { display:flex; align-items:flex-start; justify-content:space-between; padding:1rem 1.5rem; border-bottom:1px solid #f1f5f9; }
.table-wrapper { overflow-x:auto; }
.righe-table { width:100%; border-collapse:collapse; font-size:0.85rem; }
.righe-table th { padding:0.6rem 0.5rem; text-align:left; font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; color:#64748b; background:#f8fafc; border-bottom:1px solid #e2e8f0; white-space:nowrap; }
.righe-table td { padding:0.4rem 0.5rem; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.form-actions { padding:1rem 1.5rem; background:#f8fafc; display:flex; align-items:center; justify-content:space-between; border-top:1px solid #e2e8f0; }
.righe-count { font-size:0.85rem; color:#64748b; }
</style>
