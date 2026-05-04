<template>
  <v-container fluid class="pa-6">
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-5">
      <div>
        <h1 class="text-h5 font-weight-bold text-primary">Туры</h1>
        <p class="text-body-2 text-medium-emphasis mt-1">Управление всеми турами</p>
      </div>
      <v-btn color="primary" rounded="lg" to="/tours/create">
        <v-icon start>mdi-plus</v-icon>
        Новый тур
      </v-btn>
    </div>

    <!-- Filters -->
    <v-card rounded="xl" elevation="0" class="border mb-4">
      <v-card-text class="pa-4">
        <!-- Status chips -->
        <div class="d-flex flex-wrap gap-2 mb-3">
          <v-chip
            v-for="f in statusFilters"
            :key="f.value"
            :color="activeStatus === f.value ? 'primary' : 'default'"
            :variant="activeStatus === f.value ? 'flat' : 'outlined'"
            size="small"
            clickable
            @click="activeStatus = f.value; fetchTours()"
          >
            {{ f.label }}
          </v-chip>
        </div>

        <!-- Search & other filters -->
        <v-row dense>
          <v-col cols="12" sm="4">
            <v-text-field
              v-model="search"
              placeholder="Поиск (код, название)..."
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="compact"
              hide-details
              clearable
              @update:model-value="debouncedFetch"
            />
          </v-col>
          <v-col cols="12" sm="3">
            <v-text-field
              v-model="dateFrom"
              label="Начало (от)"
              type="date"
              variant="outlined"
              density="compact"
              hide-details
              clearable
              @update:model-value="fetchTours"
            />
          </v-col>
          <v-col cols="12" sm="3">
            <v-text-field
              v-model="dateTo"
              label="Начало (до)"
              type="date"
              variant="outlined"
              density="compact"
              hide-details
              clearable
              @update:model-value="fetchTours"
            />
          </v-col>
          <v-col cols="12" sm="2">
            <v-text-field
              v-model="countryFilter"
              label="Страна"
              variant="outlined"
              density="compact"
              hide-details
              clearable
              @update:model-value="debouncedFetch"
            />
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Table -->
    <v-card rounded="xl" elevation="0" class="border">
      <v-progress-linear v-if="loading" indeterminate color="primary" />
      <v-data-table
        :headers="headers"
        :items="tours"
        :loading="loading"
        :items-per-page="perPage"
        hide-default-footer
        class="rounded-xl"
      >
        <template #item.tour_code="{ item }">
          <router-link :to="`/tours/${item.id}`" class="text-primary text-decoration-none font-weight-medium">
            {{ item.tour_code }}
          </router-link>
        </template>

        <template #item.counterparty="{ item }">
          {{ item.counterparty?.company_name || '—' }}
        </template>

        <template #item.start_date="{ item }">
          {{ formatDate(item.start_date) }}
        </template>

        <template #item.end_date="{ item }">
          {{ formatDate(item.end_date) }}
        </template>

        <template #item.status="{ item }">
          <v-chip :color="statusColor(item.status)" size="x-small" label>
            {{ statusLabel(item.status) }}
          </v-chip>
        </template>

        <template #item.actions="{ item }">
          <div class="d-flex align-center gap-1">
            <v-tooltip text="Просмотр">
              <template #activator="{ props }">
                <v-btn v-bind="props" icon size="x-small" variant="text" color="primary" :to="`/tours/${item.id}`">
                  <v-icon size="16">mdi-eye</v-icon>
                </v-btn>
              </template>
            </v-tooltip>
            <v-tooltip text="Редактировать">
              <template #activator="{ props }">
                <v-btn v-bind="props" icon size="x-small" variant="text" color="blue" :to="`/tours/${item.id}/edit`">
                  <v-icon size="16">mdi-pencil</v-icon>
                </v-btn>
              </template>
            </v-tooltip>
            <v-menu location="bottom end">
              <template #activator="{ props }">
                <v-tooltip text="Скачать PDF">
                  <template #activator="{ props: tip }">
                    <v-btn v-bind="{ ...props, ...tip }" icon size="x-small" variant="text" color="red-darken-1">
                      <v-icon size="16">mdi-file-pdf-box</v-icon>
                    </v-btn>
                  </template>
                </v-tooltip>
              </template>
              <v-list density="compact" rounded="lg" elevation="4" min-width="150">
                <v-list-item prepend-icon="mdi-translate" title="Русский" @click="downloadPDF(item, 'ru')" />
                <v-list-item prepend-icon="mdi-translate" title="English" @click="downloadPDF(item, 'en')" />
              </v-list>
            </v-menu>
            <v-tooltip text="Дублировать">
              <template #activator="{ props }">
                <v-btn v-bind="props" icon size="x-small" variant="text" color="orange" @click="duplicateTour(item)">
                  <v-icon size="16">mdi-content-copy</v-icon>
                </v-btn>
              </template>
            </v-tooltip>
            <v-tooltip text="Удалить">
              <template #activator="{ props }">
                <v-btn v-bind="props" icon size="x-small" variant="text" color="error" @click="confirmDelete(item)">
                  <v-icon size="16">mdi-delete</v-icon>
                </v-btn>
              </template>
            </v-tooltip>
          </div>
        </template>

        <template #no-data>
          <div class="text-center py-10 text-medium-emphasis">
            <v-icon size="48" class="mb-2">mdi-airplane-off</v-icon>
            <p>Туры не найдены</p>
          </div>
        </template>
      </v-data-table>

      <!-- Pagination -->
      <v-divider />
      <div class="d-flex align-center justify-space-between pa-3">
        <div class="text-body-2 text-medium-emphasis">
          Всего: {{ total }} туров
        </div>
        <v-pagination
          v-model="page"
          :length="totalPages"
          :total-visible="5"
          density="compact"
          @update:model-value="fetchTours"
        />
        <v-select
          v-model="perPage"
          :items="[10, 20, 50, 100]"
          label="На странице"
          variant="outlined"
          density="compact"
          hide-details
          style="max-width: 80px"
          @update:model-value="fetchTours"
        />
      </div>
    </v-card>

    <!-- Delete confirmation dialog -->
    <v-dialog v-model="deleteDialog" max-width="420" persistent>
      <v-card rounded="xl">
        <v-card-title class="pa-5 pb-2">
          <v-icon color="error" class="mr-2">mdi-alert-circle</v-icon>
          Удалить тур
        </v-card-title>
        <v-card-text class="pa-5 pt-2">
          <p>
            Подтвердите удаление тура <strong>{{ deletingTour?.tour_code }}</strong> — {{ deletingTour?.tour_name }}? Это действие необратимо.
          </p>
        </v-card-text>
        <v-card-actions class="pa-5 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="deleteDialog = false">Отмена</v-btn>
          <v-btn color="error" variant="flat" :loading="deleting" @click="deleteTour">
            Удалить
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

const uiStore = useUiStore()

const loading = ref(false)
const tours = ref([])
const total = ref(0)
const page = ref(1)
const perPage = ref(20)
const search = ref('')
const dateFrom = ref('')
const dateTo = ref('')
const countryFilter = ref('')
const activeStatus = ref('')

const deleteDialog = ref(false)
const deleting = ref(false)
const deletingTour = ref(null)

let debounceTimer = null

const totalPages = computed(() => Math.ceil(total.value / perPage.value) || 1)

const statusFilters = [
  { label: 'Все', value: '' },
  { label: 'Черновик', value: 'draft' },
  { label: 'Подтверждён', value: 'confirmed' },
  { label: 'В процессе', value: 'in_progress' },
  { label: 'Завершён', value: 'completed' },
  { label: 'Отменён', value: 'cancelled' },
]

const headers = [
  { title: 'Код тура', key: 'tour_code', width: 110 },
  { title: 'Название', key: 'tour_name' },
  { title: 'Страна', key: 'country', width: 100 },
  { title: 'Контрагент', key: 'counterparty', width: 150 },
  { title: 'Начало', key: 'start_date', width: 110 },
  { title: 'Конец', key: 'end_date', width: 110 },
  { title: 'Пасс.', key: 'pax_count', width: 70 },
  { title: 'Статус', key: 'status', width: 120 },
  { title: 'Действия', key: 'actions', sortable: false, width: 160 },
]

const statusColorMap = {
  draft: 'grey',
  confirmed: 'blue',
  in_progress: 'green',
  completed: 'success',
  cancelled: 'error',
}

const statusLabelMap = {
  draft: 'Черновик',
  confirmed: 'Подтверждён',
  in_progress: 'В процессе',
  completed: 'Завершён',
  cancelled: 'Отменён',
}

function statusColor(s) { return statusColorMap[s] || 'grey' }
function statusLabel(s) { return statusLabelMap[s] || s }

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

async function fetchTours() {
  loading.value = true
  try {
    const params = {
      page: page.value,
      per_page: perPage.value,
      search: search.value || undefined,
      status: activeStatus.value || undefined,
      date_from: dateFrom.value || undefined,
      date_to: dateTo.value || undefined,
      country: countryFilter.value || undefined,
    }
    const res = await api.get('/tours', { params })
    const d = res.data
    tours.value = d.data || d.tours || []
    total.value = d.total || d.meta?.total || tours.value.length
  } catch {
    uiStore.showSnackbar('Ошибка загрузки туров', 'error')
  } finally {
    loading.value = false
  }
}

function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetchTours, 400)
}

function confirmDelete(tour) {
  deletingTour.value = tour
  deleteDialog.value = true
}

async function deleteTour() {
  deleting.value = true
  try {
    await api.delete(`/tours/${deletingTour.value.id}`)
    uiStore.showSnackbar('Тур удалён', 'success')
    deleteDialog.value = false
    fetchTours()
  } catch {
    uiStore.showSnackbar('Ошибка удаления', 'error')
  } finally {
    deleting.value = false
  }
}

async function duplicateTour(tour) {
  try {
    await api.post(`/tours/${tour.id}/duplicate`)
    uiStore.showSnackbar('Дубликат тура создан', 'success')
    fetchTours()
  } catch {
    uiStore.showSnackbar('Ошибка дублирования', 'error')
  }
}

function downloadPDF(tour, lang = 'ru') {
  const token = localStorage.getItem('auth_token')
  window.open(`/api/tours/${tour.id}/pdf?token=${token}&lang=${lang}`, '_blank')
}

onMounted(fetchTours)
</script>

<style scoped>
.border { border: 1px solid #E0E0E0 !important; }
.gap-1 { gap: 4px; }
.gap-2 { gap: 8px; }
</style>
