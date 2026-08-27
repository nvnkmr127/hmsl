import os
import re
import glob

files_to_process = glob.glob('resources/views/livewire/reports/*.blade.php')

def process_file(filepath):
    with open(filepath, 'r') as f:
        content = f.read()

    def repl_class(m):
        cls_str = m.group(1)
        
        if 'bg-slate-900' in cls_str and 'dark:bg-slate-900' not in cls_str:
            return f'class="{cls_str}"'
        if 'bg-slate-800' in cls_str and 'dark:bg-slate-800' not in cls_str:
            return f'class="{cls_str}"'
        if 'bg-slate-950' in cls_str and 'dark:bg-slate-950' not in cls_str:
            return f'class="{cls_str}"'
        if 'bg-emerald-950' in cls_str or 'bg-rose-950' in cls_str or 'bg-amber-950' in cls_str or 'bg-blue-950' in cls_str or 'bg-purple-950' in cls_str or 'bg-orange-950' in cls_str:
            if 'dark:bg-' not in cls_str:
                 return f'class="{cls_str}"'
        
        classes = cls_str.split()
        new_classes = []
        
        replacements = {
            'text-slate-300': ['text-slate-500', 'dark:text-slate-300'],
            'text-slate-400': ['text-slate-500', 'dark:text-slate-400'],
            'text-slate-500': ['text-slate-600', 'dark:text-slate-400'],
            'hover:text-slate-700': ['hover:text-slate-700', 'dark:hover:text-slate-300']
        }
        
        has_dark_text = any(cls.startswith('dark:text-') for cls in classes)
        has_dark_hover_text = any(cls.startswith('dark:hover:text-') for cls in classes)
        
        for c in classes:
            if c in replacements:
                if 'hover:' in c:
                    if not has_dark_hover_text:
                        new_classes.extend(replacements[c])
                    else:
                        new_classes.append(c)
                else:
                    if not has_dark_text:
                        new_classes.extend(replacements[c])
                    else:
                        new_classes.append(c)
            else:
                new_classes.append(c)
                
        return 'class="' + ' '.join(new_classes) + '"'

    new_content = re.sub(r'class="([^"]+)"', repl_class, content)
    
    with open(filepath, 'w') as f:
        f.write(new_content)

for f in files_to_process:
    if os.path.exists(f):
        process_file(f)
        print(f"Processed {f}")

