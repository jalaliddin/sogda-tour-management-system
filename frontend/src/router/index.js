import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    component: () => import('@/layouts/AuthLayout.vue'),
    children: [
      { path: '', name: 'login', component: () => import('@/pages/auth/LoginPage.vue') },
    ],
    meta: { guest: true },
  },
  {
    path: '/',
    component: () => import('@/layouts/MainLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', redirect: '/dashboard' },
      { path: 'dashboard', name: 'dashboard', component: () => import('@/pages/dashboard/DashboardPage.vue') },
      {
        path: 'tours',
        children: [
          { path: '', name: 'tours', component: () => import('@/pages/tours/ToursPage.vue') },
          { path: 'create', name: 'tours.create', component: () => import('@/pages/tours/TourCreatePage.vue'), meta: { roles: ['super_admin', 'manager'] } },
          { path: ':id', name: 'tours.show', component: () => import('@/pages/tours/TourDetailPage.vue') },
          { path: ':id/edit', name: 'tours.edit', component: () => import('@/pages/tours/TourEditPage.vue'), meta: { roles: ['super_admin', 'manager'] } },
        ],
      },
      {
        path: 'hotels',
        children: [
          { path: '', name: 'hotels', component: () => import('@/pages/hotels/HotelsPage.vue') },
          { path: 'bookings', name: 'hotel-bookings', component: () => import('@/pages/hotels/HotelBookingsPage.vue') },
        ],
      },
      { path: 'counterparties', name: 'counterparties', component: () => import('@/pages/counterparties/CounterpartiesPage.vue'), meta: { roles: ['super_admin', 'manager'] } },
      { path: 'offers', name: 'offers', component: () => import('@/pages/offers/OffersPage.vue') },
      { path: 'visas', name: 'visas', component: () => import('@/pages/visas/VisasPage.vue') },
      { path: 'transport', name: 'transport', component: () => import('@/pages/transport/TransportPage.vue'), meta: { roles: ['super_admin', 'manager', 'transport_manager'] } },
      { path: 'reports', name: 'reports', component: () => import('@/pages/reports/ReportsPage.vue'), meta: { roles: ['super_admin', 'manager', 'accountant'] } },
      {
        path: 'settings',
        children: [
          { path: 'users', name: 'settings.users', component: () => import('@/pages/settings/UsersPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
          { path: 'branches', name: 'settings.branches', component: () => import('@/pages/settings/BranchesPage.vue'), meta: { requiresAuth: true, roles: ['super_admin'] } },
          { path: 'destinations', name: 'settings.destinations', component: () => import('@/pages/settings/DestinationsPage.vue'), meta: { requiresAuth: true, roles: ['super_admin', 'manager'] } },
        ],
      },
    ],
  },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  if (to.meta.guest && auth.isAuthenticated) {
    return next({ name: 'dashboard' })
  }

  if (to.meta.roles && !auth.hasRole(to.meta.roles)) {
    return next({ name: 'dashboard' })
  }

  next()
})

export default router
