@props([
    'type' => 'bar',
    'data' => [],
    'id' => 'chart-' . uniqid(),
    'height' => '300px',
    'options' => []
])

<div class="relative w-full" style="height: {{ $height }};">
    <canvas id="{{ $id }}" wire:ignore></canvas>
</div>

@once
    @push('scripts')
        <script src="/js/chart.js"></script>
    @endpush
@endonce

<script>
document.addEventListener('livewire:initialized', () => {
    const ctx = document.getElementById('{{ $id }}');
    
    let chartData = @json($data);
    let chartOptions = @json($options);

    const chart = new Chart(ctx, {
        type: '{{ $type === "donut" ? "doughnut" : $type }}',
        data: {
            labels: Object.keys(chartData),
            datasets: [{
                label: '{{ $attributes->get('label', 'Data') }}',
                data: Object.values(chartData),
                backgroundColor: [
                    'rgba(79, 70, 229, 0.2)',
                    'rgba(16, 185, 129, 0.2)',
                    'rgba(245, 158, 11, 0.2)',
                    'rgba(239, 68, 68, 0.2)',
                    'rgba(139, 92, 246, 0.2)',
                    'rgba(236, 72, 153, 0.2)'
                ],
                borderColor: [
                    'rgba(79, 70, 229, 1)',
                    'rgba(16, 185, 129, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(239, 68, 68, 1)',
                    'rgba(139, 92, 246, 1)',
                    'rgba(236, 72, 153, 1)'
                ],
                borderWidth: 2,
                borderRadius: 8,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            ...chartOptions
        }
    });

    Livewire.on('refreshChart-{{ $id }}', (eventData) => {
        chart.data.labels = Object.keys(eventData[0].data);
        chart.data.datasets[0].data = Object.values(eventData[0].data);
        chart.update();
    });
});
</script>
