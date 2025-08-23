<template>
  <div class="yesno-question">
    <input v-model="question" placeholder="Question text" />
    <div>
      <label><input type="radio" value="Yes" v-model="answer" /> Yes</label>
      <label><input type="radio" value="No" v-model="answer" /> No</label>
    </div>
    <p><strong>Preview:</strong> {{ question }}</p>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";

const props = defineProps({ config: Object });
const question = ref(props.config.question || "Yes/No Question?");
const answer = ref("");

watch([question], () => {
  props.config.question = question.value;
});
</script>

<style scoped>
.yesno-question input[type="text"] { width: 100%; margin-bottom:5px; }
.yesno-question label { margin-right: 10px; }
</style>
