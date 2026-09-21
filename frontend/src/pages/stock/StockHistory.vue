<script setup>
import { ref, reactive, onMounted } from 'vue'
import { getStockHistory } from '../../services/stockService'
import { getCategories } from '../../services/categoryService'
import { getBrands } from '../../services/brandService'
import { formatCurrency } from '../../utils/formatCurrency'
import { formatDate } from '../../utils/formatDate'
import LoadingSpinner from '../../components/common/LoadingSpinner.vue'
import { Search, Filter, RotateCcw, ArrowDownRight, ArrowUpRight } from 'lucide-vue-next'

const transactions = ref([])
const categories = ref([])
const brands = ref([])
const loading = ref(false)
const error = ref('')

const filters = reactive({
  search: '',
  category_id: '',
  brand_id: '',
  type: '',
})

let debounceTimeout = null

const fetchHistory = async () => {
  loading.value = true
  error.value = ''
  try {
    const params = {}
    if (filters.search) params.search = filters.search
    if (filters.category_id) params.category_id = filters.category_id
    if (filters.brand_id) params.brand_id = filters.brand_id
    if (filters.type) params.type = filters.type

    const res = await getStockHistory(params)
    const data = res.data.data
    transactions.value = Array.isArray(data) ? data : (data?.data || [])
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal memuat riwayat transaksi.'
  } finally {
    loading.value = false
  }
}

const onSearchInput = () => {
  clearTimeout(debounceTimeout)
  debounceTimeout = setTimeout(() => {
    fetchHistory()
  }, 350)
}

const resetFilters = () => {
  filters.search = ''
  filters.category_id = ''
  filters.brand_id = ''
  filters.type = ''
  fetchHistory()
}

onMounted(async () => {
  try {
    const [catsRes, brandsRes] = await Promise.all([
      getCategories(),
      getBrands(),
    ])
    categories.value = catsRes.data.data || []
    brands.value = brandsRes.data.data || []
  } catch (e) {
    console.error(e)
  }
  fetchHistory()
})
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold text-slate-800">Riwayat Mutasi Stok</h1>
      <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
        Histori lengkap pergerakan stok masuk dan keluar dengan data snapshot audit trail.
      </p>
    </div>

    <!-- Filter Bar (Responsive for Samsung/Android & Desktop) -->
    <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-xs space-y-3">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Search Input -->
        <div class="relative">
          <Search :size="16" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            v-model="filters.search"
            @input="onSearchInput"
            type="text"
            placeholder="Cari nama / kode produk..."
            class="w-full rounded-xl border border-slate-300 pl-9 pr-3.5 py-2.5 text-xs sm:text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
          />
        </div>

        <!-- Filter Kategori -->
        <div>
          <select
            v-model="filters.category_id"
            @change="fetchHistory"
            class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-xs sm:text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-white"
          >
            <option value="">Semua Kategori</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>

        <!-- Filter Brand -->
        <div>
          <select
            v-model="filters.brand_id"
            @change="fetchHistory"
            class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-xs sm:text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-white"
          >
            <option value="">Semua Brand</option>
            <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
          </select>
        </div>

        <!-- Filter Tipe (IN / OUT) & Reset Button -->
        <div class="flex items-center gap-2">
          <select
            v-model="filters.type"
            @change="fetchHistory"
            class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-xs sm:text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100 bg-white"
          >
            <option value="">Semua Tipe (IN & OUT)</option>
            <option value="IN">Stok Masuk (IN)</option>
            <option value="OUT">Stok Keluar (OUT)</option>
          </select>

          <button
            type="button"
            @click="resetFilters"
            title="Reset Filter"
            class="rounded-xl border border-slate-300 p-2.5 text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition active:scale-95 shrink-0"
          >
            <RotateCcw :size="16" />
          </button>
        </div>
      </div>
    </div>

    <LoadingSpinner v-if="loading" text="Memuat riwayat stok..." />

    <div v-else-if="error" class="rounded-2xl bg-red-50 p-4 text-sm text-red-600 border border-red-200">
      {{ error }}
    </div>

    <!-- Table Container (Horizontal Scroll optimized for Mobile Samsung) -->
    <div v-else class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[760px] text-left text-sm">
          <thead class="border-b border-slate-100 bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider">
            <tr>
              <th class="px-4 py-3.5">Tipe</th>
              <th class="px-4 py-3.5">Tanggal</th>
              <th class="px-4 py-3.5">Produk Snapshot</th>
              <th class="px-4 py-3.5 text-center">Jumlah (Qty)</th>
              <th class="px-4 py-3.5">Harga Beli</th>
              <th class="px-4 py-3.5">Harga Jual</th>
              <th class="px-4 py-3.5">Admin</th>
              <th class="px-4 py-3.5">Keterangan</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="transactions.length === 0">
              <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                <p class="text-3xl mb-1">📋</p>
                <p class="font-medium text-sm">Tidak ada riwayat transaksi yang cocok dengan filter.</p>
              </td>
            </tr>

            <tr v-for="t in transactions" :key="t.id" class="hover:bg-slate-50/80 transition">
              <!-- Type Badge -->
              <td class="px-4 py-3.5 whitespace-nowrap">
                <span
                  class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold"
                  :class="t.type === 'IN' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                >
                  <ArrowDownRight v-if="t.type === 'IN'" :size="13" />
                  <ArrowUpRight v-else :size="13" />
                  {{ t.type }}
                </span>
              </td>

              <!-- Date -->
              <td class="px-4 py-3.5 text-xs text-slate-500 whitespace-nowrap font-medium">
                {{ formatDate(t.transaction_date, true) }}
              </td>

              <!-- Product Snapshot -->
              <td class="px-4 py-3.5">
                <div class="font-bold text-slate-900">{{ t.product_name_snapshot }}</div>
                <div class="text-xs text-slate-400 font-mono">
                  {{ t.product_code_snapshot }} • {{ t.brand_name_snapshot || '-' }} <span v-if="t.product_type_snapshot">({{ t.product_type_snapshot }})</span>
                </div>
              </td>

              <!-- Qty -->
              <td class="px-4 py-3.5 text-center font-extrabold text-slate-900">
                <span :class="t.type === 'IN' ? 'text-emerald-700' : 'text-rose-700'">
                  {{ t.type === 'IN' ? '+' : '-' }}{{ t.quantity }}
                </span>
              </td>

              <!-- Prices -->
              <td class="px-4 py-3.5 text-slate-600 font-medium whitespace-nowrap">
                {{ formatCurrency(t.purchase_price) }}
              </td>
              <td class="px-4 py-3.5 text-blue-600 font-semibold whitespace-nowrap">
                {{ formatCurrency(t.selling_price) }}
              </td>

              <!-- Admin User -->
              <td class="px-4 py-3.5 text-xs font-medium text-slate-600 whitespace-nowrap">
                {{ t.creator?.name || 'Admin' }}
              </td>

              <!-- Description -->
              <td class="px-4 py-3.5 text-xs text-slate-500 max-w-xs truncate">
                {{ t.description || '-' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
