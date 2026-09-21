<script setup>
import { AlertTriangle } from 'lucide-vue-next'
import AppModal from '../common/AppModal.vue'

defineProps({
  product: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close', 'confirm'])
</script>

<template>
  <AppModal title="Hapus Produk" maxWidth="max-w-md" @close="emit('close')">
    <div class="flex items-start gap-4">
      <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
        <AlertTriangle :size="24" />
      </div>
      <div class="space-y-2">
        <p class="text-sm text-slate-700">
          Apakah Anda yakin ingin menghapus produk
          <strong class="font-semibold text-slate-900">{{ product?.name }}</strong> ({{ product?.code }})?
        </p>
        <p class="text-xs text-slate-500 bg-amber-50 p-2.5 rounded-lg border border-amber-200">
          Catatan: Histori transaksi stok barang ini akan tetap tersimpan sebagai arsip snapshot.
        </p>
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
          Batal
        </button>
        <button
          type="button"
          :disabled="loading"
          @click="emit('confirm')"
          class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition disabled:opacity-50"
        >
          {{ loading ? 'Menghapus...' : 'Hapus Produk' }}
        </button>
      </div>
    </template>
  </AppModal>
</template>
