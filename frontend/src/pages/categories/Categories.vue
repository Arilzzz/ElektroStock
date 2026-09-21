<script setup>
import { ref, reactive, onMounted } from 'vue'
import {
  getCategories,
  createCategory,
  updateCategory,
  deleteCategory,
} from '../../services/categoryService'
import LoadingSpinner from '../../components/common/LoadingSpinner.vue'
import AppModal from '../../components/common/AppModal.vue'
import { Tags, Plus, Search } from 'lucide-vue-next'

const categories = ref([])
const loading = ref(false)
const error = ref('')
const search = ref('')

const showModal = ref(false)
const editingCategory = ref(null)
const modalLoading = ref(false)
const modalError = ref('')

const form = reactive({
  name: '',
  description: '',
})

const fetchCategories = async () => {
  loading.value = true
  error.value = ''
  try {
    const res = await getCategories()
    categories.value = res.data.data || []
  } catch (err) {
    error.value = err.response?.data?.message || 'Gagal mengambil data kategori.'
  } finally {
    loading.value = false
  }
}

const openCreate = () => {
  editingCategory.value = null
  form.name = ''
  form.description = ''
  modalError.value = ''
  showModal.value = true
}

const openEdit = (cat) => {
  editingCategory.value = cat
  form.name = cat.name
  form.description = cat.description || ''
  modalError.value = ''
  showModal.value = true
}

const handleSave = async () => {
  if (!form.name.trim()) {
    modalError.value = 'Nama kategori wajib diisi.'
    return
  }

  modalLoading.value = true
  modalError.value = ''
  try {
    if (editingCategory.value) {
      await updateCategory(editingCategory.value.id, form)
    } else {
      await createCategory(form)
    }
    showModal.value = false
    fetchCategories()
  } catch (err) {
    modalError.value = err.response?.data?.message || 'Gagal menyimpan kategori.'
  } finally {
    modalLoading.value = false
  }
}

const handleDelete = async (cat) => {
  if (!confirm(`Hapus kategori "${cat.name}"?`)) return
  try {
    await deleteCategory(cat.id)
    fetchCategories()
  } catch (err) {
    alert(err.response?.data?.message || 'Kategori tidak dapat dihapus.')
  }
}

const filteredCategories = ref([])
const updateFiltered = () => {
  const q = search.value.toLowerCase().trim()
  if (!q) {
    filteredCategories.value = categories.value
  } else {
    filteredCategories.value = categories.value.filter(
      (c) =>
        c.name.toLowerCase().includes(q) ||
        (c.description && c.description.toLowerCase().includes(q))
    )
  }
}

onMounted(async () => {
  await fetchCategories()
  updateFiltered()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-800 flex items-center gap-2">
          <Tags class="text-blue-600" :size="24" />
          Kategori Produk
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
          Kelola kelompok kategori barang elektronik pada sistem inventaris.
        </p>
      </div>

      <button
        @click="openCreate"
        class="self-start sm:self-auto rounded-xl bg-blue-600 px-4 py-2.5 text-xs sm:text-sm font-bold text-white shadow-xs hover:bg-blue-700 active:scale-95 transition flex items-center gap-1.5"
      >
        <Plus :size="16" />
        <span>Tambah Kategori</span>
      </button>
    </div>

    <!-- Search input -->
    <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-xs max-w-md">
      <div class="relative">
        <Search :size="16" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
        <input
          v-model="search"
          @input="updateFiltered"
          type="text"
          placeholder="Cari kategori..."
          class="w-full rounded-xl border border-slate-300 pl-9 pr-3.5 py-2 text-xs sm:text-sm outline-none focus:border-blue-500"
        />
      </div>
    </div>

    <LoadingSpinner v-if="loading" text="Memuat data kategori..." />

    <div v-else-if="error" class="rounded-xl bg-red-50 p-4 text-sm text-red-600 border border-red-200">
      {{ error }}
    </div>

    <div v-else class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[500px] text-left text-sm">
          <thead class="border-b border-slate-100 bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider">
            <tr>
              <th class="px-5 py-3.5">Nama Kategori</th>
              <th class="px-5 py-3.5">Deskripsi</th>
              <th class="px-5 py-3.5 text-center">Jumlah Produk</th>
              <th class="px-5 py-3.5 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="(search ? filteredCategories : categories).length === 0">
              <td colspan="4" class="px-6 py-10 text-center text-slate-400">
                <p class="text-2xl mb-1">🏷️</p>
                <p class="text-sm">Belum ada kategori yang cocok.</p>
              </td>
            </tr>
            <tr
              v-for="cat in (search ? filteredCategories : categories)"
              :key="cat.id"
              class="hover:bg-slate-50/80 transition"
            >
              <td class="px-5 py-3.5 font-bold text-slate-900">{{ cat.name }}</td>
              <td class="px-5 py-3.5 text-slate-500 text-xs sm:text-sm">{{ cat.description || '-' }}</td>
              <td class="px-5 py-3.5 text-center">
                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-700">
                  {{ cat.products_count ?? 0 }} produk
                </span>
              </td>
              <td class="px-5 py-3.5 text-center whitespace-nowrap">
                <div class="inline-flex items-center gap-1 bg-slate-100/80 p-1 rounded-xl border border-slate-200/60">
                  <button
                    @click="openEdit(cat)"
                    title="Edit Kategori"
                    class="rounded-lg px-2 py-1 text-sm hover:bg-white transition active:scale-95"
                  >
                    ✏️
                  </button>
                  <button
                    @click="handleDelete(cat)"
                    title="Hapus Kategori"
                    class="rounded-lg px-2 py-1 text-sm hover:bg-white transition active:scale-95 text-red-600"
                  >
                    🗑️
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form Kategori -->
    <AppModal
      v-if="showModal"
      :title="editingCategory ? 'Edit Kategori' : 'Tambah Kategori Baru'"
      maxWidth="max-w-md"
      @close="showModal = false"
    >
      <form @submit.prevent="handleSave" class="space-y-4">
        <div v-if="modalError" class="rounded-lg bg-red-50 p-3 text-xs text-red-600 border border-red-200">
          {{ modalError }}
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
            Nama Kategori <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.name"
            type="text"
            required
            placeholder="misal: Televisi, Kulkas, Speaker"
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Deskripsi</label>
          <textarea
            v-model="form.description"
            rows="2"
            placeholder="Keterangan singkat kategori"
            class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
          ></textarea>
        </div>

        <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
          <button
            type="button"
            @click="showModal = false"
            class="rounded-xl border border-slate-300 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 transition"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="modalLoading"
            class="rounded-xl bg-blue-600 px-5 py-2 text-xs font-bold text-white hover:bg-blue-700 disabled:opacity-50 transition"
          >
            {{ modalLoading ? 'Menyimpan...' : 'Simpan Kategori' }}
          </button>
        </div>
      </form>
    </AppModal>
  </div>
</template>
