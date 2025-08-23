<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axiosClient from '@/utils/axiosClient';
import { message } from 'ant-design-vue';

const route = useRoute();
const uuid = route.params.uuid;

const loading = ref(false);
const form = ref({
  name: '',
  description: '',
  questions: []
});

const content = ref('');

// Hình tạm cho Good / Not Good
const images = [
  {
    src: 'https://tufttheworld.com/cdn/shop/products/Duo_HandsShot_2048x.jpg?v=1737990613',
    label: 'Good'
  },
  {
    src: 'https://hackaday.com/wp-content/uploads/2022/03/tufting-gun-800.jpg?w=800',
    label: 'Not Good'
  }
];

async function fetchForm() {
  try {
    loading.value = true;
    const res = await axiosClient.get(`/forms/plan/${uuid}`);

    content.value = res.data.content;
    
    form.value.name = res.data.form.name;
    form.value.description = res.data.form.description;
    form.value.questions = res.data.form.questions.map(q => ({
      uuid: q.uuid,
      content: q.question,
      answer: q.answer ?? null,
      order_number: q.order_number
    }));
  } finally {
    loading.value = false;
  }
}

async function submitForm() {
  const incomplete = form.value.questions.some(q => q.answer === null);
  if (incomplete) {
    message.warning('Please answer all questions before submitting.');
    return;
  }

  const payload = form.value.questions.map(q => ({
    uuid: q.uuid,
    answer: q.answer
  }));

  console.log('Submit payload:', payload);
  message.success('Form submitted successfully!');
}

onMounted(() => {
  fetchForm();
});
</script>

<template>
  <div v-if="loading" class="loading-container" style="text-align: center; margin-top: 50px; font-size: 20px;">
    <a-spin size="large"  />
  </div>

  <div v-else style="max-width: 900px; margin: 0 auto; font-size: 18px; padding-bottom: 50px;">
    <!-- Form title -->
    <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 36px;">{{ content }}</h1>

    <!-- Hình Good / Not Good responsive -->
    <div class="image-container">
      <div v-for="img in images" :key="img.label" class="image-item">
        <img :src="img.src" :alt="img.label" />
        <div class="image-label">{{ img.label }}</div>
      </div>
    </div>

    <a-form layout="vertical" @submit.prevent="submitForm">
<a-form-item
  v-for="(q, index) in form.questions"
  :key="q.uuid"
  :label="`Câu ${q.order_number}`"
  class="form-item"
>
  <a-textarea
    v-model:value="q.content"
    placeholder="Nhập nội dung câu hỏi"
    class="question-input"
    auto-size
  />
</a-form-item>


      <a-form-item style="margin-top: 32px;">
        <a-button type="primary" html-type="submit" style="font-size: 18px; padding-bottom: 34px;">
          Save
        </a-button>
      </a-form-item>
    </a-form>
  </div>
</template>

<style scoped>
.loading-container {
  display: flex;
  justify-content: center; /* canh ngang */
  align-items: center;     /* canh dọc */
  height: 100vh;           /* full màn hình */
  width: 100%;
}

.loading-container .ant-spin {
  transform: scale(2); /* phóng to 3 lần */
}

/* Container ảnh */
.image-container {
  display: flex;
  gap: 20px;
  justify-content: space-between;
  flex-wrap: wrap;
  margin-bottom: 32px;
}

/* Mặc định mobile: mỗi ảnh chiếm full width */
.image-item {
  flex: 1 1 100%;
  text-align: center;
  max-width: 100%;
}

/* Ảnh */
.image-item img {
  width: 100%;
  height: auto;
  object-fit: cover;
  border-radius: 8px;
}

.image-label {
  font-size: 16px;
  font-weight: 500;
  margin-top: 8px;
}

/* Radio buttons */
.radio-group {
  display: flex;
  gap: 16px; /* khoảng cách giữa 2 radio */
}

/* Mobile: căn giữa 2 radio */
@media (max-width: 767px) {
  .radio-group {
    justify-content: space-between;
    max-width: 200px;
    margin: 0 auto;
  }
}

/* Desktop: mặc định dính trái */
@media (min-width: 768px) {
  .radio-group {
    justify-content: flex-start;
  }
}


/* Desktop: 2 ảnh nằm 1 hàng, dãn đều */
@media (min-width: 768px) {
  .image-item {
    flex: 1 1 45%; /* 2 ảnh ~ 45% container, cách nhau 10% gap */
  }
}


.form-item {
  margin-bottom: 24px;
}

.question-input {
  font-size: 18px;
  width: 100%;        /* chiếm full container */
  min-width: 100px;   /* tránh quá nhỏ trên desktop */
}

/* Mobile: cho input tự xuống dòng và co dãn */
@media (max-width: 767px) {
  .question-input {
    width: 100%;       /* luôn chiếm full chiều ngang */
    white-space: normal; /* chữ xuống dòng khi dài */
  }
}


</style>
