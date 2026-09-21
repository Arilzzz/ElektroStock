<script setup>
import { ref, reactive, computed, watch } from 'vue'
import AppModal from '../common/AppModal.vue'
import { createProduct, updateProduct } from '../../services/productService'

const props = defineProps({
  product: {
    type: Object,
    default: null,
  },
  categories: {
    type: Array,
    default: () => [],
  },
  brands: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['close', 'saved'])

const isEdit = computed(() => !!props.product)
const loading = ref(false)
const errorMessage = ref('')
const errors = ref({})

const form = reactive({
  code: '',
  name: '',
  category_id: '',
  brand_id: '',
  type_model: '',
  purchase_price: '',
  selling_price: '',
  minimum_stock: 0,
  unit: 'unit',
  description: '',
  image: null,
})

const imagePreview = ref(null)

watch(
  () => props.product,
  (val) => {
    if (val) {
      form.code = val.code || ''
      form.name = val.name || ''
      form.category_id = val.category_id || ''
      form.brand_id = val.brand_id || ''
      form.type_model = val.type_model || ''
      form.purchase_price = val.purchase_price || ''
      form.selling_price = val.selling_price || ''
      form.minimum_stock = val.minimum_stock ?? 0
      form.unit = val.unit || 'unit'
      form.description = val.description || ''
      form.image = null
      imagePreview.value = val.image_url || (val.image ? '/storage/' + val.image : null)
    } else {
      form.code = ''
      form.name = ''
      form.category_id = ''
      form.brand_id = ''
      form.type_model = ''
      form.purchase_price = ''
      form.selling_price = ''
      form.minimum_stock = 0
      form.unit = 'unit'
      form.description = ''
      form.image = null
      imagePreview.value = null
    }
  },
  { immediate: true }
)

const handleFileChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    form.image = file
    imagePreview.value = URL.createObjectURL(file)
  }
}

const handleSubmit = async () => {
  loading.value = true
  errorMessage.value = ''
  errors.value = {}

  try {
    const formData = new FormData()
    formData.append('code', form.code)
    formData.append('name', form.name)
    formData.append('category_id', form.category_id)
    formData.append('brand_id', form.brand_id)
    formData.append('type_model', form.type_model)
    formData.append('purchase_price', form.purchase_price)
    formData.append('selling_price', form.selling_price)
    formData.append('minimum_stock', form.minimum_stock)
    formData.append('unit', form.unit)
    if (form.description) formData.append('description', form.description)
    if (form.image) formData.append('image', form.image)

    if (isEdit.value) {
      await updateProduct(props.product.id, formData)
    } else {
      await createProduct(formData)
    }

    emit('saved')
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
      errorMessage.value = err.response.data.message || 'Harap periksa kembali inputan Anda.'
    } else {
      errorMessage.value = err.response?.data?.message || 'Terjadi kesalahan sistem.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <AppModal
    :title="isEdit ? 'Edit Produk' : 'Tambah Produk Baru'"
    maxWidth="max-w-2xl"
    @close="emit('close')"
  >
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div v-if="errorMessage" class="rounded-lg bg-red-50 p-3 text-sm text-red-600">
        {{ errorMessage }}
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Kode Produk -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
            Kode Produk <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.code"
            type="text"
            required
            placeholder="misal: TV-SAM-001"
            class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition"
            :class="errors.code ? 'border-red-500' : 'border-slate-300 focus:border-blue-500'"
          />
          <span v-if="errors.code" class="text-xs text-red-500">{{ errors.code[0] }}</span>
        </div>

        <!-- Nama Produk -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
            Nama Produk <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.name"
            type="text"
            required
            placeholder="misal: Samsung Smart TV 43"
            class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition"
            :class="errors.name ? 'border-red-500' : 'border-slate-300 focus:border-blue-500'"
          />
          <span v-if="errors.name" class="text-xs text-red-500">{{ errors.name[0] }}</span>
        </div>

        <!-- Kategori -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
            Kategori <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.category_id"
            required
            class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition"
            :class="errors.category_id ? 'border-red-500' : 'border-slate-300 focus:border-blue-500'"
          >
            <option value="" disabled>Pilih Kategori</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
          <span v-if="errors.category_id" class="text-xs text-red-500">{{ errors.category_id[0] }}</span>
        </div>

        <!-- Brand -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
            Brand / Merek <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.brand_id"
            required
            class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition"
            :class="errors.brand_id ? 'border-red-500' : 'border-slate-300 focus:border-blue-500'"
          >
            <option value="" disabled>Pilih Brand</option>
            <option v-for="b in brands" :key="b.id" :value="b.id">
              {{ b.name }}
            </option>
          </select>
          <span v-if="errors.brand_id" class="text-xs text-red-500">{{ errors.brand_id[0] }}</span>
        </div>

        <!-- Tipe / Model -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
            Tipe / Model <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.type_model"
            type="text"
            required
            placeholder="misal: UA43T6500"
            class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition"
            :class="errors.type_model ? 'border-red-500' : 'border-slate-300 focus:border-blue-500'"
          />
          <span v-if="errors.type_model" class="text-xs text-red-500">{{ errors.type_model[0] }}</span>
        </div>

        <!-- Satuan -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
            Satuan <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.unit"
            type="text"
            required
            placeholder="misal: unit / pcs"
            class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition"
            :class="errors.unit ? 'border-red-500' : 'border-slate-300 focus:border-blue-500'"
          />
          <span v-if="errors.unit" class="text-xs text-red-500">{{ errors.unit[0] }}</span>
        </div>

        <!-- Harga Beli -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
            Harga Beli (Rp) <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.purchase_price"
            type="number"
            min="0"
            step="100"
            required
            placeholder="0"
            class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition"
            :class="errors.purchase_price ? 'border-red-500' : 'border-slate-300 focus:border-blue-500'"
          />
          <span v-if="errors.purchase_price" class="text-xs text-red-500">{{ errors.purchase_price[0] }}</span>
        </div>

        <!-- Harga Jual -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
            Harga Jual (Rp) <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.selling_price"
            type="number"
            min="0"
            step="100"
            required
            placeholder="0"
            class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition"
            :class="errors.selling_price ? 'border-red-500' : 'border-slate-300 focus:border-blue-500'"
          />
          <span v-if="errors.selling_price" class="text-xs text-red-500">{{ errors.selling_price[0] }}</span>
        </div>

        <!-- Minimum Stok -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
            Minimum Stok <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.minimum_stock"
            type="number"
            min="0"
            required
            placeholder="0"
            class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition"
            :class="errors.minimum_stock ? 'border-red-500' : 'border-slate-300 focus:border-blue-500'"
          />
          <span class="text-xs text-slate-400">Peringatan status 'Menipis' jika stok ≤ batas ini.</span>
        </div>

        <!-- Gambar -->
        <div>
          <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
            Foto Produk (Opsional)
          </label>
          <input
            type="file"
            accept="image/png, image/jpeg, image/webp"
            @change="handleFileChange"
            class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
          />
        </div>
      </div>

      <!-- Deskripsi -->
      <div>
        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
          Deskripsi
        </label>
        <textarea
          v-model="form.description"
          rows="3"
          placeholder="Catatan atau spesifikasi ringkas produk..."
          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500 transition"
        ></textarea>
      </div>

      <div class="rounded-lg bg-blue-50 p-2.5 text-xs text-blue-700">
        💡 <strong>Info:</strong> Stok produk awal otomatis bernilai <strong>0</strong> sesuai aturan sistem. Penambahan stok hanya dilakukan melalui transaksi <strong>Stock In</strong>.
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
        <button
          type="button"
          :disabled="loading"
          @click="emit('close')"
          class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition"
        >
          Batal
        </button>
        <button
          type="submit"
          :disabled="loading"
          class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700 transition disabled:opacity-50"
        >
          {{ loading ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Tambah Produk') }}
        </button>
      </div>
    </form>
  </AppModal>
</template>
