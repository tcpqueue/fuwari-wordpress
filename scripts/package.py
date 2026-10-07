import pathlib
import zipfile
import hashlib
import re
import subprocess

root=pathlib.Path(__file__).resolve().parent.parent
dist=root/'dist'
dist.mkdir(exist_ok=True)
version=re.search(r'^Version:\s*(\S+)',(root/'theme/style.css').read_text(),re.M)[1]
package=dist/f'fuwari-wp-{version}.zip'
with zipfile.ZipFile(package,'w',zipfile.ZIP_DEFLATED,compresslevel=9) as archive:
    for file in sorted((root/'theme').rglob('*')):
        if file.is_file():
            archive.write(file,'fuwari-wp/'+str(file.relative_to(root/'theme')))
checksum=hashlib.sha256(package.read_bytes()).hexdigest()+'  '+package.name+'\n'
(dist/f'fuwari-wp-{version}.sha256').write_text(checksum)
source=dist/f'fuwari-wordpress-source-{version}.zip'
files=set(subprocess.check_output(['git','ls-files','-z'],cwd=root).decode().split('\0'))
if not any(files):
    raise RuntimeError('Track the source in Git before packaging it.')
files.update(str(file.relative_to(root)) for file in (root/'theme').rglob('*') if file.is_file())
with zipfile.ZipFile(source,'w',zipfile.ZIP_DEFLATED,compresslevel=9) as archive:
    for name in sorted(files):
        if name and (root/name).is_file():archive.write(root/name,f'fuwari-wordpress-{version}/{name}')
checksum+=hashlib.sha256(source.read_bytes()).hexdigest()+'  '+source.name+'\n'
(dist/'SHA256SUMS.txt').write_text(checksum)
print(f'{package.name}: {package.stat().st_size:,} bytes')
