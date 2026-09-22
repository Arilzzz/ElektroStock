<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import {
  X,
  ZoomIn,
  ZoomOut,
  RotateCcw,
  Maximize2,
  Minimize2,
  ExternalLink,
} from 'lucide-vue-next'

const props = defineProps({
  src: {
    type: String,
    required: true,
  },
  alt: {
    type: String,
    default: 'Foto Produk',
  },
  title: {
    type: String,
    default: '',
  },
  subtitle: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['close'])

const scale = ref(1)
const isFullscreen = ref(false)
const modalRef = ref(null)

const zoomIn = () => {
  if (scale.value < 3) {
    scale.value = Math.min(scale.value + 0.35, 3)
  }
}

const zoomOut = () => {
  if (scale.value > 0.6) {
    scale.value = Math.max(scale.value - 0.35, 0.6)
  }
}

const resetZoom = () => {
  scale.value = 1
}

const toggleFullscreen = () => {
  if (!document.fullscreenElement) {
    modalRef.value?.requestFullscreen?.().catch(() => {
      isFullscreen.value = !isFullscreen.value
    })
    isFullscreen.value = true
  } else {
    document.exitFullscreen?.().catch(() => {})
    isFullscreen.value = false
  }
}

const handleKeydown = (e) => {
  if (e.key === 'Escape') {
    emit('close')
  } else if (e.key === '+' || e.key === '=') {
    zoomIn()
  } else if (e.key === '-') {
    zoomOut()
  } else if (e.key === '0') {
    resetZoom()
  }
}

onMounted(() => {
  document.body.style.overflow = 'hidden'
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.body.style.overflow = ''
  window.removeEventListener('keydown', handleKeydown)
  if (document.fullscreenElement) {
    document.exitFullscreen?.().catch(() => {})
  }
})
</script>

<template>
  <Teleport to="body">
    <div
      ref="modalRef"
      class="fixed inset-0 z-[70] flex flex-col items-center justify-between bg-slate-950/90 backdrop-blur-md transition-opacity animate-in fade-in duration-200 select-none p-4 sm:p-6"
      @click.self="emit('close')"
    >
      <!-- Top Control Bar -->
      <div class="w-full flex items-center justify-between gap-4 text-white z-10">
        <!-- Title & Subtitle Info -->
        <div class="min-w-0 flex-1">
          <h4 v-if="title" class="text-sm sm:text-base font-bold text-white truncate drop-shadow-sm">
            {{ title }}
          </h4>
          <p v-if="subtitle" class="text-xs text-slate-300 truncate font-mono">
            {{ subtitle }}
          </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
          <!-- Zoom Controls -->
          <div class="flex items-center rounded-xl bg-slate-800/80 border border-slate-700/80 p-1 backdrop-blur-xs">
            <button
              @click="zoomOut"
              :disabled="scale <= 0.6"
              type="button"
              class="p-1.5 sm:p-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/80 active:scale-95 disabled:opacity-35 transition"
              title="Perkecil (-)"
            >
              <ZoomOut :size="18" />
            </button>
            <button
              @click="resetZoom"
              type="button"
              class="px-2 py-1 text-xs font-mono font-medium text-slate-200 hover:text-white hover:bg-slate-700/80 rounded-lg active:scale-95 transition"
              title="Reset Ukuran (0)"
            >
              {{ Math.round(scale * 100) }}%
            </button>
            <button
              @click="zoomIn"
              :disabled="scale >= 3"
              type="button"
              class="p-1.5 sm:p-2 rounded-lg text-slate-300 hover:text-white hover:bg-slate-700/80 active:scale-95 disabled:opacity-35 transition"
              title="Perbesar (+)"
            >
              <ZoomIn :size="18" />
            </button>
          </div>

          <!-- Fullscreen Toggle -->
          <button
            @click="toggleFullscreen"
            type="button"
            class="p-2 sm:p-2.5 rounded-xl bg-slate-800/80 border border-slate-700/80 text-slate-300 hover:text-white hover:bg-slate-700/80 active:scale-95 transition backdrop-blur-xs"
            :title="isFullscreen ? 'Keluar Fullscreen' : 'Layar Penuh'"
          >
            <component :is="isFullscreen ? Minimize2 : Maximize2" :size="18" />
          </button>

          <!-- Open in new tab -->
          <a
            :href="src"
            target="_blank"
            rel="noopener noreferrer"
            class="p-2 sm:p-2.5 rounded-xl bg-slate-800/80 border border-slate-700/80 text-slate-300 hover:text-white hover:bg-slate-700/80 active:scale-95 transition backdrop-blur-xs"
            title="Buka Gambar di Tab Baru"
          >
            <ExternalLink :size="18" />
          </a>

          <!-- Close Button -->
          <button
            @click="emit('close')"
            type="button"
            class="p-2 sm:p-2.5 rounded-xl bg-red-600/80 hover:bg-red-600 text-white active:scale-95 transition backdrop-blur-xs shadow-lg"
            title="Tutup (Esc)"
          >
            <X :size="18" />
          </button>
        </div>
      </div>

      <!-- Main Image Display Container -->
      <div
        class="flex-1 w-full flex items-center justify-center overflow-auto p-2 sm:p-4 cursor-zoom-out"
        @click.self="emit('close')"
      >
        <div
          class="relative flex items-center justify-center transition-transform duration-150 ease-out"
          :style="{ transform: `scale(${scale})` }"
        >
          <img
            :src="src"
            :alt="alt"
            class="max-h-[75vh] max-w-[90vw] object-contain rounded-2xl shadow-2xl border border-slate-700/50 cursor-default bg-slate-900/40"
            @click.stop
          />
        </div>
      </div>

      <!-- Bottom Hint Bar -->
      <div class="w-full text-center py-1">
        <p class="text-xs text-slate-400 font-medium">
          Klik di luar gambar atau tekan <kbd class="px-1.5 py-0.5 rounded-md bg-slate-800 border border-slate-700 text-slate-300 text-[11px]">ESC</kbd> untuk menutup
        </p>
      </div>
    </div>
  </Teleport>
</template>
