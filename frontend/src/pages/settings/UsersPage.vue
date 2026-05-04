<template>
  <v-container fluid class="pa-6">
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-5">
      <div>
        <h1 class="text-h5 font-weight-bold text-primary">Пользователи</h1>
        <p class="text-body-2 text-medium-emphasis mt-1">Управление пользователями системы</p>
      </div>
      <v-btn color="primary" rounded="lg" @click="openAddDialog">
        <v-icon start>mdi-plus</v-icon>
        Добавить пользователя
      </v-btn>
    </div>

    <!-- Table -->
    <v-card rounded="xl" elevation="0" class="border">
      <v-progress-linear v-if="loading" indeterminate color="primary" />

      <div class="pa-4">
        <v-text-field
          v-model="search"
          placeholder="Поиск (имя, email)..."
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="compact"
          hide-details
          clearable
          style="max-width: 320px"
          @update:model-value="debouncedFetch"
        />
      </div>

      <v-data-table
        :headers="headers"
        :items="users"
        :loading="loading"
        hide-default-footer
        class="rounded-xl"
      >
        <template #item.roles="{ item }">
          <div class="d-flex flex-wrap gap-1">
            <v-chip
              v-for="role in (item.roles || [])"
              :key="role"
              :color="roleColor(role)"
              size="x-small"
              label
            >
              {{ roleLabel(role) }}
            </v-chip>
          </div>
        </template>

        <template #item.is_active="{ item }">
          <v-chip :color="item.is_active ? 'success' : 'grey'" size="x-small" label>
            {{ item.is_active ? 'Активен' : 'Неактивен' }}
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
                  :color="item.is_active ? 'error' : 'success'"
                  @click="toggleStatus(item)"
                >
                  <v-icon size="16">{{ item.is_active ? 'mdi-account-off' : 'mdi-account-check' }}</v-icon>
                </v-btn>
              </template>
            </v-tooltip>
          </div>
        </template>

        <template #no-data>
          <div class="text-center py-10 text-medium-emphasis">
            <v-icon size="48" class="mb-2">mdi-account-group</v-icon>
            <p>Пользователи не найдены</p>
          </div>
        </template>
      </v-data-table>

      <v-divider />
      <div class="d-flex align-center justify-space-between pa-3">
        <div class="text-body-2 text-medium-emphasis">Всего: {{ total }} пользователей</div>
        <v-pagination v-model="page" :length="totalPages" :total-visible="5" density="compact" @update:model-value="fetchUsers" />
      </div>
    </v-card>

    <!-- Add/Edit Dialog -->
    <v-dialog v-model="dialog" max-width="620" persistent>
      <v-card rounded="xl">
        <v-card-title class="pa-5 pb-2">
          {{ editingUser ? 'Редактировать пользователя' : 'Добавить пользователя' }}
        </v-card-title>
        <v-divider />
        <v-card-text class="pa-5">
          <v-form ref="formRef">
            <v-row dense>
              <v-col cols="12" md="6">
                <v-text-field v-model="userForm.name" label="Полное имя *" variant="outlined" density="compact" :rules="[req]" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="userForm.email" label="Email *" type="email" variant="outlined" density="compact" :rules="[req, emailRule]" />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="userForm.password"
                  label="Пароль *"
                  type="password"
                  variant="outlined"
                  density="compact"
                  :rules="editingUser ? [] : [req, minLength6]"
                  :hint="editingUser ? 'Оставьте пустым чтобы не менять пароль' : ''"
                  persistent-hint
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="userForm.roles"
                  :items="availableRoles"
                  item-title="label"
                  item-value="name"
                  label="Роли *"
                  variant="outlined"
                  density="compact"
                  multiple
                  chips
                  closable-chips
                  :rules="[v => v?.length > 0 || 'Выберите минимум 1 роль']"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="userForm.department" label="Отдел" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="userForm.branch_id"
                  :items="branches"
                  item-title="name"
                  item-value="id"
                  label="Филиал"
                  variant="outlined"
                  density="compact"
                  clearable
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-text-field v-model="userForm.phone" label="Телефон" variant="outlined" density="compact" />
              </v-col>
              <v-col cols="12" md="6">
                <v-switch v-model="userForm.is_active" label="Активен" color="success" density="compact" hide-details />
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-card-actions class="pa-5 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="dialog = false">Отмена</v-btn>
          <v-btn color="primary" variant="flat" :loading="dialogSaving" @click="saveUser">
            {{ editingUser ? 'Сохранить' : 'Добавить' }}
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
const users = ref([])
const total = ref(0)
const page = ref(1)
const perPage = ref(20)
const search = ref('')

const dialog = ref(false)
const dialogSaving = ref(false)
const editingUser = ref(null)
const formRef = ref(null)

const availableRoles = ref([])
const branches = ref([])

let debounceTimer = null

const totalPages = computed(() => Math.ceil(total.value / perPage.value) || 1)
const req = v => !!v || 'Поле обязательно'
const emailRule = v => /.+@.+\..+/.test(v) || 'Неверный email'
const minLength6 = v => v?.length >= 6 || 'Минимум 6 символов'

const roleColorMap = {
  super_admin: 'red', manager: 'blue', accountant: 'green',
  guide: 'teal', driver: 'orange', hotel_khiva: 'purple',
  hotel_samarkand: 'purple', hotel_bukhara: 'purple', transport_manager: 'indigo',
}
const roleLabelMap = {
  super_admin: 'Супер Админ', manager: 'Менеджер', accountant: 'Бухгалтер',
  guide: 'Гид', driver: 'Водитель', hotel_khiva: 'Отель (Хива)',
  hotel_samarkand: 'Отель (Самарканд)', hotel_bukhara: 'Отель (Бухара)',
  transport_manager: 'Менеджер транспорта',
}

function roleColor(r) { return roleColorMap[r] || 'grey' }
function roleLabel(r) { return roleLabelMap[r] || r }

const headers = [
  { title: 'Имя', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Роли', key: 'roles' },
  { title: 'Отдел', key: 'department', width: 130 },
  { title: 'Филиал', key: 'branch.name', width: 120 },
  { title: 'Статус', key: 'is_active', width: 90 },
  { title: 'Действия', key: 'actions', sortable: false, width: 90 },
]

const defaultForm = () => ({
  name: '', email: '', password: '', roles: [],
  department: '', branch_id: null, phone: '', is_active: true,
})

const userForm = ref(defaultForm())

function openAddDialog() {
  editingUser.value = null
  userForm.value = defaultForm()
  dialog.value = true
}

function openEditDialog(user) {
  editingUser.value = user
  userForm.value = {
    ...defaultForm(),
    ...user,
    password: '',
    roles: user.roles || [],
    branch_id: user.branch?.id || null,
  }
  dialog.value = true
}

function debouncedFetch() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(fetchUsers, 400)
}

async function fetchUsers() {
  loading.value = true
  try {
    const params = { page: page.value, per_page: perPage.value, search: search.value || undefined }
    const res = await api.get('/users', { params })
    const d = res.data
    users.value = d.data || d.users || []
    total.value = d.total || d.meta?.total || users.value.length
  } catch {
    uiStore.showSnackbar('Ошибка загрузки пользователей', 'error')
  } finally {
    loading.value = false
  }
}

async function loadRolesAndBranches() {
  try {
    const [rolesRes, branchesRes] = await Promise.all([
      api.get('/users/roles'),
      api.get('/branches'),
    ])
    const rawRoles = rolesRes.data.data || rolesRes.data || []
    availableRoles.value = Array.isArray(rawRoles)
      ? rawRoles.map(r => typeof r === 'string' ? { name: r, label: roleLabelMap[r] || r } : r)
      : []
    branches.value = branchesRes.data.data || branchesRes.data || []
  } catch { /* silent */ }
}

async function saveUser() {
  const { valid } = await formRef.value.validate()
  if (!valid) return
  dialogSaving.value = true
  try {
    const payload = { ...userForm.value }
    if (editingUser.value && !payload.password) delete payload.password
    if (editingUser.value) {
      await api.put(`/users/${editingUser.value.id}`, payload)
      uiStore.showSnackbar('Пользователь обновлён', 'success')
    } else {
      await api.post('/users', payload)
      uiStore.showSnackbar('Пользователь добавлен', 'success')
    }
    dialog.value = false
    fetchUsers()
  } catch (error) {
    const msg = error.response?.data?.message || 'Ошибка сохранения'
    uiStore.showSnackbar(msg, 'error')
  } finally {
    dialogSaving.value = false
  }
}

async function toggleStatus(user) {
  try {
    await api.put(`/users/${user.id}`, { is_active: !user.is_active })
    uiStore.showSnackbar(
      user.is_active ? 'Пользователь деактивирован' : 'Пользователь активирован',
      'success'
    )
    fetchUsers()
  } catch {
    uiStore.showSnackbar('Ошибка', 'error')
  }
}

onMounted(() => {
  fetchUsers()
  loadRolesAndBranches()
})
</script>

<style scoped>
.border { border: 1px solid #E0E0E0 !important; }
.gap-1 { gap: 4px; }
</style>
