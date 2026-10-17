# Supplied logo, auth forms and Friends tabs

## Changes

- The provided transparent blue logo is the source in `assets/brand/logo_source.png`.
  Flutter brand widgets contain it without cropping/stretching. The lock screen
  uses the same silhouette in white so it remains visible on its dark background.
- Android legacy icons have a white circular background. Adaptive icons use a
  white backing and keep the logo within the central mask area. iOS icons have
  an opaque white backing, as required by iOS; iOS applies its own outer rounding.
  All active launcher, native splash, themed and fallback brand assets are included.
- Native Android/iOS launch images and the Flutter splash share the blue symbol
  and Dahimail wordmark on white. Existing splash lifetime/launch logic is retained.
- Login/registration share a centered form up to 420 logical pixels wide, with
  smaller consistent type and spacing. The whole header/form scrolls with the
  keyboard; username domain text is below the field. Authentication, two-factor,
  recovery phrase, policy and terms behavior are retained.
- Everyone, Friends and Team use one PeopleCard component. Customers uses that
  same appearance with CRM contact details, bulk selection and actions retained.
  Controls wrap below the card content on small screens or with large text.

## Apply

Extract the ZIP into the repository root, preserving its `flutter_app/` paths.
Rebuild and install the Android/iOS app. A running installed version or hot reload
will not replace native launcher icons/splash images. No server deployment is needed.
Existing previous ZIPs contain earlier task scopes; this archive contains this
update's changed/new files only.

## Regenerate artwork

With Python and Pillow 11.3.0 installed:

```sh
cd flutter_app
python tools/update_logo.py
python tools/update_logo.py --check
python -m unittest discover -s tools -p test_update_logo.py
```

`tools/apply_official_logo.py` is a compatibility entry point to the same generator.
The generator uses the existing supplied logo, brand configuration and bundled
Inter font, changing artwork only. The launcher configuration also provides an
Android round source and opaque iOS source, with no extra foreground inset.

## Verification

Flutter widget checks cover 320/390/800 logical widths, text scales 1/1.7,
light/dark themes, keyboard visibility and scrolling to the submit button.
They cover username/password/two-factor submission, registration policy, all four
people filters, deduplication, profile/contact navigation and CRM selection.

Raster checks cover source shape/color, Android mask safety, monochrome icons,
white circle/opaque iOS backgrounds, catalog sizes and platform references.
Android resources compile with `:app:processDebugResources`. Physical Android/iOS
appearance still needs device verification; Xcode builds require macOS.
