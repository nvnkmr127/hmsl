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
                    'rgba(79, 70, 229, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(236, 72, 153, 0.8)'
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
                borderRadius: '{{ $type === "bar" ? 8 : 0 }}',
                tension: 0.4,
                fill: '{{ $type === "line" }}' ? true : false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: '{{ in_array($type, ["doughnut", "pie", "donut"]) }}' == '1',
                    position: 'bottom'
                }
            },
            scales: '{{ in_array($type, ["doughnut", "pie", "donut", "polarArea"]) }}' == '1' ? {} : {
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
        let payload = (Array.isArray(eventData) && eventData.length > 0) ? eventData[0] : eventData;
        let dataToUpdate = payload.data !== undefined ? payload.data : payload;
        
        if (dataToUpdate) {
            chart.data.labels = Object.keys(dataToUpdate);
            chart.data.datasets[0].data = Object.values(dataToUpdate);
            chart.update();
        }
    });
});
</script>
