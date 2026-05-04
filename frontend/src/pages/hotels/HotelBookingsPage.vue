<template>
  <v-container fluid class="pa-6">
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-5">
      <div>
        <h1 class="text-h5 font-weight-bold text-primary">Бронирования отелей</h1>
        <p class="text-body-2 text-medium-emphasis mt-1">Управление всеми запросами на бронирование</p>
      </div>
    </div>

    <!-- Status tabs -->
    <v-tabs v-model="activeStatus" color="primary" class="mb-4" @update:model-value="fetchBookings">
      <v-tab v-for="tab in statusTabs" :key="tab.value" :value="tab.value">
        {{ tab.label }}
        <v-chip v-if="tab.count" class="ml-2" size="x-small" :color="tab.chipColor">{{ tab.count }}</v-chip>
      </v-tab>
    </v-tabs>

    <!-- Table -->
    <v-card rounded="xl" elevation="0" class="border">
      <v-progress-linear v-if="loading" indeterminate color="primary" />
      <v-data-table
        :headers="headers"
        :items="bookings"
        :loading="loading"
        hide-default-footer
        class="rounded-xl"
      >
        <template #item.tour_code="{ item }">
          <router-link
            v-if="item.tour?.id"
            :to="`/tours/${item.tour.id}`"
            class="text-primary text-decoration-none font-weight-medium"
          >
            {{ item.tour?.tour_code || '—' }}
          </router-link>
          <span v-else>{{ item.tour?.tour_code || '—' }}</span>
        </template>

        <template #item.hotel_name="{ item }">
          {{ item.hotel?.name || '—' }}
        </template>

        <template #item.check_in_date="{ item }">
          {{ formatDate(item.check_in_date) }}
        </template>

        <template #item.check_out_date="{ item }">
          {{ formatDate(item.check_out_date) }}
        </template>

        <template #item.status="{ item }">
          <v-chip
            :color="statusColor(item.status)"
            size="small"
            label
            clickable
            @click="openStatusDialog(item)"
          >
            {{ statusLabel(item.status) }}
            <v-icon end size="12">mdi-pencil</v-icon>
          </v-chip>
        </template>

        <template #item.hotel_confirmation_number="{ item }">
          <span v-if="item.hotel_confirmation_number" class="text-body-2 font-weight-medium">
            {{ item.hotel_confirmation_number }}
          </span>
          <v-chip v-else size="x-small" variant="text" color="grey">—</v-chip>
        </template>

        <template #no-data>
          <div class="text-center py-10 text-medium-emphasis">
            <v-icon size="48" class="mb-2">mdi-hotel</v-icon>
            <p>Бронирования не найдены</p>
          </div>
        </template>
      </v-data-table>

      <!-- Pagination -->
      <v-divider />
      <div class="d-flex align-center justify-space-between pa-3">
        <div class="text-body-2 text-medium-emphasis">Всего: {{ total }} бронирований</div>
        <v-pagination
          v-model="page"
          :length="totalPages"
          :total-visible="5"
          density="compact"
          @update:model-value="fetchBookings"
        />
      </div>
    </v-card>

    <!-- Status change dialog -->
    <v-dialog v-model="statusDialog" max-width="460" persistent>
      <v-card rounded="xl">
        <v-card-title class="pa-5 pb-2">Изменить статус бронирования</v-card-title>
        <v-card-text class="pa-5 pt-2">
          <p class="text-body-2 mb-4">
            <strong>{{ selectedBooking?.hotel?.name }}</strong> —
            {{ formatDate(selectedBooking?.check_in) }} – {{ formatDate(selectedBooking?.check_out) }}
          </p>
          <v-select
            v-model="newStatus"
            :items="statusOptions"
            item-title="label"
            item-value="value"
            label="Новый статус"
            variant="outlined"
            density="compact"
          />
          <v-text-field
            v-model="hotelConfirmationNumber"
            label="Номер подтверждения (необязательно)"
            variant="outlined"
            density="compact"
            class="mt-3"
          />
        </v-card-text>
        <v-card-actions class="pa-5 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="statusDialog = false">Отмена</v-btn>
          <v-btn color="primary" variant="flat" :loading="statusSaving" @click="updateStatus">
            Сохранить
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'
import { useAuthStore } from '@/stores/auth'

const uiStore = useUiStore()
const authStore = useAuthStore()

const loading = ref(false)
const bookings = ref([])
const total = ref(0)
const page = ref(1)
const perPage = ref(20)
const activeStatus = ref('')

const statusDialog = ref(false)
const statusSaving = ref(false)
const selectedBooking = ref(null)
const newStatus = ref('')
const hotelConfirmationNumber = ref('')

const totalPages = computed(() => Math.ceil(total.value / perPage.value) || 1)

const statusTabs = ref([
  { label: 'Все', value: '', chipColor: 'grey', count: 0 },
  { label: 'Ожидание', value: 'pending', chipColor: 'orange', count: 0 },
  { label: 'Лист ожидания', value: 'waiting_list', chipColor: 'deep-orange', count: 0 },
  { label: 'OK', value: 'ok', chipColor: 'teal', count: 0 },
  { label: 'Подтверждён', value: 'confirmed', chipColor: 'blue', count: 0 },
  { label: 'Отменён', value: 'cancelled', chipColor: 'error', count: 0 },
])

const statusOptions = [
  { label: 'Ожидание', value: 'pending' },
  { label: 'Лист ожидания', value: 'waiting_list' },
  { label: 'OK', value: 'ok' },
  { label: 'Подтверждён', value: 'confirmed' },
  { label: 'Отменён', value: 'cancelled' },
]

const statusColorMap = {
  pending: 'orange', waiting_list: 'deep-orange',
  ok: 'teal', confirmed: 'blue', cancelled: 'error',
}
const statusLabelMap = {
  pending: 'Ожидание', waiting_list: 'Лист ожидания',
  ok: 'OK', confirmed: 'Подтверждён', cancelled: 'Отменён',
}

function statusColor(s) { return statusColorMap[s] || 'grey' }
function statusLabel(s) { return statusLabelMap[s] || s }

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const headers = [
  { title: 'Код тура', key: 'tour_code', width: 110 },
  { title: 'Название тура', key: 'tour.tour_name' },
  { title: 'Отель', key: 'hotel_name' },
  { title: 'Check-in', key: 'check_in_date', width: 110 },
  { title: 'Check-out', key: 'check_out_date', width: 110 },
  { title: 'Номера', key: 'room_count', width: 80 },
  { title: 'Статус', key: 'status', width: 150 },
  { title: 'Подтв. №', key: 'hotel_confirmation_number', width: 130 },
]

async function fetchBookings() {
  loading.value = true
  try {
    const params = {
      page: page.value,
      per_page: perPage.value,
      status: activeStatus.value || undefined,
    }
    const res = await api.get('/hotel-bookings', { params })
    const d = res.data
    bookings.value = d.data || d.bookings || []
    total.value = d.total || d.meta?.total || bookings.value.length
  } catch {
    uiStore.showSnackbar('Ошибка загрузки бронирований', 'error')
  } finally {
    loading.value = false
  }
}

function openStatusDialog(booking) {
  selectedBooking.value = booking
  newStatus.value = booking.status
  hotelConfirmationNumber.value = booking.hotel_confirmation_number || ''
  statusDialog.value = true
}

async function updateStatus() {
  statusSaving.value = true
  try {
    const payload = { status: newStatus.value }
    if (hotelConfirmationNumber.value) payload.hotel_confirmation_number = hotelConfirmationNumber.value
    await api.put(`/hotel-bookings/${selectedBooking.value.id}/status`, payload)
    uiStore.showSnackbar('Статус обновлён!', 'success')
    statusDialog.value = false
    await fetchBookings()
    await fetchStats()
  } catch {
    uiStore.showSnackbar('Ошибка обновления', 'error')
  } finally {
    statusSaving.value = false
  }
}

async function fetchStats() {
  try {
    const res = await api.get('/hotel-bookings/statistics')
    const stats = res.data.data || {}
    statusTabs.value.forEach(tab => {
      if (tab.value && stats[tab.value] !== undefined) {
        tab.count = stats[tab.value]
      }
    })
  } catch { /* silent */ }
}

onMounted(async () => {
  await fetchBookings()
  await fetchStats()
})
</script>

<style scoped>
.border { border: 1px solid #E0E0E0 !important; }
</style>
