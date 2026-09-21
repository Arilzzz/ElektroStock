import { createRouter, createWebHistory } from 'vue-router'
import Login from '../pages/auth/Login.vue'
import MainLayout from '../layouts/MainLayout.vue'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { guestOnly: true },
  },
  {
    path: '/',
    component: MainLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/dashboard',
      },
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('../pages/dashboard/Dashboard.vue'),
      },
      {
        path: 'products',
        name: 'products',
        component: () => import('../pages/products/Product.vue'),
      },
      {
        path: 'categories',
        name: 'categories',
        component: () => import('../pages/categories/Categories.vue'),
      },
      {
        path: 'brands',
        name: 'brands',
        component: () => import('../pages/brands/Brands.vue'),
      },
      {
        path: 'stock/in',
        name: 'stock-in',
        component: () => import('../pages/stock/StockIn.vue'),
      },
      {
        path: 'stock/out',
        name: 'stock-out',
        component: () => import('../pages/stock/StockOut.vue'),
      },
      {
        path: 'stock/history',
        name: 'stock-history',
        component: () => import('../pages/stock/StockHistory.vue'),
      },
      {
        path: 'reports',
        name: 'reports',
        component: () => import('../pages/reports/Reports.vue'),
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/dashboard',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')

  if (to.meta.requiresAuth && !token) {
    return next({ name: 'login' })
  }

  if (to.meta.guestOnly && token) {
    return next({ name: 'dashboard' })
  }

  next()
})

export default router