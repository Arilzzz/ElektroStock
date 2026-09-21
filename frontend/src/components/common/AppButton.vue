<script setup>
defineProps({
  variant: {
    type: String,
    default: 'primary', // primary, secondary, danger, outline
  },
  size: {
    type: String,
    default: 'md', // sm, md, lg
  },
  loading: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  type: {
    type: String,
    default: 'button',
  },
})
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    class="inline-flex items-center justify-center font-medium transition rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-50"
    :class="[
      // Size
      size === 'sm' ? 'px-3 py-1.5 text-xs' : size === 'lg' ? 'px-6 py-3 text-base' : 'px-4 py-2 text-sm',
      // Variant
      variant === 'primary' ? 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500' :
      variant === 'secondary' ? 'bg-slate-100 text-slate-700 hover:bg-slate-200 focus:ring-slate-400' :
      variant === 'danger' ? 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500' :
      variant === 'outline' ? 'border border-slate-300 text-slate-700 hover:bg-slate-50 focus:ring-slate-300' :
      'bg-blue-600 text-white hover:bg-blue-700'
    ]"
  >
    <svg
      v-if="loading"
      class="mr-2 h-4 w-4 animate-spin"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
    </svg>
    <slot />
  </button>
</template>
