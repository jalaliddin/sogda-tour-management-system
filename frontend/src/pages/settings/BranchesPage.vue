<template>
  <v-container fluid class="pa-6">
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-5">
      <div>
        <h1 class="text-h5 font-weight-bold text-primary">Филиалы</h1>
        <p class="text-body-2 text-medium-emphasis mt-1">Управление филиалами компании</p>
      </div>
      <v-btn color="primary" rounded="lg" @click="openAddDialog">
        <v-icon start>mdi-plus</v-icon>
        Добавить филиал
      </v-btn>
    </div>

    <!-- Table -->
    <v-card rounded="xl" elevation="0" class="border">
      <v-progress-linear v-if="loading" indeterminate color="primary" />
      <v-data-table
        :headers="headers"
        :items="branches"
        :loading="loading"
        hide-default-footer
        class="rounded-xl"
      >
        <template #item.type="{ item }">
          <v-chip :color="typeColor(item.type)" size="x-small" label>
            {{ typeLabel(item.type) }}
          </v-chip>
        </template>

        <template #item.is_active="{ item }">
          <v-chip :color="item.is_active ? 'success' : 'grey'" size="x-small" label>
            {{ item.is_active ? 'Активен' : 'Неактивен' }}
          </v-chip>
        </template>

        <template #item.users_count="{ item }">
          <v-chip color="primary" size="x-small" variant="tonal">
            {{ item.users_count || 0 }} сотр.
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
            <v-tooltip :text="item.is_active ? 'Деактивировать' : 'Активировать'">
              <template #activator="{ props }">
                <v-btn
                  v-bind="props"
                  icon size="x-small" variant="text"
                  :color="item.is_active ? 'warning' : 'success'"
                  @click="toggleActive(item)"
                >
                  <v-icon size="16">{{ item.is_active ? 'mdi-pause-circle' : 'mdi-play-circle' }}</v-icon>
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
            <v-icon size="48" class="mb-2">mdi-office-building</v-icon>
            <p>Филиалы не найдены</p>
          </div>
        </template>
      </v-data-table>
    </v-card>

    <!-- Add/Edit Dialog -->
    <v-dialog v-model="dialog" max-width="580" persistent>
      <v-card rounded="xl">
        <v-card-title class="pa-5 pb-2">
          {{ editingBranch ? 'Редактировать филиал' : 'Добавить филиал' }}
        </v-card-title>
        <v-divider />
        <v-card-text class="pa-5">
          <v-form ref="formRef">
            <v-row dense>
              <v-col cols="12" md="6">
                <v-text-field v-model="branchForm.name" label="Название *" variant="outlined" density="compact" :rules="[req]" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="branchForm.city" label="Город *" variant="outlined" density="compact" :rules="[req]" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="branchForm.type"
                  :items="branchTypes"
                  item-title="label"
                  item-value="value"
                  label="Тип *"
                  variant="outlined"
                  density="compact"
                  :rules="[req]"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="branchForm.phone" label="Телефон" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="branchForm.email" label="Email" type="email" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="branchForm.manager_id"
                  :items="managers"
                  item-title="name"
                  item-value="id"
                  label="Менеджер"
                  variant="outlined"
                  density="compact"
                  clearable
                />
              </v-col>
              <v-col cols="12">
                <v-text-field v-model="branchForm.address" label="Адрес" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="6">
                <v-switch v-model="branchForm.is_active" label="Активен" color="success" density="compact" hide-details />
              </v-col>
              <v-col cols="12" md="6">
                <v-switch v-model="branchForm.is_main" label="Главный филиал" color="primary" density="compact" hide-details />
              </v-col>
              <v-col cols="12">
                <v-textarea v-model="branchForm.notes" label="Примечание" variant="outlined" density="compact" rows="2" />
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions class="pa-5 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="dialog = false">Отмена</v-btn>
          <v-btn color="primary" variant="flat" :loading="dialogSaving" @click="saveBranch">
            {{ editingBranch ? 'Сохранить' : 'Добавить' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete Dialog -->
    <v-dialog v-model="deleteDialog" max-width="420">
      <v-card rounded="xl">
        <v-card-title class="pa-5 pb-2">
          <v-icon color="error" class="mr-2">mdi-alert-circle</v-icon>
          Удалить филиал
        </v-card-title>
        <v-card-text class="pa-5 pt-2">
          <p>
            Удалить филиал <strong>{{ deletingBranch?.name }}</strong>? Все сотрудники филиала потеряют привязку.
          </p>
        </v-card-text>
        <v-card-actions class="pa-5 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="deleteDialog = false">Отмена</v-btn>
          <v-btn color="error" variant="flat" :loading="deleting" @click="deleteBranch">Удалить</v-btn>
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
const branches = ref([])
const managers = ref([])

const dialog = ref(false)
const dialogSaving = ref(false)
const editingBranch = ref(null)
const formRef = ref(null)

const deleteDialog = ref(false)
const deleting = ref(false)
const deletingBranch = ref(null)

const req = v => !!v || 'Поле обязательно'

const branchTypes = [
  { label: 'Главный офис', value: 'head_office' },
  { label: 'Региональный офис', value: 'regional_office' },
  { label: 'Отель', value: 'hotel' },
  { label: 'Филиал', value: 'branch' },
]

const typeColorMap = {
  head_office: 'red', regional_office: 'blue',
  hotel: 'purple', branch: 'teal',
}
const typeLabelMap = {
  head_office: 'Главный офис', regional_office: 'Региональный офис',
  hotel: 'Отель', branch: 'Филиал',
}

function typeColor(t) { return typeColorMap[t] || 'grey' }
function typeLabel(t) { return typeLabelMap[t] || t }

const headers = [
  { title: 'Название', key: 'name' },
  { title: 'Город', key: 'city', width: 120 },
  { title: 'Тип', key: 'type', width: 150 },
  { title: 'Менеджер', key: 'manager_name', width: 150 },
  { title: 'Телефон', key: 'phone', width: 140 },
  { title: 'Статус', key: 'is_active', width: 90 },
  { title: 'Сотрудники', key: 'users_count', width: 120 },
  { title: 'Действия', key: 'actions', sortable: false, width: 110 },
]

const defaultForm = () => ({
  name: '', city: '', type: 'branch', phone: '', email: '',
  address: '', manager_id: null, is_active: true, is_main: false, notes: '',
})

const branchForm = ref(defaultForm())

function openAddDialog() {
  editingBranch.value = null
  branchForm.value = defaultForm()
  dialog.value = true
}

function openEditDialog(branch) {
  editingBranch.value = branch
  branchForm.value = {
    ...defaultForm(),
    ...branch,
    manager_id: branch.manager?.id || null,
  }
  dialog.value = true
}

function confirmDelete(branch) {
  deletingBranch.value = branch
  deleteDialog.value = true
}

async function fetchBranches() {
  loading.value = true
  try {
    const res = await api.get('/branches')
    branches.value = res.data.data || res.data.branches || res.data || []
  } catch {
    uiStore.showSnackbar('Ошибка загрузки филиалов', 'error')
  } finally {
    loading.value = false
  }
}

async function loadManagers() {
  try {
    const res = await api.get('/users?per_page=200&role=manager')
    managers.value = res.data.data || []
  } catch { /* silent */ }
}

async function saveBranch() {
  const { valid } = await formRef.value.validate()
  if (!valid) return
  dialogSaving.value = true
  try {
    if (editingBranch.value) {
      await api.put(`/branches/${editingBranch.value.id}`, branchForm.value)
      uiStore.showSnackbar('Филиал обновлён', 'success')
    } else {
      await api.post('/branches', branchForm.value)
      uiStore.showSnackbar('Филиал добавлен', 'success')
    }
    dialog.value = false
    fetchBranches()
  } catch (error) {
    const msg = error.response?.data?.message || 'Ошибка сохранения'
    uiStore.showSnackbar(msg, 'error')
  } finally {
    dialogSaving.value = false
  }
}

async function toggleActive(branch) {
  try {
    await api.put(`/branches/${branch.id}`, { is_active: !branch.is_active })
    uiStore.showSnackbar(
      branch.is_active ? 'Филиал деактивирован' : 'Филиал активирован',
      'success'
    )
    fetchBranches()
  } catch {
    uiStore.showSnackbar('Ошибка', 'error')
  }
}

async function deleteBranch() {
  deleting.value = true
  try {
    await api.delete(`/branches/${deletingBranch.value.id}`)
    uiStore.showSnackbar('Филиал удалён', 'success')
    deleteDialog.value = false
    fetchBranches()
  } catch {
    uiStore.showSnackbar('Ошибка удаления', 'error')
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  fetchBranches()
  loadManagers()
})
</script>

<style scoped>
.border { border: 1px solid #E0E0E0 !important; }
.gap-1 { gap: 4px; }
</style>
