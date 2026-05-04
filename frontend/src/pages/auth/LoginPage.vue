<template>
  <v-card rounded="xl" elevation="20" class="overflow-hidden" width="420">
    <div style="height:4px; background: linear-gradient(90deg, #4A90D9 0%, #1A2744 50%, #2E4B8F 100%);" />

    <v-card-text class="pa-8">
      <!-- Logo + Brand -->
      <div class="text-center mb-7">
        <img
          :src="'/logo'"
          alt="Sogda Tour"
          style="width:72px; height:72px; border-radius:16px; object-fit:cover; box-shadow:0 4px 20px rgba(26,39,68,0.3);"
        />
        <div class="mt-3">
          <div style="font-size:22px; font-weight:800; color:#1A2744; letter-spacing:1px;">SOGDA TOUR</div>
          <div style="font-size:10px; color:#4A90D9; letter-spacing:2.5px; text-transform:uppercase; font-weight:600; margin-top:2px;">
            Automated Travel System
          </div>
        </div>
      </div>

      <v-alert
        v-if="errorMessage"
        type="error"
        variant="tonal"
        density="compact"
        rounded="lg"
        class="mb-4"
        closable
        :text="errorMessage"
        @click:close="errorMessage = ''"
      />

      <v-form ref="formRef" @submit.prevent="handleLogin">
        <v-text-field
          v-model="email"
          label="Email address"
          type="email"
          prepend-inner-icon="mdi-email-outline"
          variant="outlined"
          density="comfortable"
          class="mb-3"
          :rules="[v => !!v || 'Email is required', v => /.+@.+/.test(v) || 'Invalid email format']"
          :disabled="loading"
          autocomplete="email"
          autofocus
        />
        <v-text-field
          v-model="password"
          label="Password"
          :type="showPassword ? 'text' : 'password'"
          prepend-inner-icon="mdi-lock-outline"
          :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
          variant="outlined"
          density="comfortable"
          class="mb-5"
          :rules="[v => !!v || 'Password is required']"
          :disabled="loading"
          autocomplete="current-password"
          @click:append-inner="showPassword = !showPassword"
        />
        <v-btn
          type="submit"
          color="primary"
          block
          size="large"
          :loading="loading"
          rounded="lg"
          style="font-weight:600; letter-spacing:.5px;"
        >
          Sign In
        </v-btn>
      </v-form>

      <div class="text-center mt-6 text-caption text-medium-emphasis">
        &copy; {{ new Date().getFullYear() }} Sogda Tour &nbsp;&middot;&nbsp; All rights reserved
      </div>
    </v-card-text>
  </v-card>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const formRef = ref(null)
const email = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)
const errorMessage = ref('')

async function handleLogin() {
  const { valid } = await formRef.value.validate()
  if (!valid) return
  loading.value = true
  errorMessage.value = ''
  try {
    await authStore.login(email.value, password.value)
    router.push(route.query.redirect || '/dashboard')
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Invalid email or password.'
  } finally {
    loading.value = false
  }
}
</script>
