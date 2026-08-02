<template>
  <span :class="['badge', badgeClass, 'px-2 py-1']">
    <i :class="iconClass" class="mr-1"></i>
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue';
import { projectStatusMeta } from '../constants/statuses';

const props = defineProps({
  status: {
    type: String,
    required: true
  }
});

const meta = computed(() => projectStatusMeta(props.status));
const badgeClass = computed(() => meta.value.className);
const iconClass = computed(() => meta.value.icon);
const label = computed(() => meta.value.label);
</script>
