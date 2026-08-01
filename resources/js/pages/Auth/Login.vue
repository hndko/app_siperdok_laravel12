<template>
  <main class="login-page-wrapper">
    <section class="login-shell" aria-label="Masuk SI PERDOK">
      <aside class="login-brand-panel">
        <div class="brand-mark" aria-hidden="true">
          <i class="fas fa-file-contract"></i>
        </div>
        <div>
          <p class="brand-kicker">Sistem Informasi</p>
          <h1>SI PERDOK</h1>
          <p class="brand-description">
            Kelola pengajuan, verifikasi, dan persetujuan dokumen kelayakan dalam satu alur kerja yang tertata.
          </p>
        </div>
        <div class="brand-feature-list">
          <div class="brand-feature">
            <i class="fas fa-shield-alt" aria-hidden="true"></i>
            <span>Akses berbasis peran</span>
          </div>
          <div class="brand-feature">
            <i class="fas fa-history" aria-hidden="true"></i>
            <span>Riwayat proses terdokumentasi</span>
          </div>
          <div class="brand-feature">
            <i class="fas fa-file-signature" aria-hidden="true"></i>
            <span>Review dokumen terkontrol</span>
          </div>
        </div>
      </aside>

      <section class="login-form-panel">
        <div class="mobile-brand">
          <span class="mobile-brand-icon"><i class="fas fa-file-contract"></i></span>
          <span>SI PERDOK</span>
        </div>

        <header class="form-header">
          <h2>Masuk ke Akun</h2>
          <p>Gunakan akun yang telah terdaftar untuk melanjutkan proses dokumen.</p>
        </header>

        <div v-if="flash.info" class="auth-alert auth-alert-info">
          <i class="fas fa-info-circle" aria-hidden="true"></i>
          <span>{{ flash.info }}</span>
        </div>
        <div v-if="flash.error || errors.email" class="auth-alert auth-alert-danger">
          <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
          <span>{{ flash.error || errors.email }}</span>
        </div>

        <form class="login-form" @submit.prevent="submit">
          <label class="form-field">
            <span>Email <span class="required-mark" aria-hidden="true">*</span></span>
            <div class="field-control">
              <i class="fas fa-envelope" aria-hidden="true"></i>
              <input
                type="email"
                v-model="form.email"
                placeholder="nama@email.com"
                autocomplete="email"
                required
                autofocus
              >
            </div>
          </label>

          <label class="form-field">
            <span>Password <span class="required-mark" aria-hidden="true">*</span></span>
            <div class="field-control">
              <i class="fas fa-lock" aria-hidden="true"></i>
              <input
                type="password"
                v-model="form.password"
                placeholder="Masukkan password"
                autocomplete="current-password"
                required
              >
            </div>
          </label>

          <div class="form-options">
            <label class="remember-option" for="remember">
              <input type="checkbox" id="remember" v-model="form.remember">
              <span>Ingat saya</span>
            </label>
          </div>

          <button type="submit" :disabled="form.processing" class="submit-button">
            <i :class="form.processing ? 'fas fa-circle-notch fa-spin' : 'fas fa-arrow-right'" aria-hidden="true"></i>
            <span>{{ form.processing ? 'Memproses...' : 'Masuk' }}</span>
          </button>
        </form>

        <div class="demo-actions">
          <button type="button" class="demo-trigger" @click="showDemoAccounts = true">
            <i class="fas fa-key" aria-hidden="true"></i>
            <span>Lihat akun demo</span>
          </button>
        </div>

        <p class="register-copy">
          Belum punya akun?
          <Link href="/register">Daftar akun pemohon</Link>
        </p>
      </section>
    </section>

    <div
      v-if="showDemoAccounts"
      class="demo-modal-backdrop"
      role="presentation"
      @click.self="showDemoAccounts = false"
    >
      <section class="demo-modal" role="dialog" aria-modal="true" aria-labelledby="demo-modal-title">
        <header class="demo-modal-header">
          <div>
            <p class="demo-modal-kicker">Akses cepat</p>
            <h3 id="demo-modal-title">Akun demo</h3>
          </div>
          <button type="button" class="modal-close" aria-label="Tutup modal akun demo" @click="showDemoAccounts = false">
            <i class="fas fa-times" aria-hidden="true"></i>
          </button>
        </header>

        <div class="demo-grid">
          <button type="button" class="demo-button demo-pemohon" @click="fillLogin('pemohon@example.com', 'password')">
            <span class="demo-icon"><i class="fas fa-user-tie" aria-hidden="true"></i></span>
            <span>
              <strong>Pemohon</strong>
              <small>pemohon@example.com</small>
            </span>
          </button>
          <button type="button" class="demo-button demo-penilai" @click="fillLogin('penilai@example.com', 'password')">
            <span class="demo-icon"><i class="fas fa-user-check" aria-hidden="true"></i></span>
            <span>
              <strong>Penilai</strong>
              <small>penilai@example.com</small>
            </span>
          </button>
          <button type="button" class="demo-button demo-admin" @click="fillLogin('admin@example.com', 'password')">
            <span class="demo-icon"><i class="fas fa-user-shield" aria-hidden="true"></i></span>
            <span>
              <strong>Admin</strong>
              <small>admin@example.com</small>
            </span>
          </button>
        </div>
      </section>
    </div>
  </main>
</template>

<script setup>
import { useForm, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const flash = computed(() => page.props.flash || {});
const errors = computed(() => page.props.errors || {});
const showDemoAccounts = ref(false);

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const fillLogin = (email, pass) => {
  form.email = email;
  form.password = pass;
  showDemoAccounts.value = false;
};

const submit = () => {
  form.post('/login');
};
</script>

<style src="../../../css/auth/login.css" scoped></style>
