<template>
  <div class="dashboard-container">
    <!-- Header -->
    <div class="header">
      <div class="header-content">
        <div class="header-left">
          <h1 class="main-title">
            <span class="gradient-text">CMMS Dashboard</span>
          </h1>
          <p class="subtitle">Computerized Maintenance Management System</p>
        </div>
        <div class="header-right">
          <div class="time-display">
            <p class="time-label">Current Time</p>
            <p class="time-value">{{ currentTime }}</p>
          </div>
          <select v-model="selectedTimeRange" class="time-range-selector">
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="year">This Year</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Key Metrics Cards -->
      <div class="metrics-grid">
        <div class="stat-card stat-card-green">
          <div class="stat-card-content">
            <div class="stat-info">
              <p class="stat-label">Today's Tasks</p>
              <p class="stat-value">{{ todayStats.totalTasks }}</p>
              <p class="stat-subtitle">{{ todayStats.completedTasks }} completed</p>
            </div>
            <div class="stat-icon stat-icon-green">
              <i class="icon-check-circle">✓</i>
            </div>
          </div>
          <div class="stat-trend">
            <!-- <span class="trend-up">↗ 12% vs yesterday</span> -->
          </div>
        </div>

        <div class="stat-card stat-card-orange">
          <div class="stat-card-content">
            <div class="stat-info">
              <p class="stat-label">Pending Maintenance</p>
              <p class="stat-value">{{ todayStats.pendingMaintenance }}</p>
              <p class="stat-subtitle">Due today</p>
            </div>
            <div class="stat-icon stat-icon-orange">
              <i class="icon-clock">🕐</i>
            </div>
          </div>
        </div>

        <div class="stat-card stat-card-red">
          <div class="stat-card-content">
            <div class="stat-info">
              <p class="stat-label">Urgent Alerts</p>
              <p class="stat-value">{{ todayStats.urgentAlerts }}</p>
              <p class="stat-subtitle">Require attention</p>
            </div>
            <div class="stat-icon stat-icon-red">
              <i class="icon-alert">⚠️</i>
            </div>
          </div>
        </div>

        <div class="stat-card stat-card-blue">
          <div class="stat-card-content">
            <div class="stat-info">
              <p class="stat-label">Total Machines</p>
              <p class="stat-value">{{ todayStats.totalMachines }}</p>
              <p class="stat-subtitle">{{ todayStats.workingMachines }} working</p>
            </div>
            <div class="stat-icon stat-icon-blue">
              <i class="icon-settings">⚙️</i>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="quick-actions">
        <h3 class="section-title">Quick Actions</h3>
        <div class="action-buttons">
          <button class="action-btn action-btn-blue" @click="viewSchedule">
            <i class="btn-icon">📅</i>
            View Schedule
          </button>
          <button class="action-btn action-btn-green" @click="addTask">
            <i class="btn-icon">➕</i>
            Add New Task
          </button>
          <button class="action-btn action-btn-purple" @click="viewReports">
            <i class="btn-icon">📊</i>
            View Reports
          </button>
          <button class="action-btn action-btn-orange" @click="manageAlerts">
            <i class="btn-icon">🔔</i>
            Manage Alerts
          </button>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="charts-section">
        <div class="chart-row">
          <!-- Weekly Maintenance Chart -->
          <div class="chart-container">
            <div class="chart-header">
              <h3 class="chart-title">Weekly Maintenance Overview</h3>
              <div class="chart-legend">
                <span class="legend-item legend-completed">Completed</span>
                <span class="legend-item legend-scheduled">Scheduled</span>
                <span class="legend-item legend-urgent">Urgent</span>
              </div>
            </div>
            <div class="chart-content">
              <svg viewBox="0 0 700 300" class="bar-chart">
                <g v-for="(day, index) in maintenanceData" :key="index">
                  <!-- Completed bars -->
                  <rect 
                    :x="60 + index * 90" 
                    :y="250 - day.completed * 8" 
                    width="20" 
                    :height="day.completed * 8"
                    fill="#10B981"
                    class="chart-bar"
                  />
                  <!-- Scheduled bars -->
                  <rect 
                    :x="85 + index * 90" 
                    :y="250 - day.scheduled * 8" 
                    width="20" 
                    :height="day.scheduled * 8"
                    fill="#3B82F6"
                    class="chart-bar"
                  />
                  <!-- Urgent bars -->
                  <rect 
                    :x="110 + index * 90" 
                    :y="250 - day.urgent * 8" 
                    width="20" 
                    :height="day.urgent * 8"
                    fill="#EF4444"
                    class="chart-bar"
                  />
                  <!-- Day labels -->
                  <text :x="85 + index * 90" y="270" text-anchor="middle" class="chart-label">
                    {{ day.name }}
                  </text>
                </g>
              </svg>
            </div>
          </div>

          <!-- Machine Status Pie Chart -->
          <div class="chart-container">
            <div class="chart-header">
              <h3 class="chart-title">Machine Status Distribution</h3>
            </div>
            <div class="chart-content pie-chart-content">
              <div class="pie-chart-wrapper">
                <svg viewBox="0 0 200 200" class="pie-chart">
                  <circle cx="100" cy="100" r="80" fill="none" stroke="#10B981" stroke-width="20" 
                    stroke-dasharray="425.13 425.13" stroke-dashoffset="106.28" transform="rotate(-90 100 100)" />
                  <circle cx="100" cy="100" r="80" fill="none" stroke="#F59E0B" stroke-width="20" 
                    stroke-dasharray="42.51 425.13" stroke-dashoffset="63.77" transform="rotate(-90 100 100)" />
                  <circle cx="100" cy="100" r="80" fill="none" stroke="#EF4444" stroke-width="20" 
                    stroke-dasharray="26.57 425.13" stroke-dashoffset="21.26" transform="rotate(-90 100 100)" />
                  <circle cx="100" cy="100" r="80" fill="none" stroke="#6B7280" stroke-width="20" 
                    stroke-dasharray="10.63 425.13" stroke-dashoffset="0" transform="rotate(-90 100 100)" />
                </svg>
                <div class="pie-center">
                  <span class="pie-total">{{ todayStats.totalMachines }}</span>
                  <span class="pie-label">Total</span>
                </div>
              </div>
              <div class="pie-legend">
                <div class="pie-legend-item">
                  <span class="pie-color pie-color-green"></span>
                  <span>Running (85%)</span>
                </div>
                <div class="pie-legend-item">
                  <span class="pie-color pie-color-orange"></span>
                  <span>Maintenance (8%)</span>
                </div>
                <div class="pie-legend-item">
                  <span class="pie-color pie-color-red"></span>
                  <span>Offline (5%)</span>
                </div>
                <div class="pie-legend-item">
                  <span class="pie-color pie-color-gray"></span>
                  <span>Standby (2%)</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Monthly Trend Chart -->
        <div class="chart-container chart-full-width">
          <div class="chart-header">
            <h3 class="chart-title">Monthly Performance Trend</h3>
          </div>
          <div class="chart-content">
            <svg viewBox="0 0 800 300" class="line-chart">
              <!-- Grid lines -->
              <defs>
                <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                  <path d="M 10 0 L 0 0 0 10" fill="none" stroke="#E5E7EB" stroke-width="0.5"/>
                </pattern>
              </defs>
              <rect width="800" height="300" fill="url(#grid)" opacity="0.5"/>
              
              <!-- Task line -->
              <path d="M 80,180 L 180,200 L 280,160 L 380,140 L 480,150 L 580,120" 
                fill="none" stroke="#3B82F6" stroke-width="3" class="trend-line"/>
              
              <!-- Maintenance line -->
              <path d="M 80,220 L 180,240 L 280,210 L 380,190 L 480,200 L 580,170" 
                fill="none" stroke="#10B981" stroke-width="3" class="trend-line"/>
              
              <!-- Data points -->
              <g v-for="(point, index) in monthlyTrend" :key="index">
                <circle :cx="80 + index * 100" :cy="180 - (point.tasks - 400) * 0.5" r="4" fill="#3B82F6" class="data-point"/>
                <circle :cx="80 + index * 100" :cy="220 - (point.maintenance - 70) * 2" r="4" fill="#10B981" class="data-point"/>
                <text :x="80 + index * 100" y="280" text-anchor="middle" class="chart-label">{{ point.month }}</text>
              </g>
              
              <!-- Legend -->
              <g transform="translate(620, 40)">
                <line x1="0" y1="0" x2="20" y2="0" stroke="#3B82F6" stroke-width="3"/>
                <text x="25" y="5" class="legend-text">Tasks</text>
                <line x1="0" y1="20" x2="20" y2="20" stroke="#10B981" stroke-width="3"/>
                <text x="25" y="25" class="legend-text">Maintenance</text>
              </g>
            </svg>
          </div>
        </div>
      </div>

      <!-- Tasks and Alerts Section -->
      <div class="bottom-section">
        <!-- Urgent Tasks -->
        <div class="tasks-panel">
          <div class="panel-header">
            <h3 class="panel-title">Today's Priority Tasks</h3>
            <button class="view-all-btn">View All</button>
          </div>
          <div class="tasks-list">
            <div v-for="task in urgentTasks" :key="task.id" class="task-item" :class="getPriorityClass(task.priority)">
              <div class="task-info">
                <div class="task-machine">{{ task.machine }}</div>
                <div class="task-description">{{ task.task }}</div>
                <div class="task-time">Due: {{ task.dueTime }}</div>
              </div>
              <div class="task-priority" :class="getPriorityClass(task.priority)">
                {{ task.priority }}
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Alerts -->
        <div class="alerts-panel">
          <div class="panel-header">
            <h3 class="panel-title">Recent Alerts</h3>
            <button class="clear-all-btn">Clear All</button>
          </div>
          <div class="alerts-list">
            <div v-for="alert in recentAlerts" :key="alert.id" class="alert-item" :class="getAlertClass(alert.type)">
              <div class="alert-icon" :class="getAlertClass(alert.type)">
                {{ getAlertIcon(alert.type) }}
              </div>
              <div class="alert-content">
                <div class="alert-message">{{ alert.message }}</div>
                <div class="alert-time">{{ alert.time }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Statistics Summary -->
      <div class="statistics-section">
        <h3 class="section-title">Statistics Summary</h3>
        <div class="stats-grid">
          <div class="stats-item">
            <div class="stats-number">{{ todayStats.workInstructions }}</div>
            <div class="stats-label">Work Instructions</div>
            <div class="stats-change stats-positive">+{{ todayStats.newInstructionsToday }} today</div>
          </div>
          <div class="stats-item">
            <div class="stats-number">{{ getCompletionRate() }}%</div>
            <div class="stats-label">Completion Rate</div>
            <div class="stats-change stats-positive">+5% vs last week</div>
          </div>
          <div class="stats-item">
            <div class="stats-number">{{ getAverageTime() }}</div>
            <div class="stats-label">Avg Response Time</div>
            <div class="stats-change stats-negative">-15min vs last month</div>
          </div>
          <div class="stats-item">
            <div class="stats-number">{{ getUptime() }}%</div>
            <div class="stats-label">System Uptime</div>
            <div class="stats-change stats-positive">+2% vs last quarter</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CMMSDashboard',
  data() {
    return {
      currentTime: '',
      selectedTimeRange: 'today',
      todayStats: {
        totalTasks: 45,
        completedTasks: 32,
        pendingMaintenance: 8,
        urgentAlerts: 5,
        totalMachines: 156,
        workingMachines: 142,
        workInstructions: 234,
        newInstructionsToday: 12
      },
      maintenanceData: [
        { name: 'Mon', completed: 12, scheduled: 15, urgent: 2 },
        { name: 'Tue', completed: 19, scheduled: 18, urgent: 1 },
        { name: 'Wed', completed: 15, scheduled: 20, urgent: 3 },
        { name: 'Thu', completed: 22, scheduled: 16, urgent: 0 },
        { name: 'Fri', completed: 18, scheduled: 22, urgent: 4 },
        { name: 'Sat', completed: 8, scheduled: 10, urgent: 1 },
        { name: 'Sun', completed: 5, scheduled: 6, urgent: 0 }
      ],
      monthlyTrend: [
        { month: 'Jan', tasks: 420, maintenance: 89 },
        { month: 'Feb', tasks: 380, maintenance: 76 },
        { month: 'Mar', tasks: 460, maintenance: 95 },
        { month: 'Apr', tasks: 510, maintenance: 108 },
        { month: 'May', tasks: 485, maintenance: 102 },
        { month: 'Jun', tasks: 520, maintenance: 115 }
      ],
      urgentTasks: [
        { id: 1, machine: 'CNC-001', task: 'Belt Replacement', dueTime: '10:30 AM', priority: 'High' },
        { id: 2, machine: 'PRESS-005', task: 'Hydraulic Check', dueTime: '2:00 PM', priority: 'High' },
        { id: 3, machine: 'CONV-012', task: 'Motor Inspection', dueTime: '4:15 PM', priority: 'Medium' },
        { id: 4, machine: 'DRILL-003', task: 'Calibration', dueTime: '5:30 PM', priority: 'High' },
        { id: 5, machine: 'WELD-008', task: 'Gas Leak Check', dueTime: 'Overdue', priority: 'Critical' }
      ],
      recentAlerts: [
        { id: 1, type: 'error', message: 'Temperature sensor failure on Line 3', time: '5 min ago' },
        { id: 2, type: 'warning', message: 'Scheduled maintenance due for Press-002', time: '15 min ago' },
        { id: 3, type: 'info', message: 'Maintenance completed on CNC-007', time: '1 hour ago' },
        { id: 4, type: 'error', message: 'Conveyor belt jam detected', time: '2 hours ago' }
      ]
    }
  },
  mounted() {
    this.updateTime();
    this.timeInterval = setInterval(this.updateTime, 1000);
  },
  beforeDestroy() {
    if (this.timeInterval) {
      clearInterval(this.timeInterval);
    }
  },
  methods: {
    updateTime() {
      const now = new Date();
      this.currentTime = now.toLocaleTimeString('en-US', { 
        hour12: true, 
        hour: '2-digit', 
        minute: '2-digit',
        second: '2-digit'
      });
    },
    getPriorityClass(priority) {
      const classes = {
        'Critical': 'priority-critical',
        'High': 'priority-high',
        'Medium': 'priority-medium',
        'Low': 'priority-low'
      };
      return classes[priority] || 'priority-medium';
    },
    getAlertClass(type) {
      const classes = {
        'error': 'alert-error',
        'warning': 'alert-warning',
        'info': 'alert-info'
      };
      return classes[type] || 'alert-info';
    },
    getAlertIcon(type) {
      const icons = {
        'error': '🔴',
        'warning': '🟡',
        'info': '🔵'
      };
      return icons[type] || '🔵';
    },
    getCompletionRate() {
      return Math.round((this.todayStats.completedTasks / this.todayStats.totalTasks) * 100);
    },
    getAverageTime() {
      return '2.5h';
    },
    getUptime() {
      return 99.2;
    },
    viewSchedule() {
      console.log('View Schedule clicked');
    },
    addTask() {
      console.log('Add Task clicked');
    },
    viewReports() {
      console.log('View Reports clicked');
    },
    manageAlerts() {
      console.log('Manage Alerts clicked');
    }
  }
}
</script>

<style scoped>
/* Global Styles */
.dashboard-container {
  min-height: 100vh;
  background: rgb(245, 245, 245);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Header Styles */
.header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 30px;
  max-width: 1400px;
  margin: 0 auto;
}

.header-left .main-title {
  font-size: 2.5rem;
  font-weight: 800;
  margin: 0;
  margin-bottom: 5px;
}

.gradient-text {
  background: black;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.subtitle {
  color: #6b7280;
  font-size: 1rem;
  margin: 0;
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

.time-range-selector {
  padding: 10px 15px;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  background: white;
  font-size: 0.875rem;
  font-weight: 500;
  outline: none;
  transition: all 0.3s ease;
}

.time-range-selector:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Main Content */
.main-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 30px 5px 10px 5px;
  space-y: 30px;
}

/* Metrics Grid */
.metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 25px;
  margin-bottom: 40px;
}

.stat-card {
  background: white;
  border-radius: 20px;
  padding: 25px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  border-left: 5px solid;
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 100px;
  height: 100px;
  opacity: 0.05;
  border-radius: 50%;
  transform: translate(30px, -30px);
}

.stat-card-green {
  border-left-color: #10b981;
}

.stat-card-green::before {
  background: #10b981;
}

.stat-card-orange {
  border-left-color: #f59e0b;
}

.stat-card-orange::before {
  background: #f59e0b;
}

.stat-card-red {
  border-left-color: #ef4444;
}

.stat-card-red::before {
  background: #ef4444;
}

.stat-card-blue {
  border-left-color: #3b82f6;
}

.stat-card-blue::before {
  background: #3b82f6;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
}

.stat-card-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.stat-info {
  flex: 1;
}

.stat-label {
  font-size: 0.875rem;
  color: black;
  font-weight: 500;
  margin: 0 0 8px 0;
}

.stat-value {
  font-size: 2.5rem;
  font-weight: 800;
  color: #111827;
  margin: 0 0 5px 0;
  line-height: 1;
}

.stat-subtitle {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin-left: 15px;
}

.stat-icon-green {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.stat-icon-orange {
  background: rgba(245, 158, 11, 0.1);
  color: #f59e0b;
}

.stat-icon-red {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.stat-icon-blue {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}

.stat-trend {
  margin-top: 15px;
}

.trend-up {
  color: #10b981;
  font-size: 0.875rem;
  font-weight: 500;
}

/* Quick Actions */
.quick-actions {
  margin-bottom: 40px;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: black;
  margin-bottom: 20px;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.action-buttons {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 15px 25px;
  border: none;
  border-radius: 15px;
  font-size: 1rem;
  font-weight: 600;
  color: white;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
}

.action-btn-blue {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.action-btn-green {
  background: linear-gradient(135deg, #10b981, #047857);
}

.action-btn-purple {
  background: linear-gradient(135deg, #8b5cf6, #5b21b6);
}

.action-btn-orange {
  background: linear-gradient(135deg, #f59e0b, #d97706);
}

.btn-icon {
  margin-right: 8px;
  font-size: 1.2rem;
}

/* Charts Section */
.charts-section {
  margin-bottom: 40px;
}

.chart-row {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 30px;
  margin-bottom: 30px;
}

.chart-container {
  background: white;
  border-radius: 20px;
  padding: 25px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.chart-full-width {
  grid-column: 1 / -1;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
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
  background: #ef4444;
  border-radius: 2px;
}

.chart-content {
  height: 300px;
}

.bar-chart, .line-chart, .pie-chart {
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

.trend-line {
  transition: all 0.3s ease;
}

.data-point {
  transition: all 0.3s ease;
  cursor: pointer;
}

.data-point:hover {
  r: 6;
}

.legend-text {
  font-size: 12px;
  fill: #374151;
  font-weight: 500;
}

/* Pie Chart Specific */
.pie-chart-content {
  display: flex;
  align-items: center;
  gap: 30px;
  height: 250px;
}

.pie-chart-wrapper {
  position: relative;
  width: 200px;
  height: 200px;
}

.pie-center {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
}

.pie-total {
  display: block;
  font-size: 2rem;
  font-weight: 800;
  color: #111827;
}

.pie-label {
  display: block;
  font-size: 0.875rem;
  color: #6b7280;
  font-weight: 500;
}

.pie-legend {
  flex: 1;
}

.pie-legend-item {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
}

.pie-color {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  margin-right: 12px;
}

.pie-color-green {
  background: #10b981;
}

.pie-color-orange {
  background: #f59e0b;
}

.pie-color-red {
  background: #ef4444;
}

.pie-color-gray {
  background: #6b7280;
}

/* Bottom Section */
.bottom-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  margin-bottom: 40px;
}

.tasks-panel, .alerts-panel {
  background: white;
  border-radius: 20px;
  padding: 25px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.panel-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.view-all-btn, .clear-all-btn {
  background: none;
  border: none;
  color: #3b82f6;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  padding: 5px 10px;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.view-all-btn:hover, .clear-all-btn:hover {
  background: rgba(59, 130, 246, 0.1);
}

.tasks-list, .alerts-list {
  max-height: 300px;
  overflow-y: auto;
}

.task-item, .alert-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  margin-bottom: 10px;
  border-radius: 12px;
  background: #f9fafb;
  border-left: 4px solid #e5e7eb;
  transition: all 0.3s ease;
}

.task-item:hover, .alert-item:hover {
  background: #f3f4f6;
  transform: translateX(5px);
}

.task-info {
  flex: 1;
}

.task-machine {
  font-weight: 700;
  color: #111827;
  font-size: 0.875rem;
}

.task-description {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 5px 0;
}

.task-time {
  font-size: 0.75rem;
  color: #9ca3af;
}

.task-priority {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.priority-critical {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
  border-left-color: #dc2626 !important;
}

.priority-high {
  background: rgba(245, 158, 11, 0.1);
  color: #d97706;
  border-left-color: #d97706 !important;
}

.priority-medium {
  background: rgba(59, 130, 246, 0.1);
  color: #2563eb;
  border-left-color: #2563eb !important;
}

.priority-low {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
  border-left-color: #059669 !important;
}

.alert-item {
  padding: 12px 15px;
}

.alert-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
  font-size: 1rem;
}

.alert-error {
  background: rgba(239, 68, 68, 0.1);
  border-left-color: #ef4444 !important;
}

.alert-warning {
  background: rgba(245, 158, 11, 0.1);
  border-left-color: #f59e0b !important;
}

.alert-info {
  background: rgba(59, 130, 246, 0.1);
  border-left-color: #3b82f6 !important;
}

.alert-content {
  flex: 1;
}

.alert-message {
  font-size: 0.875rem;
  color: #374151;
  font-weight: 500;
  margin-bottom: 5px;
}

.alert-time {
  font-size: 0.75rem;
  color: #9ca3af;
}

/* Statistics Section */
.statistics-section {
  background: rgba(255, 255, 255, 1);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  padding: 30px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 30px;
  margin-top: 25px;
}

.stats-item {
  text-align: center;
  padding: 20px;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.6));
  border-radius: 15px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
}

.stats-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.stats-number {
  font-size: 2.5rem;
  font-weight: 800;
  color: #111827;
  margin-bottom: 10px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.stats-label {
  font-size: 0.875rem;
  color: black;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
}

.stats-change {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 12px;
}

.stats-positive {
  color: #10b981;
  background: rgba(16, 185, 129, 0.1);
}

.stats-negative {
  color: #ef4444;
  background: rgba(239, 68, 68, 0.1);
}

/* Responsive Design */
@media (max-width: 1200px) {
  .chart-row {
    grid-template-columns: 1fr;
  }
  
  .pie-chart-content {
    flex-direction: column;
    height: auto;
    gap: 20px;
  }
}

@media (max-width: 768px) {
  .main-content {
    padding: 20px;
  }
  
  .header-content {
    flex-direction: column;
    gap: 20px;
    text-align: center;
  }
  
  .metrics-grid {
    grid-template-columns: 1fr;
  }
  
  .bottom-section {
    grid-template-columns: 1fr;
  }
  
  .action-buttons {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .stat-value {
    font-size: 2rem;
  }
  
  .stats-number {
    font-size: 2rem;
  }
}

@media (max-width: 480px) {
  .header-content {
    padding: 15px 20px;
  }
  
  .main-content {
    padding: 15px;
  }
  
  .stat-card, .chart-container, .tasks-panel, .alerts-panel {
    padding: 20px;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .main-title {
    font-size: 2rem !important;
  }
}

/* Animations */
@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.stat-card {
  animation: slideInUp 0.6s ease-out;
}

.chart-container {
  animation: slideInUp 0.8s ease-out;
}

.tasks-panel, .alerts-panel {
  animation: slideInUp 1s ease-out;
}

/* Scrollbar Styling */
.tasks-list::-webkit-scrollbar,
.alerts-list::-webkit-scrollbar {
  width: 6px;
}

.tasks-list::-webkit-scrollbar-track,
.alerts-list::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 10px;
}

.tasks-list::-webkit-scrollbar-thumb,
.alerts-list::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}

.tasks-list::-webkit-scrollbar-thumb:hover,
.alerts-list::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Loading States */
.loading-shimmer {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 2s infinite;
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}

/* Glassmorphism Effects */
.glass-effect {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.18);
}

/* Hover Effects */
.hover-lift:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);

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
}

</style>