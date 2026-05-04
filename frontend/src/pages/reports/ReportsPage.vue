<template>
  <v-container fluid class="pa-6">
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-5">
      <div>
        <h1 class="text-h5 font-weight-bold text-primary">Отчёты</h1>
        <p class="text-body-2 text-medium-emphasis mt-1">Статистика и аналитика системы</p>
      </div>
    </div>

    <!-- Report Tabs -->
    <v-tabs v-model="activeTab" color="primary" class="mb-4">
      <v-tab value="financial">
        <v-icon start size="18">mdi-currency-usd</v-icon>
        Финансы
      </v-tab>
      <v-tab value="tours">
        <v-icon start size="18">mdi-airplane</v-icon>
        Туры
      </v-tab>
      <v-tab value="hotels">
        <v-icon start size="18">mdi-hotel</v-icon>
        Отели
      </v-tab>
      <v-tab value="visas">
        <v-icon start size="18">mdi-passport</v-icon>
        Визы
      </v-tab>
      <v-tab value="counterparties">
        <v-icon start size="18">mdi-domain</v-icon>
        Контрагенты
      </v-tab>
      <v-tab value="staff">
        <v-icon start size="18">mdi-account-group</v-icon>
        Сотрудники
      </v-tab>
    </v-tabs>

    <v-window v-model="activeTab">
      <!-- Financial Tab -->
      <v-window-item value="financial">
        <v-card rounded="xl" elevation="0" class="border mb-4">
          <v-card-text class="pa-4">
            <v-row dense align="center">
              <v-col cols="12" sm="3">
                <v-text-field v-model="finFilter.date_from" label="От" type="date" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" sm="3">
                <v-text-field v-model="finFilter.date_to" label="До" type="date" variant="outlined" density="compact" hide-details />
              </v-col>
              <v-col cols="12" sm="2">
                <v-btn color="primary" variant="flat" block @click="fetchFinancial" :loading="finLoading">
                  <v-icon start>mdi-magnify</v-icon>
                  Поиск
                </v-btn>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <v-row v-if="finData" class="mb-4">
          <v-col cols="12" sm="3" v-for="card in finSummaryCards" :key="card.key">
            <v-card rounded="xl" elevation="0" class="border pa-4">
              <div class="d-flex align-center gap-3">
                <v-avatar :color="card.color" size="44">
                  <v-icon color="white" size="22">{{ card.icon }}</v-icon>
                </v-avatar>
                <div>
                  <p class="text-caption text-medium-emphasis">{{ card.label }}</p>
                  <p class="text-h6 font-weight-bold">{{ formatCurrency(finData[card.key]) }}</p>
                </div>
              </div>
            </v-card>
          </v-col>
        </v-row>

        <v-card v-if="finChartData" rounded="xl" elevation="0" class="border">
          <v-card-title class="pa-4 pb-2 text-body-1 font-weight-bold">Ежемесячная выручка</v-card-title>
          <v-card-text class="pa-4 pt-0">
            <Bar :data="finChartData" :options="barOptions" style="height: 300px" />
          </v-card-text>
        </v-card>

        <v-skeleton-loader v-if="finLoading" type="card, table" class="mt-4" />

        <v-card v-if="finData?.transactions?.length" rounded="xl" elevation="0" class="border mt-4">
          <v-card-title class="pa-4 pb-2 text-body-1 font-weight-bold">Транзакции</v-card-title>
          <v-data-table
            :headers="finHeaders"
            :items="finData.transactions || []"
            density="compact"
            class="rounded-xl"
          >
            <template #item.date="{ item }">{{ formatDate(item.date) }}</template>
            <template #item.amount="{ item }">{{ formatCurrency(item.amount) }}</template>
          </v-data-table>
        </v-card>
      </v-window-item>

      <!-- Tours Stats Tab -->
      <v-window-item value="tours">
        <v-btn color="primary" variant="flat" :loading="tourStatsLoading" class="mb-4" @click="fetchTourStats">
          <v-icon start>mdi-refresh</v-icon>
          Обновить
        </v-btn>

        <v-row v-if="tourStats">
          <v-col cols="12" md="5">
            <v-card rounded="xl" elevation="0" class="border">
              <v-card-title class="pa-4 pb-2 text-body-1 font-weight-bold">По статусу</v-card-title>
              <v-card-text class="pa-4 pt-0">
                <Doughnut
                  v-if="tourStatusChartData"
                  :data="tourStatusChartData"
                  :options="donutOptions"
                  style="height: 250px"
                />
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="12" md="7">
            <v-card rounded="xl" elevation="0" class="border">
              <v-card-title class="pa-4 pb-2 text-body-1 font-weight-bold">По стране</v-card-title>
              <v-card-text class="pa-4 pt-0">
                <Bar
                  v-if="tourCountryChartData"
                  :data="tourCountryChartData"
                  :options="barOptions"
                  style="height: 250px"
                />
              </v-card-text>
            </v-card>
          </v-col>
          <v-col cols="12">
            <v-card rounded="xl" elevation="0" class="border">
              <v-card-title class="pa-4 pb-2 text-body-1 font-weight-bold">Статистика</v-card-title>
              <v-data-table
                :headers="tourStatsHeaders"
                :items="tourStats.by_country || []"
                density="compact"
                class="rounded-xl"
              />
            </v-card>
          </v-col>
        </v-row>
        <v-skeleton-loader v-if="tourStatsLoading" type="card@2" />
      </v-window-item>

      <!-- Hotels Report Tab -->
      <v-window-item value="hotels">
        <v-btn color="primary" variant="flat" :loading="hotelReportLoading" class="mb-4" @click="fetchHotelReport">
          <v-icon start>mdi-refresh</v-icon>
          Обновить
        </v-btn>
        <v-card rounded="xl" elevation="0" class="border">
          <v-progress-linear v-if="hotelReportLoading" indeterminate color="primary" />
          <v-card-title class="pa-4 pb-2 text-body-1 font-weight-bold">Статистика по отелям</v-card-title>
          <v-data-table
            :headers="hotelReportHeaders"
            :items="hotelReport || []"
            density="comfortable"
            class="rounded-xl"
          >
            <template #no-data>
              <div class="text-center py-6 text-medium-emphasis">Нет данных</div>
            </template>
          </v-data-table>
        </v-card>
      </v-window-item>

      <!-- Visas Report Tab -->
      <v-window-item value="visas">
        <v-btn color="primary" variant="flat" :loading="visaReportLoading" class="mb-4" @click="fetchVisaReport">
          <v-icon start>mdi-refresh</v-icon>
          Обновить
        </v-btn>
        <v-card rounded="xl" elevation="0" class="border">
          <v-progress-linear v-if="visaReportLoading" indeterminate color="primary" />
          <v-card-title class="pa-4 pb-2 text-body-1 font-weight-bold">Статистика по визам</v-card-title>
          <v-data-table
            :headers="visaReportHeaders"
            :items="visaReport || []"
            density="comfortable"
            class="rounded-xl"
          >
            <template #item.status="{ item }">
              <v-chip :color="visaStatusColor(item.status)" size="x-small" label>{{ item.status }}</v-chip>
            </template>
            <template #no-data>
              <div class="text-center py-6 text-medium-emphasis">Нет данных</div>
            </template>
          </v-data-table>
        </v-card>
      </v-window-item>

      <!-- Counterparties Report Tab -->
      <v-window-item value="counterparties">
        <v-btn color="primary" variant="flat" :loading="cpReportLoading" class="mb-4" @click="fetchCpReport">
          <v-icon start>mdi-refresh</v-icon>
          Обновить
        </v-btn>
        <v-card rounded="xl" elevation="0" class="border">
          <v-progress-linear v-if="cpReportLoading" indeterminate color="primary" />
          <v-card-title class="pa-4 pb-2 text-body-1 font-weight-bold">Статистика по контрагентам</v-card-title>
          <v-data-table
            :headers="cpReportHeaders"
            :items="cpReport || []"
            density="comfortable"
            class="rounded-xl"
          >
            <template #no-data>
              <div class="text-center py-6 text-medium-emphasis">Нет данных</div>
            </template>
          </v-data-table>
        </v-card>
      </v-window-item>

      <!-- Staff Report Tab -->
      <v-window-item value="staff">
        <v-btn color="primary" variant="flat" :loading="staffReportLoading" class="mb-4" @click="fetchStaffReport">
          <v-icon start>mdi-refresh</v-icon>
          Обновить
        </v-btn>
        <v-card rounded="xl" elevation="0" class="border">
          <v-progress-linear v-if="staffReportLoading" indeterminate color="primary" />
          <v-card-title class="pa-4 pb-2 text-body-1 font-weight-bold">Статистика по сотрудникам</v-card-title>
          <v-data-table
            :headers="staffReportHeaders"
            :items="staffReport || []"
            density="comfortable"
            class="rounded-xl"
          >
            <template #no-data>
              <div class="text-center py-6 text-medium-emphasis">Нет данных</div>
            </template>
          </v-data-table>
        </v-card>
      </v-window-item>
    </v-window>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Bar, Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS, Title, Tooltip, Legend, BarElement,
  CategoryScale, LinearScale, ArcElement,
} from 'chart.js'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement)

const uiStore = useUiStore()
const activeTab = ref('financial')

// Financial
const finLoading = ref(false)
const finData = ref(null)
const finFilter = ref({ date_from: '', date_to: '' })

const finSummaryCards = [
  { key: 'total_revenue', label: 'Общая выручка', icon: 'mdi-cash', color: 'green' },
  { key: 'total_expenses', label: 'Общие расходы', icon: 'mdi-cash-minus', color: 'red' },
  { key: 'net_profit', label: 'Чистая прибыль', icon: 'mdi-chart-line', color: 'blue' },
  { key: 'pending_payments', label: 'Неоплачено', icon: 'mdi-clock', color: 'orange' },
]

const finChartData = computed(() => {
  if (!finData.value?.monthly_chart) return null
  return {
    labels: finData.value.monthly_chart.labels || [],
    datasets: [
      { label: 'Выручка', data: finData.value.monthly_chart.revenue || [], backgroundColor: '#4A90D9', borderRadius: 4 },
      { label: 'Расходы', data: finData.value.monthly_chart.expenses || [], backgroundColor: '#E53935', borderRadius: 4 },
    ],
  }
})

const finHeaders = [
  { title: 'Дата', key: 'date' },
  { title: 'Описание', key: 'description' },
  { title: 'Тур', key: 'tour_code' },
  { title: 'Сумма', key: 'amount' },
  { title: 'Тип', key: 'type' },
]

// Tour Stats
const tourStatsLoading = ref(false)
const tourStats = ref(null)

const tourStatusChartData = computed(() => {
  if (!tourStats.value?.by_status) return null
  return {
    labels: tourStats.value.by_status.map(s => s.status),
    datasets: [{
      data: tourStats.value.by_status.map(s => s.count),
      backgroundColor: ['#4A90D9', '#43A047', '#FFA000', '#8E24AA', '#E53935'],
      borderWidth: 2, borderColor: '#fff',
    }],
  }
})

const tourCountryChartData = computed(() => {
  if (!tourStats.value?.by_country) return null
  return {
    labels: tourStats.value.by_country.map(c => c.country),
    datasets: [{
      label: 'Кол-во туров',
      data: tourStats.value.by_country.map(c => c.count),
      backgroundColor: '#4A90D9', borderRadius: 4,
    }],
  }
})

const tourStatsHeaders = [
  { title: 'Страна', key: 'country' },
  { title: 'Кол-во туров', key: 'count' },
  { title: 'Пассажиры', key: 'total_pax' },
  { title: 'Выручка (USD)', key: 'revenue' },
]

// Hotels
const hotelReportLoading = ref(false)
const hotelReport = ref(null)
const hotelReportHeaders = [
  { title: 'Отель', key: 'hotel_name' },
  { title: 'Город', key: 'city' },
  { title: 'Бронирования', key: 'booking_count' },
  { title: 'Номера', key: 'total_rooms' },
  { title: 'Гости', key: 'total_guests' },
]

// Visas
const visaReportLoading = ref(false)
const visaReport = ref(null)
const visaReportHeaders = [
  { title: 'Имя', key: 'applicant_name' },
  { title: 'Гражданство', key: 'nationality' },
  { title: 'Тип', key: 'visa_type' },
  { title: 'Статус', key: 'status' },
  { title: 'Дата', key: 'expected_date' },
]

// Counterparties
const cpReportLoading = ref(false)
const cpReport = ref(null)
const cpReportHeaders = [
  { title: 'Компания', key: 'company_name' },
  { title: 'Тип', key: 'type' },
  { title: 'Туры', key: 'tours_count' },
  { title: 'Выручка (USD)', key: 'revenue' },
]

// Staff
const staffReportLoading = ref(false)
const staffReport = ref(null)
const staffReportHeaders = [
  { title: 'Сотрудник', key: 'name' },
  { title: 'Должность', key: 'role' },
  { title: 'Ответ. туры', key: 'tours_count' },
  { title: 'Завершённые', key: 'completed_tours' },
]

const barOptions = {
  responsive: true, maintainAspectRatio: false,
  plugins: { legend: { position: 'top' } },
  scales: { y: { beginAtZero: true, grid: { color: '#F0F0F0' } }, x: { grid: { display: false } } },
}

const donutOptions = {
  responsive: true, maintainAspectRatio: false,
  plugins: { legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 } } } },
}

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function formatCurrency(val) {
  if (!val && val !== 0) return '—'
  return `${Number(val).toLocaleString()} USD`
}

function visaStatusColor(s) {
  return { pending: 'orange', submitted: 'blue', approved: 'success', rejected: 'error' }[s] || 'grey'
}

async function fetchFinancial() {
  finLoading.value = true
  try {
    const res = await api.get('/reports/financial', { params: finFilter.value })
    finData.value = res.data.data || res.data
  } catch {
    uiStore.showSnackbar('Ошибка загрузки финансового отчёта', 'error')
  } finally {
    finLoading.value = false
  }
}

async function fetchTourStats() {
  tourStatsLoading.value = true
  try {
    const res = await api.get('/reports/tours')
    tourStats.value = res.data.data || res.data
  } catch {
    uiStore.showSnackbar('Ошибка загрузки статистики туров', 'error')
  } finally {
    tourStatsLoading.value = false
  }
}

async function fetchHotelReport() {
  hotelReportLoading.value = true
  try {
    const res = await api.get('/reports/hotels')
    hotelReport.value = res.data.data || res.data
  } catch {
    uiStore.showSnackbar('Ошибка загрузки отчёта по отелям', 'error')
  } finally {
    hotelReportLoading.value = false
  }
}

async function fetchVisaReport() {
  visaReportLoading.value = true
  try {
    const res = await api.get('/reports/visas')
    visaReport.value = res.data.data || res.data
  } catch {
    uiStore.showSnackbar('Ошибка загрузки отчёта по визам', 'error')
  } finally {
    visaReportLoading.value = false
  }
}

async function fetchCpReport() {
  cpReportLoading.value = true
  try {
    const res = await api.get('/reports/counterparties')
    cpReport.value = res.data.data || res.data
  } catch {
    uiStore.showSnackbar('Ошибка загрузки отчёта по контрагентам', 'error')
  } finally {
    cpReportLoading.value = false
  }
}

async function fetchStaffReport() {
  staffReportLoading.value = true
  try {
    const res = await api.get('/reports/staff')
    staffReport.value = res.data.data || res.data
  } catch {
    uiStore.showSnackbar('Ошибка загрузки отчёта по сотрудникам', 'error')
  } finally {
    staffReportLoading.value = false
  }
}

watch(activeTab, (tab) => {
  if (tab === 'financial' && !finData.value) fetchFinancial()
  if (tab === 'tours' && !tourStats.value) fetchTourStats()
  if (tab === 'hotels' && !hotelReport.value) fetchHotelReport()
  if (tab === 'visas' && !visaReport.value) fetchVisaReport()
  if (tab === 'counterparties' && !cpReport.value) fetchCpReport()
  if (tab === 'staff' && !staffReport.value) fetchStaffReport()
})

onMounted(fetchFinancial)
</script>

<style scoped>
.border { border: 1px solid #E0E0E0 !important; }
.gap-3 { gap: 12px; }
</style>
