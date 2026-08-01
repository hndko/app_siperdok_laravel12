<template>
  <div class="login-page-wrapper">
    <div class="login-box">
      <div class="card card-outline card-primary auth-card">
        <div class="card-header text-center py-4">
          <a href="#" class="h2 text-dark font-weight-bold">
            <i class="fas fa-file-contract text-primary mr-2"></i><b>SI PERDOK</b>
          </a>
          <p class="text-muted mb-0 small">Sistem Informasi Persetujuan Dokumen Kelayakan</p>
        </div>
        <div class="card-body login-card-body p-4">
          <p class="login-box-msg font-weight-bold text-secondary">Masuk ke dalam Akun Anda (Vue 3 SPA)</p>

          <div v-if="flash.info" class="alert alert-info py-2 small mb-3">{{ flash.info }}</div>
          <div v-if="flash.error || errors.email" class="alert alert-danger py-2 small mb-3">
            {{ flash.error || errors.email }}
          </div>

          <form @submit.prevent="submit">
            <div class="input-group mb-3">
              <input type="email" v-model="form.email" class="form-control" placeholder="Email" required autofocus>
              <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-envelope"></span></div>
              </div>
            </div>
            <div class="input-group mb-3">
              <input type="password" v-model="form.password" class="form-control" placeholder="Password" required>
              <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-lock"></span></div>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-8">
                <div class="icheck-primary">
                  <input type="checkbox" id="remember" v-model="form.remember">
                  <label for="remember" class="small text-muted">Ingat Saya</label>
                </div>
              </div>
              <div class="col-4">
                <button type="submit" :disabled="form.processing" class="btn btn-primary btn-block font-weight-bold">
                  {{ form.processing ? 'Proses...' : 'Masuk' }}
                </button>
              </div>
            </div>
          </form>

          <div class="border-top pt-3 mt-3">
            <p class="small text-muted font-weight-bold mb-2"><i class="fas fa-key mr-1"></i> Quick Demo Login Credentials:</p>
            <div class="btn-group-vertical w-100">
              <button class="btn btn-outline-info btn-sm btn-role mb-1" @click="fillLogin('pemohon@example.com', 'password')">
                <i class="fas fa-user-tie mr-2"></i> Pemohon: <code>pemohon@example.com</code>
              </button>
              <button class="btn btn-outline-success btn-sm btn-role mb-1" @click="fillLogin('penilai@example.com', 'password')">
                <i class="fas fa-user-check mr-2"></i> Penilai: <code>penilai@example.com</code>
              </button>
              <button class="btn btn-outline-secondary btn-sm btn-role" @click="fillLogin('admin@example.com', 'password')">
                <i class="fas fa-user-shield mr-2"></i> Admin: <code>admin@example.com</code>
              </button>
            </div>
          </div>

          <p class="mb-0 mt-3 text-center">
            Belum punya akun? <Link href="/register" class="font-weight-bold">Daftar Akun Pemohon</Link>
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
const flash = computed(() => page.props.flash || {});
const errors = computed(() => page.props.errors || {});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const fillLogin = (email, pass) => {
  form.email = email;
  form.password = pass;
};

const submit = () => {
  form.post('/login');
};
</script>

<style scoped>
.login-page-wrapper {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}
.login-box { width: 420px; }
.auth-card { border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
.btn-role { border-radius: 8px; font-weight: 600; text-align: left; }
</style>
