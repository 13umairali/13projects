import 'package:flutter/material.dart';
import 'config.dart';

/// Single place for brand identity. To rebrand: replace the PNGs in assets/brand + assets/icon,
/// run `python tools/update_logo.py`, and change the strings/colours here and in core/config.dart.
class Brand {
  static String get name => AppConfig.appName;
  static String get tagline => AppConfig.tagline;
  static const logoAsset =
      'assets/brand/official_logo.png'; // supplied blue symbol with transparency
  static const markAsset =
      'assets/brand/mark_white.png'; // white symbol on transparent, for coloured backgrounds
  /// Must equal `splash_bg` in android/app/src/main/res/values/colors.xml (white).
  static const splashColor = Color(0xFFFFFFFF);
  static const splashGradient = LinearGradient(
      colors: [Color(0xFFFFFFFF), Color(0xFFFFFFFF)],
      begin: Alignment.topLeft,
      end: Alignment.bottomRight);
  static const gradient = LinearGradient(
      colors: [Color(0xFF243142), Color(0xFF101820)],
      begin: Alignment.topLeft,
      end: Alignment.bottomRight);
}

/// Supplied artwork, contained without cropping or stretching.
class BrandLogo extends StatelessWidget {
  final double size;
  const BrandLogo({super.key, this.size = 84});
  @override
  Widget build(BuildContext context) => Semantics(
        label: '${Brand.name} logo',
        image: true,
        child: Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: Theme.of(context).colorScheme.surfaceContainerHighest.withOpacity(0.4),
            borderRadius: BorderRadius.circular(18),
          ),
          child: Image.asset(Brand.logoAsset,
              width: size,
              height: size * .75,
              fit: BoxFit.contain,
              filterQuality: FilterQuality.high),
        ),
      );
}

/// White symbol only – use on gradient / brand-coloured backgrounds (lock screen).
class BrandMark extends StatelessWidget {
  final double size;
  const BrandMark({super.key, this.size = 96});
  @override
  Widget build(BuildContext context) => Semantics(
      label: '${Brand.name} logo',
      image: true,
      child: Image.asset(Brand.markAsset,
          width: size,
          height: size * .75,
          fit: BoxFit.contain,
          filterQuality: FilterQuality.high));
}
