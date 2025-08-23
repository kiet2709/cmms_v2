<template>
  <div class="label-field">
    <input v-model="text" placeholder="Label text" />
    <select v-model="tag">
      <option v-for="n in 6" :key="n" :value="'h'+n">H{{n}}</option>
    </select>
    <label><input type="checkbox" v-model="bold" /> Bold</label>
    <label><input type="checkbox" v-model="italic" /> Italic</label>
    <label><input type="checkbox" v-model="underline" /> Underline</label>
    <component :is="tag" :style="styleObj">{{ text }}</component>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";

const props = defineProps({ config: Object });

const text = ref(props.config.text || "Sample Label");
const tag = ref(props.config.tag || "h3");
const bold = ref(props.config.bold || false);
const italic = ref(props.config.italic || false);
const underline = ref(props.config.underline || false);

const styleObj = computed(() => ({
  fontWeight: bold.value ? "bold" : "normal",
  fontStyle: italic.value ? "italic" : "normal",
  textDecoration: underline.value ? "underline" : "none",
}));

watch([text, tag, bold, italic, underline], () => {
  props.config.text = text.value;
  props.config.tag = tag.value;
  props.config.bold = bold.value;
  props.config.italic = italic.value;
  props.config.underline = underline.value;
});
</script>

<style scoped>
.label-field input, .label-field select {
  margin: 5px 0;
}
.label-field label {
  margin-right: 5px;
}
</style>
