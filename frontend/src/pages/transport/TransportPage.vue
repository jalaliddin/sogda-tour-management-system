<template>
  <v-container fluid class="pa-6">
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-5">
      <div>
        <h1 class="text-h5 font-weight-bold text-primary">Транспорт</h1>
        <p class="text-body-2 text-medium-emphasis mt-1">Управление транспортными средствами</p>
      </div>
      <v-btn color="primary" rounded="lg" @click="openAddDialog">
        <v-icon start>mdi-plus</v-icon>
        Добавить транспорт
      </v-btn>
    </div>

    <!-- Filters -->
    <v-card rounded="xl" elevation="0" class="border mb-4">
      <v-card-text class="pa-4">
        <div class="d-flex flex-wrap gap-2 mb-3">
          <v-chip
            v-for="f in statusFilters"
            :key="f.value"
            :color="activeStatus === f.value ? 'primary' : 'default'"
            :variant="activeStatus === f.value ? 'flat' : 'outlined'"
            size="small"
            clickable
            @click="activeStatus = f.value; fetchTransports()"
          >
            {{ f.label }}
          </v-chip>
        </div>
        <v-row dense>
          <v-col cols="12" sm="4">
            <v-text-field
              v-model="search"
              placeholder="Марка, модель, гос. номер..."
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="compact"
              hide-details
              clearable
              @update:model-value="debouncedFetch"
            />
          </v-col>
          <v-col cols="12" sm="3">
            <v-select
              v-model="typeFilter"
              :items="transportTypes"
              label="Тип транспорта"
              variant="outlined"
              density="compact"
              hide-details
              clearable
              @update:model-value="fetchTransports"
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
        :items="transports"
        :loading="loading"
        hide-default-footer
        class="rounded-xl"
      >
        <template #item.type="{ item }">
          <div class="d-flex align-center gap-2">
            <v-icon :color="typeIconColor(item.type)" size="18">{{ typeIcon(item.type) }}</v-icon>
            <span>{{ typeLabel(item.type) }}</span>
          </div>
        </template>

        <template #item.brand_model="{ item }">
          <div>
            <div class="font-weight-medium">{{ item.brand }} {{ item.model }}</div>
            <div v-if="item.year" class="text-caption text-medium-emphasis">{{ item.year }} г.</div>
          </div>
        </template>

        <template #item.driver="{ item }">
          <div v-if="item.driver_name">
            <div class="text-body-2">{{ item.driver_name }}</div>
            <div v-if="item.driver_phone" class="text-caption text-medium-emphasis">{{ item.driver_phone }}</div>
          </div>
          <span v-else class="text-medium-emphasis">—</span>
        </template>

        <template #item.status="{ item }">
          <v-chip :color="statusColor(item.status)" size="x-small" label>
            {{ statusLabel(item.status) }}
          </v-chip>
        </template>

        <template #item.is_own="{ item }">
          <v-chip :color="item.is_own ? 'teal' : 'blue'" size="x-small" label>
            {{ item.is_own ? 'Собственный' : 'Аренда' }}
          </v-chip>
        </template>

        <template #item.actions="{ item }">
          <div class="d-flex gap-1">
            <v-tooltip text="Редактировать">
              <template #activator="{ props }">
                <v-btn v-bind="props" icon size="x-small" variant="text" color="primary" @click="openEditDialog(item)">
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
            <v-icon size="48" class="mb-2">mdi-bus</v-icon>
            <p>Транспортные средства не найдены</p>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- Add/Edit Dialog -->
    <v-dialog v-model="dialog" max-width="680" persistent>
      <v-card rounded="xl">
        <v-card-title class="pa-5 pb-2">
          {{ editingItem ? 'Редактировать транспорт' : 'Добавить транспорт' }}
        </v-card-title>
        <v-divider />
        <v-card-text class="pa-5">
          <v-form ref="formRef">
            <v-row dense>
              <v-col cols="12" md="4">
                <v-select v-model="tForm.type" :items="transportTypes" label="Тип *" variant="outlined" density="compact" :rules="[req]" />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field v-model="tForm.brand" label="Марка *" variant="outlined" density="compact" :rules="[req]" />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field v-model="tForm.model" label="Модель" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field v-model="tForm.plate_number" label="Гос. номер" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field v-model.number="tForm.capacity" label="Вместимость (чел.)" type="number" variant="outlined" density="compact" min="1" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="tForm.driver_name" label="Имя водителя" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="tForm.driver_phone" label="Телефон водителя" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="4">
                <v-select
                  v-model="tForm.status"
                  :items="statusOptions"
                  item-title="label"
                  item-value="value"
                  label="Статус"
                  variant="outlined"
                  density="compact"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-switch v-model="tForm.is_own" label="Собственный" color="teal" density="compact" hide-details />
              </v-col>
              <v-col cols="12">
                <v-textarea v-model="tForm.notes" label="Примечание" variant="outlined" density="compact" rows="2" />
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
          Удалить транспорт <strong>{{ deletingItem?.brand }} {{ deletingItem?.model }}</strong>?
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
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'

const uiStore = useUiStore()

const loading = ref(false)
const transports = ref([])
const search = ref('')
const activeStatus = ref('')
const typeFilter = ref('')

const dialog = ref(false)
const dialogSaving = ref(false)
const editingItem = ref(null)
const formRef = ref(null)

const deleteDialog = ref(false)
const deleting = ref(false)
const deletingItem = ref(null)

let debounceTimer = null
const req = v => !!v || 'Поле обязательно'

const transportTypes = [
  { title: 'Автобус', value: 'bus' },
  { title: 'Микроавтобус', value: 'minibus' },
  { title: 'Легковой', value: 'car' },
  { title: 'Поезд', value: 'train' },
  { title: 'Внутренний рейс', value: 'internal_flight' },
  { title: 'Трансфер', value: 'transfer' },
]

const statusFilters = [
  { label: 'Все', value: '' },
  { label: 'Свободен', value: 'available' },
  { label: 'В работе', value: 'in_use' },
  { label: 'Ремонт', value: 'maintenance' },
]

const statusOptions = [
  { label: 'Свободен', value: 'available' },
  { label: 'В работе', value: 'in_use' },
  { label: 'Ремонт', value: 'maintenance' },
]

const statusColorMap = {
  available: 'success', in_use: 'orange', maintenance: 'blue',
}
const statusLabelMap = {
  available: 'Свободен', in_use: 'В работе', maintenance: 'Ремонт',
}

function statusColor(s) { return statusColorMap[s] || 'grey' }
function statusLabel(s) { return statusLabelMap[s] || s }

function typeIcon(t) {
  const icons = {
    bus: 'mdi-bus', minibus: 'mdi-bus', car: 'mdi-car',
    train: 'mdi-train', internal_flight: 'mdi-airplane', transfer: 'mdi-car-arrow-right',
  }
  return icons[t] || 'mdi-car'
}

function typeIconColor(t) {
  const colors = {
    bus: 'blue', minibus: 'indigo', car: 'green',
    train: 'red', internal_flight: 'teal', transfer: 'orange',
  }
  return colors[t] || 'grey'
}

const typeLabelMap = {
  bus: 'Автобус', minibus: 'Микроавтобус', car: 'Легковой',
  train: 'Поезд', internal_flight: 'Внутренний рейс', transfer: 'Трансфер',
}
function typeLabel(t) { return typeLabelMap[t] || t }

const headers = [
  { title: 'Тип', key: 'type', width: 140 },
  { title: 'Марка / Модель', key: 'brand_model' },
  { title: 'Гос. номер', key: 'plate_number', width: 130 },
  { title: 'Вместимость', key: 'capacity', width: 110 },
  { title: 'Водитель', key: 'driver', width: 180 },
  { title: 'Статус', key: 'status', width: 130 },
  { title: 'Транспорт', key: 'is_own', width: 130 },
  { title: 'Действия', key: 'actions', sortable: false, width: 90 },
]

const defaultForm = () => ({
  type: 'bus', brand: '', model: '', plate_number: '',
  capacity: 20,
  driver_name: '', driver_phone: '', status: 'available',
  is_own: true, notes: '',
})

const tForm = ref(defaultForm())

function openAddDialog() {
  editingItem.value = null
  tForm.value = defaultForm()
  dialog.value = true
}

function openEditDialog(item) {
  editingItem.value = item
  tForm.value = { ...defaultForm(), ...item }
  dialog.value = true
}

function confirmDelete(item) {
  deletingItem.value = item
  deleteDialog.value = true
}

function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetchTransports, 400)
}

async function fetchTransports() {
  loading.value = true
  try {
    const params = {
      search: search.value || undefined,
      status: activeStatus.value || undefined,
      type: typeFilter.value || undefined,
    }
    const res = await api.get('/transports', { params })
    transports.value = res.data.data || res.data.transports || res.data || []
  } catch {
    uiStore.showSnackbar('Ошибка загрузки транспорта', 'error')
  } finally {
    loading.value = false
  }
}

async function saveItem() {
  const { valid } = await formRef.value.validate()
  if (!valid) return
  dialogSaving.value = true
  try {
    if (editingItem.value) {
      await api.put(`/transports/${editingItem.value.id}`, tForm.value)
      uiStore.showSnackbar('Транспорт обновлён', 'success')
    } else {
      await api.post('/transports', tForm.value)
      uiStore.showSnackbar('Транспорт добавлен', 'success')
    }
    dialog.value = false
    fetchTransports()
  } catch {
    uiStore.showSnackbar('Ошибка сохранения', 'error')
  } finally {
    dialogSaving.value = false
  }
}

async function deleteItem() {
  deleting.value = true
  try {
    await api.delete(`/transports/${deletingItem.value.id}`)
    uiStore.showSnackbar('Транспорт удалён', 'success')
    deleteDialog.value = false
    fetchTransports()
  } catch {
    uiStore.showSnackbar('Ошибка удаления', 'error')
  } finally {
    deleting.value = false
  }
}

onMounted(fetchTransports)
</script>

<style scoped>
.border { border: 1px solid #E0E0E0 !important; }
.gap-1 { gap: 4px; }
.gap-2 { gap: 8px; }
</style>
