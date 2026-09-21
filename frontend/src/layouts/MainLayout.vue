<script setup>
import { ref } from 'vue'
import {
  LayoutDashboard,
  Package,
  Tags,
  Warehouse,
  ArrowDownToLine,
  ArrowUpFromLine,
  History,
  BarChart3,
  LogOut,
  Menu,
  X,
  User,
} from 'lucide-vue-next'

import { useRouter, useRoute } from 'vue-router'
import { useAuth } from '../stores/auth'
import { logout as apiLogout } from '../services/authService'

const router = useRouter()
const route = useRoute()
const { user, logout } = useAuth()

const isMobileMenuOpen = ref(false)

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
}

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

const handleLogout = async () => {
  try {
    await apiLogout()
  } catch (e) {
    // ignore network/auth errors during logout
  } finally {
    logout()
    router.push('/login')
  }
}

const navLinks = [
  { name: 'Dashboard', to: '/dashboard', icon: LayoutDashboard },
  { name: 'Produk', to: '/products', icon: Package },
  { name: 'Kategori', to: '/categories', icon: Tags },
  { name: 'Brand / Merek', to: '/brands', icon: Warehouse },
  { type: 'divider' },
  { name: 'Stock In', to: '/stock/in', icon: ArrowDownToLine },
  { name: 'Stock Out', to: '/stock/out', icon: ArrowUpFromLine },
  { name: 'Riwayat Stok', to: '/stock/history', icon: History },
  { type: 'divider' },
  { name: 'Laporan', to: '/reports', icon: BarChart3 },
]
</script>

<template>
  <div class="min-h-screen bg-slate-100 flex flex-col md:flex-row antialiased">
    <!-- Backdrop Overlay for Mobile -->
    <div
      v-if="isMobileMenuOpen"
      @click="closeMobileMenu"
      class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs md:hidden transition-opacity"
    ></div>

    <!-- Sidebar Drawer (Responsive for Mobile Samsung & Desktop) -->
    <aside
      class="fixed inset-y-0 left-0 z-50 w-72 md:w-64 bg-white border-r border-slate-200 flex flex-col transition-transform duration-300 ease-in-out md:translate-x-0"
      :class="isMobileMenuOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full md:translate-x-0'"
    >
      <!-- Logo & Close Button (Mobile) -->
      <div class="flex h-16 items-center justify-between border-b border-slate-100 px-5 md:px-6">
        <div class="flex items-center gap-2.5">
          <div class="h-9 w-9 rounded-xl bg-blue-600 flex items-center justify-center text-white font-black text-lg shadow-sm">
            ⚡
          </div>
          <div>
            <h1 class="text-base font-bold text-slate-800 leading-tight">ElectroStock</h1>
            <p class="text-[11px] text-slate-400 font-medium">Inventory System</p>
          </div>
        </div>
        <button
          @click="closeMobileMenu"
          class="md:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 active:bg-slate-200 transition"
          aria-label="Tutup Menu"
        >
          <X :size="20" />
        </button>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 overflow-y-auto px-3.5 py-4 space-y-1">
        <template v-for="(item, idx) in navLinks" :key="idx">
          <div v-if="item.type === 'divider'" class="my-3 border-t border-slate-100 mx-2"></div>
          <RouterLink
            v-else
            :to="item.to"
            @click="closeMobileMenu"
            class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition duration-150"
            :class="
              route.path.startsWith(item.to)
                ? 'bg-blue-50 text-blue-700 font-semibold shadow-xs'
                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 active:bg-slate-200'
            "
          >
            <component
              :is="item.icon"
              :size="18"
              :class="route.path.startsWith(item.to) ? 'text-blue-600' : 'text-slate-400'"
            />
            <span>{{ item.name }}</span>
          </RouterLink>
        </template>
      </nav>

      <!-- User Profile & Logout Bottom Section -->
      <div class="border-t border-slate-100 p-3.5 bg-slate-50/50">
        <div class="flex items-center gap-3 px-2 py-2 mb-2">
          <div class="h-9 w-9 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm shrink-0">
            {{ user?.name ? user.name.charAt(0).toUpperCase() : 'A' }}
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-xs font-semibold text-slate-800 truncate">{{ user?.name || 'Admin ElectroStock' }}</p>
            <p class="text-[11px] text-slate-400 truncate">{{ user?.email || 'admin@electrostock.test' }}</p>
          </div>
        </div>

        <button
          @click="handleLogout"
          class="flex w-full items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 active:bg-red-100 transition"
        >
          <LogOut :size="18" />
          <span>Keluar (Logout)</span>
        </button>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 md:ml-64 flex flex-col min-w-0 min-h-screen">
      <!-- Topbar with Hamburger on Mobile -->
      <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-4 md:px-8 backdrop-blur-xs">
        <div class="flex items-center gap-3">
          <button
            @click="toggleMobileMenu"
            class="md:hidden rounded-lg p-2 text-slate-600 hover:bg-slate-100 active:bg-slate-200 focus:outline-none"
            aria-label="Buka Menu"
          >
            <Menu :size="22" />
          </button>
          <div>
            <h2 class="text-base md:text-lg font-bold text-slate-800">
              ElectroStock
            </h2>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 border border-emerald-200">
            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Online
          </span>
          <div class="hidden sm:flex items-center gap-2 text-xs font-semibold text-slate-700 bg-slate-100 px-3 py-1.5 rounded-lg">
            <User :size="14" class="text-slate-500" />
            <span>{{ user?.name || 'Admin' }}</span>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 p-4 sm:p-6 md:p-8 max-w-7xl w-full mx-auto">
        <RouterView />
      </main>
    </div>
  </div>
</template>