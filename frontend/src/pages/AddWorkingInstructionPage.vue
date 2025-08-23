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
      >
        <!-- Label Component -->
        <div v-if="item.type === 'label'">
          <select v-model="item.heading">
            <option v-for="n in 6" :key="n" :value="'h' + n">H{{ n }}</option>
          </select>
          <input v-model="item.text" placeholder="Label text" />

          <label><input type="checkbox" v-model="item.bold" /> Bold</label>
          <label><input type="checkbox" v-model="item.italic" /> Italic</label>
          <label><input type="checkbox" v-model="item.underline" /> Underline</label>
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
        <input v-model="formMeta.code" placeholder="Enter code..." />
      </div>

      <div class="meta-field">
        <label>Description</label>
        <input v-model="formMeta.description" placeholder="Enter description..." />
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
import { ref } from "vue";
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

// data form
const dragged = ref(null);
const formItems = ref([]);
const formMeta = ref({
  code: "",
  type: "",
  description: ""   // thêm
});

// methods
// const saveForm = async () => {
//   const payload = {
//     code: formMeta.value.code,
//     type: formMeta.value.type,
//     schema: JSON.stringify(formItems.value),
//   };

//   console.log("Saving...", payload);

//   try {
//     await axiosClient.post("/wi", payload);
//     alert("Saved!");
//   } catch (err) {
//     console.error(err);
//   }
// };







// // Store file objects for staticImage items
// const imageFiles = ref({}); // Object to map index to file object

// // Methods
// const saveForm = async () => {
//   // Create a clean schema without base64 image data
//   const cleanFormItems = formItems.value.map(item => {
//     if (item.type === 'staticImage') {
//       return { ...item, imageUrl: null }; // Clear imageUrl
//     }
//     return { ...item };
//   });

//   // Prepare FormData
//   const formData = new FormData();
  
//   // Append JSON payload
//   const payload = {
//     code: formMeta.value.code,
//     type: formMeta.value.type,
//     description: formMeta.value.code, // Use code as description (per your template)
//     schema: JSON.stringify(cleanFormItems),
//   };
//   formData.append('payload', JSON.stringify(payload));

//   // Append images from staticImage items
//   formItems.value.forEach((item, index) => {
//     if (item.type === 'staticImage' && imageFiles.value[index]) {
//       formData.append('images[]', imageFiles.value[index]);
//     }
//   });

//   // Log FormData contents
//   console.log('FormData being sent:');
//   for (const [key, value] of formData.entries()) {
//     if (key === 'payload') {
//       console.log(`${key}:`, JSON.parse(value)); // Parse and log JSON payload
//     } else if (key === 'images[]') {
//       console.log(`${key}:`, value.name); // Log filename of each image
//     }
//   }

//   try {
//     const response = await axiosClient.post(
//       '',
//       formData,
//       {
//         params: {
//           c: 'WorkingInstructionController',
//           m: 'save'
//         },
//         headers: {
//           'Content-Type': 'multipart/form-data',
//         },
//       }
//     );

//     // Log the API response
//     console.log('API response:', response.data);

//     // Update formItems with returned image paths
//     const uploadedFiles = response.data.uploaded_files || [];
//     let fileIndex = 0;
//     formItems.value = formItems.value.map(item => {
//       if (item.type === 'staticImage' && fileIndex < uploadedFiles.length) {
//         return { ...item, imageUrl: uploadedFiles[fileIndex++] }; // Assign relative path
//       }
//       return item;
//     });

//     alert("Saved successfully!");
//   } catch (err) {
//     console.error("Error saving form:", err);
//     console.log('Error details:', err.response?.data || err.message);
//     alert("Failed to save form: " + (err.response?.data?.message || err.message));
//   }
// };

const uid = () => Math.random().toString(36).slice(2, 10);

// 📌 Upload ngay khi chọn ảnh
const onImageUpload = async (event, index) => {
  const file = event.target.files[0];
  if (!file) return;

  const form = new FormData();
  form.append("file", file);

  try {
    // const res = await axiosClient.post("/workinginstruction/upload", form, {
    //   headers: { "Content-Type": "multipart/form-data" },
    // });

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
      meta: formMeta.value,
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

// const removeItem = (index) => {
//   formItems.value.splice(index, 1);
// };

// const onImageUpload = (event, item) => {
//   const file = event.target.files[0];
//   if (file) {
//     const reader = new FileReader();
//     reader.onload = (e) => {
//       item.imageUrl = e.target.result;
//     };
//     reader.readAsDataURL(file);
//   }
// };
</script>


<style>

.meta-field {
  margin-bottom: 10px;
}
.meta-field label {
  display: block;
  font-weight: bold;
  margin-bottom: 4px;
}
.meta-field input,
.meta-field select {
  width: 100%;
  padding: 6px;
  border: 1px solid #ccc;
  border-radius: 4px;
}


.save-btn {
  margin-bottom: 10px;
  padding: 6px 12px;
  background: #2ecc71;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}
.save-btn:hover {
  background: #27ae60;
}


.container {
  display: flex;
  gap: 20px;
  padding: 20px;
  font-family: Arial, sans-serif;
}

.left-panel, .form-builder, .preview {
  flex: 1;
  border: 1px solid #ccc;
  padding: 10px;
  min-height: 500px;
  background: #fdfdfd;
  box-shadow: 0 0 5px rgba(0,0,0,0.1);
  border-radius: 6px;
}

h3 {
  margin-bottom: 10px;
  font-size: 18px;
  border-bottom: 1px solid #ddd;
  padding-bottom: 5px;
}

.component {
  padding: 8px;
  border: 1px solid #aaa;
  margin-bottom: 5px;
  background: #f9f9f9;
  cursor: grab;
  border-radius: 4px;
  transition: background 0.2s;
}
.component:hover {
  background: #eee;
}

.form-item {
  border: 1px dashed #aaa;
  margin-bottom: 10px;
  padding: 10px;
  position: relative;
  background: #fafafa;
  border-radius: 4px;
}

.remove-btn {
  margin-top: 5px;
  padding: 4px 8px;
  background: #e74c3c;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
}
.remove-btn:hover {
  background: #c0392b;
}

.thumb {
  max-width: 100%;
  height: auto;
  margin-top: 5px;
  border-radius: 4px;
}




/* ===================== Form Preview ===================== */
.preview > div {
  margin-bottom: 20px; /* Khoảng cách giữa các item */
}

/* Tách riêng mỗi row trong preview */
.preview p {
  margin: 8px 0 4px 0; /* Khoảng cách trên/dưới cho câu hỏi */
  font-weight: 500;
  font-size: 14px;
}

/* Checkbox và radio */
.preview label {
  display: flex;
  align-items: center;
  gap: 8px; /* Khoảng cách giữa checkbox/radio và text */
  margin-bottom: 4px; /* Khoảng cách giữa các đáp án */
  font-size: 14px;
}

/* Thêm padding cho các loại input file hoặc hình ảnh */
.preview input[type="file"] {
  margin-top: 4px;
  margin-bottom: 8px;
}

/* Hình ảnh */
.preview img.thumb {
  display: block;
  margin-top: 8px;
  margin-bottom: 8px;
  max-width: 80%;
  border-radius: 6px;
  border: 1px solid #ccc;
}

/* Card-like style cho mỗi form item */
.preview > div {
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  background-color: #fcfcfc;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

/* Câu hỏi label */
.preview h1,
.preview h2,
.preview h3,
.preview h4,
.preview h5,
.preview h6 {
  margin-bottom: 6px;
}

/* Khoảng cách giữa các item của Multiple/Single choice */
.preview .form-item-mc,
.preview .form-item-sc {
  margin-top: 6px;
  margin-bottom: 12px;
}

/* Tăng khoảng cách dòng cho label */
.preview p, 
.preview label {
  line-height: 1.6;
}









</style>
