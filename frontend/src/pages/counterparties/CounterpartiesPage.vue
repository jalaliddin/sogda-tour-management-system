<template>
  <v-container fluid class="pa-6">
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-5">
      <div>
        <h1 class="text-h5 font-weight-bold text-primary">Контрагенты</h1>
        <p class="text-body-2 text-medium-emphasis mt-1">Управление партнёрами и контрагентами</p>
      </div>
      <v-btn color="primary" rounded="lg" @click="openAddDialog">
        <v-icon start>mdi-plus</v-icon>
        Добавить контрагента
      </v-btn>
    </div>

    <!-- Filters -->
    <v-card rounded="xl" elevation="0" class="border mb-4">
      <v-card-text class="pa-4">
        <div class="d-flex flex-wrap gap-2 mb-3">
          <v-chip
            v-for="t in typeFilters"
            :key="t.value"
            :color="activeType === t.value ? 'primary' : 'default'"
            :variant="activeType === t.value ? 'flat' : 'outlined'"
            size="small"
            clickable
            @click="activeType = t.value; fetchCounterparties()"
          >
            {{ t.label }}
          </v-chip>
        </div>
        <v-row dense>
          <v-col cols="12" sm="4">
            <v-text-field
              v-model="search"
              placeholder="Поиск..."
              prepend-inner-icon="mdi-magnify"
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
        :items="counterparties"
        :loading="loading"
        hide-default-footer
        class="rounded-xl"
      >
        <template #item.type="{ item }">
          <v-chip :color="typeColor(item.type)" size="x-small" label>
            {{ typeLabel(item.type) }}
          </v-chip>
        </template>
        <template #item.rating="{ item }">
          <v-rating :model-value="item.rating || 0" readonly density="compact" size="14" color="amber" half-increments />
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
            <v-icon size="48" class="mb-2">mdi-domain</v-icon>
            <p>Контрагенты не найдены</p>
          </div>
        </template>
      </v-data-table>

      <v-divider />
      <div class="d-flex align-center justify-space-between pa-3">
        <div class="text-body-2 text-medium-emphasis">Всего: {{ total }}</div>
        <v-pagination v-model="page" :length="totalPages" :total-visible="5" density="compact" @update:model-value="fetchCounterparties" />
      </div>
    </v-card>

    <!-- Add/Edit Dialog -->
    <v-dialog v-model="dialog" max-width="740" persistent>
      <v-card rounded="xl">
        <v-card-title class="pa-5 pb-2">
          {{ editingItem ? 'Редактировать контрагента' : 'Добавить контрагента' }}
        </v-card-title>
        <v-divider />
        <v-card-text class="pa-5">
          <v-form ref="formRef">
            <v-row dense>
              <v-col cols="12" md="6">
                <v-text-field v-model="cpForm.company_name" label="Название компании *" variant="outlined" density="compact" :rules="[req]" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select v-model="cpForm.type" :items="typeOptions" item-title="label" item-value="value" label="Тип *" variant="outlined" density="compact" :rules="[req]" />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field v-model="cpForm.country" label="Страна" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field v-model="cpForm.city" label="Город" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field v-model="cpForm.contact_person" label="Контактное лицо" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field v-model="cpForm.phone" label="Телефон" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field v-model="cpForm.email" label="Email" type="email" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field v-model="cpForm.website" label="Сайт" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field v-model="cpForm.contract_number" label="Номер контракта" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field v-model.number="cpForm.commission_percent" label="Комиссия (%)" type="number" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="4">
                <v-select v-model="cpForm.currency" :items="['USD', 'EUR', 'UZS', 'RUB']" label="Валюта" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="6">
                <div class="text-body-2 mb-1">Рейтинг:</div>
                <v-rating v-model="cpForm.rating" density="compact" color="amber" size="24" />
              </v-col>
              <v-col cols="12">
                <v-textarea v-model="cpForm.notes" label="Примечание" variant="outlined" density="compact" rows="2" />
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
          Удалить <strong>{{ deletingItem?.company_name }}</strong>?
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
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'

const uiStore = useUiStore()

const loading = ref(false)
const counterparties = ref([])
const total = ref(0)
const page = ref(1)
const perPage = ref(20)
const search = ref('')
const activeType = ref('')

const dialog = ref(false)
const dialogSaving = ref(false)
const editingItem = ref(null)
const formRef = ref(null)

const deleteDialog = ref(false)
const deleting = ref(false)
const deletingItem = ref(null)

let debounceTimer = null

const req = v => !!v || 'Поле обязательно'
const totalPages = computed(() => Math.ceil(total.value / perPage.value) || 1)

const typeFilters = [
  { label: 'Все', value: '' },
  { label: 'Международный тур', value: 'international_tour' },
  { label: 'Местный тур', value: 'local_tour' },
  { label: 'Отель', value: 'hotel' },
  { label: 'Ресторан', value: 'restaurant' },
  { label: 'Гид', value: 'guide' },
  { label: 'Транспорт', value: 'transport' },
]

const typeOptions = typeFilters.filter(t => t.value)

const typeColorMap = {
  international_tour: 'blue', local_tour: 'teal', hotel: 'purple',
  restaurant: 'orange', guide: 'green', transport: 'indigo',
}
const typeLabelMap = {
  international_tour: 'Международный тур', local_tour: 'Местный тур', hotel: 'Отель',
  restaurant: 'Ресторан', guide: 'Гид', transport: 'Транспорт',
}

function typeColor(t) { return typeColorMap[t] || 'grey' }
function typeLabel(t) { return typeLabelMap[t] || t }

const headers = [
  { title: 'Компания', key: 'company_name' },
  { title: 'Тип', key: 'type', width: 150 },
  { title: 'Страна', key: 'country', width: 100 },
  { title: 'Город', key: 'city', width: 100 },
  { title: 'Контактное лицо', key: 'contact_person', width: 150 },
  { title: 'Телефон', key: 'phone', width: 130 },
  { title: 'Рейтинг', key: 'rating', width: 130 },
  { title: 'Действия', key: 'actions', sortable: false, width: 90 },
]

const defaultForm = () => ({
  company_name: '', type: '', country: '', city: '',
  contact_person: '', phone: '', email: '', website: '',
  contract_number: '', commission_percent: 0, currency: 'USD',
  rating: 0, notes: '',
})

const cpForm = ref(defaultForm())

function openAddDialog() {
  editingItem.value = null
  cpForm.value = defaultForm()
  dialog.value = true
}

function openEditDialog(item) {
  editingItem.value = item
  cpForm.value = { ...defaultForm(), ...item }
  dialog.value = true
}

function confirmDelete(item) {
  deletingItem.value = item
  deleteDialog.value = true
}

function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetchCounterparties, 400)
}

async function fetchCounterparties() {
  loading.value = true
  try {
    const params = {
      page: page.value, per_page: perPage.value,
      search: search.value || undefined,
      type: activeType.value || undefined,
    }
    const res = await api.get('/counterparties', { params })
    const d = res.data
    counterparties.value = d.data || d.counterparties || []
    total.value = d.total || d.meta?.total || counterparties.value.length
  } catch {
    uiStore.showSnackbar('Ошибка загрузки контрагентов', 'error')
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
      await api.put(`/counterparties/${editingItem.value.id}`, cpForm.value)
      uiStore.showSnackbar('Контрагент обновлён', 'success')
    } else {
      await api.post('/counterparties', cpForm.value)
      uiStore.showSnackbar('Контрагент добавлен', 'success')
    }
    dialog.value = false
    fetchCounterparties()
  } catch {
    uiStore.showSnackbar('Ошибка сохранения', 'error')
  } finally {
    dialogSaving.value = false
  }
}

async function deleteItem() {
  deleting.value = true
  try {
    await api.delete(`/counterparties/${deletingItem.value.id}`)
    uiStore.showSnackbar('Контрагент удалён', 'success')
    deleteDialog.value = false
    fetchCounterparties()
  } catch {
    uiStore.showSnackbar('Ошибка удаления', 'error')
  } finally {
    deleting.value = false
  }
}

onMounted(fetchCounterparties)
</script>

<style scoped>
.border { border: 1px solid #E0E0E0 !important; }
.gap-1 { gap: 4px; }
.gap-2 { gap: 8px; }
</style>
