/**
 * Dashboard Analytics
 * Author: Rafi Daffa Ramadhani
 */

// Sample data for demonstration (replace with actual API calls later)
const sampleData = {
    students: {
        total: 245,
        active: 198,
        graduated: 47
    },
    ppimMembers: {
        active: 42,
        year: 2025
    },
    universities: {
        total: 28
    },
    ppiChapters: {
        active: 15
    },
    qualificationLevels: [
        { name: 'Foundation', count: 12 },
        { name: 'Diploma', count: 25 },
        { name: 'Bachelor', count: 142 },
        { name: 'Master', count: 58 },
        { name: 'PhD', count: 8 }
    ],
    topUniversities: [
        { name: 'Universiti Malaya (UM)', count: 45 },
        { name: 'Universiti Kebangsaan Malaysia (UKM)', count: 38 },
        { name: 'Universiti Putra Malaysia (UPM)', count: 32 },
        { name: 'Universiti Sains Malaysia (USM)', count: 28 },
        { name: 'Universiti Teknologi Malaysia (UTM)', count: 24 },
        { name: 'Universiti Teknologi MARA (UiTM)', count: 18 },
        { name: 'Universiti Islam Antarabangsa Malaysia (UIAM)', count: 15 },
        { name: 'Universiti Utara Malaysia (UUM)', count: 12 },
        { name: 'Universiti Malaysia Sabah (UMS)', count: 10 },
        { name: 'Universiti Pendidikan Sultan Idris (UPSI)', count: 8 }
    ],
    ppimDepartments: [
        { name: 'Keilmuan', count: 12 },
        { name: 'Sosial Masyarakat', count: 9 },
        { name: 'Media & Komunikasi', count: 7 },
        { name: 'Hubungan Luar', count: 6 },
        { name: 'Kewirausahaan', count: 5 },
        { name: 'Olahraga & Seni', count: 3 }
    ],
    coverage: [
        { state: 'Selangor', count: 85, percentage: 34.7 },
        { state: 'Kuala Lumpur', count: 52, percentage: 21.2 },
        { state: 'Johor', count: 28, percentage: 11.4 },
        { state: 'Pulau Pinang', count: 24, percentage: 9.8 },
        { state: 'Perak', count: 18, percentage: 7.3 },
        { state: 'Negeri Sembilan', count: 12, percentage: 4.9 },
        { state: 'Pahang', count: 10, percentage: 4.1 },
        { state: 'Kedah', count: 8, percentage: 3.3 },
        { state: 'Kelantan', count: 5, percentage: 2.0 },
        { state: 'Terengganu', count: 3, percentage: 1.2 }
    ]
};

// Color scheme (matching template theme)
const colors = {
    primary: '#313a46',
    secondary: '#669776',
    success: '#70bb63',
    danger: '#ed6060',
    warning: '#ebb751',
    info: '#60addf',
    light: '#f3f7f9',
    dark: '#313a46'
};

// Chart colors array
const chartColors = [colors.primary, colors.success, colors.info, colors.warning, colors.danger, colors.secondary];

/**
 * Update stat cards with data
 */
function updateStatCards() {
    // Total Students
    document.getElementById('total-students').textContent = sampleData.students.total;
    document.getElementById('active-students').innerHTML =
        `<i class="ti ti-check"></i> ${sampleData.students.active} Active`;
    document.getElementById('graduated-students').innerHTML =
        `<i class="ti ti-school"></i> ${sampleData.students.graduated} Graduated`;

    // Active PPIM Members
    document.getElementById('active-ppim-members').textContent = sampleData.ppimMembers.active;
    document.getElementById('ppim-year').textContent = `Current Year (${sampleData.ppimMembers.year})`;

    // Total Universities
    document.getElementById('total-universities').textContent = sampleData.universities.total;

    // PPI Campus Chapters
    document.getElementById('active-ppi-chapters').textContent = sampleData.ppiChapters.active;
}

/**
 * Initialize Qualification Level Chart (Donut)
 */
function initQualificationLevelChart() {
    const series = sampleData.qualificationLevels.map(item => item.count);
    const labels = sampleData.qualificationLevels.map(item => item.name);

    const options = {
        series: series,
        chart: {
            type: 'donut',
            height: 320,
            fontFamily: 'Lexend, sans-serif'
        },
        labels: labels,
        colors: chartColors,
        legend: {
            position: 'bottom',
            horizontalAlign: 'center',
            fontSize: '13px',
            markers: {
                width: 10,
                height: 10,
                radius: 3
            },
            itemMargin: {
                horizontal: 8,
                vertical: 4
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return val.toFixed(1) + "%";
            },
            style: {
                fontSize: '12px',
                fontWeight: 600
            }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        name: {
                            fontSize: '14px',
                            fontWeight: 600,
                            offsetY: -5
                        },
                        value: {
                            fontSize: '24px',
                            fontWeight: 700,
                            color: colors.dark,
                            offsetY: 5,
                            formatter: function (val) {
                                return val;
                            }
                        },
                        total: {
                            show: true,
                            label: 'Total',
                            fontSize: '14px',
                            fontWeight: 600,
                            color: '#8a99b5',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                            }
                        }
                    }
                }
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    height: 280
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    const chart = new ApexCharts(document.querySelector("#chart-qualification-level"), options);
    chart.render();
}

/**
 * Initialize Students by University Chart (Bar)
 */
function initStudentsByUniversityChart() {
    const universities = sampleData.topUniversities.map(item => item.name);
    const counts = sampleData.topUniversities.map(item => item.count);

    const options = {
        series: [{
            name: 'Students',
            data: counts
        }],
        chart: {
            type: 'bar',
            height: 320,
            fontFamily: 'Lexend, sans-serif',
            toolbar: {
                show: false
            }
        },
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 4,
                dataLabels: {
                    position: 'top'
                }
            }
        },
        colors: [colors.info],
        dataLabels: {
            enabled: true,
            offsetX: 30,
            style: {
                fontSize: '12px',
                fontWeight: 600,
                colors: ['#304758']
            }
        },
        xaxis: {
            categories: universities,
            labels: {
                style: {
                    fontSize: '11px'
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    fontSize: '11px'
                },
                maxWidth: 180
            }
        },
        grid: {
            borderColor: '#f1f3fa',
            xaxis: {
                lines: {
                    show: true
                }
            }
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + " students";
                }
            }
        }
    };

    const chart = new ApexCharts(document.querySelector("#chart-students-university"), options);
    chart.render();
}

/**
 * Initialize PPIM Members by Department Chart (Donut)
 */
function initPPIMDepartmentChart() {
    const series = sampleData.ppimDepartments.map(item => item.count);
    const labels = sampleData.ppimDepartments.map(item => item.name);

    const options = {
        series: series,
        chart: {
            type: 'donut',
            height: 320,
            fontFamily: 'Lexend, sans-serif'
        },
        labels: labels,
        colors: chartColors,
        legend: {
            position: 'bottom',
            horizontalAlign: 'center',
            fontSize: '13px',
            markers: {
                width: 10,
                height: 10,
                radius: 3
            },
            itemMargin: {
                horizontal: 8,
                vertical: 4
            }
        },
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return val.toFixed(1) + "%";
            },
            style: {
                fontSize: '12px',
                fontWeight: 600
            }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        name: {
                            fontSize: '14px',
                            fontWeight: 600,
                            offsetY: -5
                        },
                        value: {
                            fontSize: '24px',
                            fontWeight: 700,
                            color: colors.dark,
                            offsetY: 5,
                            formatter: function (val) {
                                return val;
                            }
                        },
                        total: {
                            show: true,
                            label: 'Total',
                            fontSize: '14px',
                            fontWeight: 600,
                            color: '#8a99b5',
                            formatter: function (w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                            }
                        }
                    }
                }
            }
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    height: 280
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    const chart = new ApexCharts(document.querySelector("#chart-ppim-department"), options);
    chart.render();
}

/**
 * Initialize Coverage Map Table
 */
function initCoverageTable() {
    const tableBody = document.getElementById('coverage-table-body');
    let html = '';

    sampleData.coverage.forEach((item, index) => {
        const barWidth = item.percentage;
        html += `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-primary-subtle text-primary me-2">${index + 1}</span>
                        <strong>${item.state}</strong>
                    </div>
                </td>
                <td class="text-end">
                    <span class="badge bg-info-subtle text-info">${item.count}</span>
                </td>
                <td class="text-end">
                    <div class="d-flex align-items-center justify-content-end">
                        <div class="progress flex-grow-1 me-2" style="height: 6px; max-width: 80px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                style="width: ${barWidth}%"
                                aria-valuenow="${barWidth}"
                                aria-valuemin="0"
                                aria-valuemax="100">
                            </div>
                        </div>
                        <span class="fw-semibold">${item.percentage.toFixed(1)}%</span>
                    </div>
                </td>
            </tr>
        `;
    });

    tableBody.innerHTML = html;
}

/**
 * Fetch data from API
 */
async function fetchDashboardData() {
    try {
        const response = await fetch('assets/php/API/dashboard-stats.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Failed to fetch dashboard data');
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.error?.message || 'Failed to fetch dashboard data');
        }

        const data = result.data;

        // Update sampleData with real API data
        sampleData.students = data.students;
        sampleData.ppimMembers = data.ppimMembers;
        sampleData.universities = data.universities;
        sampleData.ppiChapters = data.ppiChapters;
        sampleData.qualificationLevels = data.qualificationLevels;
        sampleData.topUniversities = data.topUniversities;
        sampleData.ppimDepartments = data.ppimDepartments;
        sampleData.coverage = data.coverage;

        // Refresh all components with real data
        updateStatCards();
        initQualificationLevelChart();
        initStudentsByUniversityChart();
        initPPIMDepartmentChart();
        initCoverageTable();

        console.log('Dashboard data loaded successfully from API!');

    } catch (error) {
        console.error('Error fetching dashboard data:', error);
        console.log('Using sample data as fallback...');

        // Use sample data as fallback
        updateStatCards();
        initQualificationLevelChart();
        initStudentsByUniversityChart();
        initPPIMDepartmentChart();
        initCoverageTable();
    }
}

/**
 * Initialize all dashboard components
 */
document.addEventListener('DOMContentLoaded', function () {
    console.log('Dashboard initializing...');

    // Fetch real data from API
    fetchDashboardData();

    console.log('Dashboard initialized successfully!');
});
