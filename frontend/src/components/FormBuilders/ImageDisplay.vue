<template>
  <div class="image-display">
    <input type="file" @change="onFileChange" />
    <div v-if="imgSrc">
      <p><strong>Preview:</strong></p>
      <img :src="imgSrc" alt="Uploaded" style="max-width:100%; border:1px solid #ccc;" />
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
const props = defineProps({ config: Object });

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

watch(imgSrc, () => {
  props.config.src = imgSrc.value;
});
</script>

<style scoped>
.image-display input { margin-bottom: 5px; }
</style>
