<template>
  <div class="container">
    <!-- Left Panel: Toolbox -->
    <div class="left-panel">
      <h3>Toolbox</h3>
      <div
        class="component"
        v-for="comp in components"
        :key="comp.type"
        draggable="true"
        @dragstart="onDragStart(comp)"
      >
        {{ comp.label }}
      </div>
    </div>

    <!-- Middle Panel: Form Builder -->
    <div
      class="form-builder"
      @dragover.prevent
      @drop="onDrop"
    >
      <h3>Form Builder</h3>
      <div
        v-for="(item, index) in formItems"
        :key="item.id"   
        class="form-item"
        :class="{ 'label-component': item.type === 'label' }"
      >
        <!-- Label Component -->
        <div v-if="item.type === 'label'">
          <select v-model="item.heading">
            <option v-for="n in 6" :key="n" :value="'h' + n">H{{ n }}</option>
          </select>
          <input v-model="item.text" placeholder="Label text" />
          <div class="checkbox-group">
            <label><input type="checkbox" v-model="item.bold" /> Bold</label>
            <label><input type="checkbox" v-model="item.italic" /> Italic</label>
            <label><input type="checkbox" v-model="item.underline" /> Underline</label>
          </div>
        </div>

        <!-- Yes/No Question -->
        <div v-else-if="item.type === 'yesno'">
          <input v-model="item.question" placeholder="Yes/No Question" />
        </div>

        <!-- Multiple Choice -->
        <div v-else-if="item.type === 'multiple'">
          <input v-model="item.question" placeholder="Multiple Choice Question" />
          <textarea v-model="item.options" placeholder="Options (comma separated)"></textarea>
        </div>

        <!-- Single Choice -->
        <div v-else-if="item.type === 'single'">
          <input v-model="item.question" placeholder="Single Choice Question" />
          <textarea v-model="item.options" placeholder="Options (comma separated)"></textarea>
        </div>

        <!-- Static Image -->
        <div v-else-if="item.type === 'staticImage'">
          <input type="file" @change="onImageUpload($event, index)" />
          <div v-if="item.imageUrl">
            <img :src="item.imageUrl" class="thumb" />
          </div>
        </div>

        <!-- User Upload Image -->
        <div v-else-if="item.type === 'userImage'">
          <label>Upload image (user)</label>
        </div>

        <!-- Remove button -->
        <button class="remove-btn" @click="removeItem(index)">Remove</button>
      </div>
    </div>

    <!-- Right Panel: Preview -->
    <div class="preview">
      <h3>Form Preview</h3>
      <!-- Input for Code -->
      <div class="meta-field">
        <label>Code</label>
        <input disabled v-model="generatedCode" placeholder="Enter code..." />
      </div>

      <!-- Dropdown for Type -->
      <div class="meta-field">
        <label>Type</label>
        <select v-model="formMeta.type">
          <option disabled value="">-- Select Type --</option>
          <option>Daily Inspection</option>
          <option>Maintenance Level 1</option>
          <option>Maintenance Level 2</option>
          <option>Maintenance Level 3</option>
        </select>
      </div>

      <!-- Dropdown for Category -->
      <div class="meta-field">
        <label>Category</label>
        <select v-model="formMeta.category">
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
        <label>Description</label>
        <input v-model="formMeta.description" placeholder="Enter description..." />
      </div>

      <button class="save-btn" @click="saveForm">💾 Save WI</button>
      <div v-for="(item, index) in formItems" :key="index">
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
import { ref, computed, watch, onMounted } from "vue";
import axiosClient from "../utils/axiosClient"; // nhớ import axios client của bạn

// danh sách các component có thể kéo thả
const components = ref([
  { type: "label", label: "Label" },
  { type: "yesno", label: "Yes/No Question" },
  { type: "multiple", label: "Multiple Choice" },
  { type: "single", label: "Single Choice" },
  { type: "staticImage", label: "Static Image" },
  { type: "userImage", label: "User Image Upload" },
]);

const categories = ref([]);

// data form
const dragged = ref(null);
const formItems = ref([]);
const formMeta = ref({
  code: "",
  type: "",
  description: "",
  category: ""
});

// 📌 mapping type → prefix
const typeMap = {
  "Daily Inspection": "DI",
  "Maintenance Level 1": "ML01",
  "Maintenance Level 2": "ML02",
  "Maintenance Level 3": "ML03",
};

// 📌 mapping category → suffix
const categoryMap = {
  "Injection mold": "AC",
  "Tufting": "VT",
  "Injection": "VI",
  "Packaging": "VP",
};

// 📌 computed code
const generatedCode = computed(() => {
  const typeCode = typeMap[formMeta.value.type] || "";
  const categoryCode = formMeta.value.category || "";
  if (typeCode && categoryCode) {
    return `${typeCode}-${categoryCode}-XXXXXX`;
  }
  return "";
});

// đồng bộ ra meta.code (để khi save gửi kèm)
watch(generatedCode, (val) => {
  formMeta.value.code = val;
});



const uid = () => Math.random().toString(36).slice(2, 10);

// 📌 Upload ngay khi chọn ảnh
const onImageUpload = async (event, index) => {
  const file = event.target.files[0];
  if (!file) return;

  const form = new FormData();
  form.append("file", file);

  try {
    const res = await axiosClient.post(
      '',
      form,
      {
        params: {
          c: 'WorkingInstructionController',
          m: 'upload'
        },
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      }
    );
        // Cập nhật trực tiếp vào mảng theo index để chắc ăn
    const url = res.data.url;
    // cách 1: gán thuộc tính (đủ cho Vue 3)
    formItems.value[index].imageUrl = url;
    console.log("Uploaded URL:", url);
    
  } catch (err) {
    console.error("Upload failed:", err);
  }
};

// 📌 Remove item, nếu có ảnh thì xóa server
const removeItem = async (index) => {
  const item = formItems.value[index];
  if (item.type === "staticImage" && item.imageUrl) {
    try {
      // await axiosClient.post("/workinginstruction/delete_image", {
      //   path: item.imageUrl,
      // });
      console.log('img: ' + item.imageUrl);
      

      await axiosClient.post(
        '',
        {}, // body rỗng
        {
          params: {
            c: 'WorkingInstructionController',
            m: 'delete_image',
            path: item.imageUrl
          },
        }
      );
    } catch (err) {
      console.error("Delete failed:", err);
    }
  }
  formItems.value.splice(index, 1);
};

// 📌 Save form
const saveForm = async () => {
  try {
    const payload = {
      meta: {
        ...formMeta.value,
        category_code: formMeta.value.category, // gửi code
      },
      content: JSON.parse(JSON.stringify(formItems.value)),
    };

    await axiosClient.post(
      '',
      payload,
      {
        params: { c: 'WorkingInstructionController', m: 'save' },
        headers: { 'Content-Type': 'application/json' },
      }
    );

    alert("Form saved!");
  } catch (err) {
    console.error("Save failed:", err);
  }
};



const onDragStart = (comp) => {
  dragged.value = comp;
};

const onDrop = () => {
  if (!dragged.value) return;

  switch (dragged.value.type) {
    case "label":
      formItems.value.push({
        type: "label",
        text: "",
        heading: "h3",
        bold: false,
        italic: false,
        underline: false,
      });
      break;
    case "yesno":
      formItems.value.push({ type: "yesno", question: "" });
      break;
    case "multiple":
      formItems.value.push({ type: "multiple", question: "", options: "" });
      break;
    case "single":
      formItems.value.push({ type: "single", question: "", options: "" });
      break;
    case "staticImage":
      formItems.value.push({ id: uid(), type: "staticImage", imageUrl: "" });
      break;
    case "userImage":
      formItems.value.push({ type: "userImage" });
      break;
  }

  dragged.value = null;
};


const getCategories = async () => {
  try {
    const res = await axiosClient.get('', {
      params: {
        c: 'CategoryController',
        m: 'getAllCategories',
        limit: 1000
      }
    });
    categories.value = res.data.data; // giả sử API trả về [{id, name, code}]
    console.log("Categories:", categories.value);
  } catch (err) {
    console.error("Load categories failed:", err);
  }
};

onMounted(() => {
  getCategories();
});

</script>


<style>

/* ===================== Layout ===================== */
.container {
  display: flex;
  gap: 20px;
  padding: 20px;
  font-family: Arial, sans-serif;
}

/* Panel styles */
.left-panel,
.form-builder,
.preview {
  flex: 1;
  border: 1px solid #ddd;
  padding: 15px;
  min-height: 500px;
  background: #fff;
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
  border-radius: 8px;
  display: flex;
  flex-direction: column;
}

/* Panel headers */
h3 {
  margin-bottom: 12px;
  font-size: 18px;
  font-weight: 600;
  border-bottom: 2px solid #eee;
  padding-bottom: 6px;
}

/* ===================== Toolbox ===================== */
.component {
  padding: 10px;
  border: 1px solid #bbb;
  margin-bottom: 8px;
  background: #fdfdfd;
  cursor: grab;
  border-radius: 6px;
  transition: all 0.2s ease;
  text-align: center;
  font-weight: 500;
}
.component:hover {
  background: #f2f2f2;
  border-color: #999;
}

/* ===================== Form Builder ===================== */
.form-item {
  border: 1px solid #ccc;
  margin-bottom: 12px;
  padding: 12px;
  background: #fafafa;
  border-radius: 6px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.form-item input,
.form-item textarea,
.form-item select {
  width: 100%;
  padding: 8px;
  margin-top: 6px;
  margin-bottom: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 14px;
}

/* General label styling */
.form-item label {
  font-size: 13px;
  margin-bottom: 4px;
}

/* Specific styling for label component checkbox group */
.form-item.label-component .checkbox-group {
  display: flex;
  align-items: center;
  gap: 15px; /* Space between each checkbox group (Bold, Italic, Underline) */
  margin-top: 8px;
}

.form-item.label-component .checkbox-group label {
  display: flex;
  align-items: center;
  gap: 4px; /* Reduced gap for closer checkbox-text alignment */
}

/* Remove button */
.remove-btn {
  padding: 6px 10px;
  background: #e74c3c;
  color: #fff;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 13px;
}
.remove-btn:hover {
  background: #c0392b;
}

/* ===================== Meta Fields ===================== */
.meta-field {
  margin-bottom: 12px;
}
.meta-field label {
  font-weight: bold;
  margin-bottom: 4px;
  display: block;
}
.meta-field input,
.meta-field select {
  width: 100%;
  padding: 8px;
  border: 1px solid #bbb;
  border-radius: 5px;
  font-size: 14px;
}

/* Save button */
.save-btn {
  margin-bottom: 16px;
  padding: 8px 14px;
  background: #2ecc71;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
}
.save-btn:hover {
  background: #27ae60;
}

/* ===================== Preview ===================== */
.preview > div {
  margin-bottom: 16px;
  padding: 12px;
  border: 1px solid #eee;
  border-radius: 6px;
  background: #fcfcfc;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.preview p {
  margin: 8px 0 6px 0;
  font-weight: 500;
  font-size: 14px;
}

.preview label {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 6px;
  font-size: 14px;
}

.preview input[type="file"] {
  margin-top: 6px;
}

.preview img.thumb {
  display: block;
  margin-top: 10px;
  max-width: 85%;
  border-radius: 6px;
  border: 1px solid #ccc;
}

/* Heading spacing */
.preview h1,
.preview h2,
.preview h3,
.preview h4,
.preview h5,
.preview h6 {
  margin-bottom: 8px;
  font-weight: 600;
}

</style>
