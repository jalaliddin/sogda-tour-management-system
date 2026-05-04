<template>
  <v-container fluid class="pa-6">
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-5">
      <div class="d-flex align-center">
        <v-btn icon variant="text" to="/tours" class="mr-2">
          <v-icon>mdi-arrow-left</v-icon>
        </v-btn>
        <div>
          <h1 class="text-h5 font-weight-bold text-primary">
            {{ tour?.tour_code || 'Детали тура' }}
          </h1>
          <p class="text-body-2 text-medium-emphasis mt-1">{{ tour?.tour_name }}</p>
        </div>
      </div>

      <div class="d-flex align-center gap-2">
        <v-chip v-if="tour" :color="statusColor(tour.status)" label>
          {{ statusLabel(tour.status) }}
        </v-chip>

        <v-btn
          v-if="tour?.status === 'draft'"
          color="success"
          variant="flat"
          :loading="confirming"
          @click="confirmTour"
        >
          <v-icon start>mdi-check-circle</v-icon>
          Подтвердить
        </v-btn>

        <v-menu>
          <template #activator="{ props }">
            <v-btn v-bind="props" variant="tonal" color="red-darken-1">
              <v-icon start>mdi-file-pdf-box</v-icon>
              PDF
              <v-icon end size="16">mdi-chevron-down</v-icon>
            </v-btn>
          </template>
          <v-list density="compact" rounded="lg" elevation="4" min-width="160">
            <v-list-item prepend-icon="mdi-translate" title="Русский" @click="downloadPDF('ru')" />
            <v-list-item prepend-icon="mdi-translate" title="English" @click="downloadPDF('en')" />
          </v-list>
        </v-menu>

        <v-btn variant="tonal" color="primary" :to="`/tours/${tourId}/edit`">
          <v-icon start>mdi-pencil</v-icon>
          Редактировать
        </v-btn>
      </div>
    </div>

    <!-- Loading -->
    <template v-if="loading">
      <v-skeleton-loader type="card@3" />
    </template>

    <template v-else-if="tour">
      <!-- Tabs -->
      <v-tabs v-model="activeTab" color="primary" class="mb-4">
        <v-tab value="overview">
          <v-icon start size="18">mdi-information-outline</v-icon>
          Обзор
        </v-tab>
        <v-tab value="hotels">
          <v-icon start size="18">mdi-hotel</v-icon>
          Отели
        </v-tab>
        <v-tab value="transport">
          <v-icon start size="18">mdi-bus</v-icon>
          Транспорт
        </v-tab>
        <v-tab value="meals">
          <v-icon start size="18">mdi-silverware-fork-knife</v-icon>
          Питание
        </v-tab>
        <v-tab value="tickets">
          <v-icon start size="18">mdi-ticket-outline</v-icon>
          Билеты
        </v-tab>
        <v-tab value="visas">
          <v-icon start size="18">mdi-passport</v-icon>
          Визы
        </v-tab>
      </v-tabs>

      <v-window v-model="activeTab">
        <!-- Overview Tab -->
        <v-window-item value="overview">
          <v-row>
            <v-col cols="12" md="6">
              <v-card rounded="xl" elevation="0" class="border mb-4">
                <v-card-title class="pa-4 pb-2 text-body-1 font-weight-bold">Основные данные</v-card-title>
                <v-list density="compact" class="pa-2">
                  <v-list-item>
                    <template #prepend><v-icon size="18" color="primary" class="mr-2">mdi-identifier</v-icon></template>
                    <template #title>Код тура</template>
                    <template #subtitle>{{ tour.tour_code }}</template>
                  </v-list-item>
                  <v-list-item>
                    <template #prepend><v-icon size="18" color="primary" class="mr-2">mdi-map-marker</v-icon></template>
                    <template #title>Страна</template>
                    <template #subtitle>{{ tour.country }}</template>
                  </v-list-item>
                  <v-list-item>
                    <template #prepend><v-icon size="18" color="primary" class="mr-2">mdi-account-group</v-icon></template>
                    <template #title>Пассажиры</template>
                    <template #subtitle>Взрослых: {{ tour.pax_adults }}, Детей: {{ tour.pax_children }}</template>
                  </v-list-item>
                  <v-list-item>
                    <template #prepend><v-icon size="18" color="primary" class="mr-2">mdi-calendar-range</v-icon></template>
                    <template #title>Даты</template>
                    <template #subtitle>{{ formatDate(tour.start_date) }} – {{ formatDate(tour.end_date) }}</template>
                  </v-list-item>
                  <v-list-item v-if="tour.counterparty">
                    <template #prepend><v-icon size="18" color="primary" class="mr-2">mdi-domain</v-icon></template>
                    <template #title>Контрагент</template>
                    <template #subtitle>{{ tour.counterparty.company_name }}</template>
                  </v-list-item>
                  <v-list-item v-if="tour.assigned_staff">
                    <template #prepend><v-icon size="18" color="primary" class="mr-2">mdi-account-tie</v-icon></template>
                    <template #title>Ответственный</template>
                    <template #subtitle>{{ tour.assigned_staff?.name }}</template>
                  </v-list-item>
                </v-list>
              </v-card>
            </v-col>

            <v-col cols="12" md="6">
              <v-card rounded="xl" elevation="0" class="border mb-4">
                <v-card-title class="pa-4 pb-2 text-body-1 font-weight-bold">Данные рейса</v-card-title>
                <v-list density="compact" class="pa-2">
                  <v-list-item>
                    <template #prepend><v-icon size="18" color="primary" class="mr-2">mdi-airplane-landing</v-icon></template>
                    <template #title>Тип прибытия</template>
                    <template #subtitle>{{ tour.arrival_type || '—' }}</template>
                  </v-list-item>
                  <v-list-item v-if="tour.arrival_flight_number">
                    <template #prepend><v-icon size="18" color="primary" class="mr-2">mdi-airplane</v-icon></template>
                    <template #title>Рейс прибытия</template>
                    <template #subtitle>{{ tour.arrival_flight_number }} / {{ formatDateTime(tour.arrival_flight_time) }} / {{ tour.arrival_terminal }}</template>
                  </v-list-item>
                  <v-list-item v-if="tour.departure_flight_number">
                    <template #prepend><v-icon size="18" color="primary" class="mr-2">mdi-airplane-takeoff</v-icon></template>
                    <template #title>Рейс отправления</template>
                    <template #subtitle>{{ tour.departure_flight_number }} / {{ formatDateTime(tour.departure_flight_time) }} / {{ tour.departure_terminal }}</template>
                  </v-list-item>
                  <v-list-item v-if="tour.notes">
                    <template #prepend><v-icon size="18" color="primary" class="mr-2">mdi-note-text</v-icon></template>
                    <template #title>Примечание</template>
                    <template #subtitle>{{ tour.notes }}</template>
                  </v-list-item>
                </v-list>
              </v-card>

              <v-card rounded="xl" elevation="0" class="border">
                <v-card-title class="pa-4 pb-2 text-body-1 font-weight-bold">Направления</v-card-title>
                <v-list density="compact" class="pa-2">
                  <v-list-item
                    v-for="(dest, i) in (tour.destinations || [])"
                    :key="i"
                  >
                    <template #prepend>
                      <v-avatar color="primary" size="22" class="mr-2">
                        <span class="text-caption text-white">{{ i + 1 }}</span>
                      </v-avatar>
                    </template>
                    <template #title>{{ dest.city }}</template>
                    <template #subtitle>{{ formatDate(dest.arrival_date) }} – {{ formatDate(dest.departure_date) }}</template>
                  </v-list-item>
                  <v-list-item v-if="!tour.destinations?.length">
                    <template #title><span class="text-medium-emphasis">Направления не указаны</span></template>
                  </v-list-item>
                </v-list>
              </v-card>
            </v-col>
          </v-row>
        </v-window-item>

        <!-- Hotels Tab -->
        <v-window-item value="hotels">
          <v-card rounded="xl" elevation="0" class="border">
            <v-card-title class="pa-4 pb-2 text-body-1 font-weight-bold">Бронирования отелей</v-card-title>
            <v-data-table
              :headers="hotelHeaders"
              :items="tour.hotels || []"
              density="comfortable"
              hide-default-footer
              class="rounded-xl"
            >
              <template #item.hotel_name="{ item }">{{ item.hotel?.name || '—' }}</template>
              <template #item.check_in_date="{ item }">{{ formatDate(item.check_in_date) }}</template>
              <template #item.check_out_date="{ item }">{{ formatDate(item.check_out_date) }}</template>
              <template #item.status="{ item }">
                <v-chip :color="statusColor(item.status)" size="x-small" label>
                  {{ statusLabel(item.status) }}
                </v-chip>
              </template>
              <template #no-data>
                <div class="text-center py-6 text-medium-emphasis">Нет данных об отелях</div>
              </template>
            </v-data-table>
          </v-card>
        </v-window-item>

        <!-- Transport Tab -->
        <v-window-item value="transport">
          <v-card rounded="xl" elevation="0" class="border">
            <v-card-title class="pa-4 pb-2 text-body-1 font-weight-bold">Транспорт</v-card-title>
            <v-data-table
              :headers="transportHeaders"
              :items="tour.transports || []"
              density="comfortable"
              hide-default-footer
              class="rounded-xl"
            >
              <template #item.transport_date="{ item }">{{ formatDate(item.transport_date) }}</template>
              <template #item.is_own_fleet="{ item }">
                <v-chip :color="item.is_own_fleet ? 'teal' : 'blue'" size="x-small" label>
                  {{ item.is_own_fleet ? 'Собственный' : 'Аренда' }}
                </v-chip>
              </template>
              <template #no-data>
                <div class="text-center py-6 text-medium-emphasis">Нет данных о транспорте</div>
              </template>
            </v-data-table>
          </v-card>
        </v-window-item>

        <!-- Meals Tab -->
        <v-window-item value="meals">
          <v-card rounded="xl" elevation="0" class="border">
            <v-card-title class="pa-4 pb-2 text-body-1 font-weight-bold">Питание</v-card-title>
            <v-data-table
              :headers="mealHeaders"
              :items="tour.meals || []"
              density="comfortable"
              hide-default-footer
              class="rounded-xl"
            >
              <template #item.meal_date="{ item }">{{ formatDate(item.meal_date) }}</template>
              <template #item.restaurant_name="{ item }">{{ item.restaurant?.company_name || '—' }}</template>
              <template #item.meal_type="{ item }">
                <v-chip
                  :color="item.meal_type === 'breakfast' ? 'orange' : item.meal_type === 'lunch' ? 'green' : 'blue'"
                  size="x-small" label
                >
                  {{ mealTypeLabel(item.meal_type) }}
                </v-chip>
              </template>
              <template #no-data>
                <div class="text-center py-6 text-medium-emphasis">Нет данных о питании</div>
              </template>
            </v-data-table>
          </v-card>
        </v-window-item>

        <!-- Tickets Tab -->
        <v-window-item value="tickets">
          <v-card rounded="xl" elevation="0" class="border">
            <v-card-title class="pa-4 pb-2 text-body-1 font-weight-bold">Входные билеты</v-card-title>
            <v-data-table
              :headers="ticketHeaders"
              :items="tour.entrance_tickets || []"
              density="comfortable"
              hide-default-footer
              class="rounded-xl"
            >
              <template #item.visit_date="{ item }">{{ formatDate(item.visit_date) }}</template>
              <template #no-data>
                <div class="text-center py-6 text-medium-emphasis">Нет данных о билетах</div>
              </template>
            </v-data-table>
          </v-card>
        </v-window-item>

        <!-- Visas Tab -->
        <v-window-item value="visas">
          <v-card rounded="xl" elevation="0" class="border">
            <v-card-title class="pa-4 pb-2 text-body-1 font-weight-bold">Визы</v-card-title>
            <v-data-table
              :headers="visaHeaders"
              :items="tour.visas || []"
              density="comfortable"
              hide-default-footer
              class="rounded-xl"
            >
              <template #item.status="{ item }">
                <v-chip :color="statusColor(item.status)" size="x-small" label>
                  {{ statusLabel(item.status) }}
                </v-chip>
              </template>
              <template #no-data>
                <div class="text-center py-6 text-medium-emphasis">Нет данных о визах</div>
              </template>
            </v-data-table>
          </v-card>
        </v-window-item>
      </v-window>
    </template>

    <v-alert v-else-if="!loading" type="error" variant="tonal" class="mt-4">
      Тур не найден.
    </v-alert>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'

const route = useRoute()
const uiStore = useUiStore()

const tourId = route.params.id
const loading = ref(false)
const confirming = ref(false)
const tour = ref(null)
const activeTab = ref('overview')

const statusColorMap = {
  draft: 'grey', confirmed: 'blue', in_progress: 'green',
  completed: 'success', cancelled: 'error', pending: 'orange',
  ok: 'teal', waiting_list: 'deep-orange',
}
const statusLabelMap = {
  draft: 'Черновик', confirmed: 'Подтверждён', in_progress: 'В процессе',
  completed: 'Завершён', cancelled: 'Отменён', pending: 'Ожидание',
  ok: 'OK', waiting_list: 'Лист ожидания',
}

function statusColor(s) { return statusColorMap[s] || 'grey' }
function statusLabel(s) { return statusLabelMap[s] || s }
function mealTypeLabel(t) {
  return { breakfast: 'Завтрак', lunch: 'Обед', dinner: 'Ужин' }[t] || t
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' })
}
function formatDateTime(d) {
  if (!d) return '—'
  return new Date(d).toLocaleString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const hotelHeaders = [
  { title: 'Отель', key: 'hotel_name' },
  { title: 'Тип номера', key: 'room_type' },
  { title: 'Номера', key: 'room_count' },
  { title: 'Check-in', key: 'check_in_date' },
  { title: 'Check-out', key: 'check_out_date' },
  { title: 'Цена/ночь (USD)', key: 'price_per_night_usd' },
  { title: 'Статус', key: 'status' },
]
const transportHeaders = [
  { title: 'Откуда', key: 'route_from' },
  { title: 'Куда', key: 'route_to' },
  { title: 'Дата', key: 'transport_date' },
  { title: 'Тип', key: 'transport_type' },
  { title: 'Транспорт', key: 'is_own_fleet' },
]
const mealHeaders = [
  { title: 'Дата', key: 'meal_date' },
  { title: 'Тип питания', key: 'meal_type' },
  { title: 'Ресторан', key: 'restaurant_name' },
  { title: 'Меню', key: 'menu_type' },
  { title: 'Цена/чел (USD)', key: 'price_per_person_usd' },
]
const ticketHeaders = [
  { title: 'Место', key: 'attraction_name' },
  { title: 'Город', key: 'city' },
  { title: 'Дата', key: 'visit_date' },
  { title: 'Чел.', key: 'pax_count' },
  { title: 'Цена/чел (USD)', key: 'price_per_person_usd' },
]
const visaHeaders = [
  { title: 'Имя', key: 'applicant_name' },
  { title: 'Паспорт', key: 'passport_number' },
  { title: 'Гражданство', key: 'nationality' },
  { title: 'Тип визы', key: 'visa_type' },
  { title: 'Статус', key: 'status' },
]

async function fetchTour() {
  loading.value = true
  try {
    const res = await api.get(`/tours/${tourId}`)
    tour.value = res.data.data || res.data
  } catch {
    uiStore.showSnackbar('Ошибка загрузки тура', 'error')
  } finally {
    loading.value = false
  }
}

async function confirmTour() {
  confirming.value = true
  try {
    await api.post(`/tours/${tourId}/confirm`)
    uiStore.showSnackbar('Тур подтверждён!', 'success')
    fetchTour()
  } catch {
    uiStore.showSnackbar('Ошибка подтверждения', 'error')
  } finally {
    confirming.value = false
  }
}

function downloadPDF(lang = 'ru') {
  const token = localStorage.getItem('auth_token')
  const base = import.meta.env.VITE_API_URL || '/api'
  window.open(`${base}/tours/${tourId}/pdf?token=${token}&lang=${lang}`, '_blank')
}

onMounted(fetchTour)
</script>

<style scoped>
.border { border: 1px solid #E0E0E0 !important; }
.gap-2 { gap: 8px; }
</style>
