
<template>
  <div class="done-rating-chart">
    <div class="chart-header">
      <h3 class="chart-title">
        <svg class="title-icon" viewBox="0 0 24 24" fill="currentColor">
          <!-- Check Circle Icon -->
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 
                  10-4.48 10-10S17.52 2 12 2zm-1.2 14.3l-4.1-4.1 
                  1.4-1.4 2.7 2.7 5.7-5.7 1.4 1.4-7.1 7.1z"/>
        </svg>
        Done Rating Progress
      </h3>
      <div class="date-picker">
        <div class="date-input-wrapper">
          <input 
            type="date" 
            v-model="dateFrom" 
            :max="dateTo"
            class="date-input"
          />
          <svg class="date-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
          </svg>
        </div>
        <span class="date-separator">to</span>
        <div class="date-input-wrapper">
          <input 
            type="date" 
            v-model="dateTo" 
            :min="dateFrom"
            class="date-input"
          />
          <svg class="date-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
          </svg>
        </div>
        <button class="apply-button" @click="applyDates">Apply</button>
      </div>
    </div>

    <div class="chart-stats">
      <div class="stat-card">
        <div class="stat-value">{{ averageCompletion }}%</div>
        <div class="stat-label">Average Completion</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">{{ totalDays }}</div>
        <div class="stat-label">Total Days</div>
      </div>
      <div class="stat-card">
        <div class="stat-value">{{ bestDay.completion }}%</div>
        <div class="stat-label">Best Day ({{ bestDay.day }})</div>
      </div>
    </div>
    
    <div class="chart-content">
      <svg 
        :viewBox="`0 0 ${svgWidth} 500`" 
        class="done-rating-svg"
        ref="svgRef"
      >
        <!-- Background grid -->
        <defs>
          <pattern id="grid" width="40" height="30" patternUnits="userSpaceOnUse">
            <path d="M 40 0 L 0 0 0 30" fill="none" stroke="#e5e7eb" stroke-width="1" opacity="0.5"/>
          </pattern>
        </defs>
        <rect width="100%" height="100%" fill="url(#grid)" />

        <!-- Y-axis grid lines -->
        <g class="grid-lines">
          <line v-for="i in [1,2,3,4,5]" :key="i" 
            :x1="60" 
            :x2="svgWidth - 30" 
            :y1="80 + i * gridSpacing" 
            :y2="80 + i * gridSpacing" 
            stroke="#d1d5db" 
            stroke-width="1" 
            stroke-dasharray="4,4"
          />
        </g>

        <!-- Chart bars (complete 100% height columns) -->
        <g class="bars-group">
          <g v-for="(data, index) in chartData" :key="index" class="bar-group">
            <!-- Full bar background (gray - incomplete) -->
            <rect 
              :x="getBarX(index) - barWidth / 2"
              :y="80"
              :width="barWidth"
              :height="chartHeight"
              fill="#e5e7eb"
              class="bar-background"
              rx="8"
              ry="8"
            />
            
            <!-- Completed portion -->
            <rect 
              :x="getBarX(index) - barWidth / 2"
              :y="80 + chartHeight - (data.completed / 100 * chartHeight)"
              :width="barWidth"
              :height="data.completed / 100 * chartHeight"
              fill="#32CD32"
              class="bar-completed"
              rx="8"
              ry="8"
              @mouseenter="showTooltip($event, data, index)"
              @mouseleave="hideTooltip"
            />

            <!-- Percentage labels on bars -->
            <text 
              :x="getBarX(index)"
              :y="70"
              text-anchor="middle"
              class="percentage-label"
              :class="{ 'high-performance': data.completed >= 80 }"
            >
              {{ data.completed }}%
            </text>

            <!-- Day labels -->
            <text 
              :x="getBarX(index)"
              :y="chartHeight + 110"
              text-anchor="middle"
              class="day-label"
            >
              {{ data.day }}
            </text>
          </g>
        </g>

        <!-- Trend line connecting completed portions -->
        <path 
          class="trend-line"
          :d="trendPath"
          stroke="#ffb175"
          stroke-width="3"
          fill="none"
          stroke-linecap="round"
          stroke-linejoin="round"
          filter="url(#glow)"
        />

        <!-- Trend line dots -->
        <g class="trend-dots">
          <circle
            v-for="(data, index) in chartData" 
            :key="'dot' + index"
            :cx="getBarX(index)"
            :cy="80 + chartHeight - (data.completed / 100 * chartHeight)"
            r="4"
            fill="#1e3a8a"
            class="trend-dot"
            @mouseenter="showTooltip($event, data, index)"
            @mouseleave="hideTooltip"
          />
        </g>

        <!-- Y-axis labels -->
        <g class="y-axis-labels">
          <text v-for="level in [0,1,2,3,4,5]" :key="level" 
            x="45" 
            :y="85 + level * gridSpacing" 
            text-anchor="end" 
            class="y-label"
          >
            {{ 100 - level * 20 }}%
          </text>
        </g>

        <!-- Glow effect -->
        <defs>
          <filter id="glow">
            <feGaussianBlur stdDeviation="2" result="coloredBlur"/>
            <feMerge> 
              <feMergeNode in="coloredBlur"/>
              <feMergeNode in="SourceGraphic"/>
            </feMerge>
          </filter>
        </defs>
      </svg>

      <!-- Tooltip -->
      <div 
        v-if="tooltip.visible" 
        class="tooltip"
        :style="{ left: tooltip.x + 'px', top: tooltip.y + 'px' }"
      >
        <div class="tooltip-header">Day {{ tooltip.data.day }}</div>
        <div class="tooltip-body">
          <div class="tooltip-row">
            <span class="tooltip-label">Completed:</span>
            <span class="tooltip-value completed">{{ tooltip.data.completed }}%</span>
          </div>
          <div class="tooltip-row">
            <span class="tooltip-label">Remaining:</span>
            <span class="tooltip-value remaining">{{ tooltip.data.incomplete }}%</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="modalVisible" class="modal-overlay" @click="closeModal">
      <div class="modal-box" @click.stop>
        <p class="modal-message">{{ modalMessage }}</p>
        <button class="modal-button" @click="handleModalOk">OK</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'

const chartData = ref([])
const dateFrom = ref('')
const dateTo = ref('')
const svgRef = ref(null)
const tooltip = ref({
  visible: false,
  x: 0,
  y: 0,
  data: {}
})
const modalVisible = ref(false)
const modalMessage = ref('')
const modalAction = ref(null)

const maxDays = 35
const barWidth = 40
const barSpacing = 70
const chartHeight = 300

const currentDate = new Date(2025, 8, 12) // September 12, 2025

// Computed properties
const svgWidth = computed(() => {
  const numDays = chartData.value.length
  if (numDays === 0) return 200
  return 120 + (numDays - 1) * barSpacing + barWidth
})

const gridSpacing = computed(() => chartHeight / 5)

const totalDays = computed(() => chartData.value.length)

const averageCompletion = computed(() => {
  if (chartData.value.length === 0) return 0
  const sum = chartData.value.reduce((acc, data) => acc + data.completed, 0)
  return Math.round(sum / chartData.value.length)
})

const bestDay = computed(() => {
  if (chartData.value.length === 0) return { day: '-', completion: 0 }
  const best = chartData.value.reduce((max, current) => 
    current.completed > max.completed ? current : max
  )
  return { day: best.day, completion: best.completed }
})

const trendPath = computed(() => {
  if (chartData.value.length < 2) return ''
  
  const points = chartData.value.map((data, index) => {
    const x = getBarX(index)
    const y = 80 + chartHeight - (data.completed / 100 * chartHeight)
    return `${x},${y}`
  })
  
  return `M${points.join(' L')}`
})

// Helper functions
const getBarX = (index) => {
  return 90 + index * barSpacing
}

const showTooltip = (event, data, index) => {
  const rect = svgRef.value.getBoundingClientRect()
  tooltip.value = {
    visible: true,
    x: event.clientX - rect.left + 10,
    y: event.clientY - rect.top - 10,
    data: data
  }
}

const hideTooltip = () => {
  tooltip.value.visible = false
}

const showModal = (message, action = null) => {
  modalMessage.value = message
  modalAction.value = action
  modalVisible.value = true
}

const closeModal = () => {
  modalVisible.value = false
  modalAction.value = null
}

const handleModalOk = () => {
  if (modalAction.value) {
    modalAction.value()
  }
  closeModal()
}

// Initialize dates for current month
const initDates = () => {
  const now = currentDate
  const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
  const today = new Date(now)

  dateFrom.value = firstDay.toISOString().split('T')[0]
  dateTo.value = today.toISOString().split('T')[0]
}

// Generate realistic mock data with better distribution
const generateMockData = (startDate, endDate) => {
  const start = new Date(startDate)
  const end = new Date(endDate)
  const data = []
  let current = new Date(start)
  let previousCompletion = 70 // Start with a reasonable baseline

  while (current <= end) {
    const dayStr = current.getDate().toString().padStart(2, '0')
    
    // Generate more realistic data with some continuity
    const variation = (Math.random() - 0.5) * 30 // ±15%
    let completed = Math.round(previousCompletion + variation)
    
    // Keep within realistic bounds
    completed = Math.max(40, Math.min(95, completed))
    
    // Weekend effect (slightly lower on weekends)
    const dayOfWeek = current.getDay()
    if (dayOfWeek === 0 || dayOfWeek === 6) {
      completed = Math.max(45, completed - 10)
    }
    
    const incomplete = 100 - completed
    previousCompletion = completed

    data.push({
      day: dayStr,
      completed,
      incomplete,
      date: new Date(current)
    })

    current.setDate(current.getDate() + 1)
  }

  return data
}

// Update chart data
const updateChartData = async () => {
  if (!dateFrom.value || !dateTo.value) return

  let start = new Date(dateFrom.value)
  let end = new Date(dateTo.value)
  const current = currentDate

  // Check if dateTo is before dateFrom
  if (end < start) {
    showModal('Date To cannot be before Date From. Resetting to valid range.', () => {
      const now = current
      const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
      dateFrom.value = firstDay.toISOString().split('T')[0]
      dateTo.value = now.toISOString().split('T')[0]
      updateChartData()
    })
    return
  }

  // Check if dateFrom is after dateTo
  if (start > end) {
    showModal('Date From cannot be after Date To. Resetting to valid range.', () => {
      const now = current
      const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
      dateFrom.value = firstDay.toISOString().split('T')[0]
      dateTo.value = now.toISOString().split('T')[0]
      updateChartData()
    })
    return
  }

  // Check if dateFrom equals current date (today)
  if (start.toDateString() === current.toDateString()) {
    dateTo.value = current.toISOString().split('T')[0]
    chartData.value = generateMockData(dateFrom.value, dateTo.value)
    await nextTick()
    return
  }

  // Check if dateTo is after current date
  if (end > current) {
    showModal('Date To cannot be after the current date. Resetting to current date.', () => {
      const firstDay = new Date(current.getFullYear(), current.getMonth(), 1)
      dateFrom.value = firstDay.toISOString().split('T')[0]
      dateTo.value = current.toISOString().split('T')[0]
      updateChartData()
    })
    return
  }

  const daysDiff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1

  // Check if date range exceeds maxDays
  if (daysDiff > maxDays) {
    showModal(`Maximum date range is ${maxDays} days. Adjusting to ${maxDays} days from Date From.`, () => {
      const newEnd = new Date(start)
      newEnd.setDate(newEnd.getDate() + 34)
      dateTo.value = newEnd.toISOString().split('T')[0]
      updateChartData()
    })
    return
  }

  chartData.value = generateMockData(dateFrom.value, dateTo.value)
  await nextTick()
}

const applyDates = () => {
  updateChartData()
}

// Lifecycle hooks
onMounted(() => {
  initDates()
  updateChartData()
})
</script>

<style scoped>
.done-rating-chart {
  width: 100%;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  overflow: hidden;
  margin: 20px 0;
  border: 1px solid #e5e7eb;
  position: relative;
}

.chart-header {
  background: #f8fafc;
  padding: 20px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
}

.chart-header::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
}

.chart-title {
  color: black;
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 10px;
}

.title-icon {
  width: 22px;
  height: 22px;
  color: #9ca3af;
}

.date-picker {
  display: flex;
  align-items: center;
  gap: 12px;
}

.date-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.date-input {
  padding: 12px 16px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.95rem;
  background: rgba(255, 255, 255, 0.1);
  color: black;
  backdrop-filter: blur(8px);
  transition: all 0.3s ease;
  padding-right: 45px;
  width: 160px;
  cursor: pointer;
}

.date-input:focus {
  outline: none;
  border-color: rgba(255, 255, 255, 0.5);
  background: rgba(255, 255, 255, 0.2);
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
}

.date-input::-webkit-calendar-picker-indicator {
  opacity: 0;
  width: 100%;
  height: 100%;
  position: absolute;
  cursor: pointer;
}

.date-icon {
  position: absolute;
  right: 12px;
  width: 20px;
  height: 20px;
  color: #9ca3af;
  pointer-events: none;
}

.date-separator {
  font-size: 0.95rem;
  color: #9ca3af;
  font-weight: 500;
}

.apply-button {
  padding: 12px 20px;
  background: #15803d;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.apply-button:hover {
  background: #0f6b2e;
}

.chart-stats {
  display: flex;
  padding: 20px 24px;
  gap: 16px;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.stat-card {
  flex: 1;
  background: #ffffff;
  padding: 16px 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
  transition: transform 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 4px;
}

.stat-label {
  font-size: 0.85rem;
  color: #6b7280;
  font-weight: 500;
}

.chart-content {
  padding: 24px;
  position: relative;
  background: #ffffff;
  overflow-x: auto;
}

.done-rating-svg {
  width: 100%;
  height: 500px;
  overflow: visible;
}

.bar-background {
  transition: all 0.3s ease;
}

.bar-completed {
  transition: all 0.3s ease;
  cursor: pointer;
}

.bar-completed:hover {
  filter: brightness(1.1);
}

.trend-line {
  transition: all 0.5s ease;
  animation: drawLine 2s ease-in-out;
}

@keyframes drawLine {
  0% {
    stroke-dasharray: 1000;
    stroke-dashoffset: 1000;
  }
  100% {
    stroke-dasharray: 1000;
    stroke-dashoffset: 0;
  }
}

.trend-dot {
  cursor: pointer;
  transition: all 0.3s ease;
  animation: popIn 0.6s ease-out;
  animation-fill-mode: both;
}

.trend-dot:hover {
  r: 6;
  filter: drop-shadow(0 4px 8px rgba(31, 41, 55, 0.3));
}

@keyframes popIn {
  0% {
    transform: scale(0);
    opacity: 0;
  }
  80% {
    transform: scale(1.2);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.percentage-label {
  font-size: 12px;
  font-weight: 600;
  fill: #374151;
  transition: all 0.3s ease;
}

.percentage-label.high-performance {
  fill: #15803d;
  font-weight: 700;
}

.day-label {
  font-size: 13px;
  font-weight: 600;
  fill: #374151;
}

.y-label {
  font-size: 11px;
  font-weight: 500;
  fill: #6b7280;
}

.tooltip {
  position: absolute;
  background: rgba(31, 41, 55, 0.95);
  color: #f3f4f6;
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 13px;
  z-index: 1000;
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
  pointer-events: none;
  transform: translateX(-50%);
}

.tooltip-header {
  font-weight: 600;
  margin-bottom: 8px;
  color: #f3f4f6;
}

.tooltip-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.tooltip-row:last-child {
  margin-bottom: 0;
}

.tooltip-label {
  color: #9ca3af;
  margin-right: 12px;
}

.tooltip-value.completed {
  color: #15803d;
  font-weight: 600;
}

.tooltip-value.remaining {
  color: #dc2626;
  font-weight: 600;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
}

.modal-box {
  background: white;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  max-width: 400px;
  width: 90%;
}

.modal-message {
  margin-bottom: 15px;
  font-size: 1rem;
  color: #333;
}

.modal-button {
  padding: 8px 16px;
  background: #15803d;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.3s;
}

.modal-button:hover {
  background: #0f6b2e;
}

/* Responsive design */
@media (max-width: 768px) {
  .chart-header {
    flex-direction: column;
    gap: 16px;
    padding: 16px 20px;
  }

  .date-picker {
    width: 100%;
    justify-content: center;
    flex-wrap: wrap;
    gap: 8px;
  }

  .chart-stats {
    flex-direction: column;
    gap: 12px;
  }

  .stat-card {
    padding: 14px 18px;
  }

  .stat-value {
    font-size: 1.5rem;
  }

  .chart-content {
    padding: 20px 15px;
  }

  .done-rating-svg {
    height: 450px;
  }

  .percentage-label,
  .day-label {
    font-size: 10px;
  }
}

@media (max-width: 480px) {
  .chart-title {
    font-size: 1.1rem;
  }
  
  .date-input {
    padding: 10px 14px;
    font-size: 0.85rem;
    width: 140px;
  }

  .apply-button {
    padding: 10px 16px;
    font-size: 0.85rem;
  }
}
</style>
