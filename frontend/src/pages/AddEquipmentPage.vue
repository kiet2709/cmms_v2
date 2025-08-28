<template>
  <div class="page-container" style="margin-top: 16px;">
    <!-- Left Section - Fixed -->
    <div class="left-section">
      <div class="left-content">
        <h2 class="section-title">Machine Information</h2>

        <div class="form-group">
          <label class="form-label">Machine ID</label>
          <input v-model="modelId" placeholder="Enter Machine ID" class="input" />
        </div>

        <div class="form-group">
          <label class="form-label">Model</label>
          <input v-model="creator" placeholder="Enter Model" class="input" />
        </div>

        <div class="form-group">
          <label class="form-label">Family</label>
          <input v-model="serialNumber" placeholder="Enter Family" class="input" />
        </div>

        <div class="form-group">
          <label class="form-label">Manufacture Date</label>
          <input v-model="manufactureDate" placeholder="YYYY-MM-DD" class="input" />
        </div>

        <div class="form-group">
          <label class="form-label">Manufacturer</label>
          <input v-model="location" placeholder="Enter Manufacturer" class="input" />
        </div>

        <div class="form-group">
          <label class="form-label">History Count</label>
          <input v-model="status" placeholder="Enter History Count" class="input" />
        </div>

        <div class="form-group">
          <label class="form-label">Unit</label>
          <input v-model="version" placeholder="Enter Unit" class="input" />
        </div>

        <div class="form-group">
          <label class="form-label">Cavity</label>
          <input v-model="cavity" placeholder="Enter Cavity" class="input" />
        </div>

        <div class="form-group">
          <label class="form-label">Category</label>
          <select v-model="category" class="input" @change="handleSelect">
            <option disabled value="">Select Category</option>
            <option v-for="item in categories" :key="item.id" :value="item.id">{{ item.name }}</option>
          </select>
        </div>

        <button class="btn btn-primary save-btn" @click="createMachine">Save</button>
      </div>
    </div>

    <!-- Right Section - Scrollable -->
    <div class="right-section">
      <div class="grid-horizontal">
        <!-- Inspection -->
        <div class="field">
          <label class="field-label">Daily Inspection</label>
          <input v-model="searchInspection" placeholder="Search inspection..." class="input" />
          <div class="list-box">
            <div v-for="item in filteredInspection" :key="item.value" class="list-item-row">
              <div class="col-left">
                <input type="checkbox" :id="item.value" :value="item.value" v-model="selectedInspection" />
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

        <!-- Maintenance Level 1 -->
        <div class="field">
          <label class="field-label">Maintenance Level 1</label>
          <input v-model="searchM1" placeholder="Search level 1..." class="input" />
          <div class="list-box">
            <div v-for="item in filteredM1" :key="item.value" class="list-item-row">
              <div class="col-left">
                <input type="checkbox" :id="item.value" :value="item.value" v-model="selectedMaintenance1" />
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

        <!-- Maintenance Level 2 -->
        <div class="field">
          <label class="field-label">Maintenance Level 2</label>
          <input v-model="searchM2" placeholder="Search level 2..." class="input" />
          <div class="list-box">
            <div v-for="item in filteredM2" :key="item.value" class="list-item-row">
              <div class="col-left">
                <input type="checkbox" :id="item.value" :value="item.value" v-model="selectedMaintenance2" />
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

        <!-- Maintenance Level 3 -->
        <div class="field">
          <label class="field-label">Maintenance Level 3</label>
          <input v-model="searchM3" placeholder="Search level 3..." class="input" />
          <div class="list-box">
            <div v-for="item in filteredM3" :key="item.value" class="list-item-row">
              <div class="col-left">
                <input type="checkbox" :id="item.value" :value="item.value" v-model="selectedMaintenance3" />
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

    <!-- Modal Overlay -->
    <div v-if="isModalOpen" class="modal-overlay">
      <div class="modal-content">
        <button class="modal-close" @click="closeModal">×</button>
        <FormViewer v-if="isModalOpen" :id="currentId" />
      </div>
    </div>

    <!-- Toast Notifications -->
    <div v-if="showToast" :class="['toast', toastType]">
      <div class="toast-content">
        <span class="toast-icon">{{ toastType === 'success' ? '✓' : '✗' }}</span>
        <span class="toast-message">{{ toastMessage }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { EyeOutlined } from "@ant-design/icons-vue";
import FormViewer from "./FormViewer.vue";
import axiosClient from "../utils/axiosClient";

// left
const modelId = ref("");
const creator = ref("");
const serialNumber = ref("");
const manufactureDate = ref("");
const location = ref("");
const status = ref("");
const version = ref("");
const category = ref("");
const categories = ref([]);
const cavity = ref("");

// right - selections
const selectedInspection = ref([]);
const selectedMaintenance1 = ref([]);
const selectedMaintenance2 = ref([]);
const selectedMaintenance3 = ref([]);

// options
const inspectionOptions = ref([]);
const m1Options = ref([]);
const m2Options = ref([]);
const m3Options = ref([]);

// search
const searchInspection = ref("");
const searchM1 = ref("");
const searchM2 = ref("");
const searchM3 = ref("");

// modal
const isModalOpen = ref(false);
const currentId = ref(null);

// toast
const showToast = ref(false);
const toastType = ref('success');
const toastMessage = ref('');

const viewItem = (item) => {
  currentId.value = item.value;
  isModalOpen.value = true;
};
const closeModal = () => {
  isModalOpen.value = false;
};

// toast functions
const showToastMessage = (type, message) => {
  toastType.value = type;
  toastMessage.value = message;
  showToast.value = true;
  setTimeout(() => {
    showToast.value = false;
  }, 3000);
};

// upload
const images = ref([]);
const handleUpload = (event) => {
  const files = event.target.files;
  if (!files.length) return;
  images.value = [];
  for (let file of files) {
    const url = URL.createObjectURL(file);
    images.value.push(url);
  }
};
const openImageModal = () => {};
const closeImageModal = () => {};

function handleSelect(event) {
  // console.log("Selected ID:", category.value); // chính là item.id
  // console.log("Selected name:", categories.value.find(c => c.id === category.value)?.name);
  fetchWiByCategoryID(category.value);
}

const createMachine = async () => {
  try {
    const payload = {
      data: {
        machineId: modelId.value,
        model: creator.value,
        family: serialNumber.value,
        manufactureDate: manufactureDate.value,
        manufacturer: location.value,
        historyCount: status.value,
        unit: version.value,
        category: category.value,
        cavity: cavity.value,

        // checkbox selections
        inspectionCodes: selectedInspection.value,
        maintenanceLevel1: selectedMaintenance1.value,
        maintenanceLevel2: selectedMaintenance2.value,
        maintenanceLevel3: selectedMaintenance3.value,
      },
    };

    // console.log("Posting payload:", payload);

    // const res = await axiosClient.post("", payload);

    await axiosClient.post('', {}, {
        params: {
          c: 'EquipmentController',
          m: 'createEquipment',
          payload
        },
      });
    
    // console.log("API response:", res.data);
    showToastMessage('success', 'Machine created successfully!');
    
    // Reset form after success
    modelId.value = "";
    creator.value = "";
    serialNumber.value = "";
    manufactureDate.value = "";
    location.value = "";
    status.value = "";
    version.value = "";
    category.value = "";
    cavity.value = "";
    selectedInspection.value = [];
    selectedMaintenance1.value = [];
    selectedMaintenance2.value = [];
    selectedMaintenance3.value = [];
    
  } catch (e) {
    console.error("Error creating machine:", e);
    showToastMessage('error', 'Failed to create machine. Please try again.');
  }
};

const fetchCategoryData = async () => {
    try {
    const res = await axiosClient.get("", {
      params: { c: "CategoryController", m: "getAllCategories", limit: 100000 },
    });
    if (res.data.status === "success") {
      categories.value = res.data.data;
      console.log('category: ' +categories.value[0].name);
      
    }
  } catch (e) {
    console.error("Error fetching Category:", e);
  }
}

const fetchWiByCategoryID = async (id) => {
  try {
    const res = await axiosClient.get("", {
      params: { c: "WorkingInstructionController", m: "getWiByCategoryId", category_id: id },
    });
    if (res.data.status === "success") {
      const data = res.data.data;
      console.log(data);
      
      inspectionOptions.value = [];
      m1Options.value = [];
      m2Options.value = [];
      m3Options.value = [];
      data.forEach((item) => {
        const option = { value: item.id, label: item.code };
        switch (item.type) {
          case "Daily Inspection":
            inspectionOptions.value.push(option);
            break;
          case "Maintenance Level 1":
            m1Options.value.push(option);
            break;
          case "Maintenance Level 2":
            m2Options.value.push(option);
            break;
          case "Maintenance Level 3":
            m3Options.value.push(option);
            break;
        }
      });
    }
  } catch (e) {
    console.error("Error fetching WI:", e);
  }
};

// fetch data
const fetchWiData = async () => {
  try {
    const res = await axiosClient.get("", {
      params: { c: "WorkingInstructionController", m: "getAllWi", limit: 100000 },
    });
    if (res.data.status === "success") {
      const data = res.data.data;
      inspectionOptions.value = [];
      m1Options.value = [];
      m2Options.value = [];
      m3Options.value = [];
      data.forEach((item) => {
        const option = { value: item.id, label: item.code };
        switch (item.type) {
          case "Daily Inspection":
            inspectionOptions.value.push(option);
            break;
          case "Maintenance Level 1":
            m1Options.value.push(option);
            break;
          case "Maintenance Level 2":
            m2Options.value.push(option);
            break;
          case "Maintenance Level 3":
            m3Options.value.push(option);
            break;
        }
      });
    }
  } catch (e) {
    console.error("Error fetching WI:", e);
  }
};

onMounted(() => {
  fetchWiData();
  fetchCategoryData();
});

// filters
const filteredInspection = computed(() =>
  inspectionOptions.value.filter((o) =>
    o.label.toLowerCase().includes(searchInspection.value.toLowerCase())
  )
);
const filteredM1 = computed(() =>
  m1Options.value.filter((o) =>
    o.label.toLowerCase().includes(searchM1.value.toLowerCase())
  )
);
const filteredM2 = computed(() =>
  m2Options.value.filter((o) =>
    o.label.toLowerCase().includes(searchM2.value.toLowerCase())
  )
);
const filteredM3 = computed(() =>
  m3Options.value.filter((o) =>
    o.label.toLowerCase().includes(searchM3.value.toLowerCase())
  )
);
</script>

<style scoped>
.page-container {
  display: flex;
  height: 100vh;
  overflow: hidden;
  background: #f5f6fa;
  font-family: "Segoe UI", Roboto, sans-serif;
}

/* LEFT SECTION - FIXED */
.left-section {
  width: 20%;
  background: #fff;
  border-right: 1px solid #eee;
  box-shadow: 2px 0 6px rgba(0, 0, 0, 0.05);
  position: fixed;
  height: 100vh;
  left: 0;
  top: 0;
  z-index: 100;
}

.left-content {
  margin-top: 80px;
  padding: 20px;
  height: 100%;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 9px;
}

.section-title {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 8px;
  padding-bottom: 6px;
  border-bottom: 2px solid #1890ff;
  color: #333;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.form-label {
  font-size: 13px;
  font-weight: 500;
  color: #555;
}

.input {
  width: 100%;
  padding: 8px 10px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s ease;
}
.input:focus {
  border-color: #1890ff;
  outline: none;
  box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.15);
}

.save-btn {
  margin-top: 12px;
  width: 100%;
  padding: 10px;
  border: none;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 600;
  color: #fff;
  cursor: pointer;
  background: linear-gradient(90deg, #1890ff, #40a9ff);
  box-shadow: 0 2px 6px rgba(24, 144, 255, 0.4);
  transition: 0.25s;
}
.save-btn:hover {
  opacity: 0.9;
}

/* RIGHT SECTION - SCROLLABLE */
.right-section {
  margin-left: 20%;
  width: 80%;
  height: 100vh;
  overflow-y: auto;
  padding: 20px;
}

.grid-horizontal {
  display: flex;
  flex-direction: column;
  gap: 15px;
  min-height: 100%;
}

.field {
  background: #fff;
  padding: 20px;
  border-radius: 10px;
  border: 1px solid #eee;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  min-height: 200px;
  flex-shrink: 0;
}

.field-label {
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 10px;
  color: #333;
}

.list-box {
  height: 140px;
  overflow-y: auto;
  border: 1px solid #eaeaea;
  border-radius: 6px;
  padding: 8px;
  background: #fafafa;
  margin-top: 8px;
}

.list-item-row {
  display: grid;
  grid-template-columns: calc(100% - 40px) 40px;
  align-items: center;
  gap: 8px;
  padding: 6px 4px;
  border-bottom: 1px solid #f0f0f0;
  transition: background 0.2s;
}
.list-item-row:hover {
  background: #f9f9f9;
  border-radius: 4px;
}

.col-left {
  display: flex;
  align-items: center;
  gap: 6px;
  overflow: hidden;
}
.col-left label {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: 13px;
  color: #444;
  cursor: pointer;
}

.col-right {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 40px;
}

.view-btn {
  cursor: pointer;
  font-size: 18px;
  color: #1890ff;
  transition: 0.2s;
}
.view-btn:hover {
  color: #40a9ff;
  transform: scale(1.1);
}

/* MODAL */
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
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
}
.modal-close {
  position: absolute;
  right: 15px;
  top: 10px;
  border: none;
  background: none;
  font-size: 28px;
  cursor: pointer;
  color: #555;
}
.modal-close:hover {
  color: #000;
}

/* TOAST NOTIFICATIONS */
.toast {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 16px 20px;
  border-radius: 8px;
  color: white;
  font-weight: 500;
  z-index: 3000;
  min-width: 300px;
  transform: translateX(100%);
  animation: slideIn 0.3s ease forwards;
}

.toast.success {
  background: linear-gradient(135deg, #52c41a, #73d13d);
  box-shadow: 0 4px 12px rgba(82, 196, 26, 0.3);
}

.toast.error {
  background: linear-gradient(135deg, #ff4d4f, #ff7875);
  box-shadow: 0 4px 12px rgba(255, 77, 79, 0.3);
}

.toast-content {
  display: flex;
  align-items: center;
  gap: 10px;
}

.toast-icon {
  font-size: 16px;
  font-weight: bold;
}

.toast-message {
  font-size: 14px;
}

@keyframes slideIn {
  to {
    transform: translateX(0);
  }
}

/* Responsive adjustments */
@media (max-width: 1200px) {
  .left-section {
    width: 25%;
  }
  .right-section {
    margin-left: 25%;
    width: 75%;
  }
}

@media (max-width: 768px) {
  .left-section {
    width: 30%;
  }
  .right-section {
    margin-left: 30%;
    width: 70%;
  }
}
</style>