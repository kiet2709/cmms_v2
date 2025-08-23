<template>
  <div style="display: flex; height: 100vh;">
    <!-- Left Section -->
    <div class="left-section" style="width: 40%; padding: 20px;">
      <h2>Machine Information</h2>

      <input v-model="modelId" placeholder="Enter Machine ID" class="input" />
      <input v-model="creator" placeholder="Enter Model" class="input" />
      <input v-model="serialNumber" placeholder="Enter Family" class="input" />
      <input v-model="manufactureDate" placeholder="Enter Manufacture Date" class="input" />
      <input v-model="location" placeholder="Enter Manufacturer" class="input" />
      <input v-model="status" placeholder="Enter History Count" class="input" />
      <input v-model="version" placeholder="Enter Unit" class="input" />

      <select v-model="category" class="input">
        <option disabled value="">Select Category</option>
        <option value="cat1">Mold</option>
        <option value="cat2">Injection</option>
        <option value="cat3">Tuft</option>
      </select>

      <!-- Upload ảnh -->
      <div class="upload-box">
        <input type="file" multiple accept="image/*" @change="handleUpload" />
        <div v-if="images.length" class="upload-actions">
          <button class="btn btn-sm" @click="openImageModal">View Images</button>
        </div>
      </div>


          <!-- Modal xem ảnh -->
    <div v-if="isImageModalOpen" class="modal-overlay">
      <div class="modal-content">
        <button class="modal-close" @click="closeImageModal">×</button>
        <h3>Uploaded Images</h3>
        <div class="image-grid">
          <div v-for="(img, idx) in images" :key="idx" class="img-wrapper">
            <img :src="img" alt="uploaded" />
          </div>
        </div>
      </div>
    </div>


      <button class="btn btn-primary">Save</button>
    </div>

    <!-- Right Section -->
    <div class="right-section">
      <!-- Inspection Code -->
      <div class="field" >
        <label class="field-label">Inspection Code</label>
        <input
          v-model="searchInspection"
          placeholder="Search inspection..."
          class="input"
        />
        <div class="list-box">
          <div
            v-for="item in filteredInspection"
            :key="item.value"
            class="list-item-row"
          >
           <div class="col-left">
              <input
                type="checkbox"
                :id="item.value"
                :value="item.value"
                v-model="selectedInspection"
              />
              <label :for="item.value">{{ item.label }}</label>
            </div>
                  <!-- nút con mắt -->
              <div class="col-right">
                <span class="view-btn" @click="viewItem(item)">
                  <EyeOutlined />
                </span>
              </div>
          </div>
        </div>
            <!-- Modal Overlay -->
        <div v-if="isModalOpen" class="modal-overlay">
          <div class="modal-content">
            <button class="modal-close" @click="closeModal">×</button>
            <FormViewer v-if="isModalOpen" :id="currentId" />
          </div>
        </div>
      </div>

      <!-- Maintenance Levels -->
      <div style="display: flex; gap: 40px; flex: 1; width: 100%;">
        <!-- Level 1 -->
        <div class="field">
          <label class="field-label">Maintenance Level 1</label>
          <input
            v-model="searchM1"
            placeholder="Search level 1..."
            class="input"
          />
          <div class="list-box">
            <div
              v-for="item in filteredM1"
              :key="item.value"
              class="list-item-row"
            >
              <div class="col-left">
                <input
                  type="checkbox"
                  :id="item.value"
                  :value="item.value"
                  v-model="selectedMaintenance1"
                />
                <label :for="item.value">{{ item.label }}</label>
              </div>
              <div class="col-right">
                <span class="view-btn" @click="viewItem(item)">
                  <EyeOutlined />
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Level 2 -->
        <div class="field">
          <label class="field-label">Maintenance Level 2</label>
          <input
            v-model="searchM2"
            placeholder="Search level 2..."
            class="input"
          />
          <div class="list-box">
            <div
              v-for="item in filteredM2"
              :key="item.value"
              class="list-item-row"
            >
              <div class="col-left">
                <input
                  type="checkbox"
                  :id="item.value"
                  :value="item.value"
                  v-model="selectedMaintenance2"
                />
                <label :for="item.value">{{ item.label }}</label>
              </div>
              <div class="col-right">
                <span class="view-btn" @click="viewItem(item)">
                  <EyeOutlined />
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Level 3 -->
        <div class="field">
          <label class="field-label">Maintenance Level 3</label>
          <input
            v-model="searchM3"
            placeholder="Search level 3..."
            class="input"
          />
          <div class="list-box">
            <div
              v-for="item in filteredM3"
              :key="item.value"
              class="list-item-row"
            >
              <div class="col-left">
                <input
                  type="checkbox"
                  :id="item.value"
                  :value="item.value"
                  v-model="selectedMaintenance3"
                />
                <label :for="item.value">{{ item.label }}</label>
              </div>
              <div class="col-right">
                <span class="view-btn" @click="viewItem(item)">
                  <EyeOutlined />
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { EyeOutlined } from "@ant-design/icons-vue";
import FormViewer from "./FormViewer.vue";
import axiosClient from "../utils/axiosClient";

// left section
const modelId = ref("")
const creator = ref("")
const serialNumber = ref("")
const manufactureDate = ref("")
const location = ref("")
const status = ref("")
const version = ref("")
const category = ref("")

// right section - selected values
const selectedInspection = ref([])
const selectedMaintenance1 = ref([])
const selectedMaintenance2 = ref([])
const selectedMaintenance3 = ref([])

// data từ API
const inspectionOptions = ref([])
const m1Options = ref([])
const m2Options = ref([])
const m3Options = ref([])

// search text
const searchInspection = ref("")
const searchM1 = ref("")
const searchM2 = ref("")
const searchM3 = ref("")

// modal state
const isModalOpen = ref(false)
const modalContent = ref("")
const currentId = ref(null)


const viewItem = (item) => {
  currentId.value = item.value
  isModalOpen.value = true
}
const closeModal = () => {
  isModalOpen.value = false
}


// upload state
const images = ref([])
const isImageModalOpen = ref(false)

const handleUpload = (event) => {
  const files = event.target.files
  if (!files.length) return
  // reset ảnh cũ, chỉ giữ bộ mới nhất
  images.value = []
  for (let file of files) {
    const url = URL.createObjectURL(file)
    images.value.push(url)
  }
}

const openImageModal = () => {
  isImageModalOpen.value = true
}

const closeImageModal = () => {
  isImageModalOpen.value = false
}




// call API
const fetchWiData = async () => {
  try {
    const res = await axiosClient.get("", {
      params: {
        c: "WorkingInstructionController",
        m: "getAllWi",
        limit: 100000
      }
    })

    
    

    if (res.data.status === "success") {
      const data = res.data.data

      // clear trước khi push
      inspectionOptions.value = []
      m1Options.value = []
      m2Options.value = []
      m3Options.value = []

      data.forEach(item => {
        const option = {
          value: item.id,
          label: item.name
        }

        switch (item.type) {
          case "Daily Inspection":
            inspectionOptions.value.push(option)
            break
          case "Maintenance Level 1":
            m1Options.value.push(option)
            break
          case "Maintenance Level 2":
            m2Options.value.push(option)
            break
          case "Maintenance Level 3":
            m3Options.value.push(option)
            break
        }
      })
    }
  } catch (error) {
    console.error("Error fetching WI:", error)
  }
}

// gọi khi load component
onMounted(() => {
  fetchWiData()
})






// computed filters
const filteredInspection = computed(() =>
  inspectionOptions.value.filter(o =>
    o.label.toLowerCase().includes(searchInspection.value.toLowerCase())
  )
)

const filteredM1 = computed(() =>
  m1Options.value.filter(o =>
    o.label.toLowerCase().includes(searchM1.value.toLowerCase())
  )
)

const filteredM2 = computed(() =>
  m2Options.value.filter(o =>
    o.label.toLowerCase().includes(searchM2.value.toLowerCase())
  )
)

const filteredM3 = computed(() =>
  m3Options.value.filter(o =>
    o.label.toLowerCase().includes(searchM3.value.toLowerCase())
  )
)
</script>

<style scoped>
.upload-box {
  margin: 10px 0;
}

.upload-actions {
  margin-top: 8px;
}

.image-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 10px;
  margin-top: 10px;
}

.img-wrapper img {
  width: 100%;
  height: 100px;
  object-fit: cover;
  border-radius: 6px;
  border: 1px solid #ddd;
}

.view-btn {
  cursor: pointer;
  font-size: 18px;
  color: #1890ff;
  margin-left: 8px;
  flex-shrink: 0; /* không cho icon co lại */
  display: flex;
  align-items: center;
}



.input {
  width: 100%;
  padding: 8px;
  margin-bottom: 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
}

/* Left section chỉnh flex để nút Save nằm dưới */
.left-section {
  width: 40%;
  padding: 20px;
  display: flex;
  flex-direction: column;
  justify-content: space-between; /* đẩy button xuống dưới */
  height: 95vh; /* chiếm full chiều cao */
}

/* button style giống bootstrap + màu alipay */
.btn {
  display: inline-block;
  font-weight: 500;
  text-align: center;
  white-space: nowrap;
  vertical-align: middle;
  user-select: none;
  border: 1px solid transparent;
  padding: 10px 16px;
  font-size: 16px;
  line-height: 1.5;
  border-radius: 6px;
  transition: all 0.2s;
  cursor: pointer;
}

.btn-primary {
  color: #fff;
  background-color: #2932e1; /* Alipay blue */
  border-color: #2932e1;
}

.btn-primary:hover {
  background-color: #0090d1;
  border-color: #0090d1;
}

.btn-sm {
  background: #f5f5f5;
  border: 1px solid #ccc;
  font-size: 12px;
  padding: 4px 8px;
  border-radius: 4px;
  cursor: pointer;
}


/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
}

.modal-content {
  background: white;
  width: 90%;
  height: 80%;
  border-radius: 12px;
  padding: 20px;
  position: relative;
  overflow: auto;
}

.modal-close {
  position: absolute;
  right: 15px;
  top: 10px;
  border: none;
  background: none;
  font-size: 28px;
  cursor: pointer;
}




.right-section {
  width: 60%;
  padding: 20px;
  display: flex;
  flex-direction: column;
  height: 100vh;
  gap: 20px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
  flex: 1;
}

.field-label {
  font-weight: 600;
}

.list-box {
  flex: 1;
  overflow-y: auto;
  border: 1px solid #d9d9d9;
  border-radius: 6px;
  padding: 10px;
  max-height: 35vh;
  background-color: white;
}

.list-item {
  margin-bottom: 6px;
  display: flex;
  align-items: center;
  gap: 6px;
}









.list-item-row {
  display: grid;
  grid-template-columns: calc(100% - 40px) 40px; /* trái cố định trừ icon, phải 40px */
  align-items: center;
  gap: 10px;
  padding: 6px 0;
  border-bottom: 1px solid #f0f0f0;
}

.col-left {
  display: flex;
  align-items: center;
  gap: 8px;
  overflow: hidden; /* chặn label tràn */
}

.col-left label {
  flex: 1;
  min-width: 0;               /* BẮT BUỘC cho text-overflow khi dùng flex/grid */
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.col-right {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 40px; /* cố định luôn cho chắc */
}


.view-btn {
  cursor: pointer;
  font-size: 18px;
  color: #1890ff;
}



</style>
