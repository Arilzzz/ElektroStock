<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { login as apiLogin } from '../../services/authService'
import { useAuth } from '../../stores/auth'

import logoImg from '../../assets/Logo LEMS.png'

const router = useRouter()
const { setAuth } = useAuth()

const email = ref('')
const password = ref('')

const loading = ref(false)
const errorMessage = ref('')

const login = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await apiLogin({
      email: email.value,
      password: password.value,
    })

    const data = response.data.data

    setAuth(
      data.token,
      data.user
    )

    router.push('/dashboard')
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message ||
      'Email atau password salah.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-100 flex items-center justify-center px-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl">

      <div class="mb-8 text-center flex flex-col items-center">
        <div class="relative mb-3">
          <div class="absolute -inset-1 rounded-3xl bg-gradient-to-r from-blue-600 to-cyan-500 opacity-20 blur-md"></div>
          <img
            :src="logoImg"
            alt="Lilik Elektronik"
            class="relative h-16 w-16 rounded-2xl shadow-md ring-1 ring-slate-900/5 object-contain"
          />
        </div>

        <div class="flex items-center gap-1.5 text-2xl font-black tracking-tight">
          <span class="text-slate-800">Lilik</span>
          <span class="bg-gradient-to-r from-blue-600 via-indigo-600 to-cyan-500 bg-clip-text text-transparent">Elektronik</span>
        </div>

        <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-700 text-xs font-semibold">
          <span>⚡ Stock Management System</span>
        </div>
      </div>

      <div
        v-if="errorMessage"
        class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600"
      >
        {{ errorMessage }}
      </div>

      <form @submit.prevent="login" class="space-y-5">

        <div>
          <label class="mb-2 block text-sm font-medium text-slate-700">
            Email
          </label>

          <input
            v-model="email"
            type="email"
            required
            placeholder="admin@example.com"
            class="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
          />
        </div>

        <div>
          <label class="mb-2 block text-sm font-medium text-slate-700">
            Password
          </label>

          <input
            v-model="password"
            type="password"
            required
            placeholder="••••••••"
            class="w-full rounded-lg border border-slate-300 px-4 py-3 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full rounded-lg bg-blue-600 px-4 py-3 font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
        >
          {{ loading ? 'Memproses...' : 'Login' }}
        </button>

      </form>
    </div>
  </div>
</template>