<script setup>
import { ref } from 'vue'
import { formatCurrency } from '../../utils/formatCurrency'
import { getProductImageUrl } from '../../utils/productImage'
import ImageLightboxModal from '../common/ImageLightboxModal.vue'
import { Maximize2 } from 'lucide-vue-next'

defineProps({
  products: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['detail', 'edit', 'delete'])

const previewImage = ref(null)

const openPreview = (product) => {
  const url = getProductImageUrl(product)
  if (url) {
    previewImage.value = {
      src: url,
      title: product.name,
      subtitle: `${product.code || ''} ${product.type_model ? '• ' + product.type_model : ''} ${product.brand?.name ? '(' + product.brand.name + ')' : ''}`,
    }
  }
}

const getStatus = (product) => {
  if (product.stock === 0) {
    return 'habis'
  }
  if (product.stock <= product.minimum_stock) {
    return 'menipis'
  }
  return 'aman'
}
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
    <div v-if="loading" class="p-12 text-center text-slate-500 font-medium">
      <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-slate-200 border-t-blue-600 mb-2"></div>
      <p class="text-sm">Memuat data produk...</p>
    </div>

    <div v-else-if="products.length === 0" class="p-12 text-center text-slate-400">
      <p class="text-3xl mb-2">📦</p>
      <p class="text-sm font-medium">Tidak ada produk yang sesuai dengan filter.</p>
    </div>

    <!-- Responsive Table with horizontal scroll for Android/Samsung mobile -->
    <div v-else class="overflow-x-auto">
      <table class="w-full min-w-[700px] text-left text-sm">
        <thead class="border-b border-slate-100 bg-slate-50/80 text-xs font-bold text-slate-500 uppercase tracking-wider">
          <tr>
            <th class="px-4 py-3.5">Produk</th>
            <th class="px-4 py-3.5">Kategori</th>
            <th class="px-4 py-3.5">Brand</th>
            <th class="px-4 py-3.5">Harga Beli</th>
            <th class="px-4 py-3.5">Harga Jual</th>
            <th class="px-4 py-3.5">Stok</th>
            <th class="px-4 py-3.5">Status</th>
            <th class="px-4 py-3.5 text-center">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-100">
          <tr
            v-for="product in products"
            :key="product.id"
            class="hover:bg-slate-50/80 transition group"
          >
            <!-- Name & Code & Photo -->
            <td class="px-4 py-3.5">
              <div class="flex items-center gap-3">
                <div
                  class="relative h-10 w-10 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-slate-50 flex items-center justify-center text-xs text-slate-400 font-medium group/thumb select-none"
                  :class="getProductImageUrl(product) ? 'cursor-pointer hover:border-blue-500 hover:ring-2 hover:ring-blue-100 transition' : ''"
                  @click="openPreview(product)"
                  :title="getProductImageUrl(product) ? 'Klik untuk perbesar / fullscreen' : 'Belum ada foto'"
                >
                  <template v-if="getProductImageUrl(product)">
                    <img
                      :src="getProductImageUrl(product)"
                      :alt="product.name"
                      class="h-full w-full object-cover transition duration-200 group-hover/thumb:scale-110"
                    />
                    <div class="absolute inset-0 bg-black/35 opacity-0 group-hover/thumb:opacity-100 transition flex items-center justify-center text-white">
                      <Maximize2 :size="13" />
                    </div>
                  </template>
                  <span v-else>📷</span>
                </div>
                <div>
                  <div class="font-bold text-slate-900 group-hover:text-blue-600 transition">
                    {{ product.name }}
                  </div>
                  <div class="text-xs text-slate-400 font-mono">
                    {{ product.code }} <span v-if="product.type_model">• {{ product.type_model }}</span>
                  </div>
                </div>
              </div>
            </td>

            <!-- Kategori -->
            <td class="px-4 py-3.5 text-slate-600">
              <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                {{ product.category?.name ?? '-' }}
              </span>
            </td>

            <!-- Brand -->
            <td class="px-4 py-3.5 text-slate-700 font-medium">
              {{ product.brand?.name ?? '-' }}
            </td>

            <!-- Harga Beli -->
            <td class="px-4 py-3.5 text-slate-600 font-semibold">
              {{ formatCurrency(product.purchase_price) }}
            </td>

            <!-- Harga Jual -->
            <td class="px-4 py-3.5 text-blue-600 font-bold">
              {{ formatCurrency(product.selling_price) }}
            </td>

            <!-- Stok Fisik -->
            <td class="px-4 py-3.5 font-bold text-slate-800 whitespace-nowrap">
              {{ product.stock }} <span class="text-xs text-slate-400 font-normal">{{ product.unit }}</span>
            </td>

            <!-- Status Badge -->
            <td class="px-4 py-3.5">
              <span
                v-if="getStatus(product) === 'aman'"
                class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 border border-emerald-200"
              >
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                Aman
              </span>

              <span
                v-else-if="getStatus(product) === 'menipis'"
                class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-700 border border-amber-200"
              >
                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                Menipis
              </span>

              <span
                v-else
                class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-700 border border-red-200"
              >
                <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                Habis
              </span>
            </td>

            <!-- Aksi Buttons with Emoji -->
            <td class="px-4 py-3.5 text-center whitespace-nowrap">
              <div class="inline-flex items-center gap-1 rounded-xl bg-slate-100/80 p-1 border border-slate-200/60 shadow-2xs">
                <!-- Detail Emoji -->
                <button
                  @click="emit('detail', product)"
                  title="Lihat Detail Produk"
                  class="rounded-lg px-2 py-1 text-sm transition hover:bg-white hover:shadow-xs active:scale-95"
                >
                  👁️
                </button>

                <!-- Edit Emoji -->
                <button
                  @click="emit('edit', product)"
                  title="Edit Data Produk"
                  class="rounded-lg px-2 py-1 text-sm transition hover:bg-white hover:shadow-xs active:scale-95"
                >
                  ✏️
                </button>

                <!-- Hapus Emoji -->
                <button
                  @click="emit('delete', product)"
                  title="Hapus Produk"
                  class="rounded-lg px-2 py-1 text-sm transition hover:bg-white hover:shadow-xs active:scale-95 text-red-600"
                >
                  🗑️
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Image Lightbox Modal for Fullscreen View -->
    <ImageLightboxModal
      v-if="previewImage"
      :src="previewImage.src"
      :title="previewImage.title"
      :subtitle="previewImage.subtitle"
      @close="previewImage = null"
    />
  </div>
</template>