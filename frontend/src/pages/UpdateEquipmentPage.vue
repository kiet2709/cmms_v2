<template>
  <div class="app-container">
    <div class="app-header">
      <div class="header-left">
        <h1 class="app-title" v-translate>Update Equipment</h1>
        <div class="breadcrumb">
          <span class="link-span" @click="$router.push('/dashboard/equipments')">Equipment</span>
          <span class="separator">›</span>
          <span class="current">Update Equipment</span>
        </div>
      </div>
    </div>
  </div>
  <div class="page-container" style="margin-top: 16px;">
    <!-- Left Section -->
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

        <!-- <div class="form-group">
          <label class="form-label">Manufacture Date</label>
          <input v-model="manufactureDate" placeholder="YYYY-MM-DD" class="input" />
        </div> -->

        <div class="form-group">
          <label class="form-label">Manufacture Date</label>
          <a-date-picker
            v-model:value="manufactureDate"
            format="YYYY-MM-DD"
            value-format="YYYY-MM-DD"
            placeholder="YYYY-MM-DD"
            class="input"
          />
          <!-- <input v-model="manufactureDate" placeholder="YYYY-MM-DD" class="input" /> -->
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
          <select v-model="category" class="input"  @change="handleSelect">
            <option disabled value="">Select Category</option>
            <option v-for="item in categories" :key="item.id" :value="item.id">{{ item.name }}</option>
          </select>
        </div>
        <div class="action-buttons">
          <button class="btn cancel-btn" @click="cancelUpdate">Cancel</button> 
          <button class="btn save-btn" @click="updateMachine">Update</button>
        </div>
      </div>
    </div>

    <!-- Right Section -->
    <div class="right-section">
      <div class="grid-horizontal">
        <!-- Daily Inspection Table -->
        <div class="field">
          <div class="table-header">
            <h3 class="table-title">
              Daily Inspection
              <span class="selected-count">({{ selectedInspection.length }} selected)</span>
            </h3>
            <div class="search-container">
              <input 
                v-model="searchInspection" 
                placeholder="Search inspections..." 
                class="table-search"
              />
            </div>
          </div>
          
          <div class="modern-table-container">
            <table class="modern-table">
              <thead>
                <tr>
                  <th class="checkbox-col">
                    <input 
                      type="checkbox" 
                      :checked="isAllSelected('inspection')"
                      @change="toggleSelectAll('inspection')"
                      class="table-checkbox header-checkbox"
                    />
                  </th>
                  <th>Task Code</th>
                  <th>Description</th>
                  <th>Frequency</th>
                  <th class="action-col">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in filteredInspection" :key="item.value" class="table-row">
                  <td class="checkbox-col">
                    <input 
                      type="checkbox" 
                      :id="'inspection-' + item.value" 
                      :value="item.value" 
                      v-model="selectedInspection"
                      class="table-checkbox"
                    />
                  </td>
                  <td class="task-code">{{ item.label ?? '' }}</td>
                  <td class="description">{{ item.description ?? '' }}</td>
                  <td class="frequency">
                    <span class="frequency-badge">{{ item.frequency ?? '' }}</span>
                  </td>
                  <td class="action-col">
                    <button class="view-btn" @click="viewItem(item)" title="View Details">
                      <EyeOutlined />
                    </button>
                  </td>
                </tr>
                <tr v-if="filteredInspection.length === 0" class="no-data-row">
                  <td colspan="5" class="no-data">No inspection tasks found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Maintenance Level 1 Table -->
        <div class="field">
          <div class="table-header">
            <h3 class="table-title">
              Maintenance Level 1
              <span class="selected-count">({{ selectedMaintenance1.length }} selected)</span>
            </h3>
            <div class="search-container">
              <input 
                v-model="searchM1" 
                placeholder="Search level 1..." 
                class="table-search"
              />
            </div>
          </div>
          
          <div class="modern-table-container">
            <table class="modern-table">
              <thead>
                <tr>
                  <th class="checkbox-col">
                    <input 
                      type="checkbox" 
                      :checked="isAllSelected('m1')"
                      @change="toggleSelectAll('m1')"
                      class="table-checkbox header-checkbox"
                    />
                  </th>
                  <th>Task Code</th>
                  <th>Description</th>
                  <th>Frequency</th>
                  <th class="action-col">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in filteredM1" :key="item.value" class="table-row">
                  <td class="checkbox-col">
                    <input 
                      type="checkbox" 
                      :id="'m1-' + item.value" 
                      :value="item.value" 
                      v-model="selectedMaintenance1"
                      class="table-checkbox"
                    />
                  </td>
                  <td class="task-code">{{ item.label ?? '' }}</td>
                  <td class="description">{{ item.description ?? '' }}</td>
                  <td class="frequency">
                    <span class="frequency-badge">{{ item.frequency ?? '' }}</span>
                  </td>
                  <td class="action-col">
                    <button class="view-btn" @click="viewItem(item)" title="View Details">
                      <EyeOutlined />
                    </button>
                  </td>
                </tr>
                <tr v-if="filteredM1.length === 0" class="no-data-row">
                  <td colspan="5" class="no-data">No maintenance level 1 tasks found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Maintenance Level 2 Table -->
        <div class="field">
          <div class="table-header">
            <h3 class="table-title">
              Maintenance Level 2
              <span class="selected-count">({{ selectedMaintenance2.length }} selected)</span>
            </h3>
            <div class="search-container">
              <input 
                v-model="searchM2" 
                placeholder="Search level 2..." 
                class="table-search"
              />
            </div>
          </div>
          
          <div class="modern-table-container">
            <table class="modern-table">
              <thead>
                <tr>
                  <th class="checkbox-col">
                    <input 
                      type="checkbox" 
                      :checked="isAllSelected('m2')"
                      @change="toggleSelectAll('m2')"
                      class="table-checkbox header-checkbox"
                    />
                  </th>
                  <th>Task Code</th>
                  <th>Description</th>
                  <th>Frequency</th>
                  <th class="action-col">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in filteredM2" :key="item.value" class="table-row">
                  <td class="checkbox-col">
                    <input 
                      type="checkbox" 
                      :id="'m2-' + item.value" 
                      :value="item.value" 
                      v-model="selectedMaintenance2"
                      class="table-checkbox"
                    />
                  </td>
                  <td class="task-code">{{ item.label ?? '' }}</td>
                  <td class="description">{{ item.description ?? '' }}</td>
                  <td class="frequency">
                    <span class="frequency-badge">{{ item.frequency ?? '' }}</span>
                  </td>
                  <td class="action-col">
                    <button class="view-btn" @click="viewItem(item)" title="View Details">
                      <EyeOutlined />
                    </button>
                  </td>
                </tr>
                <tr v-if="filteredM2.length === 0" class="no-data-row">
                  <td colspan="5" class="no-data">No maintenance level 2 tasks found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Maintenance Level 3 Table -->
        <div class="field">
          <div class="table-header">
            <h3 class="table-title">
              Maintenance Level 3
              <span class="selected-count">({{ selectedMaintenance3.length }} selected)</span>
            </h3>
            <div class="search-container">
              <input 
                v-model="searchM3" 
                placeholder="Search level 3..." 
                class="table-search"
              />
            </div>
          </div>
          
          <div class="modern-table-container">
            <table class="modern-table">
              <thead>
                <tr>
                  <th class="checkbox-col">
                    <input 
                      type="checkbox" 
                      :checked="isAllSelected('m3')"
                      @change="toggleSelectAll('m3')"
                      class="table-checkbox header-checkbox"
                    />
                  </th>
                  <th>Task Code</th>
                  <th>Description</th>
                  <th>Frequency</th>
                  <th class="action-col">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in filteredM3" :key="item.value" class="table-row">
                  <td class="checkbox-col">
                    <input 
                      type="checkbox" 
                      :id="'m3-' + item.value" 
                      :value="item.value" 
                      v-model="selectedMaintenance3"
                      class="table-checkbox"
                    />
                  </td>
                  <td class="task-code">{{ item.label ?? '' }}</td>
                  <td class="description">{{ item.description ?? '' }}</td>
                  <td class="frequency">
                    <span class="frequency-badge">{{ item.frequency ?? '' }}</span>
                  </td>
                  <td class="action-col">
                    <button class="view-btn" @click="viewItem(item)" title="View Details">
                      <EyeOutlined />
                    </button>
                  </td>
                </tr>
                <tr v-if="filteredM3.length === 0" class="no-data-row">
                  <td colspan="5" class="no-data">No maintenance level 3 tasks found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Overlay -->
    <!-- Modal -->
    <Modal 
      v-model:open="isModalOpen" 
      :title="code" 
      @cancel="closeModal" 
      :style="{ top: '3px'}"
      width="800px" :footer="null">
      <FormViewer v-if="isModalOpen" :key="currentId" :id="currentId" />
    </Modal>

    <!-- Toast Notification -->
    <div v-if="showToast" :class="['toast', toastType]">
      <div class="toast-content">
        <span class="toast-icon">{{ toastIcon }}</span>
        <span class="toast-message">{{ toastMessage }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { 
  Modal, 
} from 'ant-design-vue';
import { EyeOutlined } from "@ant-design/icons-vue";
import FormViewer from "./FormViewer.vue";
import axiosClient from "../utils/axiosClient";
import { useRoute, useRouter } from 'vue-router'
import dayjs from 'dayjs'

const route = useRoute()
const router = useRouter()
const uuid = route.params.uuid;
const code = ref('');
// left
const modelId = ref("");
const creator = ref("");
const serialNumber = ref("");
const manufactureDate = ref(null);
const location = ref("");
const status = ref("");
const version = ref("");
const category = ref("");
const cavity = ref("");
const categories = ref([]);

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
const toastMessage = ref("");
const toastType = ref("success");
const toastIcon = computed(() => toastType.value === "success" ? "✓" : "✗");

const WIOfMachine = ref([]);

// New helper functions for table functionality
const isAllSelected = (type) => {
  switch(type) {
    case 'inspection':
      return filteredInspection.value.length > 0 && 
             filteredInspection.value.every(item => selectedInspection.value.includes(item.value));
    case 'm1':
      return filteredM1.value.length > 0 && 
             filteredM1.value.every(item => selectedMaintenance1.value.includes(item.value));
    case 'm2':
      return filteredM2.value.length > 0 && 
             filteredM2.value.every(item => selectedMaintenance2.value.includes(item.value));
    case 'm3':
      return filteredM3.value.length > 0 && 
             filteredM3.value.every(item => selectedMaintenance3.value.includes(item.value));
    default:
      return false;
  }
};

const toggleSelectAll = (type) => {
  switch(type) {
    case 'inspection':
      if (isAllSelected('inspection')) {
        selectedInspection.value = selectedInspection.value.filter(
          id => !filteredInspection.value.some(item => item.value === id)
        );
      } else {
        const newSelections = filteredInspection.value
          .filter(item => !selectedInspection.value.includes(item.value))
          .map(item => item.value);
        selectedInspection.value.push(...newSelections);
      }
      break;
    case 'm1':
      if (isAllSelected('m1')) {
        selectedMaintenance1.value = selectedMaintenance1.value.filter(
          id => !filteredM1.value.some(item => item.value === id)
        );
      } else {
        const newSelections = filteredM1.value
          .filter(item => !selectedMaintenance1.value.includes(item.value))
          .map(item => item.value);
        selectedMaintenance1.value.push(...newSelections);
      }
      break;
    case 'm2':
      if (isAllSelected('m2')) {
        selectedMaintenance2.value = selectedMaintenance2.value.filter(
          id => !filteredM2.value.some(item => item.value === id)
        );
      } else {
        const newSelections = filteredM2.value
          .filter(item => !selectedMaintenance2.value.includes(item.value))
          .map(item => item.value);
        selectedMaintenance2.value.push(...newSelections);
      }
      break;
    case 'm3':
      if (isAllSelected('m3')) {
        selectedMaintenance3.value = selectedMaintenance3.value.filter(
          id => !filteredM3.value.some(item => item.value === id)
        );
      } else {
        const newSelections = filteredM3.value
          .filter(item => !selectedMaintenance3.value.includes(item.value))
          .map(item => item.value);
        selectedMaintenance3.value.push(...newSelections);
      }
      break;
  }
};

const cancelUpdate = () => {
  router.push('/dashboard/equipments');
};

const showToastNotification = (message, type = "success") => {
  toastMessage.value = message;
  toastType.value = type;
  showToast.value = true;
  
  setTimeout(() => {
    showToast.value = false;
  }, 3000);
};

const viewItem = (item) => {
  currentId.value = item.value;
  code.value = item.label
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const fetchCategoryData = async () => {
  try {
    const res = await axiosClient.get("", {
      params: { c: "CategoryController", m: "getAllCategories", limit: 100000 },
    });
    if (res.data.status === "success") {
      categories.value = res.data.data;
      console.log('category: ' + categories.value[0].name);
    }
  } catch (e) {
    console.error("Error fetching Category:", e);
  }
}

const fetchWIByMachineId = async (machineId) => {
  try {
    const res = await axiosClient.get("", {
      params: { c: "WorkingInstructionController", m: "getWiByMachineId", equipment_id: machineId },
    });
    if (res.data.status === "success") {
      WIOfMachine.value = res.data.data;

      selectedInspection.value = WIOfMachine.value
        .filter(wi => wi.type?.toLowerCase() === "daily inspection")
        .map(wi => wi.wi_id);

      selectedMaintenance1.value = WIOfMachine.value
        .filter(wi => wi.type?.toLowerCase() === "maintenance level 1")
        .map(wi => wi.wi_id);

      selectedMaintenance2.value = WIOfMachine.value
        .filter(wi => wi.type?.toLowerCase() === "maintenance level 2")
        .map(wi => wi.wi_id);

      selectedMaintenance3.value = WIOfMachine.value
        .filter(wi => wi.type?.toLowerCase() === "maintenance level 3")
        .map(wi => wi.wi_id);
    }
  } catch (e) {
    console.error("Error fetching Category:", e);
  }
}

function handleSelect(event) {
  fetchWiByCategoryID(category.value);
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
        const option = { value: item.uuid, label: item.code, type: item.type };
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

const fetchEquipmentData = async () => {
  const res = await axiosClient.get('', {
    params: {
      c: 'EquipmentController',
      m: 'getEquipmentById',
      equipment_id: uuid
    }
  })
  
  const eqArr = res.data.data
  console.log(eqArr)

  if (eqArr && eqArr.length > 0) {
    const eq = eqArr[0]
    modelId.value = eq.machine_id
    creator.value = eq.model
    serialNumber.value = eq.family
    manufactureDate.value = dayjs(eq.manufacturing_date)
    location.value = eq.manufacturer
    status.value = eq.history_count
    version.value = eq.unit;
    cavity.value = eq.cavity;
    category.value = eq.category_id;
  }
}

const updateMachine = async () => {
  try {
    const payload = {
      data: {
        uuid: uuid,
        machineId: modelId.value,
        model: creator.value,
        family: serialNumber.value,
        manufactureDate: manufactureDate.value,
        manufacturer: location.value,
        historyCount: status.value,
        unit: version.value,
        category: category.value,
        cavity: cavity.value,
        inspectionCodes: selectedInspection.value,
        maintenanceLevel1: selectedMaintenance1.value,
        maintenanceLevel2: selectedMaintenance2.value,
        maintenanceLevel3: selectedMaintenance3.value,
      },
    };

    await axiosClient.post('', {}, {
      params: {
        c: 'EquipmentController',
        m: 'updateEquipment',
        payload
      },
    });
    
    showToastNotification("Equipment updated successfully!", "success");
  } catch (e) {
    console.error("Error creating machine:", e);
    showToastNotification("Failed to update equipment. Please try again.", "error");
  }
};

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

      inspectionOptions.value = []
      m1Options.value = []
      m2Options.value = []
      m3Options.value = []

      data.forEach(item => {
        let frequency = item.frequency == 'Unit' ? item.unit_value + ' ' + item.unit_type : item.frequency;
        const option = { value: item.id, label: item.code, description: item.name, frequency: frequency, type: item.type };
        console.log(item.type);

        switch (item.type?.toLowerCase()) {
          case "daily inspection":
            inspectionOptions.value.push(option)
            break
          case "maintenance level 1":
            m1Options.value.push(option)
            break
          case "maintenance level 2":
            m2Options.value.push(option)
            break
          case "maintenance level 3":
            m3Options.value.push(option)
            break
        }
      })
    }
  } catch (error) {
    console.error("Error fetching WI:", error)
  }
}

onMounted(() => {
  fetchWiData();
  fetchEquipmentData();
  fetchCategoryData();
  fetchWIByMachineId(uuid);
})

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
.app-container {
  background: rgb(245,245,245);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}
.app-header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  padding: 20px 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 2px 6px rgba(24, 144, 255, 0.4);
  transition: 0.25s;
}
.save-btn:hover {
  opacity: 0.9;
}

/* RIGHT SECTION - SCROLLABLE */
.right-section {
  width: 80%;
  overflow-y: auto;
  padding-left: 20px;
}

.grid-horizontal {
  display: flex;
  flex-direction: column;
  gap: 15px;
  min-height: 100%;
}

.field {
  background: #fff;
  padding: 0;
  border-radius: 8px;
  border: 1px solid #e8eaed;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
  min-height: 300px;
  flex-shrink: 0;
  overflow: hidden;
  transition: all 0.3s ease;
}

.field:hover {
  box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
  transform: translateY(-2px);
}

/* TABLE HEADER */
.table-header {
  background: rgb(230, 230, 230);
  padding: 5px 15px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: black;
  border-radius: 8px 8px 0 0;
}

.table-title {
  font-size: 18px;
  font-weight: 600;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.table-icon {
  font-size: 20px;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
}

.selected-count {
  font-size: 14px;
  font-weight: 400;
  opacity: 0.9;
  background: rgba(255, 255, 255, 0.2);
  padding: 4px 12px;
  border-radius: 20px;
  backdrop-filter: blur(10px);
}

.search-container {
  position: relative;
  min-width: 280px;
}

.table-search {
  width: 100%;
  padding: 10px 16px;
  border: 1px solid rgba(0, 0, 0, 0.2);
  border-radius: 25px;
  font-size: 14px;
  background: rgba(123, 123, 123, 0.1);
  color: black;
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.table-search::placeholder {
  color: black;
}

.table-search:focus {
  border-color: rgba(0, 0, 0, 0.5);
  outline: none;
  background: rgba(123, 123, 123, 0.15);
  box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
}

/* MODERN TABLE */
.modern-table-container {
  overflow-x: hidden;
  background: white;
  max-height: 200px; /* chiều cao mong muốn */
  overflow-y: auto;
}

.modern-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  background: white;
  table-layout: fixed;
}

.modern-table thead {
  background: linear-gradient(135deg, #f8f9ff 0%, #f1f3ff 100%);
}

.modern-table thead th {
  padding: 10px 20px;
  text-align: left;
  font-weight: 600;
  font-size: 14px;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
  position: sticky;
  top: 0;
  background: #f8f9ff;
  z-index: 2;
}

.modern-table thead th:first-child {
  border-radius: 0;
}

.modern-table thead th:last-child {
  border-radius: 0;
}

.checkbox-col {
  width: 5%;
  text-align: center;
}

.action-col {
  width: 10%;
  text-align: center;
}

.table-row {
  
  transition: all 0.2s ease;
  border-bottom: 1px solid #f3f4f6;
}

.table-row:hover {
  background: linear-gradient(135deg, #f8faff 0%, #f0f4ff 100%);
  transform: scale(1.001);
}

.table-row:last-child {
  border-bottom: none;
}

.modern-table tbody td {
  padding: 4px 20px;
  font-size: 14px;
  color: #374151;
  vertical-align: middle;
  
}

.task-code {
  font-weight: 600;
  color: #1f2937;
  
  padding: 6px 10px !important;
  border-radius: 6px;
  font-size: 13px;
  width: 20%;
}

.description {
  width: 40%;
  color: #6b7280;
  line-height: 1.5;
  max-width: 300px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.frequency {
  width: 20%;
}

.frequency-badge {
  display: inline-block;
  padding: 6px 12px;
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
  text-transform: capitalize;
}

/* CHECKBOXES */
.table-checkbox {
  width: 18px;
  height: 18px;
  accent-color: #667eea;
  cursor: pointer;
  transition: all 0.2s ease;
}

.table-checkbox:hover {
  transform: scale(1.1);
}

.header-checkbox {
  width: 20px;
  height: 20px;
}



/* VIEW BUTTON */
.view-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border: none;
  
  color: white;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 16px;
}

.view-btn:hover {
  transform: translateY(-2px) scale(1.1);
 
}

.view-btn:active {
  transform: translateY(0) scale(1.05);
}

/* NO DATA STATE */
.no-data-row {
  background: #f9fafb;
}

.no-data {
  text-align: center;
  color: #9ca3af;
  font-style: italic;
  padding: 40px 20px !important;
  font-size: 16px;
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
  backdrop-filter: blur(8px);
}

.modal-content {
  background: white;
  width: 90%;
  height: 80%;
  border-radius: 16px;
  padding: 20px;
  position: relative;
  overflow: auto;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
  animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-20px) scale(0.9);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
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
  transition: all 0.2s ease;
}

.modal-close:hover {
  color: #000;
  transform: rotate(90deg);
}

/* TOAST NOTIFICATIONS */
.toast {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 16px 20px;
  border-radius: 12px;
  color: white;
  font-weight: 500;
  z-index: 3000;
  min-width: 300px;
  transform: translateX(100%);
  animation: slideIn 0.3s ease forwards;
  backdrop-filter: blur(10px);
}

.toast.success {
  background: linear-gradient(135deg, #52c41a, #73d13d);
  box-shadow: 0 4px 20px rgba(82, 196, 26, 0.3);
}

.toast.error {
  background: linear-gradient(135deg, #ff4d4f, #ff7875);
  box-shadow: 0 4px 20px rgba(255, 77, 79, 0.3);
}

.toast-content {
  display: flex;
  align-items: center;
  gap: 10px;
}

.toast-icon {
  font-size: 18px;
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

/* SCROLLBAR STYLING */
.modern-table-container::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.modern-table-container::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.modern-table-container::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 3px;
}

.modern-table-container::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(135deg, #5a6fd8, #6a4190);
}

/* RESPONSIVE */
@media (max-width: 1400px) {
  .table-search {
    min-width: 220px;
  }
  
  .description {
    max-width: 250px;
  }
}

@media (max-width: 1200px) {
  .left-section {
    width: 25%;
  }
  .right-section {
    width: 75%;
  }
  
  .table-header {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }
  
  .search-container {
    min-width: auto;
  }
}

@media (max-width: 768px) {
  .left-section {
    width: 30%;
  }
  .right-section {
    width: 70%;
  }
  
  .modern-table thead th,
  .modern-table tbody td {
    padding: 10px 12px;
    font-size: 13px;
  }
  
  .task-code {
    font-size: 12px;
    padding: 4px 8px !important;
  }
  
  .description {
    max-width: 200px;
  }
  
  .frequency-badge {
    font-size: 11px;
    padding: 4px 8px;
  }
  
  .view-btn {
    width: 32px;
    height: 32px;
    font-size: 14px;
  }
}

/* ANIMATION FOR TABLE ROWS */
.table-row {
  animation: fadeInUp 0.3s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }

}

.header-left {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.app-title {
  font-size: 28px;
  font-weight: 700;
  color: #2c3e50;
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 0;
}

.title-icon {
  font-size: 32px;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}

.breadcrumb {
  font-size: 14px;
  color: #6c757d;
  display: flex;
  align-items: center;
  gap: 8px;
}

.separator {
  color: #dee2e6;
}

.current {
  color: #667eea;
  font-weight: 500;
}
.link-span {
  cursor: pointer;
}
.action-buttons {
  display: flex;
  gap: 12px;
}

.cancel-btn {
  margin-top: 12px;
  width: 100%;
  padding: 10px;
  border: none;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 600;
  color: #fff;
  cursor: pointer;
  background: linear-gradient(90deg, #ff6a76, #fc979f);
  box-shadow: 0 2px 6px rgba(24, 144, 255, 0.4);
  transition: 0.25s;
}
.cancel-btn:hover {
  opacity: 0.9;
}

.page-container {
  display: flex;
  height: 100vh;
  overflow: hidden;
  background: #f5f6fa;
  font-family: "Segoe UI", Roboto, sans-serif;
}

/* LEFT */
.left-section {
  width: 20%;
  background: #fff;
  border-right: 1px solid #eee;
  box-shadow: 2px 0 6px rgba(0, 0, 0, 0.05);
  border-radius: 10px;
  margin-left: 20px;
  left: 0;
  top: 0;
  z-index: 100;
}

.left-content {
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
  background: linear-gradient(90deg, #64c357, #67d551);
  box-shadow: 0 2px 6px rgba(24, 144, 255, 0.4);
  transition: 0.25s;
}
.save-btn:hover {
  opacity: 0.9;
}

/* RIGHT SECTION - SCROLLABLE */
.right-section {
  width: 80%;
  /* height: 100vh; */
  overflow-y: auto;
  padding-left: 20px;
}

.grid-horizontal {
  display: flex;
  flex-direction: column;
  gap: 15px;
  min-height: 100%;
}

.field {
  background: #fff;
  border-radius: 8px;
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
