// utils/translation.js - Enhanced Translation System
import { ref, reactive, computed } from 'vue'

// Current language state
export const currentLanguage = ref(localStorage.getItem('selectedLanguage') || 'en')
export const translations = reactive({})

// Supported languages mapping
export const LANGUAGE_CODES = {
  en: 'en',
  vi: 'vi',
  cn: 'zh-cn',
  tw: 'zh-tw',
  ja: 'ja',
  ko: 'ko'
}

// ✅ Enhanced Local dictionary with more entries
const LOCAL_DICTIONARY = {
  vi: {
    'Dashboard': 'Bảng điều khiển',
    'Equipment': 'Thiết bị',
    'Categories': 'Danh mục',
    'Users': 'Người dùng',
    'Settings': 'Cài đặt',
    'Add': 'Thêm',
    'Edit': 'Sửa',
    'Delete': 'Xóa',
    'Save': 'Lưu',
    'Cancel': 'Hủy',
    'Search': 'Tìm kiếm',
    'Login': 'Đăng nhập',
    'Logout': 'Đăng xuất',
    'Profile': 'Hồ sơ',
    'Permissions': 'Quyền hạn',
    'Loading...': 'Đang tải...',
    'Success': 'Thành công',
    'Error': 'Lỗi',
    'Warning': 'Cảnh báo',
    'Information': 'Thông tin',
    'Yes': 'Có',
    'No': 'Không',
    'OK': 'Đồng ý',
    'Back': 'Quay lại',
    'Next': 'Tiếp theo',
    'Previous': 'Trước',
    'Category': 'Danh mục',
    'View all User': 'Xem tất cả người dùng',
    'Tasks': 'Công việc',
    "List User": "Danh sách người dùng",
    "Permissions User": "Phân quyền người dùng",
    "List Equipment": "Danh sách thiết bị",
    "View all equipment": "Xem tất cả thiết bị",
    "Add Equipment": "Thêm thiết bị",
    "Create new equipment": "Tạo thiết bị mới",
    "Instructions": "Hướng dẫn",
    "List Instructions": "Danh sách hướng dẫn",
    "View all instructions": "Xem tất cả hướng dẫn",
    "Add Instruction": "Thêm hướng dẫn",
    "Create new instruction": "Tạo hướng dẫn mới",
    "User": "Người dùng",
    "List Daily Inspection": "Danh sách kiểm tra hằng ngày",
    "View all daily inspection": "Xem tất cả kiểm tra hằng ngày",
    "admin": "Quản trị viên",
    "User Management": "Quản lý người dùng",
    "Manage and monitor your system users": "Quản lý và theo dõi người dùng trong hệ thống",
    "Refresh": "Làm mới",
    "Add User": "Thêm người dùng",
    "Filters": "Bộ lọc",
    "Equipment Management": "Quản lý thiết bị",
    "Manage and monitor your equipment inventory": "Quản lý và theo dõi kho thiết bị",
    "Add Equipment": "Thêm thiết bị",
    "Add New Category": "Thêm danh mục mới",
    "Category Code": "Mã danh mục",
    "Category Name": "Tên danh mục",
    "Category List": "Danh sách danh mục",
    "Actions": "Thao tác",
    "Working Instructions": "Hướng dẫn công việc",
    "Manage and monitor daily working instructions": "Quản lý và theo dõi hướng dẫn công việc hằng ngày",
    "Form Builder Studio": "Trình thiết kế biểu mẫu",
    "Component Toolbox": "Hộp công cụ thành phần",
    "Label": "Nhãn",
    "Yes/No Question": "Câu hỏi Có/Không",
    "Multiple Choice": "Câu hỏi trắc nghiệm",
    "Single Choice": "Câu hỏi một lựa chọn",
    "Static Image": "Hình ảnh tĩnh",
    "User Upload": "Người dùng tải lên",
    "Start Building Your Form": "Bắt đầu tạo biểu mẫu",
    "Drag components from the toolbox to begin creating your form": "Kéo thả thành phần từ hộp công cụ để bắt đầu tạo biểu mẫu",
    "Form Builder": "Trình tạo biểu mẫu",
    "Today's Equipments": "Thiết bị hôm nay",
    "List of equipments scheduled for today": "Danh sách thiết bị được lên lịch cho hôm nay",
    "Add Task": "Thêm công việc",
    "Authorization": "Phân quyền",
    // ✅ Add component descriptions
    "Add headings and text labels": "Thêm tiêu đề và nhãn văn bản",
    "Simple yes or no question": "Câu hỏi đơn giản có/không",
    "Multiple selection options": "Tùy chọn đa lựa chọn",
    "Single selection from options": "Lựa chọn đơn từ các tùy chọn",
    "Display an image in the form": "Hiển thị hình ảnh trong biểu mẫu",
    "Allow users to upload images": "Cho phép người dùng tải lên hình ảnh",
    // ✅ Add common user roles
    "manager": "Quản lý",
    "employee": "Nhân viên",
    "technician": "Kỹ thuật viên",
    "operator": "Vận hành viên",
    "supervisor": "Giám sát viên",
    "Acumen": "Acumen",
    "Zahoransky": "Zahoransky",
    "Creator": "Người tạo"
  },
  cn: {
    "Dashboard": "控制面板",
    "Equipment": "设备",
    "Categories": "类别",
    "Users": "用户",
    "Settings": "设置",
    "Add": "添加",
    "Edit": "编辑",
    "Delete": "删除",
    "Save": "保存",
    "Cancel": "取消",
    "Search": "搜索",
    "Login": "登录",
    "Logout": "登出",
    "Profile": "个人资料",
    "Permissions": "权限",
    "Loading...": "加载中...",
    "Success": "成功",
    "Error": "错误",
    "Warning": "警告",
    "Information": "信息",
    "Yes": "是",
    "No": "否",
    "OK": "确定",
    "Back": "返回",
    "Next": "下一步",
    "Previous": "上一步",
    "Category": "类别",
    "View all User": "查看所有用户",
    "Tasks": "任务",
    "List User": "用户列表",
    "Permissions User": "用户权限",
    "List Equipment": "设备列表",
    "View all equipment": "查看所有设备",
    "Add Equipment": "添加设备",
    "Create new equipment": "创建新设备",
    "Instructions": "操作指引",
    "List Instructions": "指引列表",
    "View all instructions": "查看所有指引",
    "Add Instruction": "添加指引",
    "Create new instruction": "创建新指引",
    "User": "用户",
    "List Daily Inspection": "日常巡检列表",
    "View all daily inspection": "查看所有日常巡检",
    "admin": "管理员",
    "User Management": "用户管理",
    "Manage and monitor your system users": "管理并监控系统用户",
    "Refresh": "刷新",
    "Add User": "添加用户",
    "Filters": "筛选",
    "Equipment Management": "设备管理",
    "Manage and monitor your equipment inventory": "管理并监控设备库存",
    "Add Equipment": "添加设备",
    "Add New Category": "新增类别",
    "Category Code": "类别代码",
    "Category Name": "类别名称",
    "Category List": "类别列表",
    "Actions": "操作",
    "Working Instructions": "工作指引",
    "Manage and monitor daily working instructions": "管理并监控每日工作指引",
    "Form Builder Studio": "表单设计工作室",
    "Component Toolbox": "组件工具箱",
    "Label": "标签",
    "Yes/No Question": "是/否问题",
    "Multiple Choice": "多选题",
    "Single Choice": "单选题",
    "Static Image": "静态图片",
    "User Upload": "用户上传",
    "Start Building Your Form": "开始创建表单",
    "Drag components from the toolbox to begin creating your form": "从工具箱拖动组件以开始创建表单",
    "Form Builder": "表单设计器",
    "Today's Equipments": "今日设备",
    "List of equipments scheduled for today": "今日安排的设备列表",
    "Add Task": "添加任务",
    "Authorization": "授权",
    // ✅ Add component descriptions
    "Add headings and text labels": "添加标题和文本标签",
    "Simple yes or no question": "简单的是/否问题",
    "Multiple selection options": "多选选项",
    "Single selection from options": "单选选项",
    "Display an image in the form": "在表单中显示图片",
    "Allow users to upload images": "允许用户上传图片",
    // ✅ Add common user roles
    "manager": "经理",
    "employee": "员工",
    "technician": "技术员",
    "operator": "操作员",
    "supervisor": "主管",
    "Acumen": "慧智",
    "Zahoransky": "扎霍兰斯基",
    "Creator": "创建者"
  },
  tw: {
    "Dashboard": "控制面板",
    "Equipment": "設備",
    "Categories": "類別",
    "Users": "用戶",
    "Settings": "設定",
    "Add": "新增",
    "Edit": "編輯",
    "Delete": "刪除",
    "Save": "儲存",
    "Cancel": "取消",
    "Search": "搜尋",
    "Login": "登入",
    "Logout": "登出",
    "Profile": "個人資料",
    "Permissions": "權限",
    "Loading...": "加載中...",
    "Success": "成功",
    "Error": "錯誤",
    "Warning": "警告",
    "Information": "資訊",
    "Yes": "是",
    "No": "否",
    "OK": "確定",
    "Back": "返回",
    "Next": "下一步",
    "Previous": "上一步",
    "Category": "類別",
    "View all User": "查看所有用戶",
    "Tasks": "任務",
    "List User": "使用者清單",
    "Permissions User": "使用者權限",
    "List Equipment": "設備清單",
    "View all equipment": "查看所有設備",
    "Add Equipment": "新增設備",
    "Create new equipment": "建立新設備",
    "Instructions": "操作指引",
    "List Instructions": "指引清單",
    "View all instructions": "查看所有指引",
    "Add Instruction": "新增指引",
    "Create new instruction": "建立新指引",
    "User": "使用者",
    "List Daily Inspection": "日常巡檢清單",
    "View all daily inspection": "查看所有日常巡檢",
    "admin": "管理員",
    "User Management": "使用者管理",
    "Manage and monitor your system users": "管理並監控系統使用者",
    "Refresh": "重新整理",
    "Add User": "新增使用者",
    "Filters": "篩選",
    "Equipment Management": "設備管理",
    "Manage and monitor your equipment inventory": "管理並監控設備庫存",
    "Add Equipment": "新增設備",
    "Add New Category": "新增類別",
    "Category Code": "類別代碼",
    "Category Name": "類別名稱",
    "Category List": "類別清單",
    "Actions": "操作",
    "Working Instructions": "工作指引",
    "Manage and monitor daily working instructions": "管理並監控每日工作指引",
    "Form Builder Studio": "表單設計工作室",
    "Component Toolbox": "元件工具箱",
    "Label": "標籤",
    "Yes/No Question": "是/否問題",
    "Multiple Choice": "多選題",
    "Single Choice": "單選題",
    "Static Image": "靜態圖片",
    "User Upload": "使用者上傳",
    "Start Building Your Form": "開始建立表單",
    "Drag components from the toolbox to begin creating your form": "從工具箱拖曳元件開始建立表單",
    "Form Builder": "表單設計器",
    "Today's Equipments": "今日設備",
    "List of equipments scheduled for today": "今日排程的設備清單",
    "Add Task": "新增工作",
    "Authorization": "授權",
    // ✅ Add component descriptions
    "Add headings and text labels": "新增標題和文字標籤",
    "Simple yes or no question": "簡單的是/否問題",
    "Multiple selection options": "多選選項",
    "Single selection from options": "單選選項",
    "Display an image in the form": "在表單中顯示圖片",
    "Allow users to upload images": "允許使用者上傳圖片",
    // ✅ Add common user roles
    "manager": "經理",
    "employee": "員工",
    "technician": "技術員",
    "operator": "操作員",
    "supervisor": "主管",
    "Acumen": "慧智",
    "Zahoransky": "扎霍蘭斯基",
    "Creator": "創建者"
  }
}

// Parameter interpolation
const interpolateParams = (text, params) => {
  let result = text
  Object.keys(params).forEach(key => {
    result = result.replace(new RegExp(`{${key}}`, 'g'), params[key])
  })
  return result
}

// Async translation (kept same API)
export const $t = async (key, params = {}) => {
  const lang = currentLanguage.value
  if (lang === 'en') return interpolateParams(key, params)

  const translated = LOCAL_DICTIONARY[lang]?.[key] || key
  return interpolateParams(translated, params)
}

// Sync translation (instant) - ✅ Main function for real-time translation
export const $tSync = (key, params = {}) => {
  const lang = currentLanguage.value
  if (lang === 'en') return interpolateParams(key, params)

  const translated = LOCAL_DICTIONARY[lang]?.[key] || key
  return interpolateParams(translated, params)
}

// ✅ Reactive translation - automatically updates when language changes
export const $tReactive = (key, params = {}) => {
  return computed(() => {
    const lang = currentLanguage.value
    if (lang === 'en') return interpolateParams(key, params)
    
    const translated = LOCAL_DICTIONARY[lang]?.[key] || key
    return interpolateParams(translated, params)
  })
}

// Preload translations (just copy dictionary)
export const preloadTranslations = async (textArray, targetLang) => {
  if (!LOCAL_DICTIONARY[targetLang]) return
  if (!translations[targetLang]) translations[targetLang] = {}

  textArray.forEach(text => {
    translations[targetLang][text] = LOCAL_DICTIONARY[targetLang][text] || text
  })
}

// Change language
export const changeLanguage = async (lang) => {
  currentLanguage.value = lang
  localStorage.setItem('selectedLanguage', lang)

  window.dispatchEvent(new CustomEvent('languageChanged', { detail: lang }))

  // Preload tất cả keys trong dictionary
  const allKeys = Object.keys(LOCAL_DICTIONARY[lang] || {})
  await preloadTranslations(allKeys, lang)
}

// ✅ Helper function to create reactive translated components array
export const createTranslatedComponents = () => {
  const baseComponents = [
    { 
      type: "label", 
      labelKey: "Label", 
      icon: "🏷️",
      descriptionKey: "Add headings and text labels"
    },
    { 
      type: "yesno", 
      labelKey: "Yes/No Question", 
      icon: "❓",
      descriptionKey: "Simple yes or no question"
    },
    { 
      type: "multiple", 
      labelKey: "Multiple Choice", 
      icon: "☑️",
      descriptionKey: "Multiple selection options"
    },
    { 
      type: "single", 
      labelKey: "Single Choice", 
      icon: "🔘",
      descriptionKey: "Single selection from options"
    },
    { 
      type: "staticImage", 
      labelKey: "Static Image", 
      icon: "🖼️",
      descriptionKey: "Display an image in the form"
    },
    { 
      type: "userImage", 
      labelKey: "User Upload", 
      icon: "📤",
      descriptionKey: "Allow users to upload images"
    },
  ]

  return computed(() => {
    return baseComponents.map(comp => ({
      ...comp,
      label: $tSync(comp.labelKey),
      description: $tSync(comp.descriptionKey)
    }))
  })
}

// Vue composable
export const useTranslation = () => {
  const translate = async (key, params = {}) => $t(key, params)
  const translateSync = (key, params = {}) => $tSync(key, params)
  const translateReactive = (key, params = {}) => $tReactive(key, params)

  return {
    currentLanguage,
    translate,
    translateSync,
    translateReactive,
    changeLanguage,
    $t,
    $tSync,
    $tReactive,
    createTranslatedComponents
  }
}

// Initialize language
export const initializeLanguage = () => {
  const urlLang = window.location.hash.split('/')[1]
  const storedLang = localStorage.getItem('selectedLanguage')
  const initialLang = LANGUAGE_CODES[urlLang] ? urlLang : (storedLang || 'en')

  if (initialLang !== currentLanguage.value) {
    changeLanguage(initialLang)
  }
}