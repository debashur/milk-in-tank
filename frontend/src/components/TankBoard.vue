<script setup>
defineProps({
  tanks: { type: Array, required: true },
})

function percent(tank) {
  if (tank.capacity <= 0) {
    return 0
  }

  return Math.round((tank.currentLiters / tank.capacity) * 100)
}
</script>

<template>
  <section class="panel" aria-labelledby="tanks-heading">
    <h2 id="tanks-heading">Цистерны</h2>
    <ul class="tanks">
      <li v-for="tank in tanks" :key="tank.id" class="tank">
        <h3>Цистерна {{ tank.id }}</h3>
        <p class="tank-volume">{{ tank.currentLiters }} из {{ tank.capacity }} л</p>
        <div
          class="meter"
          role="img"
          :aria-label="`Заполнено ${tank.currentLiters} из ${tank.capacity} литров`"
        >
          <span class="meter-fill" :style="{ width: `${percent(tank)}%` }"></span>
        </div>
        <p class="tank-free">Свободно {{ tank.freeLiters }} л</p>
      </li>
    </ul>
  </section>
</template>
