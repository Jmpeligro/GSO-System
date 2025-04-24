document.addEventListener('DOMContentLoaded', () => {
    const reportTypeSelect = document.getElementById('report_type');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const categorySelect = document.getElementById('category_id');
    const generateReportButton = document.getElementById('generate-report');
    const reportTitle = document.getElementById('report-title');
    const reportResults = document.getElementById('report-results');
    const chartContainer = document.getElementById('chart-container');
    const tableContainer = document.getElementById('table-container');

    // Set default dates
    startDateInput.value = getDateString(-30); // 30 days ago
    endDateInput.value = getDateString(0); // today

    // Fetch categories for the dropdown
    fetch('get_categories.php')
        .then(response => response.json())
        .then(categories => {
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.category_id;
                option.textContent = category.name;
                categorySelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error fetching categories:', error));

    // Connect toggle function to the report type change event
    reportTypeSelect.addEventListener('change', toggleDateFilter);
    
    // Call it once on page load to set initial state
    toggleDateFilter();

    // Toggle date filter visibility based on report type
    function toggleDateFilter() {
        const reportType = reportTypeSelect.value;
        const dateFilters = document.querySelectorAll('.date-filter');
        const categoryFilter = document.querySelectorAll('.category-filter');
        
        // Toggle date filters
        dateFilters.forEach(filter => {
            filter.style.display = (reportType === 'borrowing_activity') ? 'block' : 'none';
        });
        
        // Toggle category filter
        categoryFilter.forEach(filter => {
            filter.style.display = (reportType === 'popular_equipment') ? 'block' : 'none';
        });
    }

    // Generate report event
    generateReportButton.addEventListener('click', () => {
        const reportType = reportTypeSelect.value;
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        const categoryId = categorySelect.value;

        // Show loading state
        generateReportButton.disabled = true;
        generateReportButton.innerHTML = 'Generating...';
        
        fetch(`reports.php?report_type=${reportType}&start_date=${startDate}&end_date=${endDate}&category_id=${categoryId}&ajax=true`)
            .then(response => response.json())
            .then(data => {
                reportTitle.textContent = getReportTitle(reportType);
                renderChart(reportType, data.chart_data);
                renderTable(reportType, data.report_data);
                
                // Show the report results
                reportResults.classList.add('active');
                
                // Scroll to results
                reportResults.scrollIntoView({ behavior: 'smooth' });
            })
            .catch(error => {
                console.error('Error fetching report data:', error);
                alert('Failed to generate report. Please try again.');
            })
            .finally(() => {
                // Reset button state
                generateReportButton.disabled = false;
                generateReportButton.innerHTML = 'Generate Report';
            });
    });

    // Print report function
    document.getElementById('printReport').addEventListener('click', function() {
        window.print();
    });

    function getDateString(daysOffset) {
        const date = new Date();
        date.setDate(date.getDate() + daysOffset);
        return date.toISOString().split('T')[0];
    }

    function getReportTitle(reportType) {
        switch (reportType) {
            case 'equipment_status': return 'Equipment Status Report';
            case 'borrowing_activity': return 'Borrowing Activity Report';
            case 'overdue_items': return 'Overdue Items Report';
            case 'popular_equipment': return 'Popular Equipment Report';
            case 'user_activity': return 'User Activity Report';
            default: return 'Report';
        }
    }

    function renderChart(reportType, chartData) {
        chartContainer.innerHTML = ''; // Clear previous chart
        
        if (!chartData || chartData.length === 0) {
            const noDataMessage = document.createElement('p');
            noDataMessage.textContent = 'No chart data available.';
            noDataMessage.style.textAlign = 'center';
            noDataMessage.style.padding = '20px';
            noDataMessage.style.color = 'var(--gray)';
            chartContainer.appendChild(noDataMessage);
            return;
        }
        
        const ctx = document.createElement('canvas');
        chartContainer.appendChild(ctx);

        if (reportType === 'equipment_status') {
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: chartData.map(item => item.label),
                    datasets: [{
                        data: chartData.map(item => item.value),
                        backgroundColor: chartData.map(item => item.color),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        title: {
                            display: true,
                            text: 'Equipment Status Distribution'
                        }
                    }
                }
            });
        } else if (reportType === 'borrowing_activity') {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.map(item => item.date),
                    datasets: [{
                        label: 'Number of Borrowings',
                        data: chartData.map(item => item.count),
                        backgroundColor: 'rgba(52, 152, 219, 0.2)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 2,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Borrowing Activity Over Time'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        } else if (reportType === 'popular_equipment' || reportType === 'user_activity') {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.map(item => item.name),
                    datasets: [{
                        label: 'Number of Borrowings',
                        data: chartData.map(item => item.count),
                        backgroundColor: 'rgba(52, 152, 219, 0.7)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: reportType === 'popular_equipment' ? 'Most Popular Equipment' : 'Most Active Users'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    }

    function renderTable(reportType, tableData) {
        tableContainer.innerHTML = ''; // Clear previous table
        
        if (!tableData || tableData.length === 0) {
            const noDataMessage = document.createElement('p');
            noDataMessage.textContent = 'No data available for this report.';
            noDataMessage.style.textAlign = 'center';
            noDataMessage.style.padding = '20px';
            noDataMessage.style.color = 'var(--gray)';
            tableContainer.appendChild(noDataMessage);
            return;
        }
        
        const table = document.createElement('table');
        table.classList.add('report-table');

        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');

        // Process column headers to make them more readable
        Object.keys(tableData[0]).forEach(key => {
            const th = document.createElement('th');
            // Transform snake_case to Title Case
            const formattedHeader = key
                .replace(/_/g, ' ')
                .replace(/\b\w/g, l => l.toUpperCase());
            th.textContent = formattedHeader;
            headerRow.appendChild(th);
        });

        thead.appendChild(headerRow);
        table.appendChild(thead);

        const tbody = document.createElement('tbody');

        tableData.forEach(row => {
            const tr = document.createElement('tr');

            Object.entries(row).forEach(([key, value]) => {
                const td = document.createElement('td');
                
                // Format dates if the column name contains 'date'
                if (key.includes('date') && value) {
                    const date = new Date(value);
                    if (!isNaN(date.getTime())) {
                        value = date.toLocaleDateString('en-US', { 
                            year: 'numeric', 
                            month: 'short', 
                            day: 'numeric' 
                        });
                    }
                }
                
                // Add status badges for status columns
                if (key === 'status') {
                    const statusBadge = document.createElement('span');
                    statusBadge.classList.add('status-badge', `status-${value.toLowerCase()}`);
                    statusBadge.textContent = value.charAt(0).toUpperCase() + value.slice(1);
                    td.appendChild(statusBadge);
                } else {
                    td.textContent = value;
                }
                
                tr.appendChild(td);
            });

            tbody.appendChild(tr);
        });

        table.appendChild(tbody);
        tableContainer.appendChild(table);
    }
}); 