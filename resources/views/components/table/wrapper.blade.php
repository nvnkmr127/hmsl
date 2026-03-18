{{-- x-table.wrapper —  full-width card with data-table inside --}}
<div {{ $attributes->merge(['class' => 'card overflow-hidden']) }}>
    <div class="overflow-x-auto">
        <table class="data-table">
            {{ $slot }}
        </table>
    </div>
</div>
