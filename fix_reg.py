import re
with open('resources/views/livewire/reports/registration-report.blade.php', 'r') as f:
    content = f.read()

def repl_class(m):
    cls_str = m.group(1)
    
    # Only modify if it's not inside a hardcoded dark container. We can't know for sure in regex unless we know the line context, but we know the classes for the containers that DO switch backgrounds.
    # Actually, we can just look for specific strings we know are in the switching sections.
    # The switching sections have 'bg-white dark:bg-slate-900'
    # Wait, it's easier to just do simple string replacements if the line matches 'bg-white dark:bg-slate-900' or similar, but the classes are on child elements.
    pass

# Let's just find and replace manually using regex, but restricted to lines 112 to 199 (Analytics Dashboard Grid), which is where the switching happens.
lines = content.split('\n')
for i in range(112, min(200, len(lines))):
    line = lines[i]
    line = re.sub(r'\btext-slate-400\b(?! dark:text)', 'text-slate-500 dark:text-slate-400', line)
    line = re.sub(r'\btext-slate-300\b(?! dark:text)', 'text-slate-500 dark:text-slate-300', line)
    lines[i] = line

# Re-join and write back
with open('resources/views/livewire/reports/registration-report.blade.php', 'w') as f:
    f.write('\n'.join(lines))
