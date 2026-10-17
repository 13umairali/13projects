"""Run: python -m unittest discover -s tools -p test_update_logo.py"""
import json
import unittest
import xml.etree.ElementTree as ET
from pathlib import Path
from PIL import Image
ROOT = Path(__file__).resolve().parent.parent

class BrandAssetsTest(unittest.TestCase):
    def test_supplied_shape_and_color(self):
        source = Image.open(ROOT / 'assets/brand/logo_source.png').convert('RGBA')
        logo = Image.open(ROOT / 'assets/brand/official_logo.png').convert('RGBA')
        self.assertAlmostEqual(source.width/source.height, logo.width/logo.height, places=2)
        self.assertEqual(logo.getchannel('A').getextrema(), (0,255))
        visible = [p[:3] for p in logo.getdata() if p[3] == 255]
        self.assertTrue(visible)
        self.assertTrue(all(p == (37,99,235) for p in visible))

    def test_round_launcher_and_ios_opacity_and_dimensions(self):
        icon = Image.open(ROOT/'assets/icon/icon_round.png').convert('RGBA')
        self.assertEqual(icon.getpixel((0,0))[3],0)
        self.assertEqual(icon.getpixel((512,80)),(255,255,255,255))
        folder=ROOT/'ios/Runner/Assets.xcassets/AppIcon.appiconset'
        for item in json.loads((folder/'Contents.json').read_text())['images']:
            image=Image.open(folder/item['filename']).convert('RGBA')
            size=round(float(item['size'].split('x')[0])*float(item['scale'].rstrip('x')))
            self.assertEqual(image.size,(size,size))
            self.assertEqual(image.getchannel('A').getextrema(),(255,255))
            self.assertEqual(image.getpixel((0,0)),(255,255,255,255))

    def test_android_masks_retain_entire_blue_mark_and_splash(self):
        for name in ['assets/icon/icon_fg.png','android/app/src/main/res/drawable-mdpi/dahimail_splash.png']:
            image=Image.open(ROOT/name).convert('RGBA'); w,h=image.size; visible=0
            for y in range(h):
                for x in range(w):
                    r,g,b,a=image.getpixel((x,y))
                    if a > 16 and b > r+20:
                        visible+=1
                        self.assertLessEqual((x-w/2)**2+(y-h/2)**2,(w/3)**2)
            self.assertGreater(visible,50)
        mono=Image.open(ROOT/'assets/icon/icon_mono.png').convert('RGBA')
        self.assertTrue(any(p[3]>0 for p in mono.getdata()))
        self.assertTrue(all(p[:3]==(255,255,255) for p in mono.getdata() if p[3]>0))

    def test_active_references(self):
        ET.parse(ROOT/'ios/Runner/Base.lproj/LaunchScreen.storyboard')
        res=ROOT/'android/app/src/main/res'
        for path in res.glob('values*/styles.xml'):
            text=path.read_text()
            if 'windowSplashScreenAnimatedIcon' in text:
                self.assertIn('@drawable/dahimail_splash',text)
        for density in ['mdpi','hdpi','xhdpi','xxhdpi','xxxhdpi']:
            for path in [res/f'mipmap-{density}/ic_dahimail_launcher.png',res/f'drawable-{density}/dahimail_splash.png']:
                Image.open(path).verify()
        self.assertEqual(ET.parse(res/'values/colors.xml').find("color[@name='ic_launcher_background']").text,'#FFFFFF')

if __name__=='__main__': unittest.main()
