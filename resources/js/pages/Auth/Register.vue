<template>
  <main class="register-page-wrapper">
    <section class="register-shell" aria-label="Pendaftaran akun SI PERDOK">
      <aside class="register-brand-panel">
        <div class="brand-mark" aria-hidden="true">
          <i class="fas fa-file-contract"></i>
        </div>

        <div>
          <p class="brand-kicker">Pendaftaran Pemohon</p>
          <h1>Buat akun SI PERDOK</h1>
          <p class="brand-description">
            Lengkapi identitas penanggung jawab dan perusahaan untuk mulai mengajukan dokumen kelayakan.
          </p>
        </div>

        <div class="brand-checklist">
          <div class="brand-check">
            <i class="fas fa-user-check" aria-hidden="true"></i>
            <span>Akun khusus pemohon</span>
          </div>
          <div class="brand-check">
            <i class="fas fa-building" aria-hidden="true"></i>
            <span>Data perusahaan tercatat</span>
          </div>
          <div class="brand-check">
            <i class="fas fa-lock" aria-hidden="true"></i>
            <span>Akses aman untuk dokumen</span>
          </div>
        </div>
      </aside>

      <section class="register-form-panel">
        <div class="mobile-brand">
          <span class="mobile-brand-icon"><i class="fas fa-file-contract"></i></span>
          <span>SI PERDOK</span>
        </div>

        <header class="form-header">
          <h2>Daftar Akun Pemohon</h2>
          <p>Masukkan data resmi agar proses verifikasi dokumen dapat ditelusuri dengan jelas.</p>
        </header>

        <div v-if="Object.keys(errors).length" class="auth-alert auth-alert-danger">
          <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
          <ul>
            <li v-for="(err, key) in errors" :key="key">{{ err }}</li>
          </ul>
        </div>

        <form class="register-form" @submit.prevent="submit">
          <label class="form-field form-field-wide">
            <span>Nama Lengkap Pemohon / Penanggung Jawab</span>
            <div class="field-control">
              <i class="fas fa-user" aria-hidden="true"></i>
              <input
                type="text"
                v-model="form.name"
                placeholder="Nama lengkap"
                autocomplete="name"
                required
              >
            </div>
          </label>

          <label class="form-field form-field-wide">
            <span>Email Perusahaan / Resmi</span>
            <div class="field-control">
              <i class="fas fa-envelope" aria-hidden="true"></i>
              <input
                type="email"
                v-model="form.email"
                placeholder="alamat@email.com"
                autocomplete="email"
                required
              >
            </div>
          </label>

          <label class="form-field">
            <span>No. Telepon / WhatsApp</span>
            <div class="field-control">
              <i class="fas fa-phone" aria-hidden="true"></i>
              <input
                type="text"
                v-model="form.phone"
                placeholder="08123456789"
                autocomplete="tel"
                required
              >
            </div>
          </label>

          <label class="form-field">
            <span>NIK / NIP Pemohon</span>
            <div class="field-control">
              <i class="fas fa-id-card" aria-hidden="true"></i>
              <input
                type="text"
                v-model="form.nip_nik"
                placeholder="3171xxxxxxxx"
                required
              >
            </div>
          </label>

          <label class="form-field form-field-wide">
            <span>Nama Perusahaan / Instansi Pemohon</span>
            <div class="field-control">
              <i class="fas fa-building" aria-hidden="true"></i>
              <input
                type="text"
                v-model="form.company_name"
                placeholder="PT Contoh Sejahtera"
                autocomplete="organization"
                required
              >
            </div>
          </label>

          <label class="form-field">
            <span>Password</span>
            <div class="field-control">
              <i class="fas fa-lock" aria-hidden="true"></i>
              <input
                type="password"
                v-model="form.password"
                placeholder="Minimal 8 karakter"
                autocomplete="new-password"
                required
              >
            </div>
          </label>

          <label class="form-field">
            <span>Konfirmasi Password</span>
            <div class="field-control">
              <i class="fas fa-check-circle" aria-hidden="true"></i>
              <input
                type="password"
                v-model="form.password_confirmation"
                placeholder="Ulangi password"
                autocomplete="new-password"
                required
              >
            </div>
          </label>

          <button type="submit" :disabled="form.processing" class="submit-button form-field-wide">
            <i :class="form.processing ? 'fas fa-circle-notch fa-spin' : 'fas fa-user-plus'" aria-hidden="true"></i>
            <span>{{ form.processing ? 'Memproses...' : 'Daftar Sekarang' }}</span>
          </button>
        </form>

        <p class="login-copy">
          Sudah memiliki akun?
          <Link href="/login">Masuk di sini</Link>
        </p>
      </section>
    </section>
  </main>
</template>

<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const errors = computed(() => page.props.errors || {});

const form = useForm({
  name: '',
  email: '',
  phone: '',
  nip_nik: '',
  company_name: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post('/register');
};
</script>

<style scoped>
.register-page-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32px 20px;
  background:
    radial-gradient(circle at 88% 14%, rgba(16, 185, 129, 0.13), transparent 28%),
    linear-gradient(135deg, #f8fafc 0%, #eef7f4 52%, #e9eff5 100%);
  color: #0f172a;
}

.register-shell {
  width: min(100%, 1080px);
  min-height: 680px;
  display: grid;
  grid-template-columns: minmax(300px, 0.8fr) minmax(520px, 1.2fr);
  overflow: hidden;
  border: 1px solid rgba(148, 163, 184, 0.24);
  border-radius: 18px;
  background: #ffffff;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
}

.register-brand-panel {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 32px;
  padding: 44px;
  background: linear-gradient(160deg, #0f172a 0%, #17423f 58%, #166534 100%);
  color: #ffffff;
}

.brand-mark {
  width: 56px;
  height: 56px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.18);
  color: #86efac;
  font-size: 1.55rem;
}

.brand-kicker {
  margin: 0 0 10px;
  color: #bbf7d0;
  font-size: 0.88rem;
  font-weight: 700;
}

.register-brand-panel h1 {
  max-width: 9em;
  margin: 0;
  font-size: 2.3rem;
  line-height: 1.12;
  font-weight: 800;
  letter-spacing: 0;
  text-wrap: balance;
}

.brand-description {
  max-width: 31rem;
  margin: 18px 0 0;
  color: #dcfce7;
  font-size: 1rem;
  line-height: 1.7;
  text-wrap: pretty;
}

.brand-checklist {
  display: grid;
  gap: 12px;
}

.brand-check {
  display: flex;
  align-items: center;
  gap: 12px;
  color: #ecfdf5;
  font-weight: 600;
}

.brand-check i {
  width: 34px;
  height: 34px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.1);
  color: #a7f3d0;
}

.register-form-panel {
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 42px 48px;
}

.mobile-brand {
  display: none;
  align-items: center;
  gap: 10px;
  margin-bottom: 24px;
  font-weight: 800;
  color: #0f172a;
}

.mobile-brand-icon {
  width: 40px;
  height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  background: #0f172a;
  color: #86efac;
}

.form-header {
  margin-bottom: 24px;
}

.form-header h2 {
  margin: 0;
  color: #0f172a;
  font-size: 1.65rem;
  line-height: 1.25;
  font-weight: 800;
  letter-spacing: 0;
}

.form-header p {
  max-width: 62ch;
  margin: 8px 0 0;
  color: #475569;
  line-height: 1.6;
}

.auth-alert {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 16px;
  padding: 12px 14px;
  border-radius: 10px;
  font-size: 0.92rem;
  font-weight: 600;
}

.auth-alert-danger {
  background: #fee2e2;
  color: #991b1b;
}

.auth-alert ul {
  margin: 0;
  padding-left: 18px;
}

.register-form {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.form-field {
  display: grid;
  gap: 8px;
  margin: 0;
}

.form-field-wide {
  grid-column: 1 / -1;
}

.form-field > span {
  color: #334155;
  font-size: 0.9rem;
  font-weight: 700;
}

.field-control {
  min-height: 48px;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0 14px;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  background: #ffffff;
  color: #64748b;
  transition: border-color 180ms ease, box-shadow 180ms ease;
}

.field-control:focus-within {
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
}

.field-control input {
  width: 100%;
  border: 0;
  outline: 0;
  padding: 0;
  background: transparent;
  color: #0f172a;
  font: inherit;
}

.field-control input::placeholder {
  color: #64748b;
}

.submit-button {
  min-height: 48px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  margin-top: 4px;
  border: 0;
  border-radius: 10px;
  background: #047857;
  color: #ffffff;
  font-weight: 800;
  transition: background-color 180ms ease, transform 180ms ease;
}

.submit-button:hover:not(:disabled),
.submit-button:focus-visible:not(:disabled) {
  background: #065f46;
}

.submit-button:focus-visible {
  outline: 3px solid rgba(16, 185, 129, 0.28);
  outline-offset: 3px;
}

.submit-button:active:not(:disabled) {
  transform: translateY(1px);
}

.submit-button:disabled {
  cursor: not-allowed;
  opacity: 0.72;
}

.login-copy {
  margin: 22px 0 0;
  color: #475569;
  text-align: center;
  font-size: 0.94rem;
}

.login-copy a {
  color: #047857;
  font-weight: 800;
}

.login-copy a:hover,
.login-copy a:focus-visible {
  color: #065f46;
}

@media (max-width: 920px) {
  .register-page-wrapper {
    align-items: flex-start;
    padding: 20px;
  }

  .register-shell {
    min-height: auto;
    grid-template-columns: 1fr;
  }

  .register-brand-panel {
    display: none;
  }

  .register-form-panel {
    padding: 32px 24px;
  }

  .mobile-brand {
    display: inline-flex;
  }
}

@media (max-width: 620px) {
  .register-form {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 420px) {
  .register-page-wrapper {
    padding: 0;
    background: #ffffff;
  }

  .register-shell {
    min-height: 100vh;
    border: 0;
    border-radius: 0;
    box-shadow: none;
  }

  .register-form-panel {
    padding: 28px 18px;
  }
}

@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    transition-duration: 0.01ms !important;
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
  }
}
</style>
