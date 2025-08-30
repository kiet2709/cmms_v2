<template>
  <div class="app-container">
    <!-- Header -->
    <div class="app-header">
      <div class="header-left">
        <h1 class="app-title" v-translate>Form Builder Studio</h1>
        <div class="breadcrumb">
          <span class="link-span" @click="$router.push('/dashboard/working-instructions')">Instructions</span>
          <span class="separator">›</span>
          <span class="current">Form Builder</span>
        </div>
      </div>
      <div class="header-right">
        <button class="btn btn-outline" @click="clearForm">
          <span class="btn-icon">🗑️</span>
          Clear Form
        </button>
        <button class="btn btn-primary" @click="saveForm" :disabled="saving">
          <span v-if="!saving" class="btn-icon">💾</span>
          <div v-if="saving" class="spinner"></div>
          {{ saving ? 'Saving...' : 'Save WI' }}
        </button>
      </div>
    </div>

    <div class="main-content">
      <!-- Left Panel: Toolbox -->
      <div class="left-panel">
        <div class="panel-header">
          <h3 class="panel-title" v-translate>Component Toolbox</h3>
          <div class="component-count">{{ components.length }} items</div>
        </div>
        
        <div class="search-section">
          <div class="search-container">
            <input 
              v-model="searchToolbox" 
              placeholder="Search components..." 
              class="search-input"
            />
            <span class="search-icon">🔍</span>
          </div>
        </div>

        <div class="components-grid">
          <div
            class="component-card"
            v-for="comp in filteredComponents"
            :key="comp.type"
            draggable="true"
            @dragstart="onDragStart(comp)"
            @dragend="onDragEnd"
          >
            <div class="component-icon">{{ comp.icon }}</div>
            <div class="component-label">{{ translateReactive(comp.label).value }}</div>
            <div class="component-desc">{{ translateReactive(comp.description).value }}</div>
          </div>
        </div>
      </div>

      <!-- Middle Panel: Form Builder -->
      <div class="form-builder">
        <div class="panel-header">
          <h3 class="panel-title" v-translate>Form Builder</h3>
          <div class="items-count">{{ formItems.length }} items</div>
        </div>

        <div 
          class="drop-zone"
          :class="{ 'drag-over': isDragOver }"
          @dragover.prevent="onDragOver"
          @dragleave="onDragLeave"
          @drop="onDrop"
        >
          <div v-if="formItems.length === 0" class="empty-state">
            <div class="empty-icon">📝</div>
            <h4 v-translate>Start Building Your Form</h4>
            <p v-translate>Drag components from the toolbox to begin creating your form</p>
          </div>

          <div
            v-for="(item, index) in formItems"
            :key="item.id || index"   
            class="form-item-card"
            :class="{ 'selected': selectedItem === index }"
            @click="selectItem(index)"
          >
            <div class="item-header">
              <div class="item-type">
                <span class="type-icon">{{ getComponentIcon(item.type) }}</span>
                <span class="type-label">{{ getComponentLabel(item.type) }}</span>
              </div>
              <div class="item-actions">
                <button class="action-btn" @click.stop="duplicateItem(index)" title="Duplicate">
                  <span>📋</span>
                </button>
                <button class="action-btn danger" @click.stop="removeItem(index)" title="Remove">
                  <span>🗑️</span>
                </button>
              </div>
            </div>

            <div class="item-content">
              <!-- Label Component -->
              <div v-if="item.type === 'label'" class="config-section">
                <div class="input-group">
                  <label>Heading Level</label>
                  <select v-model="item.heading" class="form-select">
                    <option v-for="n in 6" :key="n" :value="'h' + n">Heading {{ n }}</option>
                  </select>
                </div>
                <div class="input-group">
                  <label>Label Text</label>
                  <input v-model="item.text" placeholder="Enter label text..." class="form-input" />
                </div>
                <div class="style-options">
                  <label class="checkbox-label">
                    <input type="checkbox" v-model="item.bold" />
                    <span class="checkmark"></span>
                    <strong>Bold</strong>
                  </label>
                  <label class="checkbox-label">
                    <input type="checkbox" v-model="item.italic" />
                    <span class="checkmark"></span>
                    <em>Italic</em>
                  </label>
                  <label class="checkbox-label">
                    <input type="checkbox" v-model="item.underline" />
                    <span class="checkmark"></span>
                    <u>Underline</u>
                  </label>
                </div>
              </div>

              <!-- Yes/No Question -->
              <div v-else-if="item.type === 'yesno'" class="config-section">
                <div class="input-group">
                  <label>Question</label>
                  <input v-model="item.question" placeholder="Enter yes/no question..." class="form-input" />
                </div>
              </div>

              <!-- Multiple Choice -->
              <div v-else-if="item.type === 'multiple'" class="config-section">
                <div class="input-group">
                  <label>Question</label>
                  <input v-model="item.question" placeholder="Enter multiple choice question..." class="form-input" />
                </div>
                <div class="input-group">
                  <label>Options <span class="help-text">(comma separated)</span></label>
                  <textarea v-model="item.options" placeholder="Option 1, Option 2, Option 3..." class="form-textarea"></textarea>
                </div>
              </div>

              <!-- Single Choice -->
              <div v-else-if="item.type === 'single'" class="config-section">
                <div class="input-group">
                  <label>Question</label>
                  <input v-model="item.question" placeholder="Enter single choice question..." class="form-input" />
                </div>
                <div class="input-group">
                  <label>Options <span class="help-text">(comma separated)</span></label>
                  <textarea v-model="item.options" placeholder="Option 1, Option 2, Option 3..." class="form-textarea"></textarea>
                </div>
              </div>

              <!-- Static Image -->
              <div v-else-if="item.type === 'staticImage'" class="config-section">
                <div class="image-upload-area">
                  <input 
                    type="file" 
                    :id="'file-' + index" 
                    @change="onImageUpload($event, index)" 
                    accept="image/*"
                    class="file-input"
                  />
                  <label :for="'file-' + index" class="file-upload-btn">
                    <span class="upload-icon">📤</span>
                    Choose Image
                  </label>
                  <div v-if="item.imageUrl" class="image-preview">
                    <img :src="item.imageUrl" class="preview-image" />
                    <div class="image-overlay">
                      <button @click="removeImage(index)" class="overlay-btn">
                        <span>🗑️</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- User Upload Image -->
              <div v-else-if="item.type === 'userImage'" class="config-section">
                <div class="info-box">
                  <span class="info-icon">ℹ️</span>
                  <span>This will allow users to upload images when filling the form</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Panel: Preview & Settings -->
      <div class="preview-panel">
        <div class="panel-tabs">
          <button 
            class="tab-btn" 
            :class="{ active: activeTab === 'settings' }"
            @click="activeTab = 'settings'"
          >
            Settings
          </button>
          <button 
            class="tab-btn" 
            :class="{ active: activeTab === 'preview' }"
            @click="activeTab = 'preview'"
          >
            Preview
          </button>
        </div>

        <!-- Settings Tab -->
        <div v-if="activeTab === 'settings'" class="settings-content">
          <div class="settings-section">
            <h4 class="section-title">Form Configuration</h4>
            
            <div class="meta-field">
              <label>Daily Inspection / Maintenance</label>
              <select v-model="formMeta.type" class="form-select">
                <option disabled value="">-- Select DI/ML --</option>
                <option>Daily Inspection</option>
                <option>Maintenance Level 1</option>
                <option>Maintenance Level 2</option>
                <option>Maintenance Level 3</option>
              </select>
            </div>

            <div class="meta-field">
              <label>Category</label>
              <select v-model="formMeta.category" class="form-select">
                <option disabled value="">-- Select Category --</option>
                <option 
                  v-for="cat in categories" 
                  :key="cat.code" 
                  :value="cat.code"
                >
                  {{ cat.name }}
                </option>
              </select>
            </div>

            <div class="meta-field">
              <label>Generated Code</label>
              <input 
                :value="generatedCode" 
                readonly 
                class="form-input readonly"
                placeholder="Auto-generated based on type and category"
              />
            </div>

            <div class="meta-field">
              <label>Description</label>
              <textarea 
                v-model="formMeta.description" 
                placeholder="Enter form description..." 
                class="form-textarea"
                rows="3"
              ></textarea>
            </div>
  

            <div class="meta-field">
              <label>Frequency</label>
              <select v-model="formMeta.frequency" class="form-select">
                <option disabled value="">-- Select Frequency --</option>
                <option v-for="frequency in frequencies" 
                  :key="frequency" 
                  :value="frequency">
                  {{ frequency }}
                </option>
              </select>
            </div>

            <div v-if="formMeta.frequency == 'Unit'" class="meta-field">
              <label>Unit Value</label>
              <input 
                v-model="formMeta.unitValue"
                class="form-input"
                placeholder="Enter Unit Value..."
              />
            </div>

            <div v-if="formMeta.frequency == 'Unit'" class="meta-field">
              <label>Unit Type</label>
              <input 
                v-model="formMeta.unitType"
                class="form-input"
                placeholder="Enter Unit Type..."
              />
            </div>

          </div>

          <div class="settings-section">
            <h4 class="section-title">Form Statistics</h4>
            <div class="stats-grid">
              <div class="stat-card">
                <div class="stat-value">{{ formItems.length }}</div>
                <div class="stat-label">Total Items</div>
              </div>
              <div class="stat-card">
                <div class="stat-value">{{ questionCount }}</div>
                <div class="stat-label">Questions</div>
              </div>
              <div class="stat-card">
                <div class="stat-value">{{ imageCount }}</div>
                <div class="stat-label">Images</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Preview Tab -->
        <div v-if="activeTab === 'preview'" class="preview-content">
          <div class="preview-header">
            <h4 class="section-title">Form Preview</h4>
            <div class="preview-info">
              <span class="info-badge">{{ formItems.length }} items</span>
            </div>
          </div>

          <div v-if="formItems.length === 0" class="preview-empty">
            <div class="empty-icon">📄</div>
            <p>No form items to preview</p>
          </div>

          <div class="preview-form">
            <div v-for="(item, index) in formItems" :key="index" class="preview-item">
              <!-- Render Label -->
              <component
                v-if="item.type === 'label'"
                :is="item.heading"
                :style="{
                  fontWeight: item.bold ? 'bold' : 'normal',
                  fontStyle: item.italic ? 'italic' : 'normal',
                  textDecoration: item.underline ? 'underline' : 'none'
                }"
                class="preview-heading"
              >
                {{ item.text || 'Untitled Label' }}
              </component>

              <!-- Render Yes/No -->
              <div v-else-if="item.type === 'yesno'" class="question-block">
                <p class="question-text">{{ item.question || 'Yes/No question not set' }}</p>
                <div class="options-group">
                  <label class="radio-label">
                    <input type="radio" :name="'yn' + index" value="Yes" />
                    <span class="radio-custom"></span>
                    Yes
                  </label>
                  <label class="radio-label">
                    <input type="radio" :name="'yn' + index" value="No" />
                    <span class="radio-custom"></span>
                    No
                  </label>
                </div>
              </div>

              <!-- Render Multiple Choice -->
              <div v-else-if="item.type === 'multiple'" class="question-block">
                <p class="question-text">{{ item.question || 'Multiple choice question not set' }}</p>
                <div class="options-group">
                  <template v-if="item.options && item.options.trim()">
                    <div 
                      v-for="(opt, i) in item.options.split(',')" 
                      :key="i"
                    >
                      <label class="checkbox-label">
                        <input type="checkbox" />
                        <span class="checkmark"></span>
                        {{ opt.trim() }}
                      </label>
                    </div>
                  </template>

                  <div v-else class="no-options">
                    <em>No options defined</em>
                  </div>
                </div>
              </div>
              <!-- Render Single Choice -->
              <div v-else-if="item.type === 'single'" class="question-block">
                <p class="question-text">{{ item.question || 'Single choice question not set' }}</p>
                <div class="options-group">
                  <!-- Nếu có options -->
                  <template v-if="item.options && item.options.trim()">
                    <div 
                      v-for="(opt, i) in item.options.split(',')" 
                      :key="i"
                    >
                      <label class="radio-label">
                        <input type="radio" :name="'single' + index" />
                        <span class="radio-custom"></span>
                        {{ opt.trim() }}
                      </label>
                    </div>
                  </template>

                  <!-- Nếu không có options -->
                  <div v-else class="no-options">
                    <em>No options defined</em>
                  </div>
                </div>
              </div>

              <!-- Render Static Image -->
              <div v-else-if="item.type === 'staticImage'" class="image-block">
                <img v-if="item.imageUrl" :src="item.imageUrl" class="preview-image-large" />
                <div v-else class="image-placeholder">
                  <span class="placeholder-icon">🖼️</span>
                  <p>No image uploaded</p>
                </div>
              </div>

              <!-- Render User Image Upload -->
              <div v-else-if="item.type === 'userImage'" class="upload-block">
                <label class="upload-label">Upload Image</label>
                <input type="file" class="file-input-preview" accept="image/*" disabled />
                <small class="help-text">Users will be able to upload images here</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Toast -->
    <div v-if="showSuccess" class="toast success">
      <span class="toast-icon">✅</span>
      <span>Form saved successfully!</span>
    </div>

    <!-- Loading Overlay -->
    <div v-if="saving" class="loading-overlay">
      <div class="loading-content">
        <div class="spinner large"></div>
        <p>Saving your form...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import axiosClient from "../utils/axiosClient";
import { useTranslation } from '@/utils/translation.js'

const { translateReactive } = useTranslation()

// Enhanced components with icons and descriptions
const components = ref([
  { 
    type: "label", 
    label: "Label", 
    icon: "🏷️",
    description: "Add headings and text labels"
  },
  { 
    type: "yesno", 
    label: "Yes/No Question", 
    icon: "❓",
    description: "Simple yes or no question"
  },
  { 
    type: "multiple", 
    label: "Multiple Choice", 
    icon: "☑️",
    description: "Multiple selection options"
  },
  { 
    type: "single", 
    label: "Single Choice", 
    icon: "🔘",
    description: "Single selection from options"
  },
  { 
    type: "staticImage", 
    label: "Static Image", 
    icon: "🖼️",
    description: "Display an image in the form"
  },
  { 
    type: "userImage", 
    label: "User Upload", 
    icon: "📤",
    description: "Allow users to upload images"
  },
]);

// UI State
const activeTab = ref('settings');
const selectedItem = ref(-1);
const isDragOver = ref(false);
const saving = ref(false);
const showSuccess = ref(false);
const searchToolbox = ref('');

// Data
const categories = ref([]);
const dragged = ref(null);
const formItems = ref([]);
const formMeta = ref({
  code: "",
  type: "",
  description: "",
  category: "",
  frequency: "",
  unitType: "",
  unitValue: ""
});

const frequencies = [
  'Daily',
  'Shift',
  'Unit'
]

// Type and category mappings
const typeMap = {
  "Daily Inspection": "DI",
  "Maintenance Level 1": "ML01",
  "Maintenance Level 2": "ML02",
  "Maintenance Level 3": "ML03",
};

// Computed properties
const generatedCode = computed(() => {
  const typeCode = typeMap[formMeta.value.type] || "";
  const categoryCode = formMeta.value.category || "";
  if (typeCode && categoryCode) {
    return `${typeCode}-${categoryCode}-XXXXXX`;
  }
  return "";
});

const filteredComponents = computed(() => {
  if (!searchToolbox.value) return components.value;
  return components.value.filter(comp => 
    comp.label.toLowerCase().includes(searchToolbox.value.toLowerCase()) ||
    comp.description.toLowerCase().includes(searchToolbox.value.toLowerCase())
  );
});

const questionCount = computed(() => {
  return formItems.value.filter(item => 
    ['yesno', 'multiple', 'single'].includes(item.type)
  ).length;
});

const imageCount = computed(() => {
  return formItems.value.filter(item => 
    ['staticImage', 'userImage'].includes(item.type)
  ).length;
});

// Sync generated code to meta
watch(generatedCode, (val) => {
  formMeta.value.code = val;
});

// Utility functions
const uid = () => Math.random().toString(36).slice(2, 10);

const getComponentIcon = (type) => {
  const comp = components.value.find(c => c.type === type);
  return comp ? comp.icon : '📝';
};

const getComponentLabel = (type) => {
  const comp = components.value.find(c => c.type === type);
  return comp ? comp.label : type;
};

// Form item management
const selectItem = (index) => {
  selectedItem.value = selectedItem.value === index ? -1 : index;
};

const duplicateItem = (index) => {
  const item = JSON.parse(JSON.stringify(formItems.value[index]));
  item.id = uid();
  formItems.value.splice(index + 1, 0, item);
};

const removeItem = async (index) => {
  const item = formItems.value[index];
  if (item.type === "staticImage" && item.imageUrl) {
    try {
      await axiosClient.post('', {}, {
        params: {
          c: 'WorkingInstructionController',
          m: 'delete_image',
          path: item.imageUrl
        },
      });
    } catch (err) {
      console.error("Delete image failed:", err);
    }
  }
  formItems.value.splice(index, 1);
  selectedItem.value = -1;
};

const removeImage = async (index) => {
  const item = formItems.value[index];
  if (item.imageUrl) {
    try {
      await axiosClient.post('', {}, {
        params: {
          c: 'WorkingInstructionController',
          m: 'delete_image',
          path: item.imageUrl
        },
      });
    } catch (err) {
      console.error("Delete image failed:", err);
    }
  }
  item.imageUrl = '';
};

const clearForm = () => {
  if (confirm('Are you sure you want to clear the entire form? This action cannot be undone.')) {
    formItems.value = [];
    formMeta.value = {
      code: "",
      type: "",
      description: "",
      category: "",
      frequency: "",
      unitType: "",
      unitValue: ""
    };
    selectedItem.value = -1;
  }
};

// Drag and drop handlers
const onDragStart = (comp) => {
  dragged.value = comp;
};

const onDragEnd = () => {
  dragged.value = null;
};

const onDragOver = () => {
  isDragOver.value = true;
};

const onDragLeave = () => {
  isDragOver.value = false;
};

const onDrop = () => {
  isDragOver.value = false;
  if (!dragged.value) return;

  const newItem = { id: uid() };
  
  switch (dragged.value.type) {
    case "label":
      Object.assign(newItem, {
        type: "label",
        text: "",
        heading: "h3",
        bold: false,
        italic: false,
        underline: false,
      });
      break;
    case "yesno":
      Object.assign(newItem, { type: "yesno", question: "" });
      break;
    case "multiple":
      Object.assign(newItem, { type: "multiple", question: "", options: "" });
      break;
    case "single":
      Object.assign(newItem, { type: "single", question: "", options: "" });
      break;
    case "staticImage":
      Object.assign(newItem, { type: "staticImage", imageUrl: "" });
      break;
    case "userImage":
      Object.assign(newItem, { type: "userImage" });
      break;
  }

  formItems.value.push(newItem);
  selectedItem.value = formItems.value.length - 1;
  dragged.value = null;
};

// Image upload handler
const onImageUpload = async (event, index) => {
  const file = event.target.files[0];
  if (!file) return;

  const form = new FormData();
  form.append("file", file);

  try {
    const res = await axiosClient.post('', form, {
      params: {
        c: 'WorkingInstructionController',
        m: 'upload'
      },
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    
    const url = res.data.url;
    formItems.value[index].imageUrl = url;
    console.log("Uploaded URL:", url);
  } catch (err) {
    console.error("Upload failed:", err);
  }
};

// Save form
const saveForm = async () => {
  try {
    saving.value = true;
    const payload = {
      meta: {
        ...formMeta.value,
        category_code: formMeta.value.category,
      },
      content: JSON.parse(JSON.stringify(formItems.value)),
    };

    console.log(payload);
    

    await axiosClient.post('', payload, {
      params: { c: 'WorkingInstructionController', m: 'save' },
      headers: { 'Content-Type': 'application/json' },
    });
    formItems.value = [];
    formMeta.value = {
      code: "",
      type: "",
      description: "",
      category: "",
      frequency: "",
      unitType: "",
      unitValue: ""
    };
    selectedItem.value = -1;

    showSuccess.value = true;
    setTimeout(() => {
      showSuccess.value = false;
    }, 3000);
  } catch (err) {
    console.error("Save failed:", err);
    alert("Save failed! Please try again.");
  } finally {
    saving.value = false;
  }
};

// Load categories
const getCategories = async () => {
  try {
    const res = await axiosClient.get('', {
      params: {
        c: 'CategoryController',
        m: 'getAllCategories',
        limit: 1000
      }
    });
    categories.value = res.data.data;
    console.log("Categories:", categories.value);
  } catch (err) {
    console.error("Load categories failed:", err);
  }
};

onMounted(() => {
  getCategories();
});
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.app-container {
  min-height: 100vh;
  background: rgb(245,245,245);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

/* Header */
.app-header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  padding: 20px 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
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
.header-right {
  display: flex;
  gap: 12px;
}

/* Buttons */
.btn {
  padding: 12px 20px;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: #3a8cff;
  color: white;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-outline {
  background: rgba(255, 255, 255, 0.2);
  color: #495057;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.btn-outline:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

.btn-icon {
  font-size: 16px;
}

/* Main Content */
.main-content {
  display: flex;
  gap: 24px;
  padding: 24px;
  min-height: calc(100vh - 80px);
}

/* Panels */
.left-panel,
.form-builder,
.preview-panel {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.left-panel {
  width: 300px;
  flex-shrink: 0;
}

.form-builder {
  flex: 1;
  min-width: 0;
}

.preview-panel {
  width: 350px;
  flex-shrink: 0;
}

/* Panel Headers */
.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 2px solid rgba(102, 126, 234, 0.1);
}

.panel-title {
  font-size: 18px;
  font-weight: 700;
  color: #2c3e50;
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0;
}

.panel-icon {
  font-size: 20px;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}

.component-count,
.items-count {
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

/* Search Section */
.search-section {
  margin-bottom: 20px;
}

.search-container {
  position: relative;
}

.search-input {
  width: 100%;
  padding: 12px 16px 12px 45px;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  font-size: 14px;
  transition: all 0.3s ease;
  background: rgba(248, 249, 250, 0.8);
}

.search-input:focus {
  border-color: #667eea;
  outline: none;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
  background: white;
}

.search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 16px;
  color: #6c757d;
  pointer-events: none;
}

/* Components Grid */
.components-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.component-card {
  padding: 16px;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  background: rgba(248, 249, 250, 0.8);
  cursor: grab;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.component-card:hover {
  border-color: #667eea;
  background: rgba(102, 126, 234, 0.05);
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.component-card:active {
  cursor: grabbing;
  transform: scale(0.98);
}

.component-icon {
  font-size: 24px;
  margin-bottom: 4px;
}

.component-label {
  font-weight: 600;
  color: #2c3e50;
  font-size: 14px;
}

.component-desc {
  font-size: 12px;
  color: #6c757d;
  line-height: 1.4;
}

/* Drop Zone */
.drop-zone {
  min-height: 400px;
  border: 2px dashed #dee2e6;
  border-radius: 12px;
  padding: 20px;
  transition: all 0.3s ease;
  background: rgba(248, 249, 250, 0.3);
}

.drop-zone.drag-over {
  border-color: #667eea;
  background: rgba(102, 126, 234, 0.05);
  transform: scale(1.02);
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 300px;
  text-align: center;
  color: #6c757d;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 16px;
  opacity: 0.5;
}

.empty-state h4 {
  margin: 0 0 8px 0;
  color: #495057;
  font-size: 18px;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
  max-width: 250px;
}

/* Form Item Cards */
.form-item-card {
  background: white;
  border: 2px solid #e9ecef;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 16px;
  transition: all 0.3s ease;
  cursor: pointer;
}

.form-item-card:hover {
  border-color: #667eea;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.form-item-card.selected {
  border-color: #667eea;
  background: rgba(102, 126, 234, 0.02);
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid #f1f3f4;
}

.item-type {
  display: flex;
  align-items: center;
  gap: 8px;
}

.type-icon {
  font-size: 18px;
}

.type-label {
  font-weight: 600;
  color: #495057;
  font-size: 14px;
}

.item-actions {
  display: flex;
  gap: 4px;
}

.action-btn {
  background: none;
  border: none;
  padding: 6px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 14px;
}

.action-btn:hover {
  background: rgba(0, 0, 0, 0.05);
}

.action-btn.danger:hover {
  background: rgba(220, 53, 69, 0.1);
  color: #dc3545;
}

/* Configuration Sections */
.config-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.input-group label {
  font-size: 13px;
  font-weight: 600;
  color: #495057;
}

.help-text {
  font-size: 11px;
  color: #6c757d;
  font-weight: 400;
}

.form-input,
.form-select,
.form-textarea {
  padding: 10px 12px;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  font-size: 14px;
  transition: all 0.2s ease;
  background: white;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  border-color: #667eea;
  outline: none;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-input.readonly {
  background: #f8f9fa;
  color: #6c757d;
}

.form-textarea {
  resize: vertical;
  min-height: 80px;
}

.style-options {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 14px;
}

.checkbox-label input[type="checkbox"] {
  position: absolute;
  opacity: 0;
}

.checkmark {
  width: 16px;
  height: 16px;
  border: 2px solid #dee2e6;
  border-radius: 4px;
  background: white;
  transition: all 0.2s ease;
  position: relative;
  flex-shrink: 0;
}

.checkbox-label input[type="checkbox"]:checked + .checkmark {
  background: #667eea;
  border-color: #667eea;
}

.checkbox-label input[type="checkbox"]:checked + .checkmark::after {
  content: '✓';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: white;
  font-size: 10px;
  font-weight: bold;
}

/* Image Upload */
.image-upload-area {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.file-input {
  display: none;
}

.file-upload-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  background: #3a8cff;
  color: white;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s ease;
  align-self: flex-start;
}

.file-upload-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.upload-icon {
  font-size: 16px;
}

.image-preview {
  position: relative;
  display: inline-block;
}

.preview-image {
  max-width: 200px;
  max-height: 150px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.image-overlay {
  position: absolute;
  top: 8px;
  right: 8px;
}

.overlay-btn {
  background: rgba(220, 53, 69, 0.9);
  border: none;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.overlay-btn:hover {
  background: #dc3545;
  transform: scale(1.1);
}

.info-box {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px;
  background: rgba(13, 202, 240, 0.1);
  border: 1px solid rgba(13, 202, 240, 0.2);
  border-radius: 8px;
  color: #0dcaf0;
  font-size: 14px;
}

.info-icon {
  font-size: 16px;
  flex-shrink: 0;
}

/* Panel Tabs */
.panel-tabs {
  display: flex;
  margin-bottom: 20px;
  background: rgba(248, 249, 250, 0.5);
  border-radius: 12px;
  padding: 4px;
}

.tab-btn {
  flex: 1;
  padding: 12px 16px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-weight: 600;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: transparent;
  color: #6c757d;
}

.tab-btn.active {
  background: white;
  color: #667eea;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.tab-icon {
  font-size: 16px;
}

/* Settings Content */
.settings-content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.settings-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.section-title {
  font-size: 16px;
  font-weight: 700;
  color: #2c3e50;
  margin: 0;
  padding-bottom: 8px;
  border-bottom: 1px solid #e9ecef;
}

.meta-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.meta-field label {
  font-size: 13px;
  font-weight: 600;
  color: #495057;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}

.stat-card {
  background: #3a8cff;
  color: white;
  padding: 16px;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 12px;
  opacity: 0.9;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Preview Content */
.preview-content {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}

.info-badge {
  background: rgba(102, 126, 234, 0.1);
  color: #667eea;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
}

.preview-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  text-align: center;
  color: #6c757d;
}

.preview-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.preview-item {
  padding: 16px;
  background: #fafbfc;
  border: 1px solid #e9ecef;
  border-radius: 8px;
}

.preview-heading {
  margin: 0 0 8px 0;
  color: #2c3e50;
}

.question-block {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.question-text {
  margin: 0;
  font-weight: 600;
  color: #495057;
  font-size: 14px;
}

.options-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-left: 16px;
}

.radio-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 14px;
  color: #495057;
}

.radio-label input[type="radio"] {
  position: absolute;
  opacity: 0;
}

.radio-custom {
  width: 16px;
  height: 16px;
  border: 2px solid #dee2e6;
  border-radius: 50%;
  background: white;
  transition: all 0.2s ease;
  position: relative;
  flex-shrink: 0;
}

.radio-label input[type="radio"]:checked + .radio-custom {
  border-color: #667eea;
}

.radio-label input[type="radio"]:checked + .radio-custom::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 8px;
  height: 8px;
  background: #667eea;
  border-radius: 50%;
}

.no-options {
  color: #6c757d;
  font-style: italic;
  font-size: 13px;
}

.image-block {
  display: flex;
  justify-content: center;
}

.preview-image-large {
  max-width: 100%;
  max-height: 200px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.image-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
  border: 2px dashed #dee2e6;
  border-radius: 8px;
  color: #6c757d;
}

.placeholder-icon {
  font-size: 48px;
  margin-bottom: 8px;
  opacity: 0.5;
}

.upload-block {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.upload-label {
  font-weight: 600;
  color: #495057;
  font-size: 14px;
}

.file-input-preview {
  padding: 8px 12px;
  border: 2px solid #e9ecef;
  border-radius: 6px;
  background: #f8f9fa;
  font-size: 14px;
}

.help-text {
  font-size: 12px;
  color: #6c757d;
  font-style: italic;
}

/* Toast Notifications */
.toast {
  position: fixed;
  top: 20px;
  right: 20px;
  padding: 16px 20px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 12px;
  z-index: 1000;
  animation: slideInRight 0.3s ease;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.toast.success {
  background: rgba(40, 167, 69, 0.95);
  color: white;
  backdrop-filter: blur(10px);
}

.toast-icon {
  font-size: 18px;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Loading */
.loading-overlay {
  position: fixed;
  inset: 0;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(4px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
}

.loading-content {
  text-align: center;
  color: #495057;
}

.spinner {
  width: 16px;
  height: 16px;
  border: 2px solid #f3f3f3;
  border-top: 2px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto;
}

.spinner.large {
  width: 40px;
  height: 40px;
  border-width: 3px;
  margin-bottom: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 1400px) {
  .main-content {
    flex-direction: column;
    gap: 20px;
  }
  
  .left-panel,
  .preview-panel {
    width: 100%;
  }
  
  .stats-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 1024px) {
  .app-header {
    flex-direction: column;
    gap: 16px;
    text-align: center;
  }
  
  .header-right {
    justify-content: center;
  }
  
  .main-content {
    padding: 16px;
  }
  
  .panel-tabs {
    flex-direction: column;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .app-title {
    font-size: 24px;
  }
  
  .left-panel,
  .form-builder,
  .preview-panel {
    padding: 16px;
  }
  
  .components-grid {
    gap: 8px;
  }
  
  .component-card {
    padding: 12px;
  }
  
  .style-options {
    flex-direction: column;
    gap: 8px;
  }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* Focus states for keyboard navigation */
.component-card:focus,
.form-item-card:focus,
.tab-btn:focus,
.action-btn:focus {
  outline: 2px solid #667eea;
  outline-offset: 2px;
}

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: rgba(102, 126, 234, 0.3);
  border-radius: 4px;
  transition: background 0.2s ease;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(102, 126, 234, 0.5);
}

</style>

addWI