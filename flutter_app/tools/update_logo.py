#!/usr/bin/env python3
"""Regenerate app logo, launcher and splash artwork from brand.json; requires Pillow 11.3+.

Run: python tools/update_logo.py [--check]
This changes only artwork. App IDs, URLs, permissions and theme colors are retained.
"""
import argparse
import io
import json
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont

ROOT = Path(__file__).resolve().parent.parent


def generate():
    cfg = json.loads((ROOT / 'brand.json').read_text())
    source = Image.open(ROOT / cfg['logo']).convert('RGBA')
    bounds = source.getchannel('A').getbbox()
    if bounds is None:
        raise ValueError('The source logo is empty')
    logo = source.crop(bounds)
    outputs = {}

    def save(path, picture):
        buffer = io.BytesIO()
        picture.save(buffer, format='PNG', optimize=True)
        outputs[ROOT / path] = buffer.getvalue()

    def fitted(width, white=False):
        size = (round(width), round(width * logo.height / logo.width))
        # Resize flat-color artwork's alpha separately to avoid bright RGB fringes
        # caused by rounding premultiplied edge pixels.
        colors = logo.convert('RGB').getextrema()
        if all(low == high for low, high in colors):
            image = Image.new('RGBA', size, tuple(low for low, _ in colors) + (255,))
            image.putalpha(logo.getchannel('A').resize(size, Image.Resampling.LANCZOS))
        else:
            image = logo.resize(size, Image.Resampling.LANCZOS)
        if white:
            alpha = image.getchannel('A')
            image = Image.new('RGBA', size, 'white')
            image.putalpha(alpha)
        return image

    def square(size, fraction, *, white=False, circle=False, opaque=False):
        image = Image.new('RGBA', (size, size), 'white' if opaque else (0, 0, 0, 0))
        if circle:
            inset = size * .035
            ImageDraw.Draw(image).ellipse((inset, inset, size - inset, size - inset), fill='white')
        mark = fitted(size * fraction, white)
        image.alpha_composite(mark, ((size - mark.width) // 2, (size - mark.height) // 2))
        return image

    def splash(size):
        # All content stays inside Android 12's central 192/288 circular safe area.
        image = Image.new('RGBA', (1152, 1152), (0, 0, 0, 0))
        mark = fitted(512)
        # Shift logo slightly above vertical center for standard optical centering (like TikTok)
        y_center = (1152 - mark.height) // 2 - 64
        image.alpha_composite(mark, ((1152 - mark.width) // 2, y_center))

        # --- OPTIONAL AUTOMATIC BRAND NAME TEXT ---
        # To automatically write the brand name underneath the logo icon, uncomment the lines below:
        # face = ImageFont.truetype(str(ROOT / cfg['fontBold']), 88)
        # face.set_variation_by_axes([600])
        # ImageDraw.Draw(image).text((576, 760), cfg['appName'], font=face, anchor='mt', fill=cfg['splashTextColor'])

        return image.resize((size, size), Image.Resampling.LANCZOS)

    save('assets/brand/official_logo.png', fitted(1024))
    save('assets/brand/logo.png', fitted(1024))
    save('assets/brand/mark_white.png', fitted(512, white=True))
    save('assets/brand/splash_symbol.png', splash(1152))
    legacy = square(1024, .64, circle=True)
    save('assets/icon/icon_round.png', legacy)
    save('assets/icon/icon.png', square(1024, .64, opaque=True).convert('RGB'))
    foreground = Image.new('RGBA', (1024, 1024))
    ImageDraw.Draw(foreground).ellipse((176, 176, 848, 848), fill='white')
    mark = fitted(480)
    foreground.alpha_composite(mark, ((1024 - mark.width) // 2, (1024 - mark.height) // 2))
    save('assets/icon/icon_fg.png', foreground)
    mono = square(1024, .47, white=True)
    save('assets/icon/icon_mono.png', mono)

    res = ROOT / 'android/app/src/main/res'
    for path in res.glob('mipmap-*/*.png'):
        if path.stem in ('ic_launcher', 'ic_dahimail_launcher'):
            size = Image.open(path).size
            save(path.relative_to(ROOT), legacy.resize(size, Image.Resampling.LANCZOS))
    for path in res.glob('drawable*/*.png'):
        size = Image.open(path).size
        if path.stem in ('ic_launcher_foreground', 'ic_dahimail_launcher_foreground'):
            picture = foreground.resize(size, Image.Resampling.LANCZOS)
        elif path.stem in ('ic_launcher_monochrome', 'ic_dahimail_launcher_monochrome'):
            picture = mono.resize(size, Image.Resampling.LANCZOS)
        elif path.stem == 'ic_stat_notify':
            picture = square(size[0], .88, white=True)
        elif path.stem in ('dahimail_splash', 'splash_symbol', 'splash_art', 'splash_mark', 'splash_icon', 'splash_logo'):
            picture = splash(size[0]).resize(size, Image.Resampling.LANCZOS)
        else:
            continue
        save(path.relative_to(ROOT), picture)
    catalog = ROOT / 'ios/Runner/Assets.xcassets'
    for item in json.loads((catalog / 'AppIcon.appiconset/Contents.json').read_text())['images']:
        if 'filename' in item:
            pixels = round(float(item['size'].split('x')[0]) * float(item['scale'].rstrip('x')))
            save((catalog / 'AppIcon.appiconset' / item['filename']).relative_to(ROOT), square(pixels, .64, opaque=True).convert('RGB'))
    for item in json.loads((catalog / 'LaunchImage.imageset/Contents.json').read_text())['images']:
        pixels = round(288 * float(item['scale'].rstrip('x')))
        save((catalog / 'LaunchImage.imageset' / item['filename']).relative_to(ROOT), splash(pixels))
    return outputs


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--check', action='store_true', help='Verify generated assets without writing them')
    args = parser.parse_args()
    outputs = generate()
    for path, content in outputs.items():
        if args.check:
            if not path.exists() or path.read_bytes() != content:
                raise SystemExit(f'Artwork needs regeneration: {path.relative_to(ROOT)}')
        else:
            path.parent.mkdir(parents=True, exist_ok=True)
            path.write_bytes(content)
    print(f'{"Verified" if args.check else "Updated"} {len(outputs)} logo, launcher and splash assets.')


if __name__ == '__main__':
    main()
