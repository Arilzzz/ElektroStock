<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { getProducts, createProduct } from '../../services/productService'
import { getCategories, createCategory } from '../../services/categoryService'
import { getBrands, createBrand } from '../../services/brandService'
import { stockIn } from '../../services/stockService'
import { formatCurrency } from '../../utils/formatCurrency'
import LoadingSpinner from '../../components/common/LoadingSpinner.vue'
import AppModal from '../../components/common/AppModal.vue'
import {
  PackagePlus,
  Search,
  CheckCircle2,
  AlertCircle,
  Plus,
  Sparkles,
  ArrowDownToLine,
  Filter,
} from 'lucide-vue-next'

const mode = ref('existing') // 'existing' | 'new'

const products = ref([])
const categories = ref([])
const brands = ref([])

const loading = ref(false)
const submitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

// Search & Combobox states for existing product
const searchQuery = ref('')
const selectedCategoryFilter = ref('')
const selectedBrandFilter = ref('')
const isDropdownOpen = ref(false)
const selectedProduct = ref(null)

// Form for Existing Product Stock In
const formExisting = reactive({
  product_id: '',
  quantity: 1,
  purchase_price: '',
  selling_price: '',
  transaction_date: new Date().toISOString().slice(0, 16),
  description: '',
})

// Form for New Product + Stock In
const formNew = reactive({
  code: '',
  name: '',
  category_id: '',
  brand_id: '',
  type_model: '',
  unit: 'unit',
  minimum_stock: 5,
  description: '',
  quantity: 1,
  purchase_price: '',
  selling_price: '',
  transaction_date: new Date().toISOString().slice(0, 16),
})

// Quick Category & Brand Modals
const showQuickCategoryModal = ref(false)
const quickCategoryName = ref('')
const quickCategoryDesc = ref('')
const quickCategoryLoading = ref(false)

const showQuickBrandModal = ref(false)
const quickBrandName = ref('')
const quickBrandDesc = ref('')
const quickBrandLoading = ref(false)

// Load Data
const loadMasterData = async () => {
  loading.value = true
  try {
    const [prodsRes, catsRes, brandsRes] = await Promise.all([
      getProducts(),
      getCategories(),
      getBrands(),
    ])
    const pData = prodsRes.data.data
    products.value = Array.isArray(pData) ? pData : (pData?.data || [])
    categories.value = catsRes.data.data || []
    brands.value = brandsRes.data.data || []
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

// Filtered dropdown for existing products
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
  formExisting.product_id = p.id
  formExisting.purchase_price = p.purchase_price
  formExisting.selling_price = p.selling_price
  searchQuery.value = `[${p.code}] ${p.name}`
  isDropdownOpen.value = false
}

const clearSelectedProduct = () => {
  selectedProduct.value = null
  formExisting.product_id = ''
  formExisting.purchase_price = ''
  formExisting.selling_price = ''
  searchQuery.value = ''
}

// Quick Create Category
const handleQuickCreateCategory = async () => {
  if (!quickCategoryName.value.trim()) return
  quickCategoryLoading.value = true
  try {
    const res = await createCategory({
      name: quickCategoryName.value,
      description: quickCategoryDesc.value,
    })
    const newCat = res.data.data
    categories.value.unshift(newCat)
    if (mode.value === 'new') {
      formNew.category_id = newCat.id
    } else {
      selectedCategoryFilter.value = newCat.id
    }
    showQuickCategoryModal.value = false
    quickCategoryName.value = ''
    quickCategoryDesc.value = ''
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal membuat kategori')
  } finally {
    quickCategoryLoading.value = false
  }
}

// Quick Create Brand
const handleQuickCreateBrand = async () => {
  if (!quickBrandName.value.trim()) return
  quickBrandLoading.value = true
  try {
    const res = await createBrand({
      name: quickBrandName.value,
      description: quickBrandDesc.value,
    })
    const newBrand = res.data.data
    brands.value.unshift(newBrand)
    if (mode.value === 'new') {
      formNew.brand_id = newBrand.id
    } else {
      selectedBrandFilter.value = newBrand.id
    }
    showQuickBrandModal.value = false
    quickBrandName.value = ''
    quickBrandDesc.value = ''
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal membuat brand')
  } finally {
    quickBrandLoading.value = false
  }
}

// Submit Stock In for Existing Product
const handleExistingSubmit = async () => {
  if (!formExisting.product_id) {
    errorMessage.value = 'Silakan cari dan pilih produk terlebih dahulu.'
    return
  }

  submitting.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    await stockIn({
      product_id: formExisting.product_id,
      quantity: formExisting.quantity,
      purchase_price: formExisting.purchase_price,
      selling_price: formExisting.selling_price || undefined,
      transaction_date: formExisting.transaction_date,
      description: formExisting.description,
    })

    successMessage.value = `Berhasil menambahkan ${formExisting.quantity} ${selectedProduct.value?.unit || 'unit'} ke stok ${selectedProduct.value?.name}!`
    formExisting.quantity = 1
    formExisting.description = ''
    clearSelectedProduct()
    await loadMasterData()
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Gagal mencatat Stock In.'
  } finally {
    submitting.value = false
  }
}

// Submit Stock In for New Product
const handleNewProductSubmit = async () => {
  submitting.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    // 1. Create Product first (stock default 0)
    const productPayload = {
      code: formNew.code,
      name: formNew.name,
      category_id: formNew.category_id,
      brand_id: formNew.brand_id,
      type_model: formNew.type_model,
      unit: formNew.unit,
      purchase_price: formNew.purchase_price,
      selling_price: formNew.selling_price,
      minimum_stock: formNew.minimum_stock,
      description: formNew.description,
    }

    const createdRes = await createProduct(productPayload)
    const createdProduct = createdRes.data.data

    // 2. Perform initial Stock In transaction
    await stockIn({
      product_id: createdProduct.id,
      quantity: formNew.quantity,
      purchase_price: formNew.purchase_price,
      selling_price: formNew.selling_price,
      transaction_date: formNew.transaction_date,
      description: `Stok awal produk baru: ${formNew.description || '-'}`,
    })

    successMessage.value = `Produk baru "${createdProduct.name}" berhasil dibuat dan stok awal sebanyak ${formNew.quantity} ${createdProduct.unit} berhasil dicatat!`

    // Reset Form
    formNew.code = ''
    formNew.name = ''
    formNew.category_id = ''
    formNew.brand_id = ''
    formNew.type_model = ''
    formNew.quantity = 1
    formNew.purchase_price = ''
    formNew.selling_price = ''
    formNew.description = ''

    await loadMasterData()
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Gagal membuat produk dan stok baru.'
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  loadMasterData()
})
</script>

<template>
  <div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-800 flex items-center gap-2">
          <ArrowDownToLine class="text-blue-600" :size="24" />
          Stock In (Barang Masuk)
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Catat stok masuk untuk barang yang sudah ada atau daftarkan produk baru langsung di sini.
        </p>
      </div>

      <!-- Mode Selector Tabs -->
      <div class="inline-flex rounded-xl bg-slate-200/80 p-1 text-xs font-semibold self-start sm:self-auto">
        <button
          type="button"
          @click="mode = 'existing'"
          class="rounded-lg px-3.5 py-2 transition"
          :class="mode === 'existing' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
        >
          Produk Ada
        </button>
        <button
          type="button"
          @click="mode = 'new'"
          class="rounded-lg px-3.5 py-2 flex items-center gap-1.5 transition"
          :class="mode === 'new' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
        >
          <Sparkles :size="13" />
          + Produk Baru
        </button>
      </div>
    </div>

    <!-- Alert Messages -->
    <div v-if="successMessage" class="flex items-start gap-3 rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-800 border border-emerald-200 shadow-xs">
      <CheckCircle2 class="text-emerald-600 shrink-0 mt-0.5" :size="20" />
      <div>
        <p class="font-semibold">Sukses!</p>
        <p>{{ successMessage }}</p>
      </div>
    </div>

    <div v-if="errorMessage" class="flex items-start gap-3 rounded-2xl bg-red-50 p-4 text-sm text-red-700 border border-red-200 shadow-xs">
      <AlertCircle class="text-red-600 shrink-0 mt-0.5" :size="20" />
      <div>
        <p class="font-semibold">Perhatian</p>
        <p>{{ errorMessage }}</p>
      </div>
    </div>

    <!-- TAB 1: STOCK IN PRODUK YANG SUDAH ADA -->
    <div v-if="mode === 'existing'" class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7 shadow-xs space-y-6">
      <!-- Search & Combobox Section -->
      <div class="space-y-3">
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
          Cari & Pilih Produk <span class="text-red-500">*</span>
        </label>

        <!-- Category & Brand Mini Filters -->
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

        <!-- Autocomplete Input -->
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

          <!-- Dropdown Results -->
          <div
            v-if="isDropdownOpen && filteredDropdownProducts.length > 0"
            class="absolute z-30 mt-1 max-h-60 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl"
          >
            <div
              v-for="p in filteredDropdownProducts"
              :key="p.id"
              @mousedown.prevent="selectProduct(p)"
              class="flex items-center justify-between rounded-lg p-2.5 hover:bg-blue-50 cursor-pointer transition"
            >
              <div>
                <div class="text-sm font-semibold text-slate-800">{{ p.name }}</div>
                <div class="text-xs text-slate-400">
                  <span class="font-mono text-slate-600">[{{ p.code }}]</span> • {{ p.brand?.name || '-' }} • {{ p.category?.name || '-' }}
                </div>
              </div>
              <div class="text-right">
                <span class="inline-block rounded-md bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700">
                  Stok: {{ p.stock }} {{ p.unit }}
                </span>
                <div class="text-[11px] text-slate-400 mt-0.5">{{ formatCurrency(p.purchase_price) }}</div>
              </div>
            </div>
          </div>

          <div
            v-else-if="isDropdownOpen && searchQuery.trim() && filteredDropdownProducts.length === 0"
            class="absolute z-30 mt-1 w-full rounded-xl border border-slate-200 bg-white p-4 text-center shadow-xl text-xs text-slate-500"
          >
            Produk tidak ditemukan.
            <button
              type="button"
              @click="mode = 'new'; formNew.name = searchQuery; isDropdownOpen = false"
              class="text-blue-600 font-bold ml-1 hover:underline"
            >
              + Buat Produk Ini Sekarang?
            </button>
          </div>
        </div>
      </div>

      <!-- Selected Product Summary Card -->
      <div v-if="selectedProduct" class="rounded-xl border border-blue-100 bg-blue-50/60 p-4 flex items-center justify-between">
        <div>
          <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">Produk Terpilih</span>
          <h3 class="text-base font-bold text-slate-900 mt-0.5">{{ selectedProduct.name }}</h3>
          <p class="text-xs text-slate-500">
            Kode: <span class="font-mono font-semibold text-slate-700">{{ selectedProduct.code }}</span> |
            Brand: <span class="font-semibold text-slate-700">{{ selectedProduct.brand?.name || '-' }}</span>
          </p>
        </div>
        <div class="text-right">
          <span class="text-xs text-slate-500 block">Stok Gudang</span>
          <span class="text-lg font-extrabold text-blue-700">{{ selectedProduct.stock }} {{ selectedProduct.unit }}</span>
        </div>
      </div>

      <!-- Stock In Form Inputs -->
      <form @submit.prevent="handleExistingSubmit" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
              Jumlah Masuk (Qty) <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="formExisting.quantity"
              type="number"
              min="1"
              required
              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
              Harga Beli / Modal (Rp) <span class="text-red-500">*</span>
            </label>
            <input
              v-model="formExisting.purchase_price"
              type="number"
              min="0"
              required
              placeholder="0"
              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
              Update Harga Jual (Rp)
            </label>
            <input
              v-model="formExisting.selling_price"
              type="number"
              min="0"
              placeholder="Biarkan kosong jika tetap"
              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
            />
            <span class="text-[10px] text-slate-400">Opsional: ubah harga jual jika ada kenaikan</span>
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
            Tanggal & Waktu Transaksi <span class="text-red-500">*</span>
          </label>
          <input
            v-model="formExisting.transaction_date"
            type="datetime-local"
            required
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
            Keterangan / Catatan Transaksi
          </label>
          <textarea
            v-model="formExisting.description"
            rows="2"
            placeholder="misal: Tambahan stok kulakan supplier"
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
          ></textarea>
        </div>

        <button
          type="submit"
          :disabled="submitting || !selectedProduct"
          class="w-full rounded-xl bg-blue-600 py-3.5 text-sm font-bold text-white transition hover:bg-blue-700 active:bg-blue-800 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
        >
          {{ submitting ? 'Menyimpan Mutasi Stok...' : 'Simpan Transaksi Stock In' }}
        </button>
      </form>
    </div>

    <!-- TAB 2: TAMBAH PRODUK BARU + STOCK IN SEKALIAN -->
    <div v-else class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7 shadow-xs space-y-6">
      <div class="rounded-xl bg-amber-50 p-3.5 border border-amber-200 flex items-start gap-2.5">
        <Sparkles :size="18" class="text-amber-600 mt-0.5 shrink-0" />
        <p class="text-xs text-amber-800 leading-relaxed">
          <strong>Mode All-in-One:</strong> Produk baru akan langsung didaftarkan ke sistem dan stok awal yang Anda masukkan di bawah ini akan otomatis dicatat ke riwayat transaksi <strong>Stock In</strong>.
        </p>
      </div>

      <form @submit.prevent="handleNewProductSubmit" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Kode Produk -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
              Kode Produk <span class="text-red-500">*</span>
            </label>
            <input
              v-model="formNew.code"
              type="text"
              required
              placeholder="misal: TV-LG-002"
              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
            />
          </div>

          <!-- Nama Produk -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
              Nama Produk <span class="text-red-500">*</span>
            </label>
            <input
              v-model="formNew.name"
              type="text"
              required
              placeholder="misal: LG UHD 4K Smart TV 50"
              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
            />
          </div>

          <!-- Kategori with Quick Add -->
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="text-xs font-bold text-slate-700 uppercase">
                Kategori <span class="text-red-500">*</span>
              </label>
              <button
                type="button"
                @click="showQuickCategoryModal = true"
                class="text-[11px] font-bold text-blue-600 hover:text-blue-700 flex items-center gap-0.5"
              >
                <Plus :size="12" /> + Kategori Baru
              </button>
            </div>
            <select
              v-model="formNew.category_id"
              required
              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
            >
              <option value="" disabled>-- Pilih Kategori --</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>

          <!-- Brand with Quick Add -->
          <div>
            <div class="flex items-center justify-between mb-1">
              <label class="text-xs font-bold text-slate-700 uppercase">
                Brand / Merek <span class="text-red-500">*</span>
              </label>
              <button
                type="button"
                @click="showQuickBrandModal = true"
                class="text-[11px] font-bold text-blue-600 hover:text-blue-700 flex items-center gap-0.5"
              >
                <Plus :size="12" /> + Brand Baru
              </button>
            </div>
            <select
              v-model="formNew.brand_id"
              required
              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
            >
              <option value="" disabled>-- Pilih Brand --</option>
              <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>

          <!-- Tipe / Model -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
              Tipe / Model <span class="text-red-500">*</span>
            </label>
            <input
              v-model="formNew.type_model"
              type="text"
              required
              placeholder="misal: 50UQ7500PSF"
              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
            />
          </div>

          <!-- Satuan -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
              Satuan <span class="text-red-500">*</span>
            </label>
            <input
              v-model="formNew.unit"
              type="text"
              required
              placeholder="unit / pcs"
              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
            />
          </div>

          <!-- Jumlah Masuk Awal -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
              Jumlah Masuk (Stok Awal) <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="formNew.quantity"
              type="number"
              min="1"
              required
              placeholder="1"
              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
            />
          </div>

          <!-- Minimum Stok -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
              Batas Minimum Stok <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="formNew.minimum_stock"
              type="number"
              min="0"
              required
              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
            />
          </div>

          <!-- Harga Beli -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
              Harga Beli / Modal (Rp) <span class="text-red-500">*</span>
            </label>
            <input
              v-model="formNew.purchase_price"
              type="number"
              min="0"
              required
              placeholder="0"
              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
            />
          </div>

          <!-- Harga Jual -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
              Harga Jual (Rp) <span class="text-red-500">*</span>
            </label>
            <input
              v-model="formNew.selling_price"
              type="number"
              min="0"
              required
              placeholder="0"
              class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
            Tanggal Masuk <span class="text-red-500">*</span>
          </label>
          <input
            v-model="formNew.transaction_date"
            type="datetime-local"
            required
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
            Deskripsi / Catatan Tambahan
          </label>
          <textarea
            v-model="formNew.description"
            rows="2"
            placeholder="misal: Pengadaan barang baru dari distributor"
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
          ></textarea>
        </div>

        <button
          type="submit"
          :disabled="submitting"
          class="w-full rounded-xl bg-blue-600 py-3.5 text-sm font-bold text-white transition hover:bg-blue-700 active:bg-blue-800 disabled:opacity-50 shadow-sm"
        >
          {{ submitting ? 'Mendaftarkan Produk & Stok...' : 'Daftarkan Produk & Catat Stock In' }}
        </button>
      </form>
    </div>

    <!-- Quick Modal: Tambah Kategori Baru -->
    <AppModal
      v-if="showQuickCategoryModal"
      title="Tambah Kategori Baru Cepat"
      maxWidth="max-w-md"
      @close="showQuickCategoryModal = false"
    >
      <form @submit.prevent="handleQuickCreateCategory" class="space-y-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
            Nama Kategori <span class="text-red-500">*</span>
          </label>
          <input
            v-model="quickCategoryName"
            type="text"
            required
            placeholder="misal: Audio & Speaker"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
          />
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Deskripsi</label>
          <input
            v-model="quickCategoryDesc"
            type="text"
            placeholder="Keterangan singkat"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
          />
        </div>
        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
          <button
            type="button"
            @click="showQuickCategoryModal = false"
            class="rounded-lg px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="quickCategoryLoading"
            class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
          >
            {{ quickCategoryLoading ? 'Menyimpan...' : 'Simpan Kategori' }}
          </button>
        </div>
      </form>
    </AppModal>

    <!-- Quick Modal: Tambah Brand Baru -->
    <AppModal
      v-if="showQuickBrandModal"
      title="Tambah Brand Baru Cepat"
      maxWidth="max-w-md"
      @close="showQuickBrandModal = false"
    >
      <form @submit.prevent="handleQuickCreateBrand" class="space-y-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
            Nama Brand <span class="text-red-500">*</span>
          </label>
          <input
            v-model="quickBrandName"
            type="text"
            required
            placeholder="misal: Sony / Sharp / Toshiba"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
          />
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Deskripsi</label>
          <input
            v-model="quickBrandDesc"
            type="text"
            placeholder="Keterangan singkat"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
          />
        </div>
        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
          <button
            type="button"
            @click="showQuickBrandModal = false"
            class="rounded-lg px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="quickBrandLoading"
            class="rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
          >
            {{ quickBrandLoading ? 'Menyimpan...' : 'Simpan Brand' }}
          </button>
        </div>
      </form>
    </AppModal>
  </div>
</template>
