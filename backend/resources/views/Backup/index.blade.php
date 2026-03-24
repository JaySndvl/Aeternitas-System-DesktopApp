    @extends('layouts.dashboard-base', ['user' => $user, 'activeRoute' => 'attendance.schedule'])

@section('title', 'Schedule Management')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Schedule Management</h1>
            <p class="mt-1 text-sm text-gray-600">Create and manage employee work schedules</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('attendance.schedule.reports') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-chart-bar mr-2"></i>
                View Reports
            </a>
        
        </div>
    </div>
    <!-- Schedule Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Total Schedules</p>
                    <p class="text-lg font-semibold text-gray-900">32</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Active Schedules</p>
                    <p class="text-lg font-semibold text-gray-900">28</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Shift Changes</p>
                    <p class="text-lg font-semibold text-gray-900">4</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-purple-600"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">Scheduled Today</p>
                    <p class="text-lg font-semibold text-gray-900">24</p>
                </div>
            </div>
        </div>
    </div>

        <!-- Schedule Period Management -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-medium text-gray-900">Schedule Periods</h3>
                <button onclick="addNewPeriod()" class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Add New Period
                </button>
            </div>
            
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Period Card 1 -->
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer" onclick="selectPeriod('period-1')">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar text-blue-600"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="font-medium text-gray-900">September 1-15, 2025</h4>
                            <p class="text-sm text-gray-500">Pay Period 1</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Active
                    </span>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total Employees:</span>
                        <span class="font-medium text-gray-900">24</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Scheduled:</span>
                        <span class="font-medium text-gray-900">22</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Pending:</span>
                        <span class="font-medium text-yellow-600">2</span>
                    </div>
                </div>
                <div class="mt-4 flex space-x-2">
                    <button class="text-blue-600 hover:text-blue-900 text-sm transition-colors">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    <button class="text-green-600 hover:text-green-900 text-sm transition-colors">
                        <i class="fas fa-eye mr-1"></i>View
                    </button>
                    <button class="text-purple-600 hover:text-purple-900 text-sm transition-colors">
                        <i class="fas fa-users mr-1"></i>Assign Employees
                    </button>
                </div>
            </div>

            <!-- Period Card 2 -->
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer" onclick="selectPeriod('period-2')">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar text-blue-600"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="font-medium text-gray-900">September 16-30, 2025</h4>
                            <p class="text-sm text-gray-500">Pay Period 2</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        Draft
                    </span>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total Employees:</span>
                        <span class="font-medium text-gray-900">24</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Scheduled:</span>
                        <span class="font-medium text-gray-900">18</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Pending:</span>
                        <span class="font-medium text-yellow-600">6</span>
                    </div>
                </div>
                <div class="mt-4 flex space-x-2">
                    <button class="text-blue-600 hover:text-blue-900 text-sm transition-colors">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    <button class="text-green-600 hover:text-green-900 text-sm transition-colors">
                        <i class="fas fa-eye mr-1"></i>View
                    </button>
                    <button class="text-purple-600 hover:text-purple-900 text-sm transition-colors">
                        <i class="fas fa-users mr-1"></i>Assign Employees
                    </button>
                </div>
            </div>

            <!-- Period Card 3 -->
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer" onclick="selectPeriod('period-3')">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar text-blue-600"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="font-medium text-gray-900">October 1-15, 2025</h4>
                            <p class="text-sm text-gray-500">Pay Period 3</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        Planning
                    </span>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total Employees:</span>
                        <span class="font-medium text-gray-900">24</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Scheduled:</span>
                        <span class="font-medium text-gray-900">0</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Pending:</span>
                        <span class="font-medium text-yellow-600">24</span>
                    </div>
                </div>
                <div class="mt-4 flex space-x-2">
                    <button class="text-blue-600 hover:text-blue-900 text-sm transition-colors">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </button>
                    <button class="text-green-600 hover:text-green-900 text-sm transition-colors">
                        <i class="fas fa-eye mr-1"></i>View
                    </button>
                    <button class="text-purple-600 hover:text-purple-900 text-sm transition-colors">
                        <i class="fas fa-users mr-1"></i>Assign Employees
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Period Details Screen -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6" id="periodDetailsView" style="display: none;">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900" id="periodDetailsTitle">Period Details</h3>
                <p class="mt-1 text-sm text-gray-600" id="periodDetailsSubtitle">Manage schedules for this period</p>
            </div>
            <button onclick="closePeriodDetailsView()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        

        <!-- Schedule Summary -->
        <div id="scheduleSummaryContainer" class="mb-6">
            <!-- Schedule summary will be dynamically generated here -->
        </div>

        <!-- Weekly Schedule Table -->
        <div id="scheduleTableContainer">
            <!-- Schedule table will be dynamically generated here -->
        </div>
    </div>

</div>

<!-- Add New Period Modal -->
<div id="addPeriodModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
    <div class="relative top-20 mx-auto p-6 border w-full max-w-md shadow-xl rounded-2xl bg-white">
        <!-- Modal Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-calendar-plus text-blue-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Add New Schedule Period</h3>
                    <p class="text-sm text-gray-500">Create a new scheduling period for your team</p>
                </div>
            </div>
            <button onclick="closeAddPeriodModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="addPeriodForm" class="space-y-4">
            <!-- Schedule Name -->
            <div>
                <label for="scheduleName" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-tag mr-2 text-purple-600"></i>
                    Schedule Name
                </label>
                <input 
                    type="text" 
                    id="scheduleName" 
                    name="scheduleName" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white"
                    placeholder="e.g., Q1 2025, January Schedule"
                    required
                >
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Start Date -->
                <div>
                    <label for="startDate" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-day mr-2 text-blue-600"></i>
                        Start Date
                    </label>
                    <input 
                        type="date" 
                        id="startDate" 
                        name="startDate" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white"
                        required
                    >
                </div>
                
                <!-- End Date -->
                <div>
                    <label for="endDate" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-check mr-2 text-green-600"></i>
                        End Date
                    </label>
                    <input 
                        type="date" 
                        id="endDate" 
                        name="endDate" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white"
                        required
                    >
                </div>
            </div>
            
            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-align-left mr-2 text-gray-600"></i>
                    Description (Optional)
                </label>
                <textarea 
                    id="description" 
                    name="description" 
                    rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors bg-white resize-none"
                    placeholder="Add any additional notes about this period..."
                ></textarea>
            </div>
            
            <!-- Period Preview -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h4 class="text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-eye mr-2 text-blue-600"></i>
                    Period Preview
                </h4>
                <div class="text-sm text-gray-600" id="periodPreview">
                    Select dates to see period preview
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex space-x-3 pt-4 border-t border-gray-200">
                <button type="button" onclick="closeAddPeriodModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Create Period
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Period Management
function selectPeriod(periodId) {
    document.getElementById('periodDetailsView').style.display = 'block';
    generatePeriodDetails(periodId);
}

function addNewPeriod() {
    document.getElementById('addPeriodModal').style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    
    // Set default dates (next Monday to Friday)
    const today = new Date();
    const nextMonday = new Date(today);
    nextMonday.setDate(today.getDate() + (1 + 7 - today.getDay()) % 7);
    
    const nextFriday = new Date(nextMonday);
    nextFriday.setDate(nextMonday.getDate() + 4);
    
    document.getElementById('startDate').value = nextMonday.toISOString().split('T')[0];
    document.getElementById('endDate').value = nextFriday.toISOString().split('T')[0];
    
    updatePeriodPreview();
}

function closeAddPeriodModal() {
    document.getElementById('addPeriodModal').style.display = 'none';
    document.body.style.overflow = 'auto'; // Restore scrolling
    document.getElementById('addPeriodForm').reset();
    document.getElementById('periodPreview').textContent = 'Select dates to see period preview';
}

function closePeriodDetailsView() {
    document.getElementById('periodDetailsView').style.display = 'none';
}

// Update Period Preview
function updatePeriodPreview() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const scheduleName = document.getElementById('scheduleName').value;
    
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        
        if (end < start) {
            document.getElementById('periodPreview').innerHTML = 
                '<span class="text-red-600"><i class="fas fa-exclamation-triangle mr-1"></i>End date must be after start date</span>';
            return;
        }
        
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        const weeks = Math.ceil(diffDays / 7);
        
        const startFormatted = start.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric' 
        });
        const endFormatted = end.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric' 
        });
        
        const nameDisplay = scheduleName ? `<strong>${scheduleName}</strong><br>` : '';
        
        document.getElementById('periodPreview').innerHTML = `
            ${nameDisplay}
            <div class="mt-2 space-y-1">
                <div class="flex justify-between">
                    <span class="text-gray-500">Duration:</span>
                    <span class="font-medium">${diffDays} days</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Weeks:</span>
                    <span class="font-medium">${weeks} week${weeks !== 1 ? 's' : ''}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Period:</span>
                    <span class="font-medium">${startFormatted} - ${endFormatted}</span>
                </div>
            </div>
        `;
    } else {
        document.getElementById('periodPreview').textContent = 'Select dates to see period preview';
    }
}

// Generate Period Details
function generatePeriodDetails(periodId) {
    const periodData = getPeriodData(periodId);
    
    document.getElementById('periodDetailsTitle').textContent = periodData.title;
    document.getElementById('periodDetailsSubtitle').textContent = periodData.subtitle;
    
    generateScheduleSummary(periodData.weeks[0]);
    generateScheduleTable(periodData.weeks[0]);
}

// Get period data (sample data)
function getPeriodData(periodId) {
    const periods = {
        'period-1': {
            title: 'September 1-15, 2025',
            subtitle: 'Pay Period 1 - Manage schedules for this period',
            weeks: [
                {
                    id: 'week1',
                    name: 'Week 1',
                    dateRange: 'Sep 1-7'
                },
                {
                    id: 'week2',
                    name: 'Week 2',
                    dateRange: 'Sep 8-14'
                },
                {
                    id: 'week3',
                    name: 'Week 3',
                    dateRange: 'Sep 15-21'
                }
            ]
        }
    };
    
    return periods[periodId] || periods['period-1'];
}


/**
 * Generate Schedule Summary Statistics
 * @param {Object} week - Week data object
 */
function generateScheduleSummary(week) {
    const container = document.getElementById('scheduleSummaryContainer');
    if (!container) return;
    
    // Calculate statistics for day-based view
    const statistics = calculateScheduleStatistics(week);
    
    container.innerHTML = createSummaryCards(statistics);
}

/**
 * Calculate schedule statistics
 * @param {Object} week - Week data object
 * @returns {Object} Statistics object
 */
function calculateScheduleStatistics(week) {
    return {
        totalShifts: 5,        // morning, afternoon, evening, night, off
        activeShifts: 4,       // excluding off
        totalStaffHours: 28,   // sample calculation
        weekendCoverage: 2     // limited weekend shifts
    };
}

/**
 * Create summary cards HTML
 * @param {Object} stats - Statistics object
 * @returns {string} HTML string
 */
function createSummaryCards(stats) {
    const cards = [
        {
            title: 'Total Shifts',
            value: stats.totalShifts,
            icon: 'fas fa-calendar-alt',
            bgColor: 'bg-blue-100',
            textColor: 'text-blue-600'
        },
        {
            title: 'Active Shifts',
            value: stats.activeShifts,
            icon: 'fas fa-clock',
            bgColor: 'bg-green-100',
            textColor: 'text-green-600'
        },
        {
            title: 'Staff Hours',
            value: stats.totalStaffHours,
            icon: 'fas fa-users',
            bgColor: 'bg-yellow-100',
            textColor: 'text-yellow-600'
        },
        {
            title: 'Weekend Coverage',
            value: stats.weekendCoverage,
            icon: 'fas fa-calendar-week',
            bgColor: 'bg-purple-100',
            textColor: 'text-purple-600'
        }
    ];
    
    return `
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            ${cards.map(card => createSummaryCard(card)).join('')}
        </div>
    `;
}

/**
 * Create individual summary card
 * @param {Object} card - Card data object
 * @returns {string} HTML string
 */
function createSummaryCard(card) {
    return `
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 ${card.bgColor} rounded-full flex items-center justify-center">
                        <i class="${card.icon} ${card.textColor}"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-500">${card.title}</p>
                    <p class="text-lg font-semibold text-gray-900">${card.value}</p>
                </div>
            </div>
        </div>
    `;
}

// Generate Schedule Table
function generateScheduleTable(week) {
    const container = document.getElementById('scheduleTableContainer');
    
    const dayHeaders = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    
    // Generate weeks based on period data
    const weeks = generateWeeksFromPeriod();
    
    container.innerHTML = `
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                            Week
                        </th>
                        ${dayHeaders.map(day => `
                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-gray-200">
                                ${day}
                            </th>
                        `).join('')}
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    ${weeks.map(weekData => generateWeekRow(weekData)).join('')}
                </tbody>
            </table>
        </div>
    `;
}

// Generate Weeks from Period
function generateWeeksFromPeriod() {
    // Sample period: September 1-15, 2025 (3 weeks)
    const weeks = [
        {
            weekNumber: 1,
            startDate: 'Sep 1',
            endDate: 'Sep 7',
            schedule: {
                Monday: '08:00–17:00',
                Tuesday: '08:00–17:00',
                Wednesday: '08:00–17:00',
                Thursday: '08:00–17:00',
                Friday: '08:00–17:00',
                Saturday: 'OFF',
                Sunday: 'OFF'
            }
        },
        {
            weekNumber: 2,
            startDate: 'Sep 8',
            endDate: 'Sep 14',
            schedule: {
                Monday: '08:00–17:00',
                Tuesday: '08:00–17:00',
                Wednesday: '08:00–17:00',
                Thursday: '08:00–17:00',
                Friday: '08:00–17:00',
                Saturday: 'OFF',
                Sunday: 'OFF'
            }
        },
        {
            weekNumber: 3,
            startDate: 'Sep 15',
            endDate: 'Sep 21',
            schedule: {
                Monday: '08:00–17:00',
                Tuesday: '08:00–17:00',
                Wednesday: '08:00–17:00',
                Thursday: '08:00–17:00',
                Friday: '08:00–17:00',
                Saturday: 'OFF',
                Sunday: 'OFF'
            }
        }
    ];
    
    return weeks;
}

// Generate Week Row
function generateWeekRow(weekData) {
    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    
    return `
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8">
                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                            <span class="text-sm font-medium text-blue-600">${weekData.weekNumber}</span>
                        </div>
                    </div>
                    <div class="ml-3">
                        <div class="text-sm font-medium text-gray-900">Week ${weekData.weekNumber}</div>
                        <div class="text-sm text-gray-500">${weekData.startDate} - ${weekData.endDate}</div>
                    </div>
                </div>
            </td>
            ${days.map(day => generateDayCell(weekData.schedule[day], day, weekData.weekNumber)).join('')}
        </tr>
    `;
}

// Generate Day Cell
function generateDayCell(timeRange, day, weekNumber) {
    let cellClass = 'px-3 py-4 text-center text-sm hover:bg-gray-50 cursor-pointer';
    let cellContent = '';
    
    if (timeRange === 'OFF') {
        cellClass += ' text-gray-400';
        cellContent = `
            <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                <i class="fas fa-ban mr-1"></i>OFF
            </div>
        `;
    } else {
        cellClass += ' text-gray-900';
        cellContent = `
            <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <i class="fas fa-clock mr-1"></i>${timeRange}
            </div>
            <div class="mt-1">
                <button onclick="event.stopPropagation(); editDaySchedule('${day}', ${weekNumber})" 
                        class="text-xs text-blue-600 hover:text-blue-800">
                    <i class="fas fa-edit"></i>
                </button>
            </div>
        `;
    }
    
    return `<td class="${cellClass}" onclick="editDaySchedule('${day}', ${weekNumber})">${cellContent}</td>`;
}


// Edit Day Schedule
function editDaySchedule(day, weekNumber) {
    console.log(`Editing schedule for ${day} in Week ${weekNumber}`);
    // This would open a modal to edit the specific day schedule
    // Could include: time range adjustments, holiday marking, special notes
}

// Form submission
document.addEventListener('DOMContentLoaded', function() {
    const addPeriodForm = document.getElementById('addPeriodForm');
    if (addPeriodForm) {
        addPeriodForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const scheduleName = document.getElementById('scheduleName').value;
            const description = document.getElementById('description').value;
            
            if (!startDate || !endDate || !scheduleName) {
                alert('Please fill in all required fields');
                return;
            }
            
            if (new Date(startDate) >= new Date(endDate)) {
                alert('End date must be after start date');
                return;
            }
            
            addNewPeriodCard(startDate, endDate, scheduleName, description);
            closeAddPeriodModal();
        });
        
        // Add event listeners for real-time preview updates
        document.getElementById('startDate').addEventListener('change', updatePeriodPreview);
        document.getElementById('endDate').addEventListener('change', updatePeriodPreview);
        document.getElementById('scheduleName').addEventListener('input', updatePeriodPreview);
    }

    const addPeriodModal = document.getElementById('addPeriodModal');
    if (addPeriodModal) {
        addPeriodModal.addEventListener('click', function(e) {
            if (e.target === addPeriodModal) {
                closeAddPeriodModal();
            }
        });
    }
});

// Function to add new period card
function addNewPeriodCard(startDate, endDate, scheduleName, description) {
    const periodsContainer = document.querySelector('.grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-3.gap-4');
    
    const startDateFormatted = new Date(startDate).toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric' 
    });
    const endDateFormatted = new Date(endDate).toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric' 
    });
    
    const newPeriodCard = document.createElement('div');
    newPeriodCard.className = 'border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer';
    newPeriodCard.onclick = () => selectPeriod('period-new');
    
    newPeriodCard.innerHTML = `
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar text-blue-600"></i>
                </div>
                <div class="ml-3">
                    <h4 class="font-medium text-gray-900">${scheduleName}</h4>
                    <p class="text-sm text-gray-500">${startDateFormatted} - ${endDateFormatted}</p>
                </div>
            </div>
            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                Planning
            </span>
        </div>
        <div class="space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Total Employees:</span>
                <span class="font-medium text-gray-900">0</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Scheduled:</span>
                <span class="font-medium text-gray-900">0</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Pending:</span>
                <span class="font-medium text-yellow-600">0</span>
            </div>
        </div>
        <div class="mt-4 flex space-x-2">
            <button class="text-blue-600 hover:text-blue-900 text-sm transition-colors">
                <i class="fas fa-edit mr-1"></i>Edit
            </button>
            <button class="text-green-600 hover:text-green-900 text-sm transition-colors">
                <i class="fas fa-eye mr-1"></i>View
            </button>
            <button class="text-purple-600 hover:text-purple-900 text-sm transition-colors">
                <i class="fas fa-users mr-1"></i>Assign Employees
            </button>
        </div>
    `;
    
    periodsContainer.insertBefore(newPeriodCard, periodsContainer.firstChild);
}
</script>
@endsection
