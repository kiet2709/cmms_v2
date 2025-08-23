<template>
  <div class="viewer-container">
    <h2>Loaded Form</h2>

    <div v-if="loading">Đang tải form...</div>
    <div v-else-if="formItems.length === 0">
      <p>Chưa có form nào được lưu!</p>
    </div>
    <div v-else>
      <div
        v-for="(item, index) in formItems"
        :key="index"
        class="form-section"
      >
        <!-- Render Label -->
        <component
          v-if="item.type === 'label'"
          :is="item.heading"
          :style="{
            fontWeight: item.bold ? 'bold' : 'normal',
            fontStyle: item.italic ? 'italic' : 'normal',
            textDecoration: item.underline ? 'underline' : 'none'
          }"
        >
          {{ item.text }}
        </component>

        <!-- Render Yes/No -->
        <div v-else-if="item.type === 'yesno'">
          <p>{{ item.question }}</p>
          <label><input type="radio" :name="'yn' + index" value="Yes" /> Yes</label>
          <label><input type="radio" :name="'yn' + index" value="No" /> No</label>
        </div>

        <!-- Render Multiple Choice -->
        <div v-else-if="item.type === 'multiple'">
          <p>{{ item.question }}</p>
          <div v-for="(opt, i) in item.options.split(',')" :key="i">
            <label><input type="checkbox" /> {{ opt.trim() }}</label>
          </div>
        </div>

        <!-- Render Single Choice -->
        <div v-else-if="item.type === 'single'">
          <p>{{ item.question }}</p>
          <div v-for="(opt, i) in item.options.split(',')" :key="i">
            <label><input type="radio" :name="'single' + index" /> {{ opt.trim() }}</label>
          </div>
        </div>

        <!-- Render Static Image -->
        <div v-else-if="item.type === 'staticImage'">
          <img v-if="item.imageUrl" :src="item.imageUrl" class="thumb" />
        </div>

        <!-- Render User Image Upload -->
        <div v-else-if="item.type === 'userImage'">
          <input type="file" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue"
import axiosClient from "../utils/axiosClient"

// nhận id từ props
const props = defineProps({
  id: {
    type: [String, Number],
    required: true
  }
})

const formItems = ref([])
const loading = ref(false)

const fetchForm = async (id) => {
  try {
    loading.value = true
    const res = await axiosClient.get("", {
      params: {
        c: "WorkingInstructionController",
        m: "getWiById",
        id // truyền id vào API
      }
    })

    if (res.data.status === "success" && res.data.data.length > 0) {
      const schema = res.data.data[0].schema
      formItems.value = JSON.parse(schema)
    } else {
      formItems.value = []
    }
  } catch (err) {
    console.error("Error fetching form:", err)
    formItems.value = []
  } finally {
    loading.value = false
  }
}

// khi props.id thay đổi → gọi API mới
watch(
  () => props.id,
  (newId) => {
    if (newId) fetchForm(newId)
  },
  { immediate: true }
)
</script>

<style>
.viewer-container {
  max-width: 900px;
  margin: auto;
  padding: 24px;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  background: #fdfdfd;
  border-radius: 12px;
}

.form-section {
  padding: 16px 20px;
  margin-bottom: 20px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  transition: box-shadow 0.2s ease;
}

.form-section:hover {
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
}

.form-section p {
  margin-bottom: 12px;
  font-size: 16px;
  font-weight: 500;
  color: #333;
}

.form-section label {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
  font-size: 15px;
  color: #444;
  cursor: pointer;
}

.form-section input[type="radio"],
.form-section input[type="checkbox"] {
  transform: scale(1.1);
  cursor: pointer;
}

.thumb {
  max-width: 100%;
  border-radius: 6px;
  margin-top: 10px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

h2 {
  font-size: 22px;
  font-weight: 600;
  margin-bottom: 20px;
  text-align: center;
  color: #222;
}
</style>
