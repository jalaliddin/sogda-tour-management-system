<template>
  <v-container fluid class="pa-6">
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-5">
      <div>
        <h1 class="text-h5 font-weight-bold text-primary">Предложения</h1>
        <p class="text-body-2 text-medium-emphasis mt-1">Управление туристическими предложениями</p>
      </div>
      <v-btn color="primary" rounded="lg" @click="openAddDialog">
        <v-icon start>mdi-plus</v-icon>
        Новое предложение
      </v-btn>
    </div>

    <!-- Status tabs -->
    <v-tabs v-model="activeStatus" color="primary" class="mb-4" @update:model-value="fetchOffers">
      <v-tab v-for="tab in statusTabs" :key="tab.value" :value="tab.value">
        {{ tab.label }}
      </v-tab>
    </v-tabs>

    <!-- Table -->
    <v-card rounded="xl" elevation="0" class="border">
      <v-progress-linear v-if="loading" indeterminate color="primary" />
      <v-data-table
        :headers="headers"
        :items="offers"
        :loading="loading"
        hide-default-footer
        class="rounded-xl"
      >
        <template #item.counterparty="{ item }">
          {{ item.counterparty?.company_name || '—' }}
        </template>

        <template #item.start_date="{ item }">
          {{ formatDate(item.start_date) }}
        </template>

        <template #item.end_date="{ item }">
          {{ formatDate(item.end_date) }}
        </template>

        <template #item.pax_range="{ item }">
          {{ item.pax_min }}–{{ item.pax_max }}
        </template>

        <template #item.price_per_person="{ item }">
          {{ item.price_per_person ? `${Number(item.price_per_person).toLocaleString()} USD` : '—' }}
        </template>

        <template #item.status="{ item }">
          <v-chip :color="statusColor(item.status)" size="x-small" label>
            {{ statusLabel(item.status) }}
          </v-chip>
        </template>

        <template #item.actions="{ item }">
          <div class="d-flex gap-1">
            <v-tooltip text="Принять">
              <template #activator="{ props }">
                <v-btn
                  v-bind="props"
                  v-if="item.status === 'new' || item.status === 'reviewing'"
                  icon size="x-small" variant="text" color="success"
                  @click="acceptOffer(item)"
                >
                  <v-icon size="16">mdi-check-circle</v-icon>
                </v-btn>
              </template>
            </v-tooltip>
            <v-tooltip text="Отклонить">
              <template #activator="{ props }">
                <v-btn
                  v-bind="props"
                  v-if="item.status === 'new' || item.status === 'reviewing'"
                  icon size="x-small" variant="text" color="error"
                  @click="rejectOffer(item)"
                >
                  <v-icon size="16">mdi-close-circle</v-icon>
                </v-btn>
              </template>
            </v-tooltip>
            <v-tooltip text="Конвертировать в тур">
              <template #activator="{ props }">
                <v-btn
                  v-bind="props"
                  v-if="item.status === 'accepted'"
                  icon size="x-small" variant="text" color="primary"
                  @click="convertToTour(item)"
                >
                  <v-icon size="16">mdi-airplane-takeoff</v-icon>
                </v-btn>
              </template>
            </v-tooltip>
            <v-tooltip text="Редактировать">
              <template #activator="{ props }">
                <v-btn v-bind="props" icon size="x-small" variant="text" color="blue" @click="openEditDialog(item)">
                  <v-icon size="16">mdi-pencil</v-icon>
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
            <v-icon size="48" class="mb-2">mdi-file-document-outline</v-icon>
            <p>Предложения не найдены</p>
          </div>
        </template>
      </v-data-table>

      <v-divider />
      <div class="d-flex align-center justify-space-between pa-3">
        <div class="text-body-2 text-medium-emphasis">Всего: {{ total }} предложений</div>
        <v-pagination v-model="page" :length="totalPages" :total-visible="5" density="compact" @update:model-value="fetchOffers" />
      </div>
    </v-card>

    <!-- Add/Edit Dialog -->
    <v-dialog v-model="dialog" max-width="680" persistent>
      <v-card rounded="xl">
        <v-card-title class="pa-5 pb-2">
          {{ editingItem ? 'Редактировать предложение' : 'Новое предложение' }}
        </v-card-title>
        <v-divider />
        <v-card-text class="pa-5">
          <v-form ref="formRef">
            <v-row dense>
              <v-col cols="12" md="6">
                <v-text-field v-model="offerForm.offer_name" label="Название *" variant="outlined" density="compact" :rules="[req]" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="offerForm.counterparty_id"
                  :items="counterparties"
                  item-title="company_name"
                  item-value="id"
                  label="Контрагент"
                  variant="outlined"
                  density="compact"
                  clearable
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="offerForm.offer_type" label="Тип предложения" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field v-model="offerForm.start_date" label="Начало" type="date" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field v-model="offerForm.end_date" label="Конец" type="date" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field v-model.number="offerForm.pax_min" label="Мин. чел." type="number" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field v-model.number="offerForm.pax_max" label="Макс. чел." type="number" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="3">
                <v-text-field v-model.number="offerForm.price_per_person" label="Цена/чел. (USD)" type="number" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="3">
                <v-select
                  v-model="offerForm.status"
                  :items="offerStatusItems"
                  item-title="label"
                  item-value="value"
                  label="Статус"
                  variant="outlined"
                  density="compact"
                />
              </v-col>
              <v-col cols="12">
                <v-textarea v-model="offerForm.notes" label="Примечание" variant="outlined" density="compact" rows="2" />
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions class="pa-5 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="dialog = false">Отмена</v-btn>
          <v-btn color="primary" variant="flat" :loading="dialogSaving" @click="saveItem">
            {{ editingItem ? 'Сохранить' : 'Добавить' }}
          </v-btn>
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
          Удалить предложение <strong>{{ deletingItem?.offer_name }}</strong>?
        </v-card-text>
        <v-card-actions class="pa-5 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="deleteDialog = false">Отмена</v-btn>
          <v-btn color="error" variant="flat" :loading="deleting" @click="deleteItem">Удалить</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'

const uiStore = useUiStore()
const router = useRouter()

const loading = ref(false)
const offers = ref([])
const total = ref(0)
const page = ref(1)
const perPage = ref(20)
const activeStatus = ref('')
const counterparties = ref([])

const dialog = ref(false)
const dialogSaving = ref(false)
const editingItem = ref(null)
const formRef = ref(null)

const deleteDialog = ref(false)
const deleting = ref(false)
const deletingItem = ref(null)

const totalPages = computed(() => Math.ceil(total.value / perPage.value) || 1)
const req = v => !!v || 'Поле обязательно'

const statusTabs = [
  { label: 'Все', value: '' },
  { label: 'Новые', value: 'new' },
  { label: 'На рассмотрении', value: 'reviewing' },
  { label: 'Принятые', value: 'accepted' },
  { label: 'Отклонённые', value: 'rejected' },
]

const offerStatusItems = [
  { label: 'Новый', value: 'new' },
  { label: 'На рассмотрении', value: 'reviewing' },
  { label: 'Принят', value: 'accepted' },
  { label: 'Отклонён', value: 'rejected' },
]

const statusColorMap = {
  new: 'blue', reviewing: 'orange',
  accepted: 'success', rejected: 'error',
}
const statusLabelMap = {
  new: 'Новый', reviewing: 'На рассмотрении',
  accepted: 'Принят', rejected: 'Отклонён',
}

function statusColor(s) { return statusColorMap[s] || 'grey' }
function statusLabel(s) { return statusLabelMap[s] || s }

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('ru-RU', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const headers = [
  { title: 'Название', key: 'offer_name' },
  { title: 'Контрагент', key: 'counterparty', width: 160 },
  { title: 'Тип', key: 'offer_type', width: 130 },
  { title: 'Начало', key: 'start_date', width: 110 },
  { title: 'Конец', key: 'end_date', width: 110 },
  { title: 'Чел.', key: 'pax_range', width: 90 },
  { title: 'Цена/чел.', key: 'price_per_person', width: 120 },
  { title: 'Статус', key: 'status', width: 140 },
  { title: 'Действия', key: 'actions', sortable: false, width: 150 },
]

const defaultForm = () => ({
  offer_name: '', counterparty_id: null, offer_type: '',
  start_date: '', end_date: '', pax_min: 1, pax_max: 20,
  price_per_person: 0, status: 'new', notes: '',
})

const offerForm = ref(defaultForm())

function openAddDialog() {
  editingItem.value = null
  offerForm.value = defaultForm()
  dialog.value = true
}

function openEditDialog(item) {
  editingItem.value = item
  offerForm.value = { ...defaultForm(), ...item }
  dialog.value = true
}

function confirmDelete(item) {
  deletingItem.value = item
  deleteDialog.value = true
}

async function fetchOffers() {
  loading.value = true
  try {
    const params = {
      page: page.value, per_page: perPage.value,
      status: activeStatus.value || undefined,
    }
    const res = await api.get('/offers', { params })
    const d = res.data
    offers.value = d.data || d.offers || []
    total.value = d.total || d.meta?.total || offers.value.length
  } catch {
    uiStore.showSnackbar('Ошибка загрузки предложений', 'error')
  } finally {
    loading.value = false
  }
}

async function loadCounterparties() {
  try {
    const res = await api.get('/counterparties?per_page=200')
    counterparties.value = res.data.data || []
  } catch { /* silent */ }
}

async function saveItem() {
  const { valid } = await formRef.value.validate()
  if (!valid) return
  dialogSaving.value = true
  try {
    if (editingItem.value) {
      await api.put(`/offers/${editingItem.value.id}`, offerForm.value)
      uiStore.showSnackbar('Предложение обновлено', 'success')
    } else {
      await api.post('/offers', offerForm.value)
      uiStore.showSnackbar('Предложение добавлено', 'success')
    }
    dialog.value = false
    fetchOffers()
  } catch {
    uiStore.showSnackbar('Ошибка сохранения', 'error')
  } finally {
    dialogSaving.value = false
  }
}

async function deleteItem() {
  deleting.value = true
  try {
    await api.delete(`/offers/${deletingItem.value.id}`)
    uiStore.showSnackbar('Предложение удалено', 'success')
    deleteDialog.value = false
    fetchOffers()
  } catch {
    uiStore.showSnackbar('Ошибка удаления', 'error')
  } finally {
    deleting.value = false
  }
}

async function acceptOffer(item) {
  try {
    await api.put(`/offers/${item.id}/accept`)
    uiStore.showSnackbar('Предложение принято', 'success')
    fetchOffers()
  } catch {
    uiStore.showSnackbar('Ошибка', 'error')
  }
}

async function rejectOffer(item) {
  try {
    await api.put(`/offers/${item.id}/reject`)
    uiStore.showSnackbar('Предложение отклонено', 'warning')
    fetchOffers()
  } catch {
    uiStore.showSnackbar('Ошибка', 'error')
  }
}

async function convertToTour(item) {
  try {
    const res = await api.post(`/offers/${item.id}/convert`)
    const tourId = res.data.data?.id || res.data.id
    uiStore.showSnackbar('Предложение конвертировано в тур!', 'success')
    if (tourId) router.push(`/tours/${tourId}`)
    fetchOffers()
  } catch {
    uiStore.showSnackbar('Ошибка конвертации', 'error')
  }
}

onMounted(() => {
  fetchOffers()
  loadCounterparties()
})
</script>

<style scoped>
.border { border: 1px solid #E0E0E0 !important; }
.gap-1 { gap: 4px; }
</style>
