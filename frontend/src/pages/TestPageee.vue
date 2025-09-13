<template>
  <div class="cmms-dashboard">
    <!-- Header Section -->
    <div class="dashboard-header">
      <div class="header-content">
        <div class="header-left">
          <h1><i class="fas fa-cogs"></i> CMMS Management Dashboard</h1>
        </div>
        <div class="header-right">
          <div class="time-display">
            <p class="time-label">Current Time</p>
            <p class="time-value">{{ currentTime }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card daily" @click="navigateToSection('urgent')">
        <div class="stat-icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-content">
          <h3>Daily Inspection</h3>
          <div class="stat-number">{{ stats.daily }}</div>
          <div class="stat-label">Need to inspect</div>
        </div>
      </div>

      <div class="stat-card maintenance" @click="navigateToSection('maintenance')">
        <div class="stat-icon">
          <i class="fas fa-wrench"></i>
        </div>
        <div class="stat-content">
          <h3>Maintenance</h3>
          <div class="stat-number">{{ stats.maintenance }}</div>
          <div class="stat-label">Scheduled Today</div>
        </div>
      </div>

      <div class="stat-card overdue" @click="navigateToSection('maintenance')">
        <div class="stat-icon">
          <i class="fas fa-wrench"></i>
        </div>
        <div class="stat-content">
          <h3>Total overdue</h3>
          <div class="stat-number">{{ stats.overdue }}</div>
          <div class="stat-label"></div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="main-grid">
      <!-- Today's Tasks -->
      <div class="card today-tasks">
        <div class="card-header">
          <h3><i class="fas fa-tasks"></i> Today's Tasks</h3>
          <span class="badge">{{ stats.daily + stats.maintenance }} items</span>
        </div>
        <div class="task-list">
          <div 
            v-for="task in todayTasks" 
            :key="task.id" 
            class="task-item"
            :class="task.priority"
            @click="viewTaskDetails(task)"
          >
            <div class="task-info">
              <div class="task-title">{{ task.machine_id }}</div>
              <div class="task-details">
                <span class="task-machine">{{ task.model }}</span>
                <span class="task-time">{{ task.category_name }}</span>
              </div>
            </div>
            <div class="task-status" :class="task.status">
              {{ task.status.toUpperCase() }}
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="card recent-activities">
        <div class="card-header">
          <h3><i class="fas fa-history"></i> Recent Activities</h3>
        </div>
        <div class="activity-list">
          <div 
            v-for="activity in recentActivities" 
            :key="activity.id"
            class="activity-item"
          >
            <div class="activity-icon" :class="activity.type">
              <i :class="getActivityIcon(activity.type)"></i>
            </div>
            <div class="activity-content">
              <div class="activity-text">{{ activity.text }}</div>
              <div class="activity-time">{{ activity.time }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Maintenance Calendar -->
      <div class="card maintenance-calendar">
        <div class="card-header">
          <h3><i class="fas fa-calendar"></i> Upcoming Maintenance</h3>
          <button class="view-all-btn" @click="showMaintenanceSchedule">
            View All
          </button>
        </div>
        <div class="calendar-grid">
          <div 
            v-for="maintenance in upcomingMaintenance" 
            :key="maintenance.machine_id"
            class="maintenance-item"
            :class="maintenance.urgency"
            @click="viewMaintenanceDetails(maintenance)"
          >
            <div class="maintenance-date">
              <div class="date-day">{{   }}</div>
              <div class="date-month">{{ maintenance.machine_id }}</div>
            </div>
            <div class="maintenance-info">
              <div class="maintenance-title">{{ maintenance.date_maintenance}}</div>
              <div class="maintenance-machine">{{ maintenance.status }}</div>
            </div>
            <div class="maintenance-type">
              {{ maintenance.type }}
            </div>
          </div>
        </div>
      </div>

      <!-- Weekly Maintenance Overview -->
      <div style="grid-column: 1 / -1;">
        <Chart />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import Chart from './Chart.vue'
import axiosClient from '../utils/axiosClient'


import dayjs from 'dayjs';
import 'dayjs/locale/vi'; // Import Vietnamese locale if needed
import isSameOrBefore from 'dayjs/plugin/isSameOrBefore'; // For date comparison

dayjs.extend(isSameOrBefore); // Extend dayjs with isSameOrBefore plugin
dayjs.locale('vi'); // Set locale to Vietnamese (optional, remove if not needed)

// Reactive data
const currentTime = ref('')
const stats = ref({
  daily: 0,
  maintenance: 0,
  overdue: 0
})

const getStatistic = async () => {
  try {
    const res = await axiosClient.get('', {
      params: {
        c: 'DashboardController',
        m: 'getStatistic'
      }
    })
    let statistic = res.data.data;
    stats.value.daily = statistic.daily_inspection;
    stats.value.maintenance = statistic.maintenance;
    stats.value.overdue = statistic.overdue;
    console.log(res.data.data);
    
  } catch (error) {
    console.log('statistic error: ' + error);
    
  }
}
const todayTasks = ref([]);
// const todayTasks = ref([
//   {
//     id: 1,
//     title: 'Hydraulic System Check',
//     machine: 'CNC Machine #003',
//     dueTime: '09:30 AM',
//     priority: 'high',
//     status: 'pending'
//   },
//   {
//     id: 2,
//     title: 'Belt Replacement',
//     machine: 'Conveyor Belt #12',
//     dueTime: '11:00 AM',
//     priority: 'medium',
//     status: 'in-progress'
//   },
//   {
//     id: 3,
//     title: 'Lubrication Service',
//     machine: 'Press Machine #07',
//     dueTime: '02:15 PM',
//     priority: 'low',
//     status: 'completed'
//   },
//   {
//     id: 4,
//     title: 'Safety Inspection',
//     machine: 'Welding Station #05',
//     dueTime: '04:00 PM',
//     priority: 'high',
//     status: 'pending'
//   }
// ])

const maintenanceData = ref([
  { name: 'Mon', completed: 12, scheduled: 15, urgent: 2 },
  { name: 'Tue', completed: 19, scheduled: 18, urgent: 1 },
  { name: 'Wed', completed: 15, scheduled: 20, urgent: 3 },
  { name: 'Thu', completed: 22, scheduled: 16, urgent: 0 },
  { name: 'Fri', completed: 18, scheduled: 22, urgent: 4 },
  { name: 'Sat', completed: 8, scheduled: 10, urgent: 1 },
  { name: 'Sun', completed: 5, scheduled: 6, urgent: 0 }
])

const recentActivities = ref([
  {
    id: 1,
    type: 'maintenance',
    text: 'Completed preventive maintenance on Machine #15',
    time: '2 hours ago'
  },
  {
    id: 2,
    type: 'alert',
    text: 'Temperature alert resolved on Furnace #3',
    time: '4 hours ago'
  },
  {
    id: 3,
    type: 'task',
    text: 'New work order created for Conveyor #8',
    time: '6 hours ago'
  },
  {
    id: 4,
    type: 'system',
    text: 'System backup completed successfully',
    time: '8 hours ago'
  }
])

const upcomingMaintenance = ref([]);
// const upcomingMaintenance = ref([
//   {
//     id: 1,
//     title: 'Annual Calibration',
//     machine: 'Precision Lathe #001',
//     day: '15',
//     month: 'Sep',
//     type: 'Preventive',
//     urgency: 'medium'
//   },
//   {
//     id: 2,
//     title: 'Belt Replacement',
//     machine: 'Conveyor #12',
//     day: '18',
//     month: 'Sep',
//     type: 'Corrective',
//     urgency: 'high'
//   },
//   {
//     id: 3,
//     title: 'Oil Change',
//     machine: 'Compressor #04',
//     day: '22',
//     month: 'Sep',
//     type: 'Routine',
//     urgency: 'low'
//   }
// ])

// Methods
const updateTime = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('en-US', {
    hour12: true,
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}

const getActivityIcon = (type) => {
  const icons = {
    maintenance: 'fas fa-wrench',
    alert: 'fas fa-bell',
    task: 'fas fa-tasks',
    system: 'fas fa-cog'
  }
  return icons[type] || 'fas fa-info-circle'
}

const navigateToSection = (section) => {
  console.log(`Navigating to ${section} section`)
}

const showMaintenanceSchedule = () => {
  console.log('Showing maintenance schedule')
}

const viewTaskDetails = (task) => {
  console.log('Viewing task:', task)
}

const viewMaintenanceDetails = (maintenance) => {
  console.log('Viewing maintenance:', maintenance)
}

// Lifecycle hooks
let interval = null
onMounted(() => {
  updateTime()
  interval = setInterval(updateTime, 1000)
  getStatistic();
  fetchTodayEquipmentsDailyInspection();
  fetchTodayEquipmentsMaintenance();
})

const dateFormat = 'YYYY-MM-DD';
const today = dayjs();
const dateFrom = ref(today); // Initialize as dayjs object
const dateTo = ref(today); // Initialize as dayjs object

async function fetchTodayEquipmentsDailyInspection() {
  try {
    const res = await axiosClient.get('', {
      params: {
        c: 'DailyTaskController',
        m: 'get_today_equipments',
        date_from: dateFrom.value.format(dateFormat),
        date_to: dateTo.value.format(dateFormat)
      }
    });
    todayTasks.value = res.data?.data || [];
    
    
  } catch (error) {
    console.error('Error fetching equipments:', error);
  } finally {
    
  }
}

async function fetchTodayEquipmentsMaintenance() {
  try {
    const res = await axiosClient.get('', {
      params: {
        c: 'MaintenanceController',
        m: 'getMaintenanceSessionIncomming',
      }
    });
    upcomingMaintenance.value = res.data || [];
    console.log('upcomming maintenance');
    
    console.log( res.data);
    
  } finally {
    
  }
}


onUnmounted(() => {
  clearInterval(interval);
})

</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.cmms-dashboard {
  min-height: 100vh;
  background: rgb(245, 245, 245);
  padding: 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Header Styles */
.dashboard-header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  padding: 25px 30px;
  margin-bottom: 25px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left h1 {
  color: #2c3e50;
  font-size: 2.2rem;
  font-weight: 700;
}

.header-left h1 i {
  color: #667eea;
  margin-right: 10px;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 20px;
}

.time-display {
  text-align: right;
}

.time-label {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0 0 5px 0;
}

.time-value {
  font-size: 1.25rem;
  font-weight: 600;
  color: #374151;
  margin: 0;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  padding: 25px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 20px;
  position: relative;
  overflow: hidden;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: var(--card-accent);
}

.stat-card.daily {
  --card-accent: #76adff;
}

.stat-card.maintenance {
  --card-accent: #f59e0b;
}

.stat-card.overdue {
  --card-accent: #ef4444;
}

.stat-icon {
  font-size: 2.5rem;
  color: var(--card-accent);
  opacity: 0.8;
}

.stat-content {
  flex: 1;
}

.stat-content h3 {
  color: #374151;
  font-size: 1rem;
  margin-bottom: 5px;
  font-weight: 600;
}

.stat-number {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1f2937;
  line-height: 1;
}

.stat-label {
  color: #6b7280;
  font-size: 0.85rem;
  margin-top: 5px;
}

/* Main Grid */
.main-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 25px;
  margin-bottom: 25px;
}

.chart-full-width {
  grid-column: 1 / -1; /* Span full width */
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.card-header {
  background: #f8fafc;
  padding: 20px 25px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h3 {
  color: #374151;
  font-size: 1.2rem;
  font-weight: 600;
}

.card-header h3 i {
  color: #667eea;
  margin-right: 10px;
}

.badge {
  background: #667eea;
  color: white;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 500;
}

/* Today's Tasks */
.task-list {
  padding: 0;
}

.task-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 25px;
  border-bottom: 1px solid #f3f4f6;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.task-item:hover {
  background: #f9fafb;
}

.task-item:last-child {
  border-bottom: none;
}

.task-item.high {
  border-left: 4px solid #ef4444;
}

.task-item.medium {
  border-left: 4px solid #f59e0b;
}

.task-item.low {
  border-left: 4px solid #10b981;
}

.task-info {
  flex: 1;
}

.task-title {
  font-weight: 600;
  color: #374151;
  margin-bottom: 5px;
}

.task-details {
  display: flex;
  gap: 15px;
  font-size: 0.9rem;
  color: #6b7280;
}

.task-status {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
}

.task-status.pending {
  background: rgba(245, 158, 11, 0.1);
  color: #d97706;
}

.task-status.in-progress {
  background: rgba(59, 130, 246, 0.1);
  color: #2563eb;
}

.task-status.completed {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
}

/* Weekly Maintenance Overview */
.chart-container {
  padding: 25px;
}

.chart-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.chart-legend {
  display: flex;
  gap: 20px;
}

.legend-item {
  font-size: 0.875rem;
  font-weight: 500;
  position: relative;
  padding-left: 20px;
}

.legend-completed::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 12px;
  height: 12px;
  background: #10b981;
  border-radius: 2px;
}

.legend-scheduled::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 12px;
  height: 12px;
  background: #3b82f6;
  border-radius: 2px;
}

.legend-urgent::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 12px;
  height: 12px;
  background: #ef4444;
  border-radius: 2px;
}

.chart-content {
  height: 300px;
}

.bar-chart {
  width: 100%;
  height: 100%;
}

.chart-bar {
  transition: all 0.3s ease;
}

.chart-bar:hover {
  opacity: 0.8;
}

.chart-label {
  font-size: 12px;
  fill: #6b7280;
  font-weight: 500;
}

/* Recent Activities */
.activity-list {
  padding: 0;
  max-height: 400px;
  overflow-y: auto;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 20px 25px;
  border-bottom: 1px solid #f3f4f6;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}

.activity-icon.maintenance {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
}

.activity-icon.alert {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
}

.activity-icon.task {
  background: rgba(59, 130, 246, 0.1);
  color: #2563eb;
}

.activity-icon.system {
  background: rgba(107, 114, 128, 0.1);
  color: #6b7280;
}

.activity-content {
  flex: 1;
}

.activity-text {
  color: #374151;
  font-size: 0.95rem;
  line-height: 1.4;
}

.activity-time {
  color: #6b7280;
  font-size: 0.8rem;
  margin-top: 2px;
}

/* Maintenance Calendar */
.view-all-btn {
  background: #667eea;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 0.85rem;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.view-all-btn:hover {
  background: #5b6fd8;
}

.calendar-grid {
  padding: 25px;
  display: grid;
  gap: 15px;
}

.maintenance-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s ease;
  border-left: 4px solid transparent;
}

.maintenance-item:hover {
  background: #f9fafb;
  transform: translateX(3px);
}

.maintenance-item.high {
  border-left-color: #ef4444;
  background: rgba(239, 68, 68, 0.02);
}

.maintenance-item.medium {
  border-left-color: #f59e0b;
  background: rgba(245, 158, 11, 0.02);
}

.maintenance-item.low {
  border-left-color: #10b981;
  background: rgba(16, 185, 129, 0.02);
}

.maintenance-date {
  text-align: center;
  min-width: 50px;
}

.date-day {
  font-size: 1.5rem;
  font-weight: 700;
  color: #374151;
  line-height: 1;
}

.date-month {
  font-size: 0.8rem;
  color: #6b7280;
  text-transform: uppercase;
}

.maintenance-info {
  flex: 1;
}

.maintenance-title {
  font-weight: 600;
  color: #374151;
  margin-bottom: 3px;
}

.maintenance-machine {
  color: #6b7280;
  font-size: 0.9rem;
}

.maintenance-type {
  background: #f3f4f6;
  color: #6b7280;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 500;
}

/* Responsive Design */
@media (max-width: 768px) {
  .cmms-dashboard {
    padding: 15px;
  }

  .header-content {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .header-content h1 {
    font-size: 1.8rem;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .main-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .dashboard-header,
  .card {
    border-radius: 10px;
  }

  .stat-card {
    padding: 20px;
    flex-direction: column;
    text-align: center;
  }

  .stat-icon {
    font-size: 2rem;
  }

  .stat-number {
    font-size: 2rem;
  }
}
</style>