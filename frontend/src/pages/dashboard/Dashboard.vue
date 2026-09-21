<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { getDashboard } from '../../services/dashboardService'
import { formatCurrency } from '../../utils/formatCurrency'
import { formatDate } from '../../utils/formatDate'
import LoadingSpinner from '../../components/common/LoadingSpinner.vue'
import {
  Package,
  Boxes,
  AlertTriangle,
  XCircle,
  Tags,
  Warehouse,
  ArrowDownToLine,
  ArrowUpFromLine,
  Plus,
  TrendingUp,
  History,
  ArrowRight,
  Sparkles,
} from 'lucide-vue-next'

const router = useRouter()
const loading = ref(true)
const errorMessage = ref('')
const dashboard = ref(null)

const loadDashboardData = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const res = await getDashboard()
    dashboard.value = res.data.data
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Gagal memuat data dashboard.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadDashboardData()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-xl sm:text-2xl font-black text-slate-800 flex items-center gap-2">
          <span>Dashboard Inventaris</span>
          <span class="inline-flex items-center text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-700">
            Realtime
          </span>
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Monitoring pergerakan stok, kondisi gudang, dan performa penjualan ElectroStock.
        </p>
      </div>

      <!-- Quick Action Buttons (Touch friendly on Android/Samsung) -->
      <div class="flex flex-wrap items-center gap-2">
        <button
          @click="router.push('/stock/in')"
          class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-3.5 py-2 text-xs sm:text-sm font-bold text-white shadow-xs hover:bg-blue-700 active:scale-95 transition"
        >
          <ArrowDownToLine :size="16" />
          <span>+ Stock In</span>
        </button>

        <button
          @click="router.push('/stock/out')"
          class="inline-flex items-center gap-1.5 rounded-xl bg-slate-800 px-3.5 py-2 text-xs sm:text-sm font-bold text-white shadow-xs hover:bg-slate-900 active:scale-95 transition"
        >
          <ArrowUpFromLine :size="16" />
          <span>Stock Out</span>
        </button>

        <button
          @click="router.push('/products')"
          class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs sm:text-sm font-bold text-slate-700 hover:bg-slate-50 active:scale-95 transition"
        >
          <Plus :size="16" />
          <span>Produk</span>
        </button>
      </div>
    </div>

    <LoadingSpinner v-if="loading" text="Menyiapkan data dashboard..." />

    <div v-else-if="errorMessage" class="rounded-2xl bg-red-50 p-5 text-sm text-red-600 border border-red-200">
      {{ errorMessage }}
    </div>

    <template v-else-if="dashboard">
      <!-- 1. STATS GRID (6 Key Cards) -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <!-- Total Produk -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">Total Produk</span>
            <div class="rounded-lg bg-blue-50 p-2 text-blue-600">
              <Package :size="16" />
            </div>
          </div>
          <p class="mt-2 text-xl sm:text-2xl font-black text-slate-900">
            {{ dashboard.statistics.total_products }}
          </p>
          <p class="text-[11px] text-slate-400 mt-0.5">Item terdaftar</p>
        </div>

        <!-- Total Stok Fisik -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">Total Stok</span>
            <div class="rounded-lg bg-emerald-50 p-2 text-emerald-600">
              <Boxes :size="16" />
            </div>
          </div>
          <p class="mt-2 text-xl sm:text-2xl font-black text-emerald-600">
            {{ dashboard.statistics.total_stock }}
          </p>
          <p class="text-[11px] text-slate-400 mt-0.5">Unit di gudang</p>
        </div>

        <!-- Stok Menipis -->
        <div class="rounded-2xl border border-amber-200/80 bg-amber-50/40 p-4 shadow-xs hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wide">Stok Menipis</span>
            <div class="rounded-lg bg-amber-100 p-2 text-amber-700">
              <AlertTriangle :size="16" />
            </div>
          </div>
          <p class="mt-2 text-xl sm:text-2xl font-black text-amber-700">
            {{ dashboard.statistics.low_stock }}
          </p>
          <p class="text-[11px] text-amber-600 font-medium mt-0.5">Perlu restock</p>
        </div>

        <!-- Stok Habis -->
        <div class="rounded-2xl border border-red-200/80 bg-red-50/40 p-4 shadow-xs hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold text-red-700 uppercase tracking-wide">Stok Habis</span>
            <div class="rounded-lg bg-red-100 p-2 text-red-700">
              <XCircle :size="16" />
            </div>
          </div>
          <p class="mt-2 text-xl sm:text-2xl font-black text-red-700">
            {{ dashboard.statistics.out_of_stock }}
          </p>
          <p class="text-[11px] text-red-600 font-medium mt-0.5">Segera isi stok</p>
        </div>

        <!-- Kategori -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">Kategori</span>
            <div class="rounded-lg bg-indigo-50 p-2 text-indigo-600">
              <Tags :size="16" />
            </div>
          </div>
          <p class="mt-2 text-xl sm:text-2xl font-black text-slate-900">
            {{ dashboard.statistics.total_categories }}
          </p>
          <p class="text-[11px] text-slate-400 mt-0.5">Kategori aktif</p>
        </div>

        <!-- Brand -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs hover:shadow-md transition">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wide">Brand Merek</span>
            <div class="rounded-lg bg-purple-50 p-2 text-purple-600">
              <Warehouse :size="16" />
            </div>
          </div>
          <p class="mt-2 text-xl sm:text-2xl font-black text-slate-900">
            {{ dashboard.statistics.total_brands }}
          </p>
          <p class="text-[11px] text-slate-400 mt-0.5">Merk terdaftar</p>
        </div>
      </div>

      <!-- 2. FINANCIAL HIGHLIGHT (Bulan Ini) -->
      <div class="rounded-2xl border border-blue-100 bg-linear-to-r from-blue-900 via-indigo-900 to-slate-900 p-5 sm:p-6 text-white shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-white/10">
          <div class="flex items-center gap-2.5">
            <div class="p-2 rounded-xl bg-white/10 backdrop-blur-xs text-yellow-400">
              <TrendingUp :size="20" />
            </div>
            <div>
              <h2 class="text-base sm:text-lg font-bold">Ringkasan Operasional Penjualan</h2>
              <p class="text-xs text-slate-300">Kalkulasi periode bulan ini ({{ dashboard.financial.month }})</p>
            </div>
          </div>
          <button
            @click="router.push('/reports')"
            class="text-xs font-semibold text-blue-200 hover:text-white flex items-center gap-1 self-start sm:self-auto transition"
          >
            Lihat Laporan Lengkap <ArrowRight :size="13" />
          </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4">
          <div>
            <span class="text-xs text-slate-300 block">Total Penjualan (Revenue)</span>
            <span class="text-xl sm:text-2xl font-black text-white mt-1 block">
              {{ formatCurrency(dashboard.financial.revenue) }}
            </span>
          </div>
          <div>
            <span class="text-xs text-slate-300 block">Total Modal Pokok (HPP)</span>
            <span class="text-xl sm:text-2xl font-black text-slate-300 mt-1 block">
              {{ formatCurrency(dashboard.financial.cost) }}
            </span>
          </div>
          <div>
            <span class="text-xs text-emerald-300 block font-semibold">Keuntungan Operasional (Profit)</span>
            <span class="text-xl sm:text-2xl font-black text-emerald-400 mt-1 block">
              {{ formatCurrency(dashboard.financial.profit) }}
            </span>
          </div>
        </div>
      </div>

      <!-- 3. TWO-COLUMN SPLIT: Perhatian Stok & Transaksi Terbaru -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left: Produk Perlu Perhatian (Stok Menipis / Habis) -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs space-y-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <AlertTriangle :size="18" class="text-amber-500" />
              <h3 class="text-sm sm:text-base font-bold text-slate-800">
                Peringatan Stok Rendah
              </h3>
            </div>
            <button
              @click="router.push('/products')"
              class="text-xs font-semibold text-blue-600 hover:underline"
            >
              Semua Produk
            </button>
          </div>

          <div
            v-if="dashboard.low_stock_products.length === 0 && dashboard.out_of_stock_products.length === 0"
            class="p-8 text-center text-slate-400"
          >
            <p class="text-2xl mb-1">🎉</p>
            <p class="text-xs sm:text-sm font-semibold text-slate-600">Semua stok produk dalam kondisi aman!</p>
          </div>

          <div v-else class="space-y-2.5">
            <!-- Out of Stock List -->
            <div
              v-for="p in dashboard.out_of_stock_products"
              :key="'out-' + p.id"
              class="flex items-center justify-between rounded-xl bg-red-50/60 p-3 border border-red-100 transition hover:bg-red-50"
            >
              <div class="min-w-0 pr-3">
                <div class="text-xs sm:text-sm font-bold text-slate-900 truncate">{{ p.name }}</div>
                <div class="text-[11px] text-slate-500 font-mono">
                  {{ p.code }} • {{ p.brand?.name || '-' }}
                </div>
              </div>
              <div class="flex items-center gap-2 shrink-0">
                <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-[11px] font-bold text-red-700">
                  HABIS (0)
                </span>
                <button
                  @click="router.push('/stock/in')"
                  class="rounded-lg bg-blue-600 px-2.5 py-1 text-xs font-bold text-white hover:bg-blue-700 transition"
                >
                  + Stok
                </button>
              </div>
            </div>

            <!-- Low Stock List -->
            <div
              v-for="p in dashboard.low_stock_products"
              :key="'low-' + p.id"
              class="flex items-center justify-between rounded-xl bg-amber-50/60 p-3 border border-amber-100 transition hover:bg-amber-50"
            >
              <div class="min-w-0 pr-3">
                <div class="text-xs sm:text-sm font-bold text-slate-900 truncate">{{ p.name }}</div>
                <div class="text-[11px] text-slate-500 font-mono">
                  {{ p.code }} • {{ p.brand?.name || '-' }}
                </div>
              </div>
              <div class="flex items-center gap-2 shrink-0">
                <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-[11px] font-bold text-amber-800">
                  Sisa {{ p.stock }} {{ p.unit }}
                </span>
                <button
                  @click="router.push('/stock/in')"
                  class="rounded-lg bg-blue-600 px-2.5 py-1 text-xs font-bold text-white hover:bg-blue-700 transition"
                >
                  + Stok
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Aktivitas Transaksi Terbaru -->
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs space-y-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <History :size="18" class="text-blue-600" />
              <h3 class="text-sm sm:text-base font-bold text-slate-800">
                Transaksi Mutasi Terbaru
              </h3>
            </div>
            <button
              @click="router.push('/stock/history')"
              class="text-xs font-semibold text-blue-600 hover:underline"
            >
              Lihat Riwayat
            </button>
          </div>

          <div v-if="dashboard.recent_transactions.length === 0" class="p-8 text-center text-slate-400">
            <p class="text-2xl mb-1">📝</p>
            <p class="text-xs sm:text-sm font-semibold">Belum ada aktivitas transaksi.</p>
          </div>

          <div v-else class="space-y-2.5">
            <div
              v-for="t in dashboard.recent_transactions"
              :key="t.id"
              class="flex items-center justify-between rounded-xl p-3 border border-slate-100 hover:bg-slate-50 transition"
            >
              <div class="flex items-center gap-3 min-w-0">
                <span
                  class="rounded-lg px-2 py-1 text-[11px] font-extrabold shrink-0"
                  :class="t.type === 'IN' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                >
                  {{ t.type }}
                </span>
                <div class="min-w-0 truncate">
                  <div class="text-xs sm:text-sm font-bold text-slate-800 truncate">
                    {{ t.product_name_snapshot }}
                  </div>
                  <div class="text-[11px] text-slate-400">
                    {{ formatDate(t.transaction_date, true) }} • {{ t.creator?.name || 'Admin' }}
                  </div>
                </div>
              </div>

              <div class="text-right shrink-0 pl-2">
                <span
                  class="text-xs sm:text-sm font-extrabold block"
                  :class="t.type === 'IN' ? 'text-emerald-600' : 'text-rose-600'"
                >
                  {{ t.type === 'IN' ? '+' : '-' }}{{ t.quantity }}
                </span>
                <span class="text-[11px] text-slate-400 block font-medium">
                  {{ formatCurrency(t.type === 'IN' ? t.purchase_price : t.selling_price) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>