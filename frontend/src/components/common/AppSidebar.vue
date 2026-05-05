<template>
  <v-navigation-drawer
    v-model="ui.sidebarOpen"
    :rail="ui.sidebarMini"
    permanent
    color="#0D1B2A"
    width="265"
  >
    <!-- Logo -->
    <div
      class="d-flex align-center px-4 py-3"
      style="border-bottom: 1px solid rgba(255,255,255,0.07); min-height: 64px;"
    >
      <img
        :src="'/logo'"
        alt="Sogda Tour"
        style="width: 38px; height: 38px; border-radius: 8px; object-fit: cover; flex-shrink: 0;"
        @error="logoError = true"
      />
      <div v-if="!ui.sidebarMini" class="ml-3">
        <div style="color:#FFFFFF; font-size:15px; font-weight:700; letter-spacing:.5px; line-height:1.1;">
          SOGDA TOUR
        </div>
        <div style="color:#4A90D9; font-size:10px; letter-spacing:.5px;">
          {{ ui.lang === 'ru' ? 'Автоматизированная система' : 'Automated Travel System' }}
        </div>
      </div>
    </div>

    <!-- Navigation -->
    <v-list density="compact" nav class="py-2 px-2">
      <template v-for="item in visibleMenuItems" :key="item.key ?? item.headerRu">
        <div
          v-if="item.headerRu && !ui.sidebarMini"
          class="px-2 pt-3 pb-1"
          style="color:rgba(184,201,232,0.45); font-size:10px; text-transform:uppercase; letter-spacing:1.2px; font-weight:600;"
        >
          {{ ui.lang === 'ru' ? item.headerRu : item.headerEn }}
        </div>
        <v-divider
          v-else-if="item.headerRu && ui.sidebarMini"
          class="my-2"
          style="border-color:rgba(255,255,255,0.06);"
        />

        <v-tooltip
          v-else
          :text="ui.lang === 'ru' ? item.titleRu : item.titleEn"
          location="end"
          :disabled="!ui.sidebarMini"
        >
          <template #activator="{ props: tip }">
            <v-list-item
              v-bind="tip"
              :to="item.to"
              :prepend-icon="item.icon"
              rounded="lg"
              class="mb-1 nav-item"
              active-class="nav-item-active"
              :title="!ui.sidebarMini ? (ui.lang === 'ru' ? item.titleRu : item.titleEn) : ''"
              :value="item.to"
            >
              <template v-if="!ui.sidebarMini && item.badge" #append>
                <v-chip size="x-small" color="error" density="compact">{{ item.badge }}</v-chip>
              </template>
            </v-list-item>
          </template>
        </v-tooltip>
      </template>
    </v-list>

    <!-- User footer -->
    <template #append>
      <div style="border-top: 1px solid rgba(255,255,255,0.07);" class="pa-2">
        <v-list-item rounded="lg" class="nav-item" @click="handleLogout">
          <template #prepend>
            <v-avatar color="#1E3A5F" size="32" rounded="lg">
              <span style="color:#FFFFFF; font-size:11px; font-weight:600;">{{ initials }}</span>
            </v-avatar>
          </template>
          <template v-if="!ui.sidebarMini">
            <v-list-item-title style="color:#FFFFFF; font-size:13px; font-weight:500;">
              {{ auth.user?.name }}
            </v-list-item-title>
            <v-list-item-subtitle style="color:#4A90D9; font-size:10px;">
              {{ roleLabel }}
            </v-list-item-subtitle>
          </template>
          <template v-if="!ui.sidebarMini" #append>
            <v-icon size="16" style="color:rgba(184,201,232,0.5);">mdi-logout</v-icon>
          </template>
        </v-list-item>
      </div>
    </template>
  </v-navigation-drawer>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'

const auth = useAuthStore()
const ui = useUiStore()
const router = useRouter()
const logoError = ref(false)

const initials = computed(() =>
  auth.user?.name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) ?? 'U'
)

const roleLabelMap = {
  ru: {
    super_admin: 'Супер Администратор', manager: 'Менеджер туров',
    sales: 'Продажи', accountant: 'Бухгалтер',
    visa_officer: 'Визовый офицер', transport_manager: 'Менеджер транспорта',
    hotel_khiva: 'Отель Хива', hotel_samarkand: 'Отель Самарканд',
    hotel_bukhara: 'Отель Бухара', staff: 'Сотрудник',
  },
  en: {
    super_admin: 'Super Admin', manager: 'Tour Manager',
    sales: 'Sales', accountant: 'Accountant',
    visa_officer: 'Visa Officer', transport_manager: 'Transport Manager',
    hotel_khiva: 'Hotel Khiva', hotel_samarkand: 'Hotel Samarkand',
    hotel_bukhara: 'Hotel Bukhara', staff: 'Staff',
  },
}

const roleLabel = computed(() => {
  const map = roleLabelMap[ui.lang] ?? roleLabelMap.ru
  const raw = auth.user?.roles?.[0]
  const role = raw?.name ?? raw
  return map[role] ?? role ?? (ui.lang === 'ru' ? 'Пользователь' : 'User')
})

const allMenuItems = [
  {
    headerRu: 'Обзор',
    headerEn: 'Overview',
  },
  {
    key: 'dashboard',
    titleRu: 'Главная', titleEn: 'Dashboard',
    icon: 'mdi-view-dashboard-outline', to: '/dashboard', roles: [],
  },

  {
    headerRu: 'Туры',
    headerEn: 'Tours',
  },
  {
    key: 'tours',
    titleRu: 'Все туры', titleEn: 'All Tours',
    icon: 'mdi-airplane', to: '/tours',
    roles: ['super_admin', 'manager', 'sales', 'visa_officer', 'transport_manager', 'accountant'],
  },
  {
    key: 'tours.create',
    titleRu: 'Новый тур', titleEn: 'New Tour',
    icon: 'mdi-plus-circle-outline', to: '/tours/create',
    roles: ['super_admin', 'manager'],
  },

  {
    headerRu: 'Размещение',
    headerEn: 'Accommodation',
  },
  {
    key: 'hotels',
    titleRu: 'Отели', titleEn: 'Hotels',
    icon: 'mdi-hotel', to: '/hotels',
    roles: ['super_admin', 'manager', 'hotel_khiva', 'hotel_samarkand', 'hotel_bukhara'],
  },
  {
    key: 'hotel-bookings',
    titleRu: 'Бронирования', titleEn: 'Bookings',
    icon: 'mdi-calendar-check-outline', to: '/hotels/bookings',
    roles: ['super_admin', 'manager', 'hotel_khiva', 'hotel_samarkand', 'hotel_bukhara'],
  },

  {
    headerRu: 'Операции',
    headerEn: 'Operations',
  },
  {
    key: 'counterparties',
    titleRu: 'Контрагенты', titleEn: 'Partners',
    icon: 'mdi-handshake-outline', to: '/counterparties',
    roles: ['super_admin', 'manager'],
  },
  {
    key: 'transport',
    titleRu: 'Транспорт', titleEn: 'Transport',
    icon: 'mdi-bus-side', to: '/transport',
    roles: ['super_admin', 'manager', 'transport_manager'],
  },
  {
    key: 'offers',
    titleRu: 'Предложения', titleEn: 'Offers',
    icon: 'mdi-email-outline', to: '/offers',
    roles: ['super_admin', 'manager', 'sales'],
  },
  {
    key: 'visas',
    titleRu: 'Визы', titleEn: 'Visas',
    icon: 'mdi-passport', to: '/visas',
    roles: ['super_admin', 'manager', 'visa_officer'],
  },

  {
    headerRu: 'Аналитика',
    headerEn: 'Analytics',
  },
  {
    key: 'reports',
    titleRu: 'Отчёты', titleEn: 'Reports',
    icon: 'mdi-chart-bar', to: '/reports',
    roles: ['super_admin', 'manager', 'accountant'],
  },

  {
    headerRu: 'Настройки',
    headerEn: 'Configuration',
  },
  {
    key: 'destinations',
    titleRu: 'Направления', titleEn: 'Destinations',
    icon: 'mdi-map-marker-multiple-outline', to: '/settings/destinations',
    roles: ['super_admin', 'manager'],
  },
  {
    key: 'users',
    titleRu: 'Пользователи', titleEn: 'Users',
    icon: 'mdi-account-group-outline', to: '/settings/users',
    roles: ['super_admin'],
  },
  {
    key: 'branches',
    titleRu: 'Филиалы', titleEn: 'Branches',
    icon: 'mdi-office-building-outline', to: '/settings/branches',
    roles: ['super_admin'],
  },
]

const visibleMenuItems = computed(() =>
  allMenuItems.filter(item => {
    if (item.headerRu) return true
    if (!item.roles?.length) return true
    return auth.hasRole(item.roles)
  })
)

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<style>
.nav-item {
  color: #B8C9E8 !important;
  font-size: 13px !important;
}
.nav-item:hover {
  background: rgba(74, 144, 217, 0.1) !important;
  color: #FFFFFF !important;
}
.nav-item-active {
  background: #1E3A6E !important;
  color: #FFFFFF !important;
  border-left: 3px solid #4A90D9 !important;
}
.nav-item-active .v-icon {
  color: #4A90D9 !important;
}
</style>
