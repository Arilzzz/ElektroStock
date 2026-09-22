<script setup>
import { ref, reactive, computed, watch } from 'vue'
import AppModal from '../common/AppModal.vue'
import ImageLightboxModal from '../common/ImageLightboxModal.vue'
import { createProduct, updateProduct } from '../../services/productService'
import { getProductImageUrl } from '../../utils/productImage'
import {
  Upload,
  Image as ImageIcon,
  CheckCircle2,
  RotateCcw,
  Trash2,
  Maximize2,
  Camera,
  FileCheck,
} from 'lucide-vue-next'

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

const fileInputRef = ref(null)
const newSelectedFile = ref(null)
const showPreviewLightbox = ref(false)
const isDragging = ref(false)

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

const existingImageUrl = computed(() => {
  return props.product ? getProductImageUrl(props.product) : null
})

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
      newSelectedFile.value = null
      imagePreview.value = getProductImageUrl(val)
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
      newSelectedFile.value = null
      imagePreview.value = null
    }
  },
  { immediate: true }
)

const handleFileSelect = (file) => {
  if (!file) return

  // Validasi tipe file
  const validTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg']
  if (!validTypes.includes(file.type)) {
    errors.value = { ...errors.value, image: ['Hanya format PNG, JPG, JPEG, atau WEBP yang diperbolehkan.'] }
    return
  }

  // Validasi ukuran file (maks 2MB)
  if (file.size > 2 * 1024 * 1024) {
    errors.value = { ...errors.value, image: ['Ukuran file foto maksimal adalah 2MB.'] }
    return
  }

  delete errors.value.image
  form.image = file
  newSelectedFile.value = file
  imagePreview.value = URL.createObjectURL(file)
}

const handleFileChange = (e) => {
  const file = e.target.files?.[0]
  if (file) {
    handleFileSelect(file)
  }
}

const handleDrop = (e) => {
  isDragging.value = false
  const file = e.dataTransfer?.files?.[0]
  if (file) {
    handleFileSelect(file)
  }
}

const triggerFileInput = () => {
  fileInputRef.value?.click()
}

const cancelNewImage = () => {
  form.image = null
  newSelectedFile.value = null
  if (fileInputRef.value) {
    fileInputRef.value.value = ''
  }
  if (existingImageUrl.value) {
    imagePreview.value = existingImageUrl.value
  } else {
    imagePreview.value = null
  }
  delete errors.value.image
}

const formatFileSize = (bytes) => {
  if (!bytes) return ''
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
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

        <!-- Foto Produk (Modern Upload UI without 'No file chosen' browser artifact) -->
        <div class="md:col-span-2">
          <!-- Hidden Native File Input -->
          <input
            ref="fileInputRef"
            type="file"
            accept="image/png, image/jpeg, image/webp, image/jpg"
            @change="handleFileChange"
            class="hidden"
          />

          <div class="flex items-center justify-between mb-1.5">
            <label class="block text-xs font-semibold text-slate-700 uppercase">
              Foto Produk <span class="text-slate-400 font-normal lowercase">(opsional)</span>
            </label>
            <span v-if="imagePreview" class="text-[11px] text-slate-500 font-medium">
              Klik foto untuk melihat ukuran penuh
            </span>
          </div>

          <!-- Case 1: Image exists (newly selected file or existing saved database image) -->
          <div
            v-if="imagePreview"
            class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-3.5 rounded-xl border border-slate-200 bg-slate-50/70 transition"
          >
            <!-- Thumbnail with Click to Zoom -->
            <div
              @click="showPreviewLightbox = true"
              class="relative h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-white cursor-pointer group shadow-2xs"
              title="Klik untuk memperbesar / fullscreen"
            >
              <img
                :src="imagePreview"
                alt="Pratinjau Foto"
                class="h-full w-full object-cover transition duration-200 group-hover:scale-105"
              />
              <div class="absolute inset-0 bg-black/35 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white">
                <Maximize2 :size="16" />
              </div>
            </div>

            <!-- Details & Actions -->
            <div class="min-w-0 flex-1 space-y-1.5">
              <!-- Sub-state A: New file selected -->
              <template v-if="newSelectedFile">
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                    <FileCheck :size="12" />
                    File Baru Dipilih
                  </span>
                  <span class="text-xs text-slate-400 font-mono">
                    {{ formatFileSize(newSelectedFile.size) }}
                  </span>
                </div>
                <p class="text-xs font-semibold text-slate-800 truncate">
                  {{ newSelectedFile.name }}
                </p>
                <div class="flex flex-wrap items-center gap-2 pt-1">
                  <button
                    type="button"
                    @click="triggerFileInput"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-medium text-slate-700 hover:bg-slate-100 active:scale-95 transition shadow-2xs"
                  >
                    <Camera :size="13" />
                    <span>Ganti File Lain</span>
                  </button>
                  <button
                    type="button"
                    @click="cancelNewImage"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-red-200 text-xs font-medium text-red-600 hover:bg-red-50 active:scale-95 transition shadow-2xs"
                  >
                    <RotateCcw :size="13" />
                    <span>{{ existingImageUrl ? 'Batal Ganti (Gunakan Foto Semula)' : 'Hapus Foto' }}</span>
                  </button>
                </div>
              </template>

              <!-- Sub-state B: Existing image from database (Edit mode) -->
              <template v-else>
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                    <CheckCircle2 :size="12" />
                    Foto Aktif Terpasang
                  </span>
                </div>
                <p class="text-xs text-slate-500">
                  Foto produk saat ini sudah tersimpan. Klik tombol di bawah jika ingin menggantinya dengan foto baru.
                </p>
                <div class="pt-1">
                  <button
                    type="button"
                    @click="triggerFileInput"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-medium text-blue-700 hover:bg-blue-50 active:scale-95 transition shadow-2xs"
                  >
                    <Camera :size="14" />
                    <span>Ganti Foto Produk</span>
                  </button>
                </div>
              </template>
            </div>
          </div>

          <!-- Case 2: No image selected or available (Clean dropzone) -->
          <div
            v-else
            @click="triggerFileInput"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            class="flex flex-col items-center justify-center p-5 rounded-xl border-2 border-dashed transition cursor-pointer text-center group"
            :class="isDragging ? 'border-blue-500 bg-blue-50/50' : 'border-slate-300 hover:border-blue-400 bg-slate-50/50 hover:bg-blue-50/20'"
          >
            <div class="h-10 w-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-2 group-hover:scale-110 transition">
              <Upload :size="18" />
            </div>
            <p class="text-xs font-semibold text-slate-700 group-hover:text-blue-600 transition">
              Klik untuk pilih foto produk, atau seret gambar ke sini
            </p>
            <p class="text-[11px] text-slate-400 mt-0.5">
              PNG, JPG, JPEG, atau WEBP (Maksimal 2 MB)
            </p>
          </div>

          <span v-if="errors.image" class="block text-xs text-red-500 mt-1">
            {{ errors.image[0] }}
          </span>
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

  <!-- Lightbox Modal for Photo Preview inside Form -->
  <ImageLightboxModal
    v-if="showPreviewLightbox && imagePreview"
    :src="imagePreview"
    :title="form.name || 'Pratinjau Foto Produk'"
    :subtitle="newSelectedFile ? `${newSelectedFile.name} (${formatFileSize(newSelectedFile.size)})` : (form.code || 'Foto Tersimpan')"
    @close="showPreviewLightbox = false"
  />
</template>
