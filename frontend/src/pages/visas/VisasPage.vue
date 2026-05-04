<template>
  <v-container fluid class="pa-6">
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-5">
      <div>
        <h1 class="text-h5 font-weight-bold text-primary">Визы</h1>
        <p class="text-body-2 text-medium-emphasis mt-1">Управление визовыми заявками</p>
      </div>
      <v-btn color="primary" rounded="lg" @click="openAddDialog">
        <v-icon start>mdi-plus</v-icon>
        Новая виза
      </v-btn>
    </div>

    <!-- Expiring visas warning -->
    <v-alert
      v-if="expiringVisas.length > 0"
      type="warning"
      variant="tonal"
      class="mb-4"
      closable
    >
      <strong>Внимание!</strong> {{ expiringVisas.length }} виз(ы) истекают в ближайшее время.
      <ul class="mt-1">
        <li v-for="v in expiringVisas.slice(0, 3)" :key="v.id">
          {{ v.applicant_name }} — {{ formatDate(v.expected_date) }}
        </li>
      </ul>
    </v-alert>

    <!-- Status tabs -->
    <v-tabs v-model="activeStatus" color="primary" class="mb-4" @update:model-value="fetchVisas">
      <v-tab v-for="tab in statusTabs" :key="tab.value" :value="tab.value">
        {{ tab.label }}
      </v-tab>
    </v-tabs>

    <!-- Table -->
    <v-card rounded="xl" elevation="0" class="border">
      <v-progress-linear v-if="loading" indeterminate color="primary" />
      <v-data-table
        :headers="headers"
        :items="visas"
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
          <span v-else>—</span>
        </template>

        <template #item.expected_date="{ item }">
          <span :class="isExpiringSoon(item.expected_date) ? 'text-warning font-weight-bold' : ''">
            {{ formatDate(item.expected_date) }}
            <v-icon v-if="isExpiringSoon(item.expected_date)" size="14" color="warning" class="ml-1">mdi-alert</v-icon>
          </span>
        </template>

        <template #item.status="{ item }">
          <v-chip :color="statusColor(item.status)" size="x-small" label>
            {{ statusLabel(item.status) }}
          </v-chip>
        </template>

        <template #item.actions="{ item }">
          <div class="d-flex gap-1">
            <v-tooltip text="Изменить статус">
              <template #activator="{ props }">
                <v-btn v-bind="props" icon size="x-small" variant="text" color="primary" @click="openStatusDialog(item)">
                  <v-icon size="16">mdi-cog</v-icon>
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
            <v-icon size="48" class="mb-2">mdi-passport</v-icon>
            <p>Визы не найдены</p>
          </div>
        </template>
      </v-data-table>

      <v-divider />
      <div class="d-flex align-center justify-space-between pa-3">
        <div class="text-body-2 text-medium-emphasis">Всего: {{ total }} виз</div>
        <v-pagination v-model="page" :length="totalPages" :total-visible="5" density="compact" @update:model-value="fetchVisas" />
      </div>
    </v-card>

    <!-- Add Dialog -->
    <v-dialog v-model="addDialog" max-width="580" persistent>
      <v-card rounded="xl">
        <v-card-title class="pa-5 pb-2">Новая визовая заявка</v-card-title>
        <v-divider />
        <v-card-text class="pa-5">
          <v-form ref="addFormRef">
            <v-row dense>
              <v-col cols="12" md="6">
                <v-text-field v-model="addForm.applicant_name" label="Полное имя *" variant="outlined" density="compact" :rules="[req]" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="addForm.passport_number" label="Номер паспорта *" variant="outlined" density="compact" :rules="[req]" />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field v-model="addForm.nationality" label="Гражданство" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="4">
                <v-select
                  v-model="addForm.visa_type"
                  :items="['tourist', 'business', 'transit', 'e-visa']"
                  label="Тип визы"
                  variant="outlined"
                  density="compact"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field v-model="addForm.expected_date" label="Ожидаемая дата" type="date" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12">
                <v-select
                  v-model="addForm.tour_id"
                  :items="toursList"
                  item-title="tour_code"
                  item-value="id"
                  label="Тур (необязательно)"
                  variant="outlined"
                  density="compact"
                  clearable
                />
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions class="pa-5 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="addDialog = false">Отмена</v-btn>
          <v-btn color="primary" variant="flat" :loading="addSaving" @click="saveVisa">Добавить</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Status Update Dialog -->
    <v-dialog v-model="statusDialog" max-width="460" persistent>
      <v-card rounded="xl">
        <v-card-title class="pa-5 pb-2">Обновить статус визы</v-card-title>
        <v-card-text class="pa-5 pt-2">
          <p class="text-body-2 mb-3">
            <strong>{{ selectedVisa?.applicant_name }}</strong> — {{ selectedVisa?.passport_number }}
          </p>
          <v-select
            v-model="newStatus"
            :items="statusOptions"
            item-title="label"
            item-value="value"
            label="Новый статус"
            variant="outlined"
            density="compact"
            class="mb-3"
          />
          <v-text-field
            v-model="rejectionReason"
            label="Причина отказа (необязательно)"
            variant="outlined"
            density="compact"
          />
        </v-card-text>
        <v-card-actions class="pa-5 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="statusDialog = false">Отмена</v-btn>
          <v-btn color="primary" variant="flat" :loading="statusSaving" @click="updateStatus">Сохранить</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Dialog -->
    <v-dialog v-model="deleteDialog" max-width="420">
      <v-card rounded="xl">
        <v-card-title class="pa-5 pb-2">
          <v-icon color="error" class="mr-2">mdi-alert-circle</v-icon>
          Подтвердите удаление
        </v-card-title>
        <v-card-text class="pa-5 pt-2">
          Удалить визу <strong>{{ deletingVisa?.applicant_name }}</strong>?
        </v-card-text>
        <v-card-actions class="pa-5 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="deleteDialog = false">Отмена</v-btn>
          <v-btn color="error" variant="flat" :loading="deleting" @click="deleteVisa">Удалить</v-btn>
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
const visas = ref([])
const total = ref(0)
const page = ref(1)
const perPage = ref(20)
const activeStatus = ref('')
const expiringVisas = ref([])
const toursList = ref([])

const addDialog = ref(false)
const addSaving = ref(false)
const addFormRef = ref(null)

const statusDialog = ref(false)
const statusSaving = ref(false)
const selectedVisa = ref(null)
const newStatus = ref('')
const rejectionReason = ref('')

const deleteDialog = ref(false)
const deleting = ref(false)
const deletingVisa = ref(null)

const totalPages = computed(() => Math.ceil(total.value / perPage.value) || 1)
const req = v => !!v || 'Поле обязательно'

const statusTabs = [
  { label: 'Все', value: '' },
  { label: 'Ожидание', value: 'pending' },
  { label: 'Отправлена', value: 'submitted' },
  { label: 'Одобрена', value: 'approved' },
  { label: 'Отклонена', value: 'rejected' },
]

const statusOptions = [
  { label: 'Ожидание', value: 'pending' },
  { label: 'Отправлена', value: 'submitted' },
  { label: 'Одобрена', value: 'approved' },
  { label: 'Отклонена', value: 'rejected' },
]

const statusColorMap = {
  pending: 'orange', submitted: 'blue',
  approved: 'success', rejected: 'error',
}
const statusLabelMap = {
  pending: 'Ожидание', submitted: 'Отправлена',
  approved: 'Одобрена', rejected: 'Отклонена',
}

function statusColor(s) { return statusColorMap[s] || 'grey' }
function statusLabel(s) { return statusLabelMap[s] || s }

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function isExpiringSoon(dateStr) {
  if (!dateStr) return false
  const diff = new Date(dateStr) - new Date()
  return diff > 0 && diff < 7 * 24 * 60 * 60 * 1000
}

const headers = [
  { title: 'Имя', key: 'applicant_name' },
  { title: 'Паспорт', key: 'passport_number', width: 130 },
  { title: 'Гражданство', key: 'nationality', width: 120 },
  { title: 'Тур', key: 'tour_code', width: 110 },
  { title: 'Тип визы', key: 'visa_type', width: 110 },
  { title: 'Статус', key: 'status', width: 130 },
  { title: 'Ожид. дата', key: 'expected_date', width: 130 },
  { title: 'Действия', key: 'actions', sortable: false, width: 90 },
]

const addForm = ref({
  applicant_name: '', passport_number: '', nationality: '',
  visa_type: 'tourist', expected_date: '', tour_id: null,
})

function openAddDialog() {
  addForm.value = { applicant_name: '', passport_number: '', nationality: '', visa_type: 'tourist', expected_date: '', tour_id: null }
  addDialog.value = true
}

function openStatusDialog(visa) {
  selectedVisa.value = visa
  newStatus.value = visa.status
  rejectionReason.value = ''
  statusDialog.value = true
}

function confirmDelete(visa) {
  deletingVisa.value = visa
  deleteDialog.value = true
}

async function fetchVisas() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: perPage.value, status: activeStatus.value || undefined }
    const res = await api.get('/visas', { params })
    const d = res.data
    visas.value = d.data || d.visas || []
    total.value = d.total || d.meta?.total || visas.value.length
    expiringVisas.value = visas.value.filter(v => isExpiringSoon(v.expected_date))
  } catch {
    uiStore.showSnackbar('Ошибка загрузки виз', 'error')
  } finally {
    loading.value = false
  }
}

async function loadTours() {
  try {
    const res = await api.get('/tours?per_page=200&status=confirmed')
    toursList.value = res.data.data || []
  } catch { /* silent */ }
}

async function saveVisa() {
  const { valid } = await addFormRef.value.validate()
  if (!valid) return
  addSaving.value = true
  try {
    await api.post('/visas', addForm.value)
    uiStore.showSnackbar('Виза добавлена', 'success')
    addDialog.value = false
    fetchVisas()
  } catch {
    uiStore.showSnackbar('Ошибка сохранения', 'error')
  } finally {
    addSaving.value = false
  }
}

async function updateStatus() {
  statusSaving.value = true
  try {
    const payload = { status: newStatus.value }
    if (rejectionReason.value) payload.rejection_reason = rejectionReason.value
    await api.put(`/visas/${selectedVisa.value.id}/process`, payload)
    uiStore.showSnackbar('Статус обновлён!', 'success')
    statusDialog.value = false
    fetchVisas()
  } catch {
    uiStore.showSnackbar('Ошибка обновления', 'error')
  } finally {
    statusSaving.value = false
  }
}

async function deleteVisa() {
  deleting.value = true
  try {
    await api.delete(`/visas/${deletingVisa.value.id}`)
    uiStore.showSnackbar('Виза удалена', 'success')
    deleteDialog.value = false
    fetchVisas()
  } catch {
    uiStore.showSnackbar('Ошибка удаления', 'error')
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  fetchVisas()
  loadTours()
})
</script>

<style scoped>
.border { border: 1px solid #E0E0E0 !important; }
.gap-1 { gap: 4px; }
</style>
