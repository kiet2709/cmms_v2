<template>
  <div class="image-upload">
    <label>{{ label }}</label>
    <input type="file" @change="onFileChange" />
    <div v-if="imgSrc">
      <img :src="imgSrc" alt="User Upload" style="max-width:100%; border:1px solid #ccc;" />
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
const props = defineProps({ config: Object });
const label = ref(props.config.label || "Upload Image");
const imgSrc = ref(props.config.src || "");

function onFileChange(e) {
  const file = e.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = (ev) => {
    imgSrc.value = ev.target.result;
  };
  reader.readAsDataURL(file);
}

watch([imgSrc, label], () => {
  props.config.src = imgSrc.value;
  props.config.label = label.value;
});
</script>

<style scoped>
.image-upload input { margin: 5px 0; }
</style>
