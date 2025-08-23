<template>
  <a-row
    justify="center"
    align="middle"
    style="
      min-height: 100vh; 
      padding: 16px;
      background-color: #fff7e6;
      position: relative;
    "
  >
    <!-- Overlay Spin -->
    <div
      v-if="loading"
      style="
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background-color: rgba(255, 255, 255, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
      "
    >
      <a-spin size="large" tip="Logging in..." />
    </div>

    <a-col
      :xs="24"
      :sm="20"
      :md="12"
      :lg="8"
      style="max-width: 550px; width: 100%;"
    >
      <!-- Logo -->
      <div
        style="
          display: flex;
          flex-direction: column;
          align-items: center;
          text-align: center;
          margin-top: 20px;
          margin-bottom: 14px;
          gap: 8px;
        "
      >
        <div style="display: flex; align-items: center; gap: 12px;">
          <img
            :src="logo"
            alt="Logo"
            style="width: 60px; height: 60px; object-fit: contain;"
          />
          <span
            style="
              font-family: 'Arial Black', Impact, sans-serif;
              font-size: 50px;
              font-weight: 900;
              letter-spacing: 8px;
              display: inline-block;
              margin-left: 10px;
            "
          >
            CMMS
          </span>
        </div>
      </div>

      <!-- Form -->
      <a-card
        bordered
        style="
          border: 2px solid #d9d9d9; 
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); 
          border-radius: 8px;
        "
      >
        <template #title>
          <span
            style="
              font-size: 27px; 
              font-weight: bold; 
              text-transform: uppercase;
              display: flex;
              flex-direction: column;
              align-items: center;
              text-align: center;
            "
          >
            Login
          </span>
        </template>

        <a-form
          :model="form"
          @finish="handleSubmit"
          layout="vertical"
          style="font-size: 16px;"
        >
          <!-- Username -->
          <a-form-item
            label="Username"
            name="username"
            :rules="[{ required: true, message: 'Please input your username!' }]"
            style="font-size: 18px; font-weight: bold;"
          >
            <a-input
              v-model:value="form.username"
              placeholder="Enter username"
              size="large"
            />
          </a-form-item>

          <!-- Password -->
          <a-form-item
            label="Password"
            name="password"
            :rules="[{ required: true, message: 'Please input your password!' }]"
            style="font-size: 18px; font-weight: bold;"
          >
            <a-input-password
              v-model:value="form.password"
              placeholder="Enter password"
              size="large"
            />

            <!-- Error Message -->
            <div
              v-if="errorMessage"
              style="
                background-color: #fff1f0;
                color: #cf1322;
                padding: 10px 14px;
                border: 1px solid #ffa39e;
                border-radius: 4px;
                margin-top: 16px;
                margin-bottom: 16px;
                font-size: 15px;
              "
            >
              {{ errorMessage }}
            </div>
          </a-form-item>

          <!-- Submit Button -->
          <a-form-item>
            <a-button
              type="primary"
              html-type="submit"
              block
              size="large"
              style="font-size: 16px; height: 45px; font-weight: bold;"
            >
              Login
            </a-button>
          </a-form-item>
        </a-form>
      </a-card>
    </a-col>
  </a-row>
</template>

<script setup>
import { reactive, ref } from 'vue'
import logo from '@/assets/logo.png'
import { useRouter } from 'vue-router'
import { message } from 'ant-design-vue'
import axiosClient from '@/utils/axiosClient'

const router = useRouter()

const form = reactive({
  username: '',
  password: ''
})

const errorMessage = ref('')
const loading = ref(false)

const handleSubmit = async (values) => {
  errorMessage.value = ''
  loading.value = true // bật spin
  try {
    const res = await axiosClient.post('', values, {
      params: {
        c: 'AuthController',
        m: 'login'
      }
    })
    console.log(res);
    
    localStorage.setItem('accessToken', res.data.accessToken)
    router.push('/dashboard/equipments')
  } catch (e) {
    console.error(e)
    const messageError = 'Please check your username or password again.'
    message.error(messageError)
    errorMessage.value = messageError
  } finally {
    loading.value = false // tắt spin
  }
}
</script>
