<template>
  <v-container fluid class="pa-6">
    <!-- Header -->
    <div class="d-flex align-center mb-5">
      <v-btn icon variant="text" :to="`/tours/${tourId}`" class="mr-2">
        <v-icon>mdi-arrow-left</v-icon>
      </v-btn>
      <div>
        <h1 class="text-h5 font-weight-bold" style="color:#1A2744;">{{ t('edit_tour') }}</h1>
        <p class="text-body-2 text-medium-emphasis mt-1">
          <template v-if="tour">{{ tour.tour_code }} — {{ tour.tour_name }}</template>
          <template v-else>{{ stepTitles[currentStep - 1] }}</template>
        </p>
      </div>
      <v-spacer />
      <v-btn-toggle v-model="lang" density="compact" variant="outlined" rounded="lg" mandatory>
        <v-btn value="ru" size="small">RU</v-btn>
        <v-btn value="en" size="small">EN</v-btn>
      </v-btn-toggle>
    </div>

    <v-progress-linear v-if="pageLoading" indeterminate color="primary" class="mb-4" />

    <template v-if="!pageLoading">
      <!-- Progress -->
      <v-card rounded="xl" elevation="0" class="border mb-4">
        <v-card-text class="pa-4 pb-2">
          <div class="d-flex align-center justify-space-between mb-2">
            <span class="text-body-2 font-weight-medium">{{ t('step') }} {{ currentStep }} / {{ totalSteps }}</span>
            <span class="text-caption text-medium-emphasis">{{ Math.round((currentStep / totalSteps) * 100) }}%</span>
          </div>
          <v-progress-linear :model-value="(currentStep / totalSteps) * 100" color="primary" rounded height="6" />
          <div class="d-flex mt-3 gap-1 flex-wrap">
            <v-chip
              v-for="(title, i) in stepTitles"
              :key="i"
              :color="i + 1 === currentStep ? 'primary' : i + 1 < currentStep ? 'success' : 'default'"
              :variant="i + 1 === currentStep ? 'flat' : 'tonal'"
              size="x-small"
              @click="currentStep = i + 1"
            >
              <v-icon v-if="i + 1 < currentStep" start size="12">mdi-check</v-icon>
              {{ i + 1 }}. {{ title }}
            </v-chip>
          </div>
        </v-card-text>
      </v-card>

      <!-- Steps content -->
      <v-card rounded="xl" elevation="0" class="border">
        <v-card-text class="pa-5">

          <!-- STEP 1: Basic Info -->
          <div v-show="currentStep === 1">
            <div class="text-subtitle-1 font-weight-bold mb-4" style="color:#1A2744;">
              <v-icon class="mr-2" color="primary">mdi-information-outline</v-icon>
              {{ t('basic_info') }}
            </div>
            <v-form ref="form1">
              <v-row dense>
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="form.tour_name"
                    :label="t('tour_name') + ' *'"
                    variant="outlined"
                    density="compact"
                    :rules="[req]"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-autocomplete
                    v-model="form.country"
                    :items="countries"
                    item-title="name"
                    item-value="name"
                    :label="t('country') + ' *'"
                    variant="outlined"
                    density="compact"
                    :rules="[req]"
                    clearable
                    :filter="countryFilter"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-autocomplete
                    v-model="form.counterparty_id"
                    :items="counterparties"
                    item-title="company_name"
                    item-value="id"
                    :label="t('partner')"
                    variant="outlined"
                    density="compact"
                    clearable
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-select
                    v-model="form.assigned_staff_id"
                    :items="staffList"
                    item-title="name"
                    item-value="id"
                    :label="t('responsible_staff')"
                    variant="outlined"
                    density="compact"
                    clearable
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model.number="form.pax_adults"
                    :label="t('adults') + ' *'"
                    type="number"
                    min="1"
                    variant="outlined"
                    density="compact"
                    :rules="[v => v > 0 || t('required')]"
                    @update:model-value="form.pax_count = Number(form.pax_adults) + Number(form.pax_children)"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model.number="form.pax_children"
                    :label="t('children')"
                    type="number"
                    min="0"
                    variant="outlined"
                    density="compact"
                    @update:model-value="form.pax_count = Number(form.pax_adults) + Number(form.pax_children)"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.start_date"
                    :label="t('start_date') + ' *'"
                    type="date"
                    variant="outlined"
                    density="compact"
                    :rules="[req]"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.end_date"
                    :label="t('end_date') + ' *'"
                    type="date"
                    variant="outlined"
                    density="compact"
                    :rules="[req]"
                  />
                </v-col>

                <!-- Arrival -->
                <v-col cols="12">
                  <div class="text-body-2 font-weight-medium mb-2">{{ t('arrival') }}</div>
                </v-col>
                <v-col cols="12" md="3">
                  <v-select
                    v-model="form.arrival_type"
                    :items="[{ title: t('airport'), value: 'airport' }, { title: t('border_crossing'), value: 'kpp' }]"
                    :label="t('arrival_type')"
                    variant="outlined"
                    density="compact"
                    clearable
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.arrival_flight_number"
                    :label="form.arrival_type === 'kpp' ? t('crossing_point') : t('flight_number')"
                    variant="outlined"
                    density="compact"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.arrival_flight_time"
                    :label="t('arrival_time')"
                    type="time"
                    variant="outlined"
                    density="compact"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-if="form.arrival_type === 'airport'"
                    v-model="form.arrival_terminal"
                    :label="t('terminal')"
                    variant="outlined"
                    density="compact"
                  />
                </v-col>

                <!-- Departure -->
                <v-col cols="12">
                  <div class="text-body-2 font-weight-medium mb-2">{{ t('departure') }}</div>
                </v-col>
                <v-col cols="12" md="3">
                  <v-select
                    v-model="form.departure_type"
                    :items="[{ title: t('airport'), value: 'airport' }, { title: t('border_crossing'), value: 'kpp' }]"
                    :label="t('departure_type')"
                    variant="outlined"
                    density="compact"
                    clearable
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.departure_flight_number"
                    :label="form.departure_type === 'kpp' ? t('crossing_point') : t('flight_number')"
                    variant="outlined"
                    density="compact"
                  />
                </v-col>
                <v-col cols="12" md="3">
                  <v-text-field
                    v-model="form.departure_flight_time"
                    :label="t('departure_time')"
                    type="time"
                    variant="outlined"
                    density="compact"
                  />
                </v-col>
                <v-col cols="12">
                  <v-textarea
                    v-model="form.notes"
                    :label="t('notes')"
                    variant="outlined"
                    density="compact"
                    rows="2"
                    auto-grow
                  />
                </v-col>
                <v-col cols="12">
                  <v-textarea
                    v-model="form.special_requests"
                    :label="t('special_requests')"
                    variant="outlined"
                    density="compact"
                    rows="2"
                    auto-grow
                  />
                </v-col>
              </v-row>
            </v-form>
          </div>

          <!-- STEP 2: Destinations -->
          <div v-show="currentStep === 2">
            <div class="d-flex align-center justify-space-between mb-4">
              <div class="text-subtitle-1 font-weight-bold" style="color:#1A2744;">
                <v-icon class="mr-2" color="primary">mdi-map-marker-path</v-icon>
                {{ t('destinations_itinerary') }}
              </div>
              <v-btn color="primary" variant="tonal" rounded="lg" size="small" @click="addDestination">
                <v-icon start size="16">mdi-plus</v-icon>
                {{ t('add_stop') }}
              </v-btn>
            </div>

            <div v-if="form.destinations.length === 0" class="text-center py-8 text-medium-emphasis">
              <v-icon size="48" class="mb-2">mdi-map-marker-plus-outline</v-icon>
              <p>{{ t('no_stops') }}</p>
            </div>

            <v-card
              v-for="(dest, i) in form.destinations"
              :key="i"
              rounded="xl"
              elevation="0"
              class="border mb-3"
            >
              <v-card-text class="pa-4">
                <div class="d-flex align-center mb-3">
                  <v-avatar color="primary" size="28" rounded="lg" class="mr-3">
                    <span style="font-size:12px; font-weight:700; color:white;">{{ i + 1 }}</span>
                  </v-avatar>
                  <div class="text-body-2 font-weight-medium">{{ t('stop') }} #{{ i + 1 }}</div>
                  <v-spacer />
                  <v-btn
                    v-if="i > 0"
                    icon
                    size="x-small"
                    variant="text"
                    color="primary"
                    @click="moveUp(i)"
                  >
                    <v-icon size="16">mdi-arrow-up</v-icon>
                  </v-btn>
                  <v-btn icon size="x-small" variant="text" color="error" @click="removeDestination(i)">
                    <v-icon size="16">mdi-delete</v-icon>
                  </v-btn>
                </div>
                <v-row dense>
                  <v-col cols="12" md="4">
                    <v-autocomplete
                      v-model="dest.destination_id"
                      :items="allDestinations"
                      :item-title="d => `${d.name}, ${d.country}`"
                      item-value="id"
                      :label="t('city_place') + ' *'"
                      variant="outlined"
                      density="compact"
                      clearable
                      :filter="destFilter"
                      @update:model-value="v => onDestSelect(v, dest)"
                    >
                      <template #item="{ item, props }">
                        <v-list-item v-bind="props">
                          <template #prepend>
                            <v-avatar :color="typeColor(item.raw.type)" size="24" rounded="lg" class="mr-2">
                              <v-icon size="12" color="white">{{ typeIcon(item.raw.type) }}</v-icon>
                            </v-avatar>
                          </template>
                          <template #append>
                            <v-chip size="x-small" variant="tonal">{{ item.raw.country }}</v-chip>
                          </template>
                        </v-list-item>
                      </template>
                    </v-autocomplete>
                  </v-col>
                  <v-col cols="12" md="2">
                    <v-text-field
                      v-model="dest.custom_city_name"
                      :label="t('custom_name')"
                      variant="outlined"
                      density="compact"
                      :placeholder="t('if_not_in_list')"
                    />
                  </v-col>
                  <v-col cols="12" md="3">
                    <v-text-field
                      v-model="dest.arrival_date"
                      :label="t('arrival_date') + ' *'"
                      type="date"
                      variant="outlined"
                      density="compact"
                      :rules="[req]"
                      @update:model-value="calcNights(dest)"
                    />
                  </v-col>
                  <v-col cols="12" md="3">
                    <v-text-field
                      v-model="dest.departure_date"
                      :label="t('departure_date') + ' *'"
                      type="date"
                      variant="outlined"
                      density="compact"
                      :rules="[req]"
                      @update:model-value="calcNights(dest)"
                    />
                  </v-col>
                  <v-col cols="12">
                    <v-alert
                      v-if="dest.nights_count >= 0"
                      density="compact"
                      variant="tonal"
                      color="info"
                      class="text-caption"
                    >
                      {{ dest.nights_count }} {{ t('nights') }}
                      <span v-if="dest.destination_id">
                        &nbsp;·&nbsp;
                        {{ allDestinations.find(d => d.id === dest.destination_id)?.name }}
                      </span>
                    </v-alert>
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>

            <div v-if="form.destinations.length > 1" class="mt-4 pa-4 rounded-xl" style="background:#F4F6F9;">
              <div class="text-caption font-weight-medium mb-3 text-medium-emphasis">{{ t('route_summary') }}</div>
              <div class="d-flex align-center flex-wrap gap-2">
                <template v-for="(dest, i) in form.destinations" :key="i">
                  <v-chip color="primary" variant="flat" size="small">
                    <v-icon start size="12">mdi-map-marker</v-icon>
                    {{ getDestName(dest) }}
                  </v-chip>
                  <v-icon v-if="i < form.destinations.length - 1" size="16" color="grey">mdi-arrow-right</v-icon>
                </template>
              </div>
            </div>
          </div>

          <!-- STEP 3: Hotels -->
          <div v-show="currentStep === 3">
            <div class="text-subtitle-1 font-weight-bold mb-4" style="color:#1A2744;">
              <v-icon class="mr-2" color="primary">mdi-hotel</v-icon>
              {{ t('hotel_bookings') }}
            </div>

            <div v-if="form.destinations.length === 0" class="text-center py-6 text-medium-emphasis">
              <v-icon size="40">mdi-alert-circle-outline</v-icon>
              <p>{{ t('add_destinations_first') }}</p>
            </div>

            <v-card
              v-for="(dest, i) in form.destinations"
              :key="i"
              rounded="xl"
              elevation="0"
              class="border mb-4"
            >
              <v-card-title class="pa-4 pb-2 text-body-2 font-weight-bold" style="color:#1A2744;">
                <v-icon size="16" class="mr-1">mdi-map-marker</v-icon>
                {{ getDestName(dest) }}
                <span class="text-medium-emphasis ml-2 font-weight-regular text-caption">
                  {{ dest.arrival_date }} → {{ dest.departure_date }}
                </span>
              </v-card-title>
              <v-card-text class="pa-4 pt-2">
                <v-row v-if="form.hotels[i]" dense>
                  <v-col cols="12" md="4">
                    <v-autocomplete
                      v-model="form.hotels[i].hotel_id"
                      :items="hotelsForDest(dest)"
                      :item-title="h => `${h.name} (${h.stars}★)`"
                      item-value="id"
                      :label="t('hotel')"
                      variant="outlined"
                      density="compact"
                      clearable
                    />
                  </v-col>
                  <v-col cols="12" md="3">
                    <v-text-field
                      v-model="form.hotels[i].room_type"
                      :label="t('room_type')"
                      variant="outlined"
                      density="compact"
                      placeholder="Standard, Deluxe..."
                    />
                  </v-col>
                  <v-col cols="12" md="2">
                    <v-text-field
                      v-model.number="form.hotels[i].room_count"
                      :label="t('rooms')"
                      type="number"
                      min="1"
                      variant="outlined"
                      density="compact"
                    />
                  </v-col>
                  <v-col cols="12" md="3">
                    <v-text-field
                      v-model.number="form.hotels[i].price_per_night_usd"
                      :label="t('price_per_night_usd')"
                      type="number"
                      variant="outlined"
                      density="compact"
                      prefix="$"
                    />
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>
          </div>

          <!-- STEP 4: Transport -->
          <div v-show="currentStep === 4">
            <div class="d-flex align-center justify-space-between mb-4">
              <div class="text-subtitle-1 font-weight-bold" style="color:#1A2744;">
                <v-icon class="mr-2" color="primary">mdi-bus</v-icon>
                {{ t('transport') }}
              </div>
              <v-btn color="primary" variant="tonal" rounded="lg" size="small" @click="addTransport">
                <v-icon start size="16">mdi-plus</v-icon>
                {{ t('add_transport') }}
              </v-btn>
            </div>

            <v-card
              v-for="(tr, i) in form.transports"
              :key="i"
              rounded="xl"
              elevation="0"
              class="border mb-3"
            >
              <v-card-text class="pa-4">
                <div class="d-flex align-center mb-3">
                  <div class="text-body-2 font-weight-medium">{{ t('route') }} #{{ i + 1 }}</div>
                  <v-spacer />
                  <v-btn icon size="x-small" variant="text" color="error" @click="form.transports.splice(i, 1)">
                    <v-icon size="16">mdi-delete</v-icon>
                  </v-btn>
                </div>
                <v-row dense>
                  <v-col cols="12" md="2">
                    <v-select
                      v-model="tr.transport_type"
                      :items="['bus', 'minibus', 'car', 'train', 'internal_flight', 'transfer']"
                      :label="t('type')"
                      variant="outlined"
                      density="compact"
                    />
                  </v-col>
                  <v-col cols="12" md="2">
                    <v-text-field v-model="tr.route_from" :label="t('from')" variant="outlined" density="compact" />
                  </v-col>
                  <v-col cols="12" md="2">
                    <v-text-field v-model="tr.route_to" :label="t('to')" variant="outlined" density="compact" />
                  </v-col>
                  <v-col cols="12" md="2">
                    <v-text-field v-model="tr.transport_date" :label="t('date')" type="date" variant="outlined" density="compact" />
                  </v-col>
                  <v-col cols="12" md="2">
                    <v-text-field v-model="tr.departure_time" :label="t('departure_time')" type="time" variant="outlined" density="compact" />
                  </v-col>
                  <v-col cols="12" md="2">
                    <v-text-field
                      v-model.number="tr.price_usd"
                      :label="t('price_usd')"
                      type="number"
                      variant="outlined"
                      density="compact"
                      prefix="$"
                    />
                  </v-col>
                  <v-col cols="12">
                    <v-switch
                      v-model="tr.is_own_fleet"
                      :label="t('own_fleet')"
                      color="primary"
                      density="compact"
                      hide-details
                      inline
                    />
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>

            <div v-if="form.transports.length === 0" class="text-center py-8 text-medium-emphasis">
              <v-icon size="48" class="mb-2">mdi-bus-clock</v-icon>
              <p>{{ t('no_transport') }}</p>
            </div>
          </div>

          <!-- STEP 5: Meals -->
          <div v-show="currentStep === 5">
            <div class="d-flex align-center justify-space-between mb-4">
              <div class="text-subtitle-1 font-weight-bold" style="color:#1A2744;">
                <v-icon class="mr-2" color="primary">mdi-food</v-icon>
                {{ t('meals') }}
              </div>
              <v-btn color="primary" variant="tonal" rounded="lg" size="small" @click="addMeal">
                <v-icon start size="16">mdi-plus</v-icon>
                {{ t('add_meal') }}
              </v-btn>
            </div>

            <v-card
              v-for="(meal, i) in form.meals"
              :key="i"
              rounded="xl"
              elevation="0"
              class="border mb-3"
            >
              <v-card-text class="pa-4">
                <div class="d-flex align-center mb-3">
                  <v-chip :color="mealColor(meal.meal_type)" size="small" class="mr-2">
                    {{ mealLabel(meal.meal_type) }}
                  </v-chip>
                  <v-spacer />
                  <v-btn icon size="x-small" variant="text" color="error" @click="form.meals.splice(i, 1)">
                    <v-icon size="16">mdi-delete</v-icon>
                  </v-btn>
                </div>
                <v-row dense>
                  <v-col cols="12" md="3">
                    <v-select
                      v-model="meal.meal_type"
                      :items="[
                        { title: t('breakfast'), value: 'breakfast' },
                        { title: t('lunch'), value: 'lunch' },
                        { title: t('dinner'), value: 'dinner' },
                        { title: t('snack'), value: 'snack' },
                      ]"
                      :label="t('meal_type')"
                      variant="outlined"
                      density="compact"
                    />
                  </v-col>
                  <v-col cols="12" md="2">
                    <v-text-field v-model="meal.meal_date" :label="t('date')" type="date" variant="outlined" density="compact" />
                  </v-col>
                  <v-col cols="12" md="2">
                    <v-text-field v-model="meal.meal_time" :label="t('time')" type="time" variant="outlined" density="compact" />
                  </v-col>
                  <v-col cols="12" md="2">
                    <v-autocomplete
                      v-model="meal.restaurant_id"
                      :items="restaurants"
                      item-title="company_name"
                      item-value="id"
                      :label="t('restaurant')"
                      variant="outlined"
                      density="compact"
                      clearable
                    />
                  </v-col>
                  <v-col cols="12" md="3">
                    <v-select
                      v-model="meal.menu_type"
                      :items="['standard', 'national', 'european', 'vegetarian', 'custom']"
                      :label="t('menu_type')"
                      variant="outlined"
                      density="compact"
                    />
                  </v-col>
                  <v-col cols="12" md="2">
                    <v-text-field
                      v-model.number="meal.price_per_person_usd"
                      :label="t('price_per_person')"
                      type="number"
                      variant="outlined"
                      density="compact"
                      prefix="$"
                    />
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>

            <div v-if="form.meals.length === 0" class="text-center py-8 text-medium-emphasis">
              <v-icon size="48" class="mb-2">mdi-food-off</v-icon>
              <p>{{ t('no_meals') }}</p>
            </div>
          </div>

          <!-- STEP 6: Entrance Tickets -->
          <div v-show="currentStep === 6">
            <div class="d-flex align-center justify-space-between mb-4">
              <div class="text-subtitle-1 font-weight-bold" style="color:#1A2744;">
                <v-icon class="mr-2" color="primary">mdi-ticket</v-icon>
                {{ t('entrance_tickets') }}
              </div>
              <v-btn color="primary" variant="tonal" rounded="lg" size="small" @click="addTicket">
                <v-icon start size="16">mdi-plus</v-icon>
                {{ t('add_ticket') }}
              </v-btn>
            </div>

            <v-card
              v-for="(tk, i) in form.tickets"
              :key="i"
              rounded="xl"
              elevation="0"
              class="border mb-3"
            >
              <v-card-text class="pa-4">
                <div class="d-flex align-center mb-3">
                  <v-icon class="mr-2" color="orange">mdi-ticket-outline</v-icon>
                  <span class="text-body-2 font-weight-medium">{{ t('ticket') }} #{{ i + 1 }}</span>
                  <v-spacer />
                  <v-btn icon size="x-small" variant="text" color="error" @click="form.tickets.splice(i, 1)">
                    <v-icon size="16">mdi-delete</v-icon>
                  </v-btn>
                </div>
                <v-row dense>
                  <v-col cols="12" md="4">
                    <v-text-field v-model="tk.attraction_name" :label="t('attraction') + ' *'" variant="outlined" density="compact" :rules="[req]" />
                  </v-col>
                  <v-col cols="12" md="2">
                    <v-text-field v-model="tk.city" :label="t('city')" variant="outlined" density="compact" />
                  </v-col>
                  <v-col cols="12" md="2">
                    <v-text-field v-model="tk.visit_date" :label="t('visit_date')" type="date" variant="outlined" density="compact" />
                  </v-col>
                  <v-col cols="12" md="2">
                    <v-text-field v-model="tk.visit_time" :label="t('visit_time')" type="time" variant="outlined" density="compact" />
                  </v-col>
                  <v-col cols="12" md="2">
                    <v-text-field
                      v-model.number="tk.price_per_person_usd"
                      :label="t('price_per_person')"
                      type="number"
                      variant="outlined"
                      density="compact"
                      prefix="$"
                    />
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>

            <div v-if="form.tickets.length === 0" class="text-center py-8 text-medium-emphasis">
              <v-icon size="48" class="mb-2">mdi-ticket-outline</v-icon>
              <p>{{ t('no_tickets') }}</p>
            </div>
          </div>

          <!-- STEP 7: Visas -->
          <div v-show="currentStep === 7">
            <div class="d-flex align-center justify-space-between mb-4">
              <div class="text-subtitle-1 font-weight-bold" style="color:#1A2744;">
                <v-icon class="mr-2" color="primary">mdi-passport</v-icon>
                {{ t('visas') }}
              </div>
              <v-btn color="primary" variant="tonal" rounded="lg" size="small" @click="addVisa">
                <v-icon start size="16">mdi-plus</v-icon>
                {{ t('add_applicant') }}
              </v-btn>
            </div>

            <v-card
              v-for="(visa, i) in form.visas"
              :key="i"
              rounded="xl"
              elevation="0"
              class="border mb-3"
            >
              <v-card-text class="pa-4">
                <div class="d-flex align-center mb-3">
                  <v-icon class="mr-2" color="primary">mdi-account</v-icon>
                  <span class="text-body-2 font-weight-medium">{{ t('applicant') }} #{{ i + 1 }}</span>
                  <v-spacer />
                  <v-btn icon size="x-small" variant="text" color="error" @click="form.visas.splice(i, 1)">
                    <v-icon size="16">mdi-delete</v-icon>
                  </v-btn>
                </div>
                <v-row dense>
                  <v-col cols="12" md="3">
                    <v-text-field v-model="visa.applicant_name" :label="t('full_name') + ' *'" variant="outlined" density="compact" :rules="[req]" />
                  </v-col>
                  <v-col cols="12" md="3">
                    <v-text-field v-model="visa.passport_number" :label="t('passport_number')" variant="outlined" density="compact" />
                  </v-col>
                  <v-col cols="12" md="2">
                    <v-text-field v-model="visa.passport_expiry" :label="t('passport_expiry')" type="date" variant="outlined" density="compact" />
                  </v-col>
                  <v-col cols="12" md="2">
                    <v-autocomplete
                      v-model="visa.nationality"
                      :items="countries"
                      item-title="name"
                      item-value="name"
                      :label="t('nationality')"
                      variant="outlined"
                      density="compact"
                      clearable
                    />
                  </v-col>
                  <v-col cols="12" md="2">
                    <v-select
                      v-model="visa.visa_type"
                      :items="['tourist', 'business', 'transit', 'evisa', 'visa_on_arrival']"
                      :label="t('visa_type')"
                      variant="outlined"
                      density="compact"
                    />
                  </v-col>
                </v-row>
              </v-card-text>
            </v-card>

            <div v-if="form.visas.length === 0" class="text-center py-8 text-medium-emphasis">
              <v-icon size="48" class="mb-2">mdi-passport</v-icon>
              <p>{{ t('no_visas') }}</p>
            </div>
          </div>

          <!-- STEP 8: Review -->
          <div v-show="currentStep === 8">
            <div class="text-subtitle-1 font-weight-bold mb-4" style="color:#1A2744;">
              <v-icon class="mr-2" color="primary">mdi-check-circle</v-icon>
              {{ t('review_submit') }}
            </div>

            <v-row dense>
              <v-col cols="12" md="6">
                <v-card rounded="xl" color="primary" variant="tonal" class="mb-3">
                  <v-card-text class="pa-4">
                    <div class="text-caption text-medium-emphasis mb-2">{{ t('tour_info') }}</div>
                    <div class="font-weight-bold text-body-1">{{ form.tour_name || '—' }}</div>
                    <div class="text-body-2 mt-1">{{ form.country }} &nbsp;·&nbsp; {{ form.pax_count }} pax</div>
                    <div class="text-caption mt-1">{{ form.start_date }} → {{ form.end_date }}</div>
                  </v-card-text>
                </v-card>
              </v-col>
              <v-col cols="12" md="6">
                <v-card rounded="xl" elevation="0" class="border mb-3">
                  <v-card-text class="pa-4">
                    <div class="text-caption text-medium-emphasis mb-2">{{ t('summary') }}</div>
                    <div class="d-flex justify-space-between text-body-2 mb-1">
                      <span>{{ t('destinations') }}</span>
                      <strong>{{ form.destinations.length }}</strong>
                    </div>
                    <div class="d-flex justify-space-between text-body-2 mb-1">
                      <span>{{ t('hotels') }}</span>
                      <strong>{{ form.hotels.filter(h => h?.hotel_id).length }}</strong>
                    </div>
                    <div class="d-flex justify-space-between text-body-2 mb-1">
                      <span>{{ t('transport') }}</span>
                      <strong>{{ form.transports.length }}</strong>
                    </div>
                    <div class="d-flex justify-space-between text-body-2 mb-1">
                      <span>{{ t('meals') }}</span>
                      <strong>{{ form.meals.length }}</strong>
                    </div>
                    <div class="d-flex justify-space-between text-body-2 mb-1">
                      <span>{{ t('tickets') }}</span>
                      <strong>{{ form.tickets.length }}</strong>
                    </div>
                    <div class="d-flex justify-space-between text-body-2">
                      <span>{{ t('visa_applicants') }}</span>
                      <strong>{{ form.visas.length }}</strong>
                    </div>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>

            <div class="d-flex gap-3 mt-4">
              <v-btn
                color="primary"
                variant="tonal"
                size="large"
                rounded="lg"
                :loading="saving"
                @click="saveTour('draft')"
              >
                <v-icon start>mdi-content-save</v-icon>
                {{ t('save_draft') }}
              </v-btn>
              <v-btn
                color="success"
                variant="flat"
                size="large"
                rounded="lg"
                :loading="saving"
                @click="saveTour(null)"
              >
                <v-icon start>mdi-check</v-icon>
                {{ t('save_changes') }}
              </v-btn>
            </div>
          </div>
        </v-card-text>

        <!-- Navigation -->
        <v-divider />
        <v-card-actions class="pa-4">
          <v-btn v-if="currentStep > 1" variant="text" @click="currentStep--">
            <v-icon start>mdi-arrow-left</v-icon>
            {{ t('back') }}
          </v-btn>
          <v-spacer />
          <v-btn
            v-if="currentStep < totalSteps"
            color="primary"
            variant="flat"
            rounded="lg"
            @click="nextStep"
          >
            {{ t('next') }}
            <v-icon end>mdi-arrow-right</v-icon>
          </v-btn>
        </v-card-actions>
      </v-card>
    </template>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'
import { useUiStore } from '@/stores/ui'

const route = useRoute()
const router = useRouter()
const ui = useUiStore()

const tourId = route.params.id
const pageLoading = ref(false)
const saving = ref(false)
const currentStep = ref(1)
const totalSteps = 8
const form1 = ref(null)
const tour = ref(null)

const lang = ref(ui.lang ?? 'ru')

const messages = {
  ru: {
    edit_tour: 'Редактировать тур',
    step: 'Шаг', basic_info: 'Основная информация',
    tour_name: 'Название тура', country: 'Страна',
    partner: 'Партнёр', responsible_staff: 'Ответственный',
    adults: 'Взрослых', children: 'Детей',
    start_date: 'Дата начала', end_date: 'Дата окончания',
    arrival: 'Прибытие', departure: 'Отправление',
    arrival_type: 'Тип прибытия', departure_type: 'Тип отправления',
    airport: 'Аэропорт', border_crossing: 'КПП',
    flight_number: 'Номер рейса', crossing_point: 'Пункт пропуска',
    arrival_time: 'Время прибытия', departure_time: 'Время отправления',
    terminal: 'Терминал', notes: 'Примечания', special_requests: 'Особые пожелания',
    destinations_itinerary: 'Маршрут / Пункты назначения',
    add_stop: 'Добавить пункт', stop: 'Остановка', no_stops: 'Добавьте пункты маршрута',
    city_place: 'Город / Место', custom_name: 'Своё название', if_not_in_list: 'если нет в списке',
    arrival_date: 'Дата прибытия', departure_date: 'Дата отъезда',
    nights: 'ночей', route_summary: 'Краткий маршрут',
    hotel_bookings: 'Размещение (Отели)', add_destinations_first: 'Сначала добавьте маршрут',
    hotel: 'Отель', room_type: 'Тип номера', rooms: 'Кол-во номеров', price_per_night_usd: 'Цена/ночь (USD)',
    transport: 'Транспорт', add_transport: 'Добавить транспорт', no_transport: 'Транспорт не добавлен',
    route: 'Маршрут', type: 'Тип', from: 'Откуда', to: 'Куда',
    date: 'Дата', time: 'Время', price_usd: 'Стоимость (USD)', own_fleet: 'Собственный транспорт',
    meals: 'Питание', add_meal: 'Добавить приём пищи', no_meals: 'Питание не добавлено',
    meal_type: 'Тип питания', breakfast: 'Завтрак', lunch: 'Обед', dinner: 'Ужин', snack: 'Перекус',
    restaurant: 'Ресторан', menu_type: 'Тип меню', price_per_person: 'Цена/чел (USD)',
    entrance_tickets: 'Входные билеты', add_ticket: 'Добавить билет', no_tickets: 'Билеты не добавлены',
    ticket: 'Билет', attraction: 'Достопримечательность', city: 'Город',
    visit_date: 'Дата посещения', visit_time: 'Время посещения',
    visas: 'Визы', add_applicant: 'Добавить заявителя', no_visas: 'Заявители не добавлены',
    applicant: 'Заявитель', full_name: 'ФИО', passport_number: 'Номер паспорта',
    passport_expiry: 'Срок действия', nationality: 'Гражданство', visa_type: 'Тип визы',
    review_submit: 'Проверка и сохранение', tour_info: 'Информация о туре',
    summary: 'Итого', destinations: 'Пункты', hotels: 'Отели',
    tickets: 'Билеты', visa_applicants: 'Визы',
    save_draft: 'Сохранить как черновик', save_changes: 'Сохранить изменения',
    back: 'Назад', next: 'Далее', required: 'Обязательно',
  },
  en: {
    edit_tour: 'Edit Tour',
    step: 'Step', basic_info: 'Basic Information',
    tour_name: 'Tour Name', country: 'Country',
    partner: 'Partner', responsible_staff: 'Responsible Staff',
    adults: 'Adults', children: 'Children',
    start_date: 'Start Date', end_date: 'End Date',
    arrival: 'Arrival', departure: 'Departure',
    arrival_type: 'Arrival Type', departure_type: 'Departure Type',
    airport: 'Airport', border_crossing: 'Border Crossing',
    flight_number: 'Flight Number', crossing_point: 'Border Point',
    arrival_time: 'Arrival Time', departure_time: 'Departure Time',
    terminal: 'Terminal', notes: 'Notes', special_requests: 'Special Requests',
    destinations_itinerary: 'Destinations / Itinerary',
    add_stop: 'Add Stop', stop: 'Stop', no_stops: 'Add destinations to the itinerary',
    city_place: 'City / Place', custom_name: 'Custom Name', if_not_in_list: 'if not in list',
    arrival_date: 'Arrival Date', departure_date: 'Departure Date',
    nights: 'nights', route_summary: 'Route Summary',
    hotel_bookings: 'Accommodation (Hotels)', add_destinations_first: 'Add destinations first',
    hotel: 'Hotel', room_type: 'Room Type', rooms: 'Rooms', price_per_night_usd: 'Price/Night (USD)',
    transport: 'Transport', add_transport: 'Add Transport', no_transport: 'No transport added',
    route: 'Route', type: 'Type', from: 'From', to: 'To',
    date: 'Date', time: 'Time', price_usd: 'Price (USD)', own_fleet: 'Own Fleet',
    meals: 'Meals', add_meal: 'Add Meal', no_meals: 'No meals added',
    meal_type: 'Meal Type', breakfast: 'Breakfast', lunch: 'Lunch', dinner: 'Dinner', snack: 'Snack',
    restaurant: 'Restaurant', menu_type: 'Menu Type', price_per_person: 'Price/Person (USD)',
    entrance_tickets: 'Entrance Tickets', add_ticket: 'Add Ticket', no_tickets: 'No tickets added',
    ticket: 'Ticket', attraction: 'Attraction', city: 'City',
    visit_date: 'Visit Date', visit_time: 'Visit Time',
    visas: 'Visas', add_applicant: 'Add Applicant', no_visas: 'No visa applicants added',
    applicant: 'Applicant', full_name: 'Full Name', passport_number: 'Passport Number',
    passport_expiry: 'Expiry Date', nationality: 'Nationality', visa_type: 'Visa Type',
    review_submit: 'Review & Save', tour_info: 'Tour Information',
    summary: 'Summary', destinations: 'Stops', hotels: 'Hotels',
    tickets: 'Tickets', visa_applicants: 'Visas',
    save_draft: 'Save as Draft', save_changes: 'Save Changes',
    back: 'Back', next: 'Next', required: 'Required',
  },
}
function t(key) { return messages[lang.value]?.[key] ?? messages.en[key] ?? key }

const stepTitlesRU = ['Основная информация', 'Маршрут', 'Отели', 'Транспорт', 'Питание', 'Билеты', 'Визы', 'Финал']
const stepTitlesEN = ['Basic Info', 'Destinations', 'Hotels', 'Transport', 'Meals', 'Tickets', 'Visas', 'Review']
const stepTitles = computed(() => lang.value === 'ru' ? stepTitlesRU : stepTitlesEN)

const countries = ref([])
const allDestinations = ref([])
const counterparties = ref([])
const staffList = ref([])
const hotels = ref([])
const restaurants = ref([])

const form = ref({
  tour_name: '', country: '', counterparty_id: null, assigned_staff_id: null,
  pax_count: 0, pax_adults: 2, pax_children: 0,
  start_date: '', end_date: '',
  arrival_type: null, arrival_flight_number: '', arrival_flight_time: '', arrival_terminal: '',
  departure_type: null, departure_flight_number: '', departure_flight_time: '',
  notes: '', special_requests: '',
  destinations: [], hotels: [], transports: [], meals: [], tickets: [], visas: [],
})

const req = v => !!v || t('required')

function countryFilter(item, queryText) {
  return item.raw.name.toLowerCase().includes(queryText.toLowerCase()) ||
         item.raw.code?.toLowerCase().includes(queryText.toLowerCase())
}

function destFilter(item, queryText) {
  const q = queryText.toLowerCase()
  return item.raw.name.toLowerCase().includes(q) || item.raw.country.toLowerCase().includes(q)
}

function typeColor(type) {
  return { city: 'primary', historical: 'deep-purple', nature: 'green', resort: 'blue', border_crossing: 'orange', other: 'grey' }[type] ?? 'grey'
}
function typeIcon(type) {
  return { city: 'mdi-city', historical: 'mdi-pillar', nature: 'mdi-tree', resort: 'mdi-beach', border_crossing: 'mdi-barrier', other: 'mdi-map-marker' }[type] ?? 'mdi-map-marker'
}

function mealColor(type) { return { breakfast: 'orange', lunch: 'green', dinner: 'blue', snack: 'purple' }[type] ?? 'grey' }
function mealLabel(key) { return messages[lang.value][key] ?? key }

function getDestName(dest) {
  if (dest.custom_city_name) return dest.custom_city_name
  if (dest.destination_id) {
    const d = allDestinations.value.find(x => x.id === dest.destination_id)
    return d ? d.name : (dest.city || '—')
  }
  return dest.city || '—'
}

function calcNights(dest) {
  if (dest.arrival_date && dest.departure_date) {
    const diff = (new Date(dest.departure_date) - new Date(dest.arrival_date)) / 86400000
    dest.nights_count = Math.max(0, Math.round(diff))
  }
}

function onDestSelect(id, dest) {
  const d = allDestinations.value.find(x => x.id === id)
  if (d && !dest.custom_city_name) dest.city = d.name
}

function hotelsForDest(dest) {
  const d = allDestinations.value.find(x => x.id === dest.destination_id)
  if (!d) return hotels.value
  return hotels.value.filter(h =>
    h.city?.toLowerCase() === d.name?.toLowerCase() ||
    h.city?.toLowerCase() === d.country?.toLowerCase()
  )
}

function addDestination() {
  const len = form.value.destinations.length
  form.value.destinations.push({
    destination_id: null, custom_city_name: '', city: '',
    arrival_date: form.value.start_date, departure_date: '', nights_count: 0, order_index: len,
  })
  form.value.hotels.push({ hotel_id: null, room_type: 'Standard', room_count: Math.ceil(form.value.pax_adults / 2), price_per_night_usd: 0 })
}

function removeDestination(i) {
  form.value.destinations.splice(i, 1)
  form.value.hotels.splice(i, 1)
}

function moveUp(i) {
  ;[form.value.destinations[i - 1], form.value.destinations[i]] = [form.value.destinations[i], form.value.destinations[i - 1]]
}

function addTransport() {
  form.value.transports.push({
    transport_type: 'bus', route_from: '', route_to: '',
    transport_date: form.value.start_date, departure_time: '', price_usd: 0, is_own_fleet: false,
  })
}

function addMeal() {
  form.value.meals.push({
    meal_type: 'lunch', meal_date: form.value.start_date, meal_time: '13:00',
    restaurant_id: null, menu_type: 'national', price_per_person_usd: 0,
  })
}

function addTicket() {
  form.value.tickets.push({
    attraction_name: '', city: '', visit_date: form.value.start_date, visit_time: '',
    pax_count: form.value.pax_count, price_per_person_usd: 0,
  })
}

function addVisa() {
  form.value.visas.push({
    applicant_name: '', passport_number: '', passport_expiry: '', nationality: '', visa_type: 'tourist',
  })
}

async function nextStep() {
  if (currentStep.value === 1 && form1.value) {
    const { valid } = await form1.value.validate()
    if (!valid) return
    form.value.pax_count = Number(form.value.pax_adults) + Number(form.value.pax_children)
  }
  if (currentStep.value < totalSteps) currentStep.value++
}

async function saveTour(statusOverride) {
  saving.value = true
  try {
    const payload = {
      ...form.value,
      pax_count: Number(form.value.pax_adults) + Number(form.value.pax_children),
      destinations: form.value.destinations.map((d, i) => ({
        city: getDestName(d) || 'other',
        custom_city_name: d.custom_city_name || undefined,
        arrival_date: d.arrival_date,
        departure_date: d.departure_date,
        nights_count: d.nights_count,
        order_index: i,
        day_number: i + 1,
      })),
    }
    if (statusOverride) payload.status = statusOverride

    await api.put(`/tours/${tourId}`, payload)
    ui.showSnackbar(
      lang.value === 'ru' ? 'Тур успешно обновлён!' : 'Tour updated successfully!',
      'success'
    )
    router.push(`/tours/${tourId}`)
  } catch (error) {
    const errors = error.response?.data?.errors
    if (errors) {
      Object.values(errors).flat().forEach(msg => ui.showSnackbar(msg, 'error'))
    } else {
      const msg = error.response?.data?.message || (lang.value === 'ru' ? 'Ошибка сохранения' : 'Save failed')
      ui.showSnackbar(msg, 'error')
    }
  } finally {
    saving.value = false
  }
}

async function loadData() {
  pageLoading.value = true
  try {
    const [tourRes, cRes, dRes, cpRes, staffRes, hRes, rRes] = await Promise.all([
      api.get(`/tours/${tourId}`),
      api.get('/countries'),
      api.get('/destinations/list?all_list=1&active_only=1'),
      api.get('/counterparties?per_page=200&type=foreign_tour'),
      api.get('/users?per_page=100'),
      api.get('/hotels?per_page=100'),
      api.get('/counterparties?per_page=100&type=restaurant'),
    ])

    countries.value = cRes.data.data
    allDestinations.value = dRes.data.data
    counterparties.value = cpRes.data.data || []
    staffList.value = (staffRes.data.data || []).filter(u => u.roles?.some(r => ['manager', 'staff', 'sales'].includes(r.name ?? r)))
    hotels.value = hRes.data.data || []
    restaurants.value = rRes.data.data || []

    const tourData = tourRes.data.data || tourRes.data
    tour.value = tourData

    Object.keys(form.value).forEach(key => {
      if (tourData[key] !== undefined && !['destinations', 'hotels', 'transports', 'meals', 'tickets', 'visas'].includes(key)) {
        form.value[key] = tourData[key]
      }
    })

    form.value.destinations = (tourData.destinations || []).map((d, i) => ({
      destination_id: d.destination_id ?? null,
      custom_city_name: d.custom_city_name ?? '',
      city: d.city ?? '',
      arrival_date: d.arrival_date ?? '',
      departure_date: d.departure_date ?? '',
      nights_count: d.nights_count ?? 0,
      order_index: i,
    }))

    form.value.hotels = form.value.destinations.map((_, i) => {
      const h = tourData.hotels?.[i] ?? {}
      return {
        hotel_id: h.hotel_id ?? h.id ?? null,
        room_type: h.room_type ?? 'Standard',
        room_count: h.room_count ?? 1,
        price_per_night_usd: h.price_per_night_usd ?? h.price ?? 0,
      }
    })

    form.value.transports = (tourData.transports || []).map(tr => ({
      transport_type: tr.transport_type ?? tr.type ?? 'bus',
      route_from: tr.route_from ?? '',
      route_to: tr.route_to ?? '',
      transport_date: tr.transport_date ?? tr.date ?? '',
      departure_time: tr.departure_time ?? '',
      price_usd: tr.price_usd ?? tr.price ?? 0,
      is_own_fleet: tr.is_own_fleet ?? tr.is_own ?? false,
    }))

    form.value.meals = (tourData.meals || []).map(m => ({
      meal_type: m.meal_type ?? 'lunch',
      meal_date: m.meal_date ?? m.date ?? '',
      meal_time: m.meal_time ?? '',
      restaurant_id: m.restaurant_id ?? null,
      menu_type: m.menu_type ?? 'standard',
      price_per_person_usd: m.price_per_person_usd ?? m.price ?? 0,
    }))

    form.value.tickets = (tourData.tickets || []).map(tk => ({
      attraction_name: tk.attraction_name ?? tk.attraction ?? '',
      city: tk.city ?? '',
      visit_date: tk.visit_date ?? tk.date ?? '',
      visit_time: tk.visit_time ?? '',
      pax_count: tk.pax_count ?? tk.pax ?? 1,
      price_per_person_usd: tk.price_per_person_usd ?? tk.price ?? 0,
    }))

    form.value.visas = (tourData.visas || []).map(v => ({
      applicant_name: v.applicant_name ?? '',
      passport_number: v.passport_number ?? '',
      passport_expiry: v.passport_expiry ?? '',
      nationality: v.nationality ?? '',
      visa_type: v.visa_type ?? 'tourist',
    }))

    form.value.pax_count = Number(form.value.pax_adults) + Number(form.value.pax_children)
  } catch {
    ui.showSnackbar(lang.value === 'ru' ? 'Ошибка загрузки данных' : 'Failed to load data', 'error')
  } finally {
    pageLoading.value = false
  }
}

onMounted(loadData)
</script>

<style scoped>
.border { border: 1px solid rgba(0,0,0,0.08) !important; }
.gap-1 { gap: 4px; }
.gap-2 { gap: 8px; }
.gap-3 { gap: 12px; }
</style>
