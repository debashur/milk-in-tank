<script setup>
defineProps({
  pours: { type: Array, required: true },
})
</script>

<template>
  <section class="panel" aria-labelledby="journal-heading">
    <h2 id="journal-heading">Журнал</h2>
    <p v-if="pours.length === 0" class="empty">Заливок пока нет.</p>
    <div v-else class="table-wrap">
      <table aria-labelledby="journal-heading">
        <thead>
          <tr>
            <th>Когда</th>
            <th>Кто</th>
            <th>Литры</th>
            <th>Цистерна</th>
            <th>Сейчас в цистерне</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="pour in pours" :key="pour.id">
            <td>{{ pour.createdAt }}</td>
            <td>{{ pour.personName }}</td>
            <td>{{ pour.liters }}</td>
            <td>{{ pour.tankId }}</td>
            <td>{{ pour.tankCurrentLiters }} л</td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>
