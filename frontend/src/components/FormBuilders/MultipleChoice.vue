<template>
  <div class="multiple-choice">
    <input v-model="question" placeholder="Question text" />
    <textarea v-model="optionsStr" placeholder="Options separated by comma"></textarea>
    <div>
      <p><strong>Preview:</strong> {{ question }}</p>
      <label v-for="opt in options" :key="opt">
        <input type="checkbox" :value="opt" v-model="selected" /> {{ opt }}
      </label>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";

const props = defineProps({ config: Object });
const question = ref(props.config.question || "Multiple Choice Question?");
const optionsStr = ref(props.config.optionsStr || "Option 1,Option 2");
const selected = ref([]);

const options = computed(() => optionsStr.value.split(",").map(o => o.trim()));

watch([question, optionsStr], () => {
  props.config.question = question.value;
  props.config.optionsStr = optionsStr.value;
});
</script>

<style scoped>
.multiple-choice input, .multiple-choice textarea { width: 100%; margin: 5px 0; }
.multiple-choice label { display:block; margin: 2px 0; }
</style>
