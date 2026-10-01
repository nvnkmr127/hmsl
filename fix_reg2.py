import re
with open('resources/views/livewire/reports/registration-report.blade.php', 'r') as f:
    content = f.read()

lines = content.split('\n')
for i in range(710, len(lines)):
    line = lines[i]
    line = re.sub(r'\btext-slate-400\b(?! dark:text)', 'text-slate-500 dark:text-slate-400', line)
    line = re.sub(r'\btext-slate-300\b(?! dark:text)', 'text-slate-500 dark:text-slate-300', line)
    line = re.sub(r'\btext-slate-500\b(?! hover:)(?! dark:text)', 'text-slate-600 dark:text-slate-400', line)
    lines[i] = line

with open('resources/views/livewire/reports/registration-report.blade.php', 'w') as f:
    f.write('\n'.join(lines))
