<script setup>
import { AlertTriangle } from 'lucide-vue-next'
import AppModal from './AppModal.vue'

defineProps({
  title: {
    type: String,
    default: 'Konfirmasi Tindakan',
  },
  message: {
    type: String,
    default: 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
  },
  confirmText: {
    type: String,
    default: 'Ya, Lanjutkan',
  },
  cancelText: {
    type: String,
    default: 'Batal',
  },
  loading: {
    type: Boolean,
    default: false,
  },
  danger: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['confirm', 'close'])
</script>

<template>
  <AppModal :title="title" maxWidth="max-w-md" @close="emit('close')">
    <div class="flex items-start gap-4">
      <div
        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full"
        :class="danger ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600'"
      >
        <AlertTriangle :size="24" />
      </div>
      <div>
        <p class="text-sm text-slate-600 leading-relaxed">{{ message }}</p>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end gap-3">
        <button
          type="button"
          :disabled="loading"
          @click="emit('close')"
          class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition"
        >
          {{ cancelText }}
        </button>
        <button
          type="button"
          :disabled="loading"
          @click="emit('confirm')"
          class="rounded-lg px-4 py-2 text-sm font-medium text-white transition disabled:opacity-50"
          :class="danger ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'"
        >
          {{ loading ? 'Memproses...' : confirmText }}
        </button>
      </div>
    </template>
  </AppModal>
</template>
