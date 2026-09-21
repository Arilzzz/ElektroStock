<script setup>
defineProps({
  modelValue: [String, Number],
  label: String,
  type: {
    type: String,
    default: 'text',
  },
  placeholder: String,
  required: Boolean,
  disabled: Boolean,
  error: String,
  id: String,
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <div class="space-y-1.5">
    <label v-if="label" :for="id" class="block text-sm font-medium text-slate-700">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <input
      :id="id"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
      @input="emit('update:modelValue', $event.target.value)"
      class="w-full rounded-lg border px-3.5 py-2.5 text-sm outline-none transition disabled:bg-slate-50 disabled:text-slate-400"
      :class="error ? 'border-red-500 focus:border-red-500 focus:ring-2 focus:ring-red-100' : 'border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100'"
    />
    <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
  </div>
</template>
