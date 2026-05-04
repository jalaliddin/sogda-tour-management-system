<template>
  <v-container fluid class="pa-6">
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-5">
      <div>
        <h1 class="text-h5 font-weight-bold text-primary">Отели</h1>
        <p class="text-body-2 text-medium-emphasis mt-1">Управление базой отелей</p>
      </div>
      <v-btn color="primary" rounded="lg" @click="openAddDialog">
        <v-icon start>mdi-plus</v-icon>
        Добавить отель
      </v-btn>
    </div>

    <!-- Filters -->
    <v-card rounded="xl" elevation="0" class="border mb-4">
      <v-card-text class="pa-4">
        <div class="d-flex flex-wrap gap-2 mb-3">
          <v-chip
            v-for="city in ['Все', ...cities]"
            :key="city"
            :color="activeCity === (city === 'Все' ? '' : city) ? 'primary' : 'default'"
            :variant="activeCity === (city === 'Все' ? '' : city) ? 'flat' : 'outlined'"
            size="small"
            clickable
            @click="activeCity = city === 'Все' ? '' : city; fetchHotels()"
          >
            {{ city }}
          </v-chip>
        </div>
        <v-row dense>
          <v-col cols="12" sm="4">
            <v-text-field
              v-model="search"
              placeholder="Название отеля..."
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
        :items="hotels"
        :loading="loading"
        hide-default-footer
        class="rounded-xl"
      >
        <template #item.stars="{ item }">
          <v-rating :model-value="item.stars || 0" readonly density="compact" size="14" color="amber" />
        </template>
        <template #item.is_own="{ item }">
          <v-chip :color="item.is_own ? 'teal' : 'blue'" size="x-small" label>
            {{ item.is_own ? 'Собственный' : 'Партнёр' }}
          </v-chip>
        </template>
        <template #item.contact="{ item }">
          <div>
            <div v-if="item.contact_person" class="text-body-2">{{ item.contact_person }}</div>
            <div v-if="item.phone" class="text-caption text-medium-emphasis">{{ item.phone }}</div>
          </div>
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
            <v-icon size="48" class="mb-2">mdi-hotel</v-icon>
            <p>Отели не найдены</p>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- Add/Edit Dialog -->
    <v-dialog v-model="dialog" max-width="680" persistent>
      <v-card rounded="xl">
        <v-card-title class="pa-5 pb-2">
          {{ editingHotel ? 'Редактировать отель' : 'Добавить отель' }}
        </v-card-title>
        <v-divider />
        <v-card-text class="pa-5">
          <v-form ref="formRef">
            <v-row dense>
              <v-col cols="12" md="6">
                <v-text-field v-model="hotelForm.name" label="Название отеля *" variant="outlined" density="compact" :rules="[req]" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="hotelForm.city" label="Город *" variant="outlined" density="compact" :rules="[req]" />
              </v-col>
              <v-col cols="12" md="4">
                <v-select
                  v-model="hotelForm.stars"
                  :items="[1,2,3,4,5]"
                  label="Звёзды"
                  variant="outlined"
                  density="compact"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field v-model="hotelForm.category" label="Категория" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="4">
                <v-switch v-model="hotelForm.is_own" label="Собственный отель" color="teal" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="hotelForm.contact_person" label="Контактное лицо" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="hotelForm.phone" label="Телефон" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="hotelForm.email" label="Email" type="email" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="hotelForm.address" label="Адрес" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12">
                <v-textarea v-model="hotelForm.notes" label="Примечание" variant="outlined" density="compact" rows="2" />
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions class="pa-5 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="dialog = false">Отмена</v-btn>
          <v-btn color="primary" variant="flat" :loading="dialogSaving" @click="saveHotel">
            {{ editingHotel ? 'Сохранить' : 'Добавить' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Dialog -->
    <v-dialog v-model="deleteDialog" max-width="420">
      <v-card rounded="xl">
        <v-card-title class="pa-5 pb-2">
          <v-icon color="error" class="mr-2">mdi-alert-circle</v-icon>
          Удалить отель
        </v-card-title>
        <v-card-text class="pa-5 pt-2">
          Подтвердите удаление отеля <strong>{{ deletingHotel?.name }}</strong>?
        </v-card-text>
        <v-card-actions class="pa-5 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="deleteDialog = false">Отмена</v-btn>
          <v-btn color="error" variant="flat" :loading="deleting" @click="deleteHotel">Удалить</v-btn>
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
const hotels = ref([])
const search = ref('')
const activeCity = ref('')
const cities = ref(['Tashkent', 'Samarkand', 'Bukhara', 'Khiva', 'Namangan', 'Andijan'])

const dialog = ref(false)
const dialogSaving = ref(false)
const editingHotel = ref(null)
const formRef = ref(null)

const deleteDialog = ref(false)
const deleting = ref(false)
const deletingHotel = ref(null)

let debounceTimer = null

const req = v => !!v || 'Поле обязательно'

const headers = [
  { title: 'Название', key: 'name' },
  { title: 'Город', key: 'city', width: 120 },
  { title: 'Звёзды', key: 'stars', width: 130 },
  { title: 'Категория', key: 'category', width: 120 },
  { title: 'Тип', key: 'is_own', width: 130 },
  { title: 'Контакт', key: 'contact', width: 180 },
  { title: 'Действия', key: 'actions', sortable: false, width: 90 },
]

const defaultForm = () => ({
  name: '', city: '', stars: 3, category: '', is_own: false,
  contact_person: '', phone: '', email: '', address: '', notes: '',
})

const hotelForm = ref(defaultForm())

function openAddDialog() {
  editingHotel.value = null
  hotelForm.value = defaultForm()
  dialog.value = true
}

function openEditDialog(hotel) {
  editingHotel.value = hotel
  hotelForm.value = { ...defaultForm(), ...hotel }
  dialog.value = true
}

function confirmDelete(hotel) {
  deletingHotel.value = hotel
  deleteDialog.value = true
}

function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetchHotels, 400)
}

async function fetchHotels() {
  loading.value = true
  try {
    const params = {
      search: search.value || undefined,
      city: activeCity.value || undefined,
    }
    const res = await api.get('/hotels', { params })
    hotels.value = res.data.data || res.data.hotels || res.data || []
  } catch {
    uiStore.showSnackbar('Ошибка загрузки отелей', 'error')
  } finally {
    loading.value = false
  }
}

async function saveHotel() {
  const { valid } = await formRef.value.validate()
  if (!valid) return
  dialogSaving.value = true
  try {
    if (editingHotel.value) {
      await api.put(`/hotels/${editingHotel.value.id}`, hotelForm.value)
      uiStore.showSnackbar('Отель обновлён', 'success')
    } else {
      await api.post('/hotels', hotelForm.value)
      uiStore.showSnackbar('Отель добавлен', 'success')
    }
    dialog.value = false
    fetchHotels()
  } catch {
    uiStore.showSnackbar('Ошибка сохранения', 'error')
  } finally {
    dialogSaving.value = false
  }
}

async function deleteHotel() {
  deleting.value = true
  try {
    await api.delete(`/hotels/${deletingHotel.value.id}`)
    uiStore.showSnackbar('Отель удалён', 'success')
    deleteDialog.value = false
    fetchHotels()
  } catch {
    uiStore.showSnackbar('Ошибка удаления', 'error')
  } finally {
    deleting.value = false
  }
}

onMounted(fetchHotels)
</script>

<style scoped>
.border { border: 1px solid #E0E0E0 !important; }
.gap-1 { gap: 4px; }
.gap-2 { gap: 8px; }
</style>
