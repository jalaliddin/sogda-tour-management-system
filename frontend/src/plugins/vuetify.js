import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'
import { createVuetify } from 'vuetify'
import { aliases, mdi } from 'vuetify/iconsets/mdi'

export default createVuetify({
  icons: {
    defaultSet: 'mdi',
    aliases,
    sets: { mdi },
  },
  theme: {
    defaultTheme: 'sogdaTour',
    themes: {
      sogdaTour: {
        dark: false,
        colors: {
          primary: '#1A2744',
          secondary: '#2E4B8F',
          accent: '#4A90D9',
          success: '#2E7D32',
          warning: '#F57C00',
          error: '#C62828',
          info: '#1565C0',
          background: '#F4F6F9',
          surface: '#FFFFFF',
          'surface-variant': '#EEF2F7',
          'on-primary': '#FFFFFF',
          'on-secondary': '#FFFFFF',
        },
      },
    },
  },
  defaults: {
    VCard: { elevation: 1, rounded: 'lg' },
    VBtn: { rounded: 'lg' },
    VTextField: { variant: 'outlined', density: 'comfortable' },
    VSelect: { variant: 'outlined', density: 'comfortable' },
    VAutocomplete: { variant: 'outlined', density: 'comfortable' },
    VTextarea: { variant: 'outlined', density: 'comfortable' },
    VDataTable: { density: 'comfortable' },
    VChip: { density: 'comfortable' },
  },
})
