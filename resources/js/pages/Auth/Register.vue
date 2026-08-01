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

        <form class="register-form" @submit.prevent="submit">
          <label class="form-field form-field-wide">
            <span>Nama Lengkap Pemohon / Penanggung Jawab <span class="required-mark" aria-hidden="true">*</span></span>
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
            <span>Email Perusahaan / Resmi <span class="required-mark" aria-hidden="true">*</span></span>
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
            <span>No. Telepon / WhatsApp <span class="required-mark" aria-hidden="true">*</span></span>
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
            <span>NIK / NIP Pemohon <span class="required-mark" aria-hidden="true">*</span></span>
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
            <span>Nama Perusahaan / Instansi Pemohon <span class="required-mark" aria-hidden="true">*</span></span>
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
            <span>Password <span class="required-mark" aria-hidden="true">*</span></span>
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
            <span>Konfirmasi Password <span class="required-mark" aria-hidden="true">*</span></span>
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

          <button type="submit" :disabled="processing" class="submit-button form-field-wide">
            <i :class="processing ? 'fas fa-circle-notch fa-spin' : 'fas fa-user-plus'" aria-hidden="true"></i>
            <span>{{ processing ? 'Memproses...' : 'Daftar Sekarang' }}</span>
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
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { apiErrorMessages, toast } from '../../lib/feedback';

const router = useRouter();
const processing = ref(false);

const form = reactive({
  name: '',
  email: '',
  phone: '',
  nip_nik: '',
  company_name: '',
  password: '',
  password_confirmation: '',
});

const submit = async () => {
  processing.value = true;

  try {
    const response = await window.axios.post('/api/v1/register', form);

    localStorage.setItem('siperdok_token', response.data.access_token);
    window.axios.defaults.headers.common.Authorization = `Bearer ${response.data.access_token}`;
    toast('success', 'Registrasi berhasil. Mengarahkan ke dashboard.');
    setTimeout(() => {
      router.push('/dashboard');
    }, 600);
  } catch (error) {
    apiErrorMessages(error, 'Registrasi gagal. Periksa kembali data yang diisi.')
      .slice(0, 3)
      .forEach((message) => toast('error', message));
  } finally {
    processing.value = false;
  }
};
</script>

<style src="../../../css/auth/register.css" scoped></style>
