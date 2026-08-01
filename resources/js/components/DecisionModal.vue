<template>
  <div class="card card-outline card-success shadow-sm">
    <div class="card-header bg-light">
      <h3 class="card-title font-weight-bold text-success">
        <i class="fas fa-gavel mr-2"></i> Form Keputusan Penilaian (Vue 3 Component)
      </h3>
    </div>
    <div class="card-body">
      <form :action="actionUrl" method="POST" @submit="handleSubmit">
        <input type="hidden" name="_token" :value="csrfToken">
        
        <div class="form-group mb-3">
          <label class="font-weight-bold">Pilih Keputusan Penilaian <span class="text-danger">*</span></label>
          <div class="btn-group-toggle d-flex">
            <button 
              type="button" 
              :class="['btn flex-fill mr-1 font-weight-bold', decision === 'approved' ? 'btn-success' : 'btn-outline-success']"
              @click="decision = 'approved'"
            >
              <i class="fas fa-check-circle mr-1"></i> SETUJU
            </button>

            <button 
              type="button" 
              :class="['btn flex-fill mr-1 font-weight-bold', decision === 'revision' ? 'btn-warning' : 'btn-outline-warning']"
              @click="decision = 'revision'"
            >
              <i class="fas fa-edit mr-1"></i> REVISI
            </button>

            <button 
              type="button" 
              :class="['btn flex-fill font-weight-bold', decision === 'rejected' ? 'btn-danger' : 'btn-outline-danger']"
              @click="decision = 'rejected'"
            >
              <i class="fas fa-times-circle mr-1"></i> DITOLAK
            </button>
          </div>
          <input type="hidden" name="decision" :value="decision">
        </div>

        <div class="form-group mb-4">
          <label class="font-weight-bold">Catatan Penilai / Alasan Decision <span class="text-danger">*</span></label>
          <textarea 
            name="notes" 
            v-model="notes" 
            class="form-control" 
            rows="5" 
            placeholder="Tuliskan catatan evaluasi, poin revisi yang wajib diperbaiki, atau alasan penolakan..." 
            required
          ></textarea>
        </div>

        <button 
          type="submit" 
          :disabled="isSubmitting || !notes.trim()"
          class="btn btn-success btn-block btn-lg font-weight-bold shadow-sm"
        >
          <i class="fas fa-paper-plane mr-2"></i> {{ isSubmitting ? 'Memproses...' : 'Simpan Keputusan Penilaian' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  actionUrl: {
    type: String,
    required: true
  },
  csrfToken: {
    type: String,
    required: true
  }
});

const decision = ref('approved');
const notes = ref('');
const isSubmitting = ref(false);

const handleSubmit = () => {
  isSubmitting.value = true;
};
</script>
