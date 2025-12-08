/**
 * Chart.js Configurations for Leave Analytics Dashboard
 * Renders all charts with consistent styling and interactions
 */

// Wait for DOM and Chart.js to load
document.addEventListener('DOMContentLoaded', function() {
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js not loaded');
        return;
    }

    // Global chart defaults
    Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
    Chart.defaults.color = '#6b7280';
    Chart.defaults.plugins.legend.display = true;
    Chart.defaults.plugins.legend.position = 'bottom';

    // Initialize charts based on active tab
    initializeCharts();
});

function initializeCharts() {
    const activeTab = document.querySelector('.tab-pane.active');
    if (!activeTab) return;

    const tabId = activeTab.id;

    switch(tabId) {
        case 'overviewTab':
            initOverviewCharts();
            break;
        case 'workforceTab':
            initWorkforceCharts();
            break;
        case 'workflowTab':
            initWorkflowCharts();
            break;
        case 'departmentsTab':
            initDepartmentCharts();
            break;
    }
}

// Overview Tab Charts
function initOverviewCharts() {
    if (typeof overviewChartData === 'undefined') return;

    // Utilization Gauge (Doughnut Chart)
    const utilizationCtx = document.getElementById('utilizationGaugeChart');
    if (utilizationCtx) {
        new Chart(utilizationCtx, {
            type: 'doughnut',
            data: {
                labels: ['Utilized', 'Available'],
                datasets: [{
                    data: [overviewChartData.utilization, 100 - overviewChartData.utilization],
                    backgroundColor: ['#4f46e5', '#e5e7eb'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + '%';
                            }
                        }
                    }
                }
            },
            plugins: [{
                id: 'centerText',
                beforeDraw: function(chart) {
                    const ctx = chart.ctx;
                    const centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
                    const centerY = (chart.chartArea.top + chart.chartArea.bottom) / 2;

                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font = 'bold 32px Inter';
                    ctx.fillStyle = '#1f2937';
                    ctx.fillText(overviewChartData.utilization + '%', centerX, centerY - 10);
                    ctx.font = '14px Inter';
                    ctx.fillStyle = '#6b7280';
                    ctx.fillText('Utilized', centerX, centerY + 15);
                    ctx.restore();
                }
            }]
        });
    }

    // Monthly Trend Chart
    const trendCtx = document.getElementById('monthlyTrendChart');
    if (trendCtx && overviewChartData.monthlyTrends) {
        const labels = overviewChartData.monthlyTrends.map(t => t.monthLabel);
        const applications = overviewChartData.monthlyTrends.map(t => t.applicationCount);
        const days = overviewChartData.monthlyTrends.map(t => t.totalDays);

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Applications',
                        data: applications,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Total Days',
                        data: days,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Applications'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Leave Days'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    }

    // Leave Type Pie Chart
    const typeCtx = document.getElementById('leaveTypeChart');
    if (typeCtx && overviewChartData.leaveTypes) {
        const labels = overviewChartData.leaveTypes.map(t => t.leaveTypeName);
        const data = overviewChartData.leaveTypes.map(t => t.totalDays);
        const colors = generateColors(labels.length);

        new Chart(typeCtx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' days (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Department Bar Chart
    const deptCtx = document.getElementById('departmentChart');
    if (deptCtx && overviewChartData.departments) {
        const labels = overviewChartData.departments.map(d => d.departmentName);
        const approved = overviewChartData.departments.map(d => d.approvedDays);
        const rejected = overviewChartData.departments.map(d => d.rejectedApplications);

        new Chart(deptCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Approved Days',
                        data: approved,
                        backgroundColor: '#10b981',
                        borderRadius: 4
                    },
                    {
                        label: 'Rejected Apps',
                        data: rejected,
                        backgroundColor: '#ef4444',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: false
                    },
                    y: {
                        stacked: false,
                        beginAtZero: true
                    }
                }
            }
        });
    }
}

// Workforce Tab Charts
function initWorkforceCharts() {
    if (typeof workforceChartData === 'undefined') return;

    // Absence Heatmap (Line chart showing daily absences)
    const heatmapCtx = document.getElementById('absenceHeatmapChart');
    if (heatmapCtx && workforceChartData.dailyAbsences) {
        const labels = workforceChartData.dailyAbsences.map(d => d.date);
        const counts = workforceChartData.dailyAbsences.map(d => d.count);

        new Chart(heatmapCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Employees Absent',
                    data: counts,
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Employees'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                const index = context.dataIndex;
                                if (workforceChartData.dailyAbsences[index]) {
                                    return 'Employees: ' + workforceChartData.dailyAbsences[index].employees.substring(0, 100);
                                }
                                return '';
                            }
                        }
                    }
                }
            }
        });
    }
}

// Workflow Tab Charts
function initWorkflowCharts() {
    if (typeof workflowChartData === 'undefined') return;

    // Approval Time Chart (example - you can customize)
    const timeCtx = document.getElementById('approvalTimeChart');
    if (timeCtx) {
        new Chart(timeCtx, {
            type: 'bar',
            data: {
                labels: ['< 24h', '24-48h', '48-72h', '> 72h'],
                datasets: [{
                    label: 'Applications',
                    data: [30, 45, 20, 5], // Sample data - should be calculated from actual metrics
                    backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Applications'
                        }
                    }
                }
            }
        });
    }

    // Approval Ratio Pie
    const ratioCtx = document.getElementById('approvalRatioChart');
    if (ratioCtx) {
        new Chart(ratioCtx, {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Rejected'],
                datasets: [{
                    data: [workflowChartData.approvalRate, workflowChartData.rejectionRate],
                    backgroundColor: ['#10b981', '#ef4444'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
}

// Department Tab Charts
function initDepartmentCharts() {
    if (typeof departmentChartData === 'undefined') return;

    const chartCtx = document.getElementById('deptComparisonChart');
    if (chartCtx && departmentChartData.departments) {
        const labels = departmentChartData.departments.map(d => d.departmentName);
        const utilization = departmentChartData.departments.map(d => d.utilizationRate);
        const approved = departmentChartData.departments.map(d => d.approvedDays);

        new Chart(chartCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Utilization %',
                        data: utilization,
                        backgroundColor: '#4f46e5',
                        borderRadius: 4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Approved Days',
                        data: approved,
                        backgroundColor: '#10b981',
                        borderRadius: 4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        max: 100,
                        title: {
                            display: true,
                            text: 'Utilization %'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Approved Days'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    }
}

// Helper function to generate colors
function generateColors(count) {
    const baseColors = [
        '#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
        '#06b6d4', '#84cc16', '#f97316', '#ec4899', '#14b8a6'
    ];

    const colors = [];
    for (let i = 0; i < count; i++) {
        colors.push(baseColors[i % baseColors.length]);
    }
    return colors;
}

