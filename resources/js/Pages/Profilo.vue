<template>
  <AppLayout>
    <div class="page-header">
      <h1 class="page-title">Il mio Profilo</h1>
    </div>

    <div class="profile-grid">
      <!-- Info card -->
      <div class="card info-card">
        <h2 class="section-title">Informazioni account</h2>
        <div class="info-row">
          <span class="info-label">Nome</span>
          <span class="info-value">{{ user.name }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Email</span>
          <span class="info-value">{{ user.email }}</span>
        </div>
        <div class="info-row">
          <span class="info-label">Ruolo</span>
          <Tag
            :value="user.role === 'admin' ? 'Amministratore' : 'Operatore'"
            :severity="user.role === 'admin' ? 'success' : 'warn'"
          />
        </div>
      </div>

      <!-- Change password card -->
      <div class="card">
        <h2 class="section-title">Cambia Password</h2>
        <div class="pw-form">
          <div class="field">
            <label>Password attuale *</label>
            <Password
              v-model="form.current_password"
              :feedback="false"
              toggle-mask
              :invalid="!!form.errors.current_password"
              fluid
              placeholder="Inserisci la password attuale"
            />
            <small class="error">{{ form.errors.current_password }}</small>
          </div>
          <div class="field">
            <label>Nuova password *</label>
            <Password
              v-model="form.password"
              :feedback="true"
              toggle-mask
              :invalid="!!form.errors.password"
              fluid
              placeholder="min. 8 caratteri"
              prompt-label="Scegli una password"
              weak-label="Debole"
              medium-label="Media"
              strong-label="Forte"
            />
            <small class="error">{{ form.errors.password }}</small>
          </div>
          <div class="field">
            <label>Conferma nuova password *</label>
            <Password
              v-model="form.password_confirmation"
              :feedback="false"
              toggle-mask
              fluid
              placeholder="Ripeti la nuova password"
            />
          </div>
          <Button
            label="Aggiorna password"
            icon="pi pi-check"
            @click="submit"
            :loading="form.processing"
            style="align-self:flex-start"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Button from 'primevue/button';
import Password from 'primevue/password';
import Tag from 'primevue/tag';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? {});

const form = useForm({ current_password: '', password: '', password_confirmation: '' });

function submit() {
  form.put('/profilo/password', { onSuccess: () => form.reset() });
}
</script>

<style scoped>
.page-header { margin-bottom: 1.5rem; }
.page-title { font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0; }
.profile-grid { display: grid; grid-template-columns: 320px 1fr; gap: 1.5rem; align-items: start; }
.card { background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 1.5rem; }
.section-title { font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin: 0 0 1.25rem 0; }
.info-row { display: flex; align-items: center; justify-content: space-between; padding: 0.65rem 0; border-bottom: 1px solid #f1f5f9; }
.info-row:last-child { border-bottom: none; }
.info-label { font-size: 0.85rem; color: #64748b; font-weight: 500; }
.info-value { font-size: 0.875rem; color: #1e293b; font-weight: 600; }
.pw-form { display: flex; flex-direction: column; gap: 1rem; }
.field { display: flex; flex-direction: column; gap: 0.3rem; }
.field label { font-size: 0.85rem; font-weight: 600; color: #374151; }
.error { color: #dc2626; font-size: 0.78rem; min-height: 1rem; }
@media (max-width: 768px) {
  .profile-grid { grid-template-columns: 1fr; }
}
</style>
