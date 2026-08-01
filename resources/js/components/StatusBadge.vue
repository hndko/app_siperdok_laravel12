<template>
  <span :class="['badge', badgeClass, 'px-2 py-1']">
    <i :class="iconClass" class="mr-1"></i>
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    required: true
  }
});

const badgeClass = computed(() => {
  switch (props.status) {
    case 'draft': return 'badge-draft';
    case 'submitted': return 'badge-submitted';
    case 'in_review': return 'badge-in_review';
    case 'revision': return 'badge-revision';
    case 'approved': return 'badge-approved';
    case 'certificate_issued': return 'badge-success';
    case 'rejected': return 'badge-rejected';
    default: return 'badge-secondary';
  }
});

const iconClass = computed(() => {
  switch (props.status) {
    case 'draft': return 'fas fa-pen';
    case 'submitted': return 'fas fa-paper-plane';
    case 'in_review': return 'fas fa-clock';
    case 'revision': return 'fas fa-edit';
    case 'approved': return 'fas fa-check-circle';
    case 'certificate_issued': return 'fas fa-certificate';
    case 'rejected': return 'fas fa-times-circle';
    default: return 'fas fa-info-circle';
  }
});

const label = computed(() => {
  switch (props.status) {
    case 'draft': return 'Draft';
    case 'submitted': return 'Telah Dikirim';
    case 'in_review': return 'Dalam Penilaian';
    case 'revision': return 'Perlu Revisi';
    case 'approved': return 'Disetujui (Approved)';
    case 'certificate_issued': return 'Certificate Terbit';
    case 'rejected': return 'Ditolak (Rejected)';
    default: return props.status.toUpperCase();
  }
});
</script>
