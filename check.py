depth = 0
with open('resources/views/livewire/counter/opd-booking.blade.php') as f:
    for i, line in enumerate(f, 1):
        depth += line.count('<div') - line.count('</div')
        print(f"Line {i}: depth {depth}")
