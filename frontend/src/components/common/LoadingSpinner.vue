<script setup>
defineProps({
  size: {
    type: String,
    default: 'md', // sm, md, lg
  },
  text: {
    type: String,
    default: '',
  },
})
</script>

<template>
  <div class="flex flex-col items-center justify-center gap-3 p-4">
    <div
      class="animate-spin rounded-full border-4 border-slate-200 border-t-blue-600"
      :class="{
        'h-6 w-6 border-2': size === 'sm',
        'h-10 w-10 border-3': size === 'md',
        'h-14 w-14 border-4': size === 'lg',
      }"
    ></div>
    <p v-if="text" class="text-sm font-medium text-slate-500">{{ text }}</p>
  </div>
</template>
