from fontTools.ttLib import TTFont
from pathlib import Path
root=Path(__file__).resolve().parent.parent
font=TTFont(root/'work/FangSongGB2312.ttf')
print('FangSong family:', font['name'].getDebugName(1))
print('FangSong embedding flags:',font['OS/2'].fsType)
font.flavor='woff2'
font.save(root/'work/FangSongGB2312.woff2')
print('WOFF2 bytes:',(root/'work/FangSongGB2312.woff2').stat().st_size)
