<script setup>
import { ref, reactive, onMounted } from 'vue'
import { getStockReport, getTransactionReport, getProfitReport } from '../../services/reportService'
import { getCategories } from '../../services/categoryService'
import { getBrands } from '../../services/brandService'
import { formatCurrency } from '../../utils/formatCurrency'
import { formatDate } from '../../utils/formatDate'
import LoadingSpinner from '../../components/common/LoadingSpinner.vue'
import { Search, RotateCcw, BarChart3, ArrowDownRight, ArrowUpRight } from 'lucide-vue-next'

const activeTab = ref('stock') // 'stock' | 'transactions' | 'profit'
const loading = ref(false)
const error = ref('')

const categories = ref([])
const brands = ref([])

const stockReport = ref(null)
const transactionReport = ref(null)
const profitReport = ref(null)

// Filters for Reports
const stockFilters = reactive({
  search: '',
  category_id: '',
  brand_id: '',
  status: '',
})

const transFilters = reactive({
  search: '',
  category_id: '',
  brand_id: '',
  type: '',
  start_date: '',
  end_date: '',
})

const profitYear = ref(new Date().getFullYear())

let debounceTimer = null

const loadActiveReport = async () => {
  loading.value = true
  error.value = ''
  try {
    if (activeTab.value === 'stock') {
      const params = {}
      if (stockFilters.search) params.search = stockFilters.search
      if (stockFilters.category_id) params.category_id = stockFilters.category_id
      if (stockFilters.brand_id) params.brand_id = stockFilters.brand_id
      if (stockFilters.status) params.status = stockFilters.status

      const res = await getStockReport(params)
      stockReport.value = res.data.data
    } else if (activeTab.value === 'transactions') {
      const params = {}
      if (transFilters.search) params.search = transFilters.search
      if (transFilters.category_id) params.category_id = transFilters.category_id
      if (transFilters.brand_id) params.brand_id = transFilters.brand_id
      if (transFilters.type) params.type = transFilters.type
      if (transFilters.start_date) params.start_date = transFilters.start_date
      if (transFilters.end_date) params.end_date = transFilters.end_date

      const res = await getTransactionReport(params)
      transactionReport.value = res.data.data
    } else if (activeTab.value === 'profit') {
      const res = await getProfitReport({ year: profitYear.value })
      profitReport.value = res.data.data
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal memuat laporan.'
  } finally {
    loading.value = false
  }
}

const onSearchInput = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    loadActiveReport()
  }, 350)
}

const switchTab = (tab) => {
  activeTab.value = tab
  loadActiveReport()
}

const resetStockFilters = () => {
  stockFilters.search = ''
  stockFilters.category_id = ''
  stockFilters.brand_id = ''
  stockFilters.status = ''
  loadActiveReport()
}

const resetTransFilters = () => {
  transFilters.search = ''
  transFilters.category_id = ''
  transFilters.brand_id = ''
  transFilters.type = ''
  transFilters.start_date = ''
  transFilters.end_date = ''
  loadActiveReport()
}

onMounted(async () => {
  try {
    const [cRes, bRes] = await Promise.all([
      getCategories(),
      getBrands(),
    ])
    categories.value = cRes.data.data || []
    brands.value = bRes.data.data || []
  } catch (e) {
    console.error(e)
  }
  loadActiveReport()
})
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-xl sm:text-2xl font-bold text-slate-800 flex items-center gap-2">
        <BarChart3 class="text-blue-600" :size="24" />
        Laporan Inventaris & Finansial
      </h1>
      <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
        Monitoring kondisi stok produk, audit transaksi masuk/keluar, serta kalkulasi keuntungan operasional.
      </p>
    </div>

    <!-- Navigation Tabs (3-Column Segmented Control, Fully Visible simultaneously on Mobile & Desktop) -->
    <div class="grid grid-cols-3 gap-1.5 sm:gap-2 p-1.5 bg-slate-100/90 rounded-2xl border border-slate-200/80 shadow-2xs">
      <button
        @click="switchTab('stock')"
        type="button"
        class="flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-2 py-2 sm:py-2.5 px-1.5 sm:px-3 rounded-xl transition-all text-center select-none"
        :class="
          activeTab === 'stock'
            ? 'bg-white text-blue-600 shadow-xs border border-slate-200/70 font-bold'
            : 'text-slate-600 hover:text-slate-900 hover:bg-white/60 border border-transparent font-medium'
        "
      >
        <span class="text-sm sm:text-base shrink-0">📦</span>
        <span class="text-[11px] sm:text-xs md:text-sm font-bold leading-tight">
          <span class="block sm:inline">Kondisi</span>
          <span class="sm:ml-1">Stok</span>
        </span>
      </button>

      <button
        @click="switchTab('transactions')"
        type="button"
        class="flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-2 py-2 sm:py-2.5 px-1.5 sm:px-3 rounded-xl transition-all text-center select-none"
        :class="
          activeTab === 'transactions'
            ? 'bg-white text-blue-600 shadow-xs border border-slate-200/70 font-bold'
            : 'text-slate-600 hover:text-slate-900 hover:bg-white/60 border border-transparent font-medium'
        "
      >
        <span class="text-sm sm:text-base shrink-0">🔄</span>
        <span class="text-[11px] sm:text-xs md:text-sm font-bold leading-tight">
          <span class="block sm:inline">Riwayat</span>
          <span class="sm:ml-1">Transaksi</span>
          <span class="hidden md:inline"> Mutasi</span>
        </span>
      </button>

      <button
        @click="switchTab('profit')"
        type="button"
        class="flex flex-col sm:flex-row items-center justify-center gap-1 sm:gap-2 py-2 sm:py-2.5 px-1.5 sm:px-3 rounded-xl transition-all text-center select-none"
        :class="
          activeTab === 'profit'
            ? 'bg-white text-blue-600 shadow-xs border border-slate-200/70 font-bold'
            : 'text-slate-600 hover:text-slate-900 hover:bg-white/60 border border-transparent font-medium'
        "
      >
        <span class="text-sm sm:text-base shrink-0">💰</span>
        <span class="text-[11px] sm:text-xs md:text-sm font-bold leading-tight">
          <span class="block sm:inline">Profit</span>
          <span class="sm:ml-1">Operasional</span>
        </span>
      </button>
    </div>

    <!-- TAB 1: STOCK REPORT -->
    <div v-if="activeTab === 'stock'" class="space-y-5">
      <!-- Filter Bar -->
      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
          <div class="relative">
            <Search :size="16" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              v-model="stockFilters.search"
              @input="onSearchInput"
              type="text"
              placeholder="Cari produk / kode..."
              class="w-full rounded-xl border border-slate-300 pl-9 pr-3 py-2 text-xs sm:text-sm outline-none focus:border-blue-500"
            />
          </div>

          <div>
            <select
              v-model="stockFilters.category_id"
              @change="loadActiveReport"
              class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs sm:text-sm outline-none focus:border-blue-500 bg-white"
            >
              <option value="">Semua Kategori</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <div>
            <select
              v-model="stockFilters.brand_id"
              @change="loadActiveReport"
              class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs sm:text-sm outline-none focus:border-blue-500 bg-white"
            >
              <option value="">Semua Brand</option>
              <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>

          <div class="flex items-center gap-2">
            <select
              v-model="stockFilters.status"
              @change="loadActiveReport"
              class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs sm:text-sm outline-none focus:border-blue-500 bg-white"
            >
              <option value="">Semua Status Stok</option>
              <option value="safe">Aman</option>
              <option value="low_stock">Menipis</option>
              <option value="out_of_stock">Habis</option>
            </select>
            <button
              type="button"
              @click="resetStockFilters"
              title="Reset Filter"
              class="rounded-xl border border-slate-300 p-2 text-slate-500 hover:bg-slate-100 shrink-0"
            >
              <RotateCcw :size="16" />
            </button>
          </div>
        </div>
      </div>

      <LoadingSpinner v-if="loading" text="Memuat laporan stok..." />

      <template v-else-if="stockReport">
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <div class="rounded-2xl bg-white p-4 border border-slate-200 shadow-xs">
            <p class="text-xs text-slate-500">Total Produk</p>
            <p class="text-xl sm:text-2xl font-black text-slate-800 mt-1">{{ stockReport.summary.total_products }}</p>
          </div>
          <div class="rounded-2xl bg-white p-4 border border-slate-200 shadow-xs">
            <p class="text-xs text-slate-500">Total Stok Fisik</p>
            <p class="text-xl sm:text-2xl font-black text-blue-600 mt-1">{{ stockReport.summary.total_stock }}</p>
          </div>
          <div class="rounded-2xl bg-white p-4 border border-slate-200 shadow-xs">
            <p class="text-xs text-slate-500">Stok Menipis</p>
            <p class="text-xl sm:text-2xl font-black text-amber-600 mt-1">{{ stockReport.summary.low_stock }}</p>
          </div>
          <div class="rounded-2xl bg-white p-4 border border-slate-200 shadow-xs">
            <p class="text-xs text-slate-500">Stok Habis</p>
            <p class="text-xl sm:text-2xl font-black text-red-600 mt-1">{{ stockReport.summary.out_of_stock }}</p>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
          <div class="overflow-x-auto">
            <table class="w-full min-w-[650px] text-left text-sm">
              <thead class="border-b border-slate-100 bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider">
                <tr>
                  <th class="px-4 py-3.5">Kode</th>
                  <th class="px-4 py-3.5">Produk</th>
                  <th class="px-4 py-3.5">Kategori</th>
                  <th class="px-4 py-3.5">Brand</th>
                  <th class="px-4 py-3.5">Harga Beli</th>
                  <th class="px-4 py-3.5">Harga Jual</th>
                  <th class="px-4 py-3.5">Stok Saat Ini</th>
                  <th class="px-4 py-3.5">Batas Min</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-if="(stockReport.products?.data || []).length === 0">
                  <td colspan="8" class="px-4 py-10 text-center text-slate-400">Tidak ada produk yang cocok.</td>
                </tr>
                <tr v-for="item in (stockReport.products?.data || [])" :key="item.id" class="hover:bg-slate-50/80 transition">
                  <td class="px-4 py-3.5 font-mono text-xs font-semibold text-slate-600">{{ item.code }}</td>
                  <td class="px-4 py-3.5 font-bold text-slate-900">{{ item.name }}</td>
                  <td class="px-4 py-3.5 text-slate-600">{{ item.category?.name || '-' }}</td>
                  <td class="px-4 py-3.5 text-slate-600">{{ item.brand?.name || '-' }}</td>
                  <td class="px-4 py-3.5 text-slate-600 font-medium">{{ formatCurrency(item.purchase_price) }}</td>
                  <td class="px-4 py-3.5 text-blue-600 font-bold">{{ formatCurrency(item.selling_price) }}</td>
                  <td class="px-4 py-3.5 font-extrabold" :class="item.stock === 0 ? 'text-red-600' : item.stock <= item.minimum_stock ? 'text-amber-600' : 'text-slate-800'">
                    {{ item.stock }} {{ item.unit }}
                  </td>
                  <td class="px-4 py-3.5 text-slate-400 text-xs">{{ item.minimum_stock }} {{ item.unit }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </div>

    <!-- TAB 2: TRANSACTIONS REPORT -->
    <div v-else-if="activeTab === 'transactions'" class="space-y-5">
      <!-- Filter Bar -->
      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
          <div class="relative">
            <Search :size="16" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              v-model="transFilters.search"
              @input="onSearchInput"
              type="text"
              placeholder="Cari produk / kode..."
              class="w-full rounded-xl border border-slate-300 pl-9 pr-3 py-2 text-xs sm:text-sm outline-none focus:border-blue-500"
            />
          </div>

          <div>
            <select
              v-model="transFilters.category_id"
              @change="loadActiveReport"
              class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs sm:text-sm outline-none focus:border-blue-500 bg-white"
            >
              <option value="">Semua Kategori</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <div>
            <select
              v-model="transFilters.brand_id"
              @change="loadActiveReport"
              class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs sm:text-sm outline-none focus:border-blue-500 bg-white"
            >
              <option value="">Semua Brand</option>
              <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>

          <div class="flex items-center gap-2">
            <select
              v-model="transFilters.type"
              @change="loadActiveReport"
              class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs sm:text-sm outline-none focus:border-blue-500 bg-white"
            >
              <option value="">Semua Tipe Transaksi</option>
              <option value="IN">Stok Masuk (IN)</option>
              <option value="OUT">Stok Keluar (OUT)</option>
            </select>
            <button
              type="button"
              @click="resetTransFilters"
              title="Reset Filter"
              class="rounded-xl border border-slate-300 p-2 text-slate-500 hover:bg-slate-100 shrink-0"
            >
              <RotateCcw :size="16" />
            </button>
          </div>
        </div>
      </div>

      <LoadingSpinner v-if="loading" text="Memuat laporan transaksi..." />

      <template v-else-if="transactionReport">
        <div class="grid grid-cols-2 gap-3">
          <div class="rounded-2xl bg-white p-4 border border-slate-200 shadow-xs">
            <p class="text-xs text-slate-500">Total Kuantitas Masuk (IN)</p>
            <p class="text-xl sm:text-2xl font-black text-emerald-600 mt-1">+{{ transactionReport.summary.total_in }} unit</p>
          </div>
          <div class="rounded-2xl bg-white p-4 border border-slate-200 shadow-xs">
            <p class="text-xs text-slate-500">Total Kuantitas Keluar (OUT)</p>
            <p class="text-xl sm:text-2xl font-black text-rose-600 mt-1">-{{ transactionReport.summary.total_out }} unit</p>
          </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
          <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left text-sm">
              <thead class="border-b border-slate-100 bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider">
                <tr>
                  <th class="px-4 py-3.5">Tipe</th>
                  <th class="px-4 py-3.5">Tanggal</th>
                  <th class="px-4 py-3.5">Produk Snapshot</th>
                  <th class="px-4 py-3.5 text-center">Qty</th>
                  <th class="px-4 py-3.5">Harga Beli</th>
                  <th class="px-4 py-3.5">Harga Jual</th>
                  <th class="px-4 py-3.5">Keterangan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-if="(transactionReport.transactions?.data || []).length === 0">
                  <td colspan="7" class="px-4 py-10 text-center text-slate-400">Tidak ada transaksi yang cocok.</td>
                </tr>
                <tr v-for="t in (transactionReport.transactions?.data || [])" :key="t.id" class="hover:bg-slate-50/80 transition">
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
                  <td class="px-4 py-3.5 text-xs text-slate-500 whitespace-nowrap">{{ formatDate(t.transaction_date, true) }}</td>
                  <td class="px-4 py-3.5">
                    <div class="font-bold text-slate-900">{{ t.product_name_snapshot }}</div>
                    <div class="text-xs text-slate-400">{{ t.product_code_snapshot }} • {{ t.brand_name_snapshot }}</div>
                  </td>
                  <td class="px-4 py-3.5 text-center font-extrabold" :class="t.type === 'IN' ? 'text-emerald-700' : 'text-rose-700'">
                    {{ t.quantity }}
                  </td>
                  <td class="px-4 py-3.5 text-slate-600 font-medium whitespace-nowrap">{{ formatCurrency(t.purchase_price) }}</td>
                  <td class="px-4 py-3.5 text-blue-600 font-semibold whitespace-nowrap">{{ formatCurrency(t.selling_price) }}</td>
                  <td class="px-4 py-3.5 text-xs text-slate-500 max-w-xs truncate">{{ t.description || '-' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </div>

    <!-- TAB 3: PROFIT REPORT -->
    <div v-else-if="activeTab === 'profit'" class="space-y-5">
      <!-- Year Selector -->
      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs flex items-center justify-between">
        <label class="text-xs font-bold text-slate-700 uppercase">Pilih Tahun Laporan</label>
        <select
          v-model="profitYear"
          @change="loadActiveReport"
          class="rounded-xl border border-slate-300 px-3.5 py-1.5 text-sm font-semibold text-slate-800 outline-none focus:border-blue-500 bg-white"
        >
          <option :value="2026">2026</option>
          <option :value="2025">2025</option>
          <option :value="2024">2024</option>
        </select>
      </div>

      <LoadingSpinner v-if="loading" text="Memuat laporan profit..." />

      <template v-else-if="profitReport">
        <!-- Big Financial Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          <div class="rounded-2xl bg-white p-5 border border-slate-200 shadow-xs">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Total Revenue (Penjualan)</p>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ formatCurrency(profitReport.summary.revenue) }}</p>
          </div>
          <div class="rounded-2xl bg-white p-5 border border-slate-200 shadow-xs">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wide">Total Cost (Modal Beli)</p>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ formatCurrency(profitReport.summary.cost) }}</p>
          </div>
          <div class="rounded-2xl bg-emerald-50/70 p-5 border border-emerald-200 shadow-xs">
            <p class="text-xs font-bold text-emerald-800 uppercase tracking-wide">Keuntungan Bersih (Profit)</p>
            <p class="text-2xl font-black text-emerald-700 mt-1">{{ formatCurrency(profitReport.summary.profit) }}</p>
          </div>
        </div>

        <!-- Monthly Breakdown Table -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
          <div class="overflow-x-auto">
            <table class="w-full min-w-[550px] text-left text-sm">
              <thead class="border-b border-slate-100 bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider">
                <tr>
                  <th class="px-5 py-3.5">Bulan</th>
                  <th class="px-5 py-3.5">Revenue (Penjualan)</th>
                  <th class="px-5 py-3.5">Cost (Modal)</th>
                  <th class="px-5 py-3.5">Profit Operasional</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="m in profitReport.monthly" :key="m.month" class="hover:bg-slate-50/80 transition">
                  <td class="px-5 py-3.5 font-bold text-slate-800">Bulan {{ m.month }}</td>
                  <td class="px-5 py-3.5 text-slate-700 font-medium">{{ formatCurrency(m.revenue) }}</td>
                  <td class="px-5 py-3.5 text-slate-700 font-medium">{{ formatCurrency(m.cost) }}</td>
                  <td class="px-5 py-3.5 font-extrabold" :class="m.profit >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                    {{ formatCurrency(m.profit) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>
