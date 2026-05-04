<template>
  <v-layout>
    <AppSidebar />
    <AppHeader />
    <v-main style="background: #F4F6F9; min-height: 100vh;">
      <v-container fluid class="pa-6">
        <router-view />
      </v-container>
    </v-main>
  </v-layout>
</template>

<script setup>
import AppSidebar from '@/components/common/AppSidebar.vue'
import AppHeader from '@/components/common/AppHeader.vue'
</script>
