<template>
  <v-main
    style="background: linear-gradient(135deg, #0D1B2A 0%, #1A2744 50%, #2E4B8F 100%); min-height: 100vh;"
  >
    <v-container class="fill-height" fluid>
      <v-row align="center" justify="center">
        <v-col cols="12" sm="8" md="5" lg="4">
          <router-view />
        </v-col>
      </v-row>
    </v-container>
  </v-main>
</template>
