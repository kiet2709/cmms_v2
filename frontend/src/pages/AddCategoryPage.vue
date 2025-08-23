<script setup>
import { ref } from 'vue';
import axiosClient from '@/utils/axiosClient'; // giả sử bạn dùng axiosClient để call API

const categoryName = ref('');
const loading = ref(false);

async function saveCategory() {
  if (!categoryName.value.trim()) {
    alert('Please enter a category name.');
    return;
  }

  loading.value = true;
  try {
    const res = await axiosClient.post('', {
      c: 'CategoryController',
      m: 'createCategory',
      name: categoryName.value.trim()
    });
    alert(res.data.message || 'Category saved successfully!');
    categoryName.value = '';
  } catch (err) {
    console.error(err);
    alert('Error saving category.');
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="category-page">
    <h2>Create Category</h2>
    <div class="form-group">
      <label for="category">Category Name</label>
      <input 
        id="category" 
        type="text" 
        v-model="categoryName" 
        placeholder="Enter category name" 
      />
    </div>
    <button class="btn-primary" :disabled="loading" @click="saveCategory">
      {{ loading ? 'Saving...' : 'Save' }}
    </button>
  </div>
</template>

<style scoped>
.category-page {
  max-width: 400px;
  margin: 50px auto;
  padding: 2rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  background-color: #fff;
  text-align: center;
}

.category-page h2 {
  margin-bottom: 1.5rem;
  color: #2932e1;
}

.form-group {
  margin-bottom: 1rem;
  text-align: left;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.form-group input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 1rem;
  outline: none;
  transition: border 0.2s;
}

.form-group input:focus {
  border-color: #2932e1;
  box-shadow: 0 0 5px rgba(41, 50, 225, 0.3);
}

.btn-primary { 
  color: #fff;
  background-color: #2932e1; 
  border-color: #2932e1;
  padding: 0.5rem 1.2rem;
  font-size: 1rem;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.2s, border-color 0.2s;
}

.btn-primary:hover {
  background-color: #0090d1;
  border-color: #0090d1;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
