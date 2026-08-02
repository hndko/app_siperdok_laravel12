<template>
  <div class="input-group">
    <div class="input-group-prepend">
      <span class="input-group-text">
        <i :class="icon" aria-hidden="true"></i>
      </span>
    </div>

    <select
      v-if="type === 'select'"
      class="form-control"
      :value="modelValue"
      :required="required"
      :disabled="disabled"
      :aria-label="ariaLabel || placeholder"
      @change="$emit('update:modelValue', $event.target.value)"
    >
      <slot></slot>
    </select>

    <textarea
      v-else-if="type === 'textarea'"
      class="form-control"
      :value="modelValue"
      :rows="rows"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
      :aria-label="ariaLabel || placeholder"
      @input="$emit('update:modelValue', $event.target.value)"
    ></textarea>

    <div v-else-if="type === 'file'" class="custom-file">
      <input
        type="file"
        class="custom-file-input"
        :required="required"
        :disabled="disabled"
        :accept="accept"
        :aria-label="ariaLabel || placeholder"
        @change="$emit('change', $event)"
      >
      <label class="custom-file-label text-muted">{{ fileName || placeholder || 'Pilih berkas' }}</label>
    </div>

    <input
      v-else
      class="form-control"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :autocomplete="autocomplete"
      :required="required"
      :disabled="disabled"
      :autofocus="autofocus"
      :aria-label="ariaLabel || placeholder"
      @input="$emit('update:modelValue', $event.target.value)"
    >
  </div>
</template>

<script setup>
defineEmits(['update:modelValue', 'change']);

defineProps({
  modelValue: { type: [String, Number], default: '' },
  icon: { type: String, required: true },
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  autocomplete: { type: String, default: undefined },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  autofocus: { type: Boolean, default: false },
  rows: { type: [Number, String], default: 4 },
  accept: { type: String, default: undefined },
  fileName: { type: String, default: '' },
  ariaLabel: { type: String, default: '' },
});
</script>
