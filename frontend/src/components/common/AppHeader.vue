<template>
  <v-app-bar flat color="white" border="b" height="64">
    <v-btn icon variant="text" size="small" class="ml-1" @click="ui.toggleSidebar">
      <v-icon>mdi-menu</v-icon>
    </v-btn>
    <v-btn icon variant="text" size="small" class="ml-1" @click="ui.toggleMini">
      <v-icon>mdi-dock-left</v-icon>
    </v-btn>

    <!-- Page title -->
    <v-app-bar-title class="ml-2">
      <span class="text-body-1 font-weight-medium text-medium-emphasis">
        {{ pageTitle }}
      </span>
    </v-app-bar-title>

    <v-spacer />

    <!-- Language toggle -->
    <v-btn-toggle
      v-model="ui.lang"
      mandatory
      density="compact"
      rounded="lg"
      class="mr-3"
      color="primary"
      @update:model-value="ui.setLang"
    >
      <v-btn value="ru" size="small" style="min-width:42px; font-size:11px; font-weight:700;">
        RU
      </v-btn>
      <v-btn value="en" size="small" style="min-width:42px; font-size:11px; font-weight:700;">
        EN
      </v-btn>
    </v-btn-toggle>

    <v-divider vertical class="mr-2" style="height:28px; align-self:center;" />

    <!-- User menu -->
    <v-menu>
      <template #activator="{ props }">
        <v-btn v-bind="props" variant="text" class="mr-2 text-none" density="comfortable">
          <v-avatar color="primary" size="30" class="mr-2" rounded="lg">
            <span class="text-white" style="font-size:11px; font-weight:700;">{{ initials }}</span>
          </v-avatar>
          <span class="d-none d-sm-flex text-body-2 font-weight-medium">{{ auth.user?.name }}</span>
          <v-icon size="16" class="ml-1">mdi-chevron-down</v-icon>
        </v-btn>
      </template>
      <v-card rounded="lg" elevation="4" min-width="200">
        <v-list density="compact" nav>
          <v-list-item
            prepend-icon="mdi-account-outline"
            :title="ui.lang === 'ru' ? 'Мой профиль' : 'My Profile'"
            :subtitle="ui.lang === 'ru' ? 'Настройки аккаунта' : 'Account settings'"
          />
          <v-divider class="my-1" />
          <v-list-item
            prepend-icon="mdi-logout"
            :title="ui.lang === 'ru' ? 'Выйти' : 'Sign Out'"
            @click="handleLogout"
          />
        </v-list>
      </v-card>
    </v-menu>
  </v-app-bar>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'

const auth = useAuthStore()
const ui = useUiStore()
const router = useRouter()
const route = useRoute()

const initials = computed(() =>
  auth.user?.name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2) ?? 'U'
)

const pageTitlesRu = {
  dashboard: 'Главная',
  tours: 'Туры',
  'tours.create': 'Новый тур',
  'tours.show': 'Детали тура',
  'tours.edit': 'Редактировать тур',
  hotels: 'Отели',
  'hotel-bookings': 'Бронирования отелей',
  counterparties: 'Контрагенты',
  offers: 'Предложения',
  visas: 'Визы',
  transport: 'Транспорт',
  reports: 'Отчёты и аналитика',
  'settings.users': 'Управление пользователями',
  'settings.branches': 'Филиалы',
  'settings.destinations': 'Направления',
}

const pageTitlesEn = {
  dashboard: 'Dashboard',
  tours: 'Tours',
  'tours.create': 'New Tour',
  'tours.show': 'Tour Details',
  'tours.edit': 'Edit Tour',
  hotels: 'Hotels',
  'hotel-bookings': 'Hotel Bookings',
  counterparties: 'Partners & Counterparties',
  offers: 'Offers',
  visas: 'Visas',
  transport: 'Transport',
  reports: 'Reports & Analytics',
  'settings.users': 'Users Management',
  'settings.branches': 'Branches',
  'settings.destinations': 'Destinations',
}

const pageTitle = computed(() => {
  const titles = ui.lang === 'ru' ? pageTitlesRu : pageTitlesEn
  return titles[route.name] ?? 'Sogda Tour'
})

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>
