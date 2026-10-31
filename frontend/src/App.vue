<script setup>
import { onMounted, ref } from 'vue'
import { createPour, fetchPours, fetchTanks } from './api.js'
import PourForm from './components/PourForm.vue'
import PourJournal from './components/PourJournal.vue'
import TankBoard from './components/TankBoard.vue'

const tanks = ref([])
const pours = ref([])
const loading = ref(true)
const loadError = ref('')
const submitError = ref('')
const submitting = ref(false)
const savedToken = ref(0)

async function load() {
  loading.value = true
  loadError.value = ''
  try {
    const [tankList, pourList] = await Promise.all([fetchTanks(), fetchPours()])
    tanks.value = tankList
    pours.value = pourList
  } catch (error) {
    loadError.value = error.message
  } finally {
    loading.value = false
  }
}

async function onSubmit(payload) {
  submitting.value = true
  submitError.value = ''
  try {
    const result = await createPour(payload.personName, payload.liters)
    pours.value = [result.pour, ...pours.value]
    tanks.value = result.tanks
    savedToken.value += 1
  } catch (error) {
    submitError.value = error.message
  } finally {
    submitting.value = false
  }
}

onMounted(load)
</script>

<template>
  <main class="page">
    <header class="page-header">
      <h1>Учёт молока</h1>
      <p>Порция целиком попадает в цистерну, где молока меньше всего.</p>
    </header>

    <p v-if="loading" class="status" role="status">Загружаю данные…</p>
    <p v-else-if="loadError" class="form-error" role="alert">
      {{ loadError }}
      <button type="button" class="retry" @click="load">Повторить</button>
    </p>
    <template v-else>
      <PourForm
        :submitting="submitting"
        :error="submitError"
        :saved-token="savedToken"
        @submit="onSubmit"
        @change="submitError = ''"
      />
      <TankBoard :tanks="tanks" />
      <PourJournal :pours="pours" />
    </template>
  </main>
</template>
