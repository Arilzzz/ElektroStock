<script setup>
import { X } from 'lucide-vue-next'

defineProps({
  title: {
    type: String,
    default: '',
  },
  maxWidth: {
    type: String,
    default: 'max-w-lg',
  },
})

const emit = defineEmits(['close'])
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/50 p-4 backdrop-blur-xs">
    <div
      class="w-full rounded-2xl bg-white shadow-2xl transition-all"
      :class="maxWidth"
    >
      <!-- Modal Header -->
      <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
        <h3 class="text-lg font-semibold text-slate-800">{{ title }}</h3>
        <button
          @click="emit('close')"
          type="button"
          class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
        >
          <X :size="20" />
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6">
        <slot />
      </div>

      <!-- Modal Footer (Optional) -->
      <div v-if="$slots.footer" class="border-t border-slate-100 bg-slate-50 px-6 py-4 rounded-b-2xl">
        <slot name="footer" />
      </div>
    </div>
  </div>
</template>
