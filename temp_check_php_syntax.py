from pathlib import Path
import subprocess

php = Path(r'C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe')
workspace = Path(r'C:\laragon\www\VENTAS-main')
errors = []
for f in workspace.rglob('*.php'):
    result = subprocess.run([str(php), '-l', str(f)], capture_output=True, text=True)
    if result.returncode != 0:
        errors.append((str(f), result.stderr.strip() or result.stdout.strip()))

print('TOTAL ERRORS', len(errors))
for f, err in errors:
    print(f)
    print(err)
