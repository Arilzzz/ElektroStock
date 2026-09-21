<script setup>
import AppModal from '../common/AppModal.vue'
import { formatCurrency } from '../../utils/formatCurrency'

const props = defineProps({
  product: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close'])

const getStatusBadge = (product) => {
  if (!product) return { text: '-', color: 'bg-slate-100 text-slate-700' }
  if (product.stock === 0) {
    return { text: 'Habis', color: 'bg-red-100 text-red-700' }
  }
  if (product.stock <= product.minimum_stock) {
    return { text: 'Menipis', color: 'bg-yellow-100 text-yellow-700' }
  }
  return { text: 'Aman', color: 'bg-emerald-100 text-emerald-700' }
}
</script>

<template>
  <AppModal title="Detail Produk" maxWidth="max-w-2xl" @close="emit('close')">
    <div v-if="product" class="space-y-6">
      <div class="flex flex-col md:flex-row gap-6 items-start">
        <!-- Product Image -->
        <div class="w-full md:w-48 h-48 rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
          <img
            v-if="product.image_url || product.image"
            :src="product.image_url || ('/storage/' + product.image)"
            :alt="product.name"
            class="w-full h-full object-cover"
          />
          <span v-else class="text-xs text-slate-400 font-medium">Tidak ada gambar</span>
        </div>

        <!-- Basic Info -->
        <div class="flex-1 space-y-3">
          <div class="flex items-center gap-2">
            <span class="text-xs font-mono font-semibold px-2.5 py-1 bg-slate-100 text-slate-700 rounded-md">
              {{ product.code }}
            </span>
            <span
              class="text-xs font-semibold px-2.5 py-1 rounded-full"
              :class="getStatusBadge(product).color"
            >
              {{ getStatusBadge(product).text }}
            </span>
          </div>

          <h2 class="text-xl font-bold text-slate-900">{{ product.name }}</h2>

          <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
              <span class="text-xs text-slate-400 block">Kategori</span>
              <span class="font-medium text-slate-700">{{ product.category?.name || '-' }}</span>
            </div>
            <div>
              <span class="text-xs text-slate-400 block">Brand / Merek</span>
              <span class="font-medium text-slate-700">{{ product.brand?.name || '-' }}</span>
            </div>
            <div>
              <span class="text-xs text-slate-400 block">Tipe / Model</span>
              <span class="font-medium text-slate-700">{{ product.type_model || '-' }}</span>
            </div>
            <div>
              <span class="text-xs text-slate-400 block">Satuan</span>
              <span class="font-medium text-slate-700">{{ product.unit || 'unit' }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Pricing & Stock Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
          <span class="text-xs text-slate-400 block">Harga Beli</span>
          <span class="text-sm font-bold text-slate-800">{{ formatCurrency(product.purchase_price) }}</span>
        </div>
        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
          <span class="text-xs text-slate-400 block">Harga Jual</span>
          <span class="text-sm font-bold text-blue-600">{{ formatCurrency(product.selling_price) }}</span>
        </div>
        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
          <span class="text-xs text-slate-400 block">Stok Saat Ini</span>
          <span class="text-sm font-bold text-slate-800">{{ product.stock }} {{ product.unit }}</span>
        </div>
        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
          <span class="text-xs text-slate-400 block">Minimum Stok</span>
          <span class="text-sm font-bold text-amber-600">{{ product.minimum_stock }} {{ product.unit }}</span>
        </div>
      </div>

      <!-- Description -->
      <div v-if="product.description" class="space-y-1">
        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Deskripsi</span>
        <p class="text-sm text-slate-600 bg-slate-50 p-3.5 rounded-xl whitespace-pre-line border border-slate-100">
          {{ product.description }}
        </p>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end">
        <button
          type="button"
          @click="emit('close')"
          class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200 transition"
        >
          Tutup
        </button>
      </div>
    </template>
  </AppModal>
</template>
