<template>
  <v-container fluid class="pa-6">
    <!-- Page header -->
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h1 class="text-h5 font-weight-bold text-primary">Главная</h1>
        <p class="text-body-2 text-medium-emphasis mt-1">Общая статистика и сегодняшнее состояние</p>
      </div>
      <v-btn color="primary" variant="tonal" @click="fetchDashboard" :loading="loading">
        <v-icon start>mdi-refresh</v-icon>
        Обновить
      </v-btn>
    </div>

    <!-- Loading skeleton -->
    <template v-if="loading">
      <v-row class="mb-4">
        <v-col v-for="i in 4" :key="i" cols="12" sm="6" lg="3">
          <v-skeleton-loader type="card" />
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12" md="8">
          <v-skeleton-loader type="card, table-row@5" />
        </v-col>
        <v-col cols="12" md="4">
          <v-skeleton-loader type="card" />
        </v-col>
      </v-row>
    </template>

    <template v-else>
      <!-- Stat cards -->
      <v-row class="mb-4">
        <v-col
          v-for="card in statCards"
          :key="card.key"
          cols="12" sm="6" lg="3"
        >
          <v-card rounded="xl" :color="card.bgColor" class="stat-card" elevation="0">
            <v-card-text class="pa-5">
              <div class="d-flex align-center justify-space-between">
                <div>
                  <p class="text-body-2 mb-1" :style="{ color: card.labelColor }">{{ card.label }}</p>
                  <p class="text-h4 font-weight-bold" :style="{ color: card.valueColor }">
                    {{ dashboardData[card.key] ?? 0 }}
                  </p>
                </div>
                <v-avatar :color="card.iconBg" size="52">
                  <v-icon :color="card.iconColor" size="26">{{ card.icon }}</v-icon>
                </v-avatar>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Charts Row -->
      <v-row class="mb-4">
        <!-- Revenue bar chart -->
        <v-col cols="12" md="8">
          <v-card rounded="xl" elevation="0" class="border">
            <v-card-title class="pa-5 pb-2 text-body-1 font-weight-bold">
              <v-icon start color="primary" size="20">mdi-chart-bar</v-icon>
              Ежемесячная выручка (USD)
            </v-card-title>
            <v-card-text class="pa-5 pt-2">
              <Bar
                v-if="revenueChartData"
                :data="revenueChartData"
                :options="barChartOptions"
                style="height: 260px"
              />
              <div v-else class="text-center py-10 text-medium-emphasis">
                <v-icon size="40">mdi-chart-bar</v-icon>
                <p class="mt-2">Данные отсутствуют</p>
              </div>
            </v-card-text>
          </v-card>
        </v-col>

        <!-- Tour status donut -->
        <v-col cols="12" md="4">
          <v-card rounded="xl" elevation="0" class="border" height="100%">
            <v-card-title class="pa-5 pb-2 text-body-1 font-weight-bold">
              <v-icon start color="primary" size="20">mdi-chart-donut</v-icon>
              Статусы туров
            </v-card-title>
            <v-card-text class="pa-5 pt-2">
              <Doughnut
                v-if="donutChartData"
                :data="donutChartData"
                :options="donutChartOptions"
                style="height: 220px"
              />
              <div v-else class="text-center py-10 text-medium-emphasis">
                <v-icon size="40">mdi-chart-donut</v-icon>
                <p class="mt-2">Данные отсутствуют</p>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>

      <!-- Tables row -->
      <v-row>
        <!-- Upcoming tours -->
        <v-col cols="12" md="7">
          <v-card rounded="xl" elevation="0" class="border">
            <v-card-title class="pa-5 pb-2 d-flex align-center justify-space-between">
              <span class="text-body-1 font-weight-bold">
                <v-icon start color="primary" size="20">mdi-calendar-clock</v-icon>
                Ближайшие туры
              </span>
              <v-btn variant="text" size="small" color="primary" to="/tours">Все</v-btn>
            </v-card-title>
            <v-data-table
              :headers="upcomingHeaders"
              :items="dashboardData.upcoming_tours || []"
              density="compact"
              hide-default-footer
              class="rounded-xl"
            >
              <template #item.status="{ item }">
                <StatusBadge :status="item.status" />
              </template>
              <template #item.start_date="{ item }">
                {{ formatDate(item.start_date) }}
              </template>
              <template #item.tour_code="{ item }">
                <router-link :to="`/tours/${item.id}`" class="text-primary text-decoration-none font-weight-medium">
                  {{ item.tour_code }}
                </router-link>
              </template>
              <template #no-data>
                <div class="text-center py-6 text-medium-emphasis">Ближайших туров нет</div>
              </template>
            </v-data-table>
          </v-card>
        </v-col>

        <!-- Recent tours -->
        <v-col cols="12" md="5">
          <v-card rounded="xl" elevation="0" class="border">
            <v-card-title class="pa-5 pb-2 d-flex align-center justify-space-between">
              <span class="text-body-1 font-weight-bold">
                <v-icon start color="primary" size="20">mdi-history</v-icon>
                Последние туры
              </span>
              <v-btn variant="text" size="small" color="primary" to="/tours">Все</v-btn>
            </v-card-title>
            <v-data-table
              :headers="recentHeaders"
              :items="dashboardData.recent_tours || []"
              density="compact"
              hide-default-footer
              class="rounded-xl"
            >
              <template #item.status="{ item }">
                <StatusBadge :status="item.status" />
              </template>
              <template #item.start_date="{ item }">
                {{ formatDate(item.start_date) }}
              </template>
              <template #item.tour_code="{ item }">
                <router-link :to="`/tours/${item.id}`" class="text-primary text-decoration-none font-weight-medium">
                  {{ item.tour_code }}
                </router-link>
              </template>
              <template #no-data>
                <div class="text-center py-6 text-medium-emphasis">Последних туров нет</div>
              </template>
            </v-data-table>
          </v-card>
        </v-col>
      </v-row>
    </template>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Bar, Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement,
} from 'chart.js'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement)

const uiStore = useUiStore()
const loading = ref(false)
const dashboardData = ref({})

const statCards = [
  {
    key: 'today_tours',
    label: 'Туры сегодня',
    icon: 'mdi-airplane-takeoff',
    bgColor: '#EBF3FE',
    labelColor: '#4A90D9',
    valueColor: '#1A2744',
    iconBg: '#4A90D9',
    iconColor: 'white',
  },
  {
    key: 'active_tours',
    label: 'Активные туры',
    icon: 'mdi-map-marker-path',
    bgColor: '#E8F5E9',
    labelColor: '#388E3C',
    valueColor: '#1A2744',
    iconBg: '#43A047',
    iconColor: 'white',
  },
  {
    key: 'pending_visas',
    label: 'Ожидают визы',
    icon: 'mdi-passport',
    bgColor: '#FFF8E1',
    labelColor: '#F57F17',
    valueColor: '#1A2744',
    iconBg: '#FFA000',
    iconColor: 'white',
  },
  {
    key: 'upcoming_checkouts',
    label: 'Ближайшие выезды',
    icon: 'mdi-hotel',
    bgColor: '#F3E5F5',
    labelColor: '#7B1FA2',
    valueColor: '#1A2744',
    iconBg: '#8E24AA',
    iconColor: 'white',
  },
]

const upcomingHeaders = [
  { title: 'Код', key: 'tour_code', width: 100 },
  { title: 'Название', key: 'tour_name' },
  { title: 'Страна', key: 'country', width: 90 },
  { title: 'Начало', key: 'start_date', width: 100 },
  { title: 'Пасс.', key: 'pax_count', width: 80 },
  { title: 'Статус', key: 'status', width: 110 },
]

const recentHeaders = [
  { title: 'Код', key: 'tour_code', width: 100 },
  { title: 'Название', key: 'tour_name' },
  { title: 'Начало', key: 'start_date', width: 100 },
  { title: 'Статус', key: 'status', width: 110 },
]

const revenueChartData = computed(() => {
  const chart = dashboardData.value.revenue_chart
  if (!chart) return null
  return {
    labels: chart.labels || [],
    datasets: [
      {
        label: 'Выручка (USD)',
        data: chart.data || [],
        backgroundColor: '#4A90D9',
        borderRadius: 6,
      },
    ],
  }
})

const barChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: { mode: 'index' },
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: { color: '#F0F0F0' },
    },
    x: {
      grid: { display: false },
    },
  },
}

const donutChartData = computed(() => {
  const chart = dashboardData.value.tour_status_chart
  if (!chart) return null
  return {
    labels: chart.labels || [],
    datasets: [
      {
        data: chart.data || [],
        backgroundColor: ['#4A90D9', '#43A047', '#FFA000', '#8E24AA', '#E53935'],
        borderWidth: 2,
        borderColor: '#fff',
      },
    ],
  }
})

const donutChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 } } },
  },
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

async function fetchDashboard() {
  loading.value = true
  try {
    const res = await api.get('/dashboard')
    dashboardData.value = res.data.data || res.data
  } catch (error) {
    uiStore.showSnackbar('Ошибка загрузки данных', 'error')
  } finally {
    loading.value = false
  }
}

onMounted(fetchDashboard)
</script>

<script>
import { defineComponent, h } from 'vue'
import { VChip } from 'vuetify/components'

const statusMap = {
  draft: { color: 'grey', text: 'Черновик' },
  confirmed: { color: 'blue', text: 'Подтверждён' },
  in_progress: { color: 'green', text: 'В процессе' },
  completed: { color: 'success', text: 'Завершён' },
  cancelled: { color: 'error', text: 'Отменён' },
  pending: { color: 'orange', text: 'Ожидание' },
}

const StatusBadge = defineComponent({
  props: { status: String },
  setup(props) {
    return () => {
      const s = statusMap[props.status] || { color: 'grey', text: props.status }
      return h(VChip, { color: s.color, size: 'x-small', label: true }, () => s.text)
    }
  },
})

export default { components: { StatusBadge } }
</script>

<style scoped>
.stat-card {
  transition: transform 0.2s;
}
.stat-card:hover {
  transform: translateY(-2px);
}
.border {
  border: 1px solid #E0E0E0 !important;
}
</style>
