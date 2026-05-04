import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useUiStore = defineStore('ui', () => {
  const sidebarOpen = ref(true)
  const sidebarMini = ref(false)
  const loading = ref(false)
  const snackbar = ref({ show: false, text: '', color: 'success' })
  const lang = ref(localStorage.getItem('app_lang') || 'ru')

  function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value
  }

  function toggleMini() {
    sidebarMini.value = !sidebarMini.value
  }

  function showSnackbar(text, color = 'success') {
    snackbar.value = { show: true, text, color }
  }

  function setLoading(val) {
    loading.value = val
  }

  function setLang(value) {
    lang.value = value
    localStorage.setItem('app_lang', value)
  }

  return { sidebarOpen, sidebarMini, loading, snackbar, lang, toggleSidebar, toggleMini, showSnackbar, setLoading, setLang }
})
