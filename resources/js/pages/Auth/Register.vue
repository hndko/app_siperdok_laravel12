<template>
  <div class="register-page-wrapper">
    <div class="register-box">
      <div class="card card-outline card-success auth-card">
        <div class="card-header text-center py-3">
          <a href="#" class="h2 text-dark font-weight-bold">
            <i class="fas fa-file-contract text-success mr-2"></i><b>SI PERDOK</b>
          </a>
          <p class="text-muted mb-0 small">Pendaftaran Akun Pemohon Dokumen Kelayakan</p>
        </div>
        <div class="card-body register-card-body p-4">
          <div v-if="Object.keys(errors).length" class="alert alert-danger py-2 small mb-3">
            <ul class="mb-0 pl-3">
              <li v-for="(err, key) in errors" :key="key">{{ err }}</li>
            </ul>
          </div>

          <form @submit.prevent="submit">
            <div class="form-group mb-3">
              <label class="small font-weight-bold">Nama Lengkap Pemohon / Penanggung Jawab</label>
              <input type="text" v-model="form.name" class="form-control" placeholder="Nama Lengkap" required>
            </div>

            <div class="form-group mb-3">
              <label class="small font-weight-bold">Email Perusahaan / Resmi</label>
              <input type="email" v-model="form.email" class="form-control" placeholder="alamat@email.com" required>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="small font-weight-bold">No. Telepon / Whatsapp</label>
                <input type="text" v-model="form.phone" class="form-control" placeholder="08123456789" required>
              </div>
              <div class="col-md-6">
                <label class="small font-weight-bold">NIK / NIP Pemohon</label>
                <input type="text" v-model="form.nip_nik" class="form-control" placeholder="3171xxxxxxxx" required>
              </div>
            </div>

            <div class="form-group mb-3">
              <label class="small font-weight-bold">Nama Perusahaan / Instansi Pemohon</label>
              <input type="text" v-model="form.company_name" class="form-control" placeholder="PT Contoh Sejahtera" required>
            </div>

            <div class="row mb-4">
              <div class="col-md-6">
                <label class="small font-weight-bold">Password</label>
                <input type="password" v-model="form.password" class="form-control" placeholder="Minimal 8 Karakter" required>
              </div>
              <div class="col-md-6">
                <label class="small font-weight-bold">Konfirmasi Password</label>
                <input type="password" v-model="form.password_confirmation" class="form-control" placeholder="Ulangi Password" required>
              </div>
            </div>

            <button type="submit" :disabled="form.processing" class="btn btn-success btn-block font-weight-bold py-2">
              <i class="fas fa-user-plus mr-1"></i> {{ form.processing ? 'Memproses...' : 'Daftar Sekarang' }}
            </button>
          </form>

          <p class="mb-0 mt-3 text-center small">
            Sudah memiliki akun? <Link href="/login" class="font-weight-bold">Masuk di sini</Link>
          </p>
        </div>
      </div>
    </div>
  </div>
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
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px 0;
}
.register-box { width: 520px; }
.auth-card { border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
</style>
