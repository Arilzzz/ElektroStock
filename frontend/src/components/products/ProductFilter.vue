<script setup>
import { Search } from 'lucide-vue-next'

defineProps({
  search: String,
  category: String,
  brand: String,
  status: String,
  categories: {
    type: Array,
    default: () => [],
  },
  brands: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits([
  'update:search',
  'update:category',
  'update:brand',
  'update:status',
])
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 rounded-2xl border border-slate-200 bg-white p-4 shadow-xs">
    <!-- Search Input -->
    <div class="relative">
      <Search :size="16" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
      <input
        :value="search"
        @input="emit('update:search', $event.target.value)"
        type="text"
        placeholder="Cari nama, kode, tipe..."
        class="w-full rounded-xl border border-slate-300 pl-9 pr-3.5 py-2.5 text-xs sm:text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
      />
    </div>

    <!-- Category Filter -->
    <div>
      <select
        :value="category"
        @change="emit('update:category', $event.target.value)"
        class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-xs sm:text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-white"
      >
        <option value="">Semua Kategori</option>
        <option v-for="item in categories" :key="item.id" :value="item.id">
          {{ item.name }}
        </option>
      </select>
    </div>

    <!-- Brand Filter -->
    <div>
      <select
        :value="brand"
        @change="emit('update:brand', $event.target.value)"
        class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-xs sm:text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-white"
      >
        <option value="">Semua Brand</option>
        <option v-for="item in brands" :key="item.id" :value="item.id">
          {{ item.name }}
        </option>
      </select>
    </div>

    <!-- Status Filter -->
    <div>
      <select
        :value="status"
        @change="emit('update:status', $event.target.value)"
        class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-xs sm:text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-white"
      >
        <option value="">Semua Status Stok</option>
        <option value="aman">Aman</option>
        <option value="menipis">Menipis</option>
        <option value="habis">Habis</option>
      </select>
    </div>
  </div>
</template>