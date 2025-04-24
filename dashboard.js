document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('equipmentStatusChart');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        const available = parseInt(canvas.getAttribute('data-available')) || 0;
        const borrowed = parseInt(canvas.getAttribute('data-borrowed')) || 0;
        const maintenance = parseInt(canvas.getAttribute('data-maintenance')) || 0;

        const equipmentStatusChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Available', 'Borrowed', 'Maintenance'],
                datasets: [{
                    data: [available, borrowed, maintenance],
                    backgroundColor: ['#4CAF50', '#2196F3', '#FF9800'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }
});