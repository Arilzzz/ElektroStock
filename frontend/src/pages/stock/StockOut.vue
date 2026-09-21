<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { getProducts } from '../../services/productService'
import { getCategories } from '../../services/categoryService'
import { getBrands } from '../../services/brandService'
import { stockOut } from '../../services/stockService'
import { formatCurrency } from '../../utils/formatCurrency'
import LoadingSpinner from '../../components/common/LoadingSpinner.vue'
import {
  ArrowUpFromLine,
  Search,
  CheckCircle2,
  AlertCircle,
  TrendingUp,
} from 'lucide-vue-next'

const products = ref([])
const categories = ref([])
const brands = ref([])

const loading = ref(false)
const submitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const searchQuery = ref('')
const selectedCategoryFilter = ref('')
const selectedBrandFilter = ref('')
const isDropdownOpen = ref(false)
const selectedProduct = ref(null)

const form = reactive({
  product_id: '',
  quantity: 1,
  selling_price: '',
  transaction_date: new Date().toISOString().slice(0, 16),
  description: '',
})

const fetchProductsAndMetadata = async () => {
  loading.value = true
  try {
    const [prodsRes, catsRes, brandsRes] = await Promise.all([
      getProducts(),
      getCategories(),
      getBrands(),
    ])
    const data = prodsRes.data.data
    products.value = Array.isArray(data) ? data : (data?.data || [])
    categories.value = catsRes.data.data || []
    brands.value = brandsRes.data.data || []
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

// Filtered products for dropdown search
const filteredDropdownProducts = computed(() => {
  const query = searchQuery.value.toLowerCase().trim()
  return products.value.filter((p) => {
    const matchQuery =
      !query ||
      p.name?.toLowerCase().includes(query) ||
      p.code?.toLowerCase().includes(query) ||
      p.type_model?.toLowerCase().includes(query) ||
      p.brand?.name?.toLowerCase().includes(query) ||
      p.category?.name?.toLowerCase().includes(query)

    const matchCat =
      !selectedCategoryFilter.value || p.category_id == selectedCategoryFilter.value
    const matchBrand =
      !selectedBrandFilter.value || p.brand_id == selectedBrandFilter.value

    return matchQuery && matchCat && matchBrand
  })
})

const selectProduct = (p) => {
  selectedProduct.value = p
  form.product_id = p.id
  form.selling_price = p.selling_price
  searchQuery.value = `[${p.code}] ${p.name}`
  isDropdownOpen.value = false
}

const clearSelectedProduct = () => {
  selectedProduct.value = null
  form.product_id = ''
  form.selling_price = ''
  searchQuery.value = ''
}

// Profit Preview calculation based on PRD Section 10
const estimatedProfit = computed(() => {
  if (!selectedProduct.value || !form.quantity || !form.selling_price) return null
  const revenue = form.quantity * form.selling_price
  const cost = form.quantity * selectedProduct.value.purchase_price
  return {
    revenue,
    cost,
    profit: revenue - cost,
  }
})

const handleSubmit = async () => {
  if (!selectedProduct.value) {
    errorMessage.value = 'Silakan pilih produk terlebih dahulu.'
    return
  }

  if (form.quantity > selectedProduct.value.stock) {
    errorMessage.value = `Stok tidak mencukupi. Stok saat ini hanya ${selectedProduct.value.stock} ${selectedProduct.value.unit}.`
    return
  }

  submitting.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    await stockOut({
      product_id: form.product_id,
      quantity: form.quantity,
      selling_price: form.selling_price,
      transaction_date: form.transaction_date,
      description: form.description,
    })

    successMessage.value = `Berhasil mencatat pengeluaran ${form.quantity} ${selectedProduct.value.unit} untuk "${selectedProduct.value.name}"!`
    form.quantity = 1
    form.description = ''
    clearSelectedProduct()
    await fetchProductsAndMetadata()
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Gagal mengeluarkan stok.'
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchProductsAndMetadata()
})
</script>

<template>
  <div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-xl sm:text-2xl font-bold text-slate-800 flex items-center gap-2">
        <ArrowUpFromLine class="text-rose-600" :size="24" />
        Stock Out (Barang Keluar)
      </h1>
      <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
        Catat penjualan atau pengeluaran barang dari gudang dengan validasi stok aktual.
      </p>
    </div>

    <!-- Alert Messages -->
    <div v-if="successMessage" class="flex items-start gap-3 rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-800 border border-emerald-200 shadow-xs">
      <CheckCircle2 class="text-emerald-600 shrink-0 mt-0.5" :size="20" />
      <div>
        <p class="font-semibold">Transaksi Berhasil</p>
        <p>{{ successMessage }}</p>
      </div>
    </div>

    <div v-if="errorMessage" class="flex items-start gap-3 rounded-2xl bg-red-50 p-4 text-sm text-red-700 border border-red-200 shadow-xs">
      <AlertCircle class="text-red-600 shrink-0 mt-0.5" :size="20" />
      <div>
        <p class="font-semibold">Validasi Stok</p>
        <p>{{ errorMessage }}</p>
      </div>
    </div>

    <!-- Main Card -->
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7 shadow-xs space-y-6">
      <!-- Search & Combobox -->
      <div class="space-y-3">
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
          Pilih Produk Yang Dikeluarkan <span class="text-red-500">*</span>
        </label>

        <!-- Mini Category & Brand Filters -->
        <div class="grid grid-cols-2 gap-2">
          <select
            v-model="selectedCategoryFilter"
            class="w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs text-slate-700 outline-none focus:border-blue-500"
          >
            <option value="">Semua Kategori</option>
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>

          <select
            v-model="selectedBrandFilter"
            class="w-full rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs text-slate-700 outline-none focus:border-blue-500"
          >
            <option value="">Semua Brand</option>
            <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
          </select>
        </div>

        <div class="relative">
          <div class="relative">
            <Search :size="18" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              v-model="searchQuery"
              @focus="isDropdownOpen = true"
              type="text"
              placeholder="Ketik nama produk, kode barang, tipe, atau brand..."
              class="w-full rounded-xl border border-slate-300 pl-10 pr-10 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
            />
            <button
              v-if="selectedProduct"
              @click="clearSelectedProduct"
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 hover:text-slate-600 p-1"
            >
              ✕
            </button>
          </div>

          <!-- Dropdown items -->
          <div
            v-if="isDropdownOpen && filteredDropdownProducts.length > 0"
            class="absolute z-30 mt-1 max-h-60 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl"
          >
            <div
              v-for="p in filteredDropdownProducts"
              :key="p.id"
              @mousedown.prevent="selectProduct(p)"
              class="flex items-center justify-between rounded-lg p-2.5 hover:bg-slate-50 cursor-pointer transition"
              :class="p.stock === 0 ? 'opacity-50 pointer-events-none bg-slate-50' : ''"
            >
              <div>
                <div class="text-sm font-semibold text-slate-800">{{ p.name }}</div>
                <div class="text-xs text-slate-400">
                  <span class="font-mono text-slate-600">[{{ p.code }}]</span> • {{ p.brand?.name || '-' }}
                </div>
              </div>
              <div class="text-right">
                <span
                  class="inline-block rounded-md px-2 py-0.5 text-xs font-bold"
                  :class="p.stock === 0 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-800'"
                >
                  {{ p.stock === 0 ? 'Habis' : `Stok: ${p.stock} ${p.unit}` }}
                </span>
                <div class="text-[11px] text-blue-600 font-bold mt-0.5">{{ formatCurrency(p.selling_price) }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Selected Product Info -->
      <div v-if="selectedProduct" class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-2">
        <div class="flex items-center justify-between">
          <div>
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Produk Terpilih</span>
            <h3 class="text-base font-bold text-slate-900">{{ selectedProduct.name }}</h3>
          </div>
          <div class="text-right">
            <span class="text-xs text-slate-500 block">Stok Gudang</span>
            <span
              class="text-lg font-black"
              :class="selectedProduct.stock === 0 ? 'text-red-600' : 'text-slate-900'"
            >
              {{ selectedProduct.stock }} {{ selectedProduct.unit }}
            </span>
          </div>
        </div>
      </div>

      <!-- Stock Out Form -->
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
              Jumlah Keluar (Qty) <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="form.quantity"
              type="number"
              min="1"
              :max="selectedProduct ? selectedProduct.stock : undefined"
              required
              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-100 transition"
            />
            <span v-if="selectedProduct" class="text-[11px] text-slate-400 mt-1 block">
              Maksimal kuantitas: {{ selectedProduct.stock }} {{ selectedProduct.unit }}
            </span>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
              Harga Jual Aktual (Rp) <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.selling_price"
              type="number"
              min="0"
              required
              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-100 transition"
            />
          </div>
        </div>

        <!-- Live Profit Preview -->
        <div v-if="estimatedProfit" class="rounded-xl bg-emerald-50/70 p-3.5 border border-emerald-200 space-y-1.5">
          <div class="flex items-center gap-1.5 text-emerald-800 text-xs font-bold">
            <TrendingUp :size="14" />
            <span>Estimasi Keuntungan Transaksi:</span>
          </div>
          <div class="grid grid-cols-3 text-xs">
            <div>
              <span class="text-slate-400 block">Total Revenue</span>
              <span class="font-bold text-slate-800">{{ formatCurrency(estimatedProfit.revenue) }}</span>
            </div>
            <div>
              <span class="text-slate-400 block">Total Modal HPP</span>
              <span class="font-bold text-slate-800">{{ formatCurrency(estimatedProfit.cost) }}</span>
            </div>
            <div>
              <span class="text-emerald-700 block font-semibold">Laba Operasional</span>
              <span class="font-black text-emerald-700">{{ formatCurrency(estimatedProfit.profit) }}</span>
            </div>
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
            Tanggal Transaksi <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.transaction_date"
            type="datetime-local"
            required
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-100 transition"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
            Keterangan Penjualan / Pengeluaran
          </label>
          <textarea
            v-model="form.description"
            rows="2"
            placeholder="misal: Penjualan langsung ke konsumen retail"
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-100 transition"
          ></textarea>
        </div>

        <button
          type="submit"
          :disabled="submitting || !selectedProduct || selectedProduct.stock === 0"
          class="w-full rounded-xl bg-rose-600 py-3.5 text-sm font-bold text-white transition hover:bg-rose-700 active:bg-rose-800 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
        >
          {{ submitting ? 'Memproses Pengeluaran...' : 'Simpan Transaksi Stock Out' }}
        </button>
      </form>
    </div>
  </div>
</template>
