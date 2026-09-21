<script setup>
import { ref, computed, onMounted } from 'vue'

import ProductFilter from '../../components/products/ProductFilter.vue'
import ProductTable from '../../components/products/ProductTable.vue'
import ProductForm from '../../components/products/ProductForm.vue'
import ProductDetail from '../../components/products/ProductDetail.vue'
import ProductDeleteModal from '../../components/products/ProductDeleteModal.vue'

import {
  getProducts,
  getCategories,
  getBrands,
  deleteProduct,
} from '../../services/productService'

const products = ref([])
const categories = ref([])
const brands = ref([])

const search = ref('')
const selectedCategory = ref('')
const selectedBrand = ref('')
const selectedStatus = ref('')

const loading = ref(false)

const showForm = ref(false)
const showDetail = ref(false)
const showDelete = ref(false)

const selectedProduct = ref(null)

const loadData = async () => {
  loading.value = true

  try {
    const [
      productsResponse,
      categoriesResponse,
      brandsResponse,
    ] = await Promise.all([
      getProducts(),
      getCategories(),
      getBrands(),
    ])

    const prodData = productsResponse.data.data
    products.value = Array.isArray(prodData) ? prodData : (prodData?.data || [])
    categories.value = categoriesResponse.data.data || []
    brands.value = brandsResponse.data.data || []
  } catch (error) {
    console.error('Gagal memuat data produk:', error)
  } finally {
    loading.value = false
  }
}

const filteredProducts = computed(() => {
  return products.value.filter((product) => {
    const keyword = search.value.toLowerCase()

    const matchSearch =
      product.name?.toLowerCase().includes(keyword) ||
      product.code?.toLowerCase().includes(keyword) ||
      product.type_model?.toLowerCase().includes(keyword)

    const matchCategory =
      !selectedCategory.value ||
      product.category_id == selectedCategory.value

    const matchBrand =
      !selectedBrand.value ||
      product.brand_id == selectedBrand.value

    let matchStatus = true

    if (selectedStatus.value === 'aman') {
      matchStatus =
        product.stock > product.minimum_stock
    }

    if (selectedStatus.value === 'menipis') {
      matchStatus =
        product.stock > 0 &&
        product.stock <= product.minimum_stock
    }

    if (selectedStatus.value === 'habis') {
      matchStatus = product.stock === 0
    }

    return (
      matchSearch &&
      matchCategory &&
      matchBrand &&
      matchStatus
    )
  })
})

const openCreate = () => {
  selectedProduct.value = null
  showForm.value = true
}

const openEdit = (product) => {
  selectedProduct.value = product
  showForm.value = true
}

const openDetail = (product) => {
  selectedProduct.value = product
  showDetail.value = true
}

const openDelete = (product) => {
  selectedProduct.value = product
  showDelete.value = true
}

const closeForm = () => {
  showForm.value = false
}

const closeDetail = () => {
  showDetail.value = false
}

const closeDelete = () => {
  showDelete.value = false
}

const handleSaved = async () => {
  closeForm()
  await loadData()
}

const handleDelete = async () => {
  try {
    await deleteProduct(selectedProduct.value.id)

    closeDelete()
    await loadData()
  } catch (error) {
    console.error(error)
  }
}

onMounted(() => {
  loadData()
})
</script>

<template>
  <div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-800">
          Katalog Produk
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Kelola data barang elektronik, harga beli, harga jual, dan batas stok.
        </p>
      </div>

      <button
        @click="openCreate"
        class="self-start sm:self-auto rounded-xl bg-blue-600 px-4 py-2.5 text-xs sm:text-sm font-bold text-white shadow-xs hover:bg-blue-700 active:scale-95 transition flex items-center gap-1.5"
      >
        <span>+</span>
        <span>Tambah Produk Baru</span>
      </button>
    </div>

    <!-- Filter -->
    <ProductFilter
      v-model:search="search"
      v-model:category="selectedCategory"
      v-model:brand="selectedBrand"
      v-model:status="selectedStatus"
      :categories="categories"
      :brands="brands"
    />

    <!-- Table -->
    <ProductTable
      :products="filteredProducts"
      :loading="loading"
      @detail="openDetail"
      @edit="openEdit"
      @delete="openDelete"
    />

    <!-- Form -->
    <ProductForm
      v-if="showForm"
      :product="selectedProduct"
      :categories="categories"
      :brands="brands"
      @close="closeForm"
      @saved="handleSaved"
    />

    <!-- Detail -->
    <ProductDetail
      v-if="showDetail"
      :product="selectedProduct"
      @close="closeDetail"
    />

    <!-- Delete -->
    <ProductDeleteModal
      v-if="showDelete"
      :product="selectedProduct"
      @close="closeDelete"
      @confirm="handleDelete"
    />

  </div>
</template>