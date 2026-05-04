<template>
  <v-container fluid class="pa-6">
    <!-- Header -->
    <div class="d-flex align-center justify-space-between mb-5">
      <div>
        <h1 class="text-h5 font-weight-bold" style="color:#1A2744;">Destinations</h1>
        <p class="text-body-2 text-medium-emphasis mt-1">
          Manage travel destinations used in tour itineraries
        </p>
      </div>
      <v-btn color="primary" rounded="lg" @click="openAdd">
        <v-icon start>mdi-plus</v-icon>
        Add Destination
      </v-btn>
    </div>

    <!-- Filters -->
    <v-card rounded="xl" elevation="0" class="border mb-4">
      <v-card-text class="pa-4">
        <v-row dense align="center">
          <v-col cols="12" md="4">
            <v-text-field
              v-model="search"
              placeholder="Search destinations..."
              prepend-inner-icon="mdi-magnify"
              variant="outlined"
              density="compact"
              hide-details
              clearable
              @update:model-value="fetchItems"
            />
          </v-col>
          <v-col cols="12" md="3">
            <v-select
              v-model="filterCountry"
              :items="countryOptions"
              placeholder="Filter by country"
              variant="outlined"
              density="compact"
              hide-details
              clearable
              @update:model-value="fetchItems"
            />
          </v-col>
          <v-col cols="12" md="3">
            <v-select
              v-model="filterType"
              :items="typeOptions"
              placeholder="Filter by type"
              variant="outlined"
              density="compact"
              hide-details
              clearable
              @update:model-value="fetchItems"
            />
          </v-col>
          <v-col cols="12" md="2" class="text-right">
            <span class="text-caption text-medium-emphasis">{{ total }} destinations</span>
          </v-col>
        </v-row>
      </v-card-text>
    </v-card>

    <!-- Table -->
    <v-card rounded="xl" elevation="0" class="border">
      <v-progress-linear v-if="loading" indeterminate color="primary" />
      <v-data-table
        :headers="headers"
        :items="items"
        :loading="loading"
        hide-default-footer
        class="rounded-xl"
      >
        <template #item.name="{ item }">
          <div class="d-flex align-center py-2">
            <v-avatar
              :color="typeColor(item.type)"
              size="32"
              rounded="lg"
              class="mr-3"
            >
              <v-icon size="16" color="white">{{ typeIcon(item.type) }}</v-icon>
            </v-avatar>
            <div>
              <div class="font-weight-medium">{{ item.name }}</div>
              <div class="text-caption text-medium-emphasis">{{ item.region }}</div>
            </div>
          </div>
        </template>

        <template #item.country="{ item }">
          <div class="d-flex align-center gap-2">
            <span class="text-caption font-weight-bold text-medium-emphasis">{{ item.country_code }}</span>
            {{ item.country }}
          </div>
        </template>

        <template #item.type="{ item }">
          <v-chip :color="typeColor(item.type)" size="x-small" label class="text-uppercase font-weight-bold">
            {{ item.type }}
          </v-chip>
        </template>

        <template #item.airport_code="{ item }">
          <v-chip v-if="item.airport_code" size="x-small" variant="outlined" color="blue">
            {{ item.airport_code }}
          </v-chip>
          <span v-else class="text-medium-emphasis">—</span>
        </template>

        <template #item.attractions="{ item }">
          <div v-if="item.attractions?.length" class="d-flex flex-wrap gap-1 py-1">
            <v-chip
              v-for="a in item.attractions.slice(0, 2)"
              :key="a"
              size="x-small"
              variant="tonal"
              color="primary"
            >
              {{ a }}
            </v-chip>
            <v-chip v-if="item.attractions.length > 2" size="x-small" variant="tonal">
              +{{ item.attractions.length - 2 }}
            </v-chip>
          </div>
          <span v-else class="text-medium-emphasis">—</span>
        </template>

        <template #item.is_active="{ item }">
          <v-chip
            :color="item.is_active ? 'success' : 'grey'"
            size="x-small"
            label
          >
            {{ item.is_active ? 'Active' : 'Inactive' }}
          </v-chip>
        </template>

        <template #item.actions="{ item }">
          <div class="d-flex gap-1">
            <v-btn icon size="x-small" variant="text" color="blue" @click="openEdit(item)">
              <v-icon size="16">mdi-pencil</v-icon>
            </v-btn>
            <v-btn icon size="x-small" variant="text" color="error" @click="confirmDelete(item)">
              <v-icon size="16">mdi-delete</v-icon>
            </v-btn>
          </div>
        </template>

        <template #no-data>
          <div class="text-center py-10 text-medium-emphasis">
            <v-icon size="48" class="mb-3">mdi-map-marker-off-outline</v-icon>
            <p>No destinations found</p>
          </div>
        </template>
      </v-data-table>

      <v-divider />
      <div class="d-flex align-center justify-space-between pa-3">
        <div class="text-caption text-medium-emphasis">Total: {{ total }}</div>
        <v-pagination
          v-model="page"
          :length="totalPages"
          :total-visible="5"
          density="compact"
          @update:model-value="fetchItems"
        />
      </div>
    </v-card>

    <!-- Add/Edit Dialog -->
    <v-dialog v-model="dialog" max-width="680" persistent scrollable>
      <v-card rounded="xl">
        <v-card-title class="pa-5 pb-3 d-flex align-center">
          <v-icon class="mr-2" color="primary">mdi-map-marker-plus</v-icon>
          {{ editing ? 'Edit Destination' : 'Add Destination' }}
        </v-card-title>
        <v-divider />
        <v-card-text class="pa-5">
          <v-form ref="formRef">
            <v-row dense>
              <v-col cols="12" md="6">
                <v-text-field
                  v-model="form.name"
                  label="City / Place Name *"
                  variant="outlined"
                  density="compact"
                  :rules="[v => !!v || 'Name is required']"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-autocomplete
                  v-model="form.country"
                  :items="allCountries"
                  item-title="name"
                  item-value="name"
                  label="Country *"
                  variant="outlined"
                  density="compact"
                  :rules="[v => !!v || 'Country is required']"
                  @update:model-value="onCountrySelect"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.country_code"
                  label="Country Code (ISO)"
                  variant="outlined"
                  density="compact"
                  placeholder="e.g. UZB"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.region"
                  label="Region / Province"
                  variant="outlined"
                  density="compact"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-select
                  v-model="form.type"
                  :items="typeOptions"
                  label="Type"
                  variant="outlined"
                  density="compact"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.airport_code"
                  label="Airport Code (IATA)"
                  variant="outlined"
                  density="compact"
                  placeholder="e.g. TAS"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model="form.timezone"
                  label="Timezone"
                  variant="outlined"
                  density="compact"
                  placeholder="e.g. Asia/Tashkent"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model.number="form.sort_order"
                  label="Sort Order"
                  type="number"
                  variant="outlined"
                  density="compact"
                />
              </v-col>
              <v-col cols="12">
                <v-textarea
                  v-model="form.description"
                  label="Description"
                  variant="outlined"
                  density="compact"
                  rows="2"
                  auto-grow
                />
              </v-col>
              <v-col cols="12">
                <v-combobox
                  v-model="form.attractions"
                  label="Top Attractions (press Enter to add)"
                  variant="outlined"
                  density="compact"
                  multiple
                  chips
                  closable-chips
                  hint="Type attraction name and press Enter"
                  persistent-hint
                />
              </v-col>
              <v-col cols="12">
                <v-switch
                  v-model="form.is_active"
                  label="Active (visible in tour forms)"
                  color="success"
                  density="compact"
                  hide-details
                />
              </v-col>
            </v-row>
          </v-form>
        </v-card-text>
        <v-divider />
        <v-card-actions class="pa-4">
          <v-spacer />
          <v-btn variant="text" @click="dialog = false">Cancel</v-btn>
          <v-btn color="primary" variant="flat" :loading="saving" @click="saveItem">
            {{ editing ? 'Save Changes' : 'Add Destination' }}
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Delete confirm -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card rounded="xl">
        <v-card-title class="pa-5 pb-2">
          <v-icon color="error" class="mr-2">mdi-alert-circle</v-icon>
          Delete Destination
        </v-card-title>
        <v-card-text class="pa-5 pt-0">
          Remove <strong>{{ deletingItem?.name }}, {{ deletingItem?.country }}</strong> from the system?
        </v-card-text>
        <v-card-actions class="pa-4 pt-0">
          <v-spacer />
          <v-btn variant="text" @click="deleteDialog = false">Cancel</v-btn>
          <v-btn color="error" variant="flat" :loading="deleting" @click="deleteItem">Delete</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'

const ui = useUiStore()

const loading = ref(false)
const items = ref([])
const total = ref(0)
const page = ref(1)
const perPage = 20
const search = ref('')
const filterCountry = ref(null)
const filterType = ref(null)
const countryOptions = ref([])
const allCountries = ref([])

const dialog = ref(false)
const saving = ref(false)
const editing = ref(null)
const formRef = ref(null)

const deleteDialog = ref(false)
const deleting = ref(false)
const deletingItem = ref(null)

const totalPages = computed(() => Math.ceil(total.value / perPage) || 1)

const typeOptions = ['city', 'resort', 'historical', 'nature', 'border_crossing', 'other']

const defaultForm = () => ({
  name: '', country: '', country_code: '', region: '', type: 'city',
  airport_code: '', timezone: '', description: '', attractions: [], is_active: true, sort_order: 0,
})
const form = ref(defaultForm())

const headers = [
  { title: 'Destination', key: 'name', minWidth: 180 },
  { title: 'Country', key: 'country', width: 160 },
  { title: 'Type', key: 'type', width: 120 },
  { title: 'Airport', key: 'airport_code', width: 90 },
  { title: 'Attractions', key: 'attractions', minWidth: 200 },
  { title: 'Status', key: 'is_active', width: 90 },
  { title: 'Actions', key: 'actions', sortable: false, width: 90 },
]

function typeColor(type) {
  const m = { city: 'primary', historical: 'deep-purple', nature: 'green', resort: 'blue', border_crossing: 'orange', other: 'grey' }
  return m[type] ?? 'grey'
}
function typeIcon(type) {
  const m = { city: 'mdi-city', historical: 'mdi-pillar', nature: 'mdi-tree', resort: 'mdi-beach', border_crossing: 'mdi-barrier', other: 'mdi-map-marker' }
  return m[type] ?? 'mdi-map-marker'
}

function onCountrySelect(countryName) {
  const found = allCountries.value.find(c => c.name === countryName)
  if (found) form.value.country_code = found.code
}

async function fetchItems() {
  loading.value = true
  try {
    const res = await api.get('/destinations', {
      params: { page: page.value, per_page: perPage, search: search.value || undefined, country: filterCountry.value || undefined, type: filterType.value || undefined },
    })
    items.value = res.data.data || []
    total.value = res.data.meta?.total ?? items.value.length
    countryOptions.value = [...new Set(items.value.map(i => i.country))].sort()
  } catch {
    ui.showSnackbar('Failed to load destinations', 'error')
  } finally {
    loading.value = false
  }
}

async function loadCountries() {
  try {
    const res = await api.get('/countries')
    allCountries.value = res.data.data
  } catch { /* silent */ }
}

function openAdd() {
  editing.value = null
  form.value = defaultForm()
  dialog.value = true
}

function openEdit(item) {
  editing.value = item
  form.value = { ...defaultForm(), ...item, attractions: Array.isArray(item.attractions) ? [...item.attractions] : [] }
  dialog.value = true
}

function confirmDelete(item) {
  deletingItem.value = item
  deleteDialog.value = true
}

async function saveItem() {
  const { valid } = await formRef.value.validate()
  if (!valid) return
  saving.value = true
  try {
    if (editing.value) {
      await api.put(`/destinations/${editing.value.id}`, form.value)
      ui.showSnackbar('Destination updated', 'success')
    } else {
      await api.post('/destinations', form.value)
      ui.showSnackbar('Destination added', 'success')
    }
    dialog.value = false
    fetchItems()
  } catch {
    ui.showSnackbar('Save failed', 'error')
  } finally {
    saving.value = false
  }
}

async function deleteItem() {
  deleting.value = true
  try {
    await api.delete(`/destinations/${deletingItem.value.id}`)
    ui.showSnackbar('Destination deleted', 'success')
    deleteDialog.value = false
    fetchItems()
  } catch {
    ui.showSnackbar('Delete failed', 'error')
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  fetchItems()
  loadCountries()
})
</script>

<style scoped>
.border { border: 1px solid rgba(0,0,0,0.08) !important; }
.gap-1 { gap: 4px; }
.gap-2 { gap: 8px; }
</style>
