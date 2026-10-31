<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  submitting: { type: Boolean, default: false },
  error: { type: String, default: '' },
  savedToken: { type: Number, default: 0 },
})

const emit = defineEmits(['submit', 'change'])

const personName = ref('')
const liters = ref('')
const localError = ref('')

watch(
  () => props.savedToken,
  () => {
    personName.value = ''
    liters.value = ''
    localError.value = ''
  },
)

watch([personName, liters], () => {
  localError.value = ''
  emit('change')
})

function onSubmit() {
  localError.value = ''
  const name = personName.value.trim()
  const amount = Number(liters.value)

  if (name.length < 1 || name.length > 100) {
    localError.value = 'Имя должно быть от 1 до 100 символов.'
    return
  }

  if (!Number.isInteger(amount) || amount < 1 || amount > 300) {
    localError.value = 'Количество молока — целое число от 1 до 300.'
    return
  }

  emit('submit', { personName: name, liters: amount })
}
</script>

<template>
  <form class="pour-form" @submit.prevent="onSubmit">
    <div class="field">
      <label for="person-name">Кто залил</label>
      <input
        id="person-name"
        v-model="personName"
        name="personName"
        type="text"
        maxlength="100"
        autocomplete="name"
      />
    </div>
    <div class="field field-liters">
      <label for="liters">Литры</label>
      <input
        id="liters"
        v-model="liters"
        name="liters"
        type="number"
        inputmode="numeric"
        step="1"
      />
    </div>
    <button type="submit" :disabled="submitting">
      {{ submitting ? 'Заливаю…' : 'Залить' }}
    </button>
    <p v-if="localError || error" class="form-error" role="alert">
      {{ localError || error }}
    </p>
  </form>
</template>
