<template>
  <app-layout page-title="Edit Profil">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="card card-outline card-primary shadow-sm">
          <div class="card-header">
            <h3 class="card-title font-weight-bold">
              <i class="fas fa-user-cog text-primary mr-2"></i> Informasi Akun
            </h3>
          </div>

          <form @submit.prevent="submit">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="font-weight-bold">Nama Lengkap <span class="text-danger">*</span></label>
                    <form-input-group v-model="form.name" icon="fas fa-user" placeholder="Nama lengkap pemohon" autocomplete="name" required />
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label class="font-weight-bold">Email <span class="text-danger">*</span></label>
                    <form-input-group v-model="form.email" icon="fas fa-envelope" type="email" placeholder="nama@email.com" autocomplete="email" required />
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label class="font-weight-bold">No. Telepon / WhatsApp</label>
                    <form-input-group v-model="form.phone" icon="fas fa-phone" placeholder="08123456789" autocomplete="tel" />
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label class="font-weight-bold">NIK / NIP</label>
                    <form-input-group v-model="form.nip_nik" icon="fas fa-id-card" placeholder="NIK atau NIP pengguna" />
                  </div>
                </div>

                <div class="col-12">
                  <div class="form-group mb-0">
                    <label class="font-weight-bold">Perusahaan / Instansi</label>
                    <form-input-group v-model="form.company_name" icon="fas fa-building" placeholder="Nama perusahaan atau instansi" autocomplete="organization" />
                  </div>
                </div>
              </div>
            </div>

            <div class="card-header border-top">
              <h3 class="card-title font-weight-bold">
                <i class="fas fa-lock text-secondary mr-2"></i> Ubah Password
              </h3>
            </div>

            <div class="card-body">
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group mb-md-0">
                    <label class="font-weight-bold">Password Saat Ini</label>
                    <form-input-group v-model="form.current_password" icon="fas fa-lock" type="password" placeholder="Masukkan password saat ini" autocomplete="current-password" />
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group mb-md-0">
                    <label class="font-weight-bold">Password Baru</label>
                    <form-input-group v-model="form.password" icon="fas fa-key" type="password" placeholder="Password baru" autocomplete="new-password" />
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group mb-0">
                    <label class="font-weight-bold">Konfirmasi Password Baru</label>
                    <form-input-group v-model="form.password_confirmation" icon="fas fa-check-circle" type="password" placeholder="Ulangi password baru" autocomplete="new-password" />
                  </div>
                </div>
              </div>
              <p class="small text-muted mb-0 mt-3">
                Kosongkan bagian password jika tidak ingin mengubah password.
              </p>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
              <Link href="/dashboard" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
              </Link>
              <button type="submit" class="btn btn-primary font-weight-bold" :disabled="processing">
                <i :class="processing ? 'fas fa-circle-notch fa-spin mr-1' : 'fas fa-save mr-1'"></i>
                {{ processing ? 'Menyimpan...' : 'Simpan Profil' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import AppLayout from '../../layouts/AppLayout.vue';
import FormInputGroup from '../../components/FormInputGroup.vue';
import { apiErrorMessages, confirmAction, toast } from '../../lib/feedback';

const processing = ref(false);

const form = reactive({
  name: '',
  email: '',
  phone: '',
  nip_nik: '',
  company_name: '',
  current_password: '',
  password: '',
  password_confirmation: '',
});

const loadProfile = async () => {
  const response = await window.axios.get('/api/v1/me');
  const user = response.data.data.user;

  form.name = user.name || '';
  form.email = user.email || '';
  form.phone = user.phone || '';
  form.nip_nik = user.nip_nik || '';
  form.company_name = user.company_name || '';
};

const resetPasswordFields = () => {
  form.current_password = '';
  form.password = '';
  form.password_confirmation = '';
};

const submit = async () => {
  const confirmed = await confirmAction({
    title: 'Simpan perubahan profil?',
    text: 'Data akun Anda akan diperbarui.',
    icon: 'question',
    confirmButtonText: 'Ya, simpan',
    confirmButtonColor: '#007bff',
  });

  if (!confirmed) {
    return;
  }

  processing.value = true;

  try {
    await window.axios.put('/api/v1/profile', { ...form });
    resetPasswordFields();
    window.dispatchEvent(new Event('siperdok:profile-updated'));
    toast('success', 'Profil berhasil diperbarui.');
  } catch (error) {
    apiErrorMessages(error, 'Profil gagal diperbarui.')
      .slice(0, 3)
      .forEach((message) => toast('error', message));
  } finally {
    processing.value = false;
  }
};

onMounted(() => {
  loadProfile();
});
</script>
