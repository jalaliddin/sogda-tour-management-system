<template>
  <v-app :theme="'sogdaTour'">
    <router-view />
    <v-snackbar
      v-model="ui.snackbar.show"
      :color="ui.snackbar.color"
      location="bottom right"
      timeout="4000"
    >
      {{ ui.snackbar.text }}
      <template #actions>
        <v-btn icon="mdi-close" variant="text" @click="ui.snackbar.show = false" />
      </template>
    </v-snackbar>
  </v-app>
</template>

<script setup>
import { useUiStore } from '@/stores/ui'
const ui = useUiStore()
</script>
