import 'dart:async';
import 'dart:io';
import 'dart:ui' as ui;
import 'package:crypto/crypto.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/painting.dart';
import 'package:flutter/widgets.dart';
import 'package:http/http.dart' as http;
import 'package:path_provider/path_provider.dart';

/// Network image that is kept on the phone.
///
/// `NetworkImage` only caches in memory, so after the app is closed every avatar and photo downloads again and
/// pops in late. This provider saves each image once in the app's private folder; after that it is read from disk
/// (a few milliseconds) and the memory cache of Flutter keeps it for the rest of the session.
@immutable
class DiskCachedImage extends ImageProvider<DiskCachedImage> {
  final String url;
  final double scale;
  const DiskCachedImage(this.url, {this.scale = 1.0});

  static Directory? _dir;
  static final Map<String, Future<Uint8List?>> _inflight = {};

  static Future<Directory> _folder() async {
    final cached = _dir;
    if (cached != null) return cached;
    final base = await getApplicationSupportDirectory();
    final dir = Directory('${base.path}/image_cache');
    if (!await dir.exists()) await dir.create(recursive: true);
    return _dir = dir;
  }

  static String _name(String url) => sha1.convert(url.codeUnits).toString();

  /// Reads the saved bytes, or downloads and saves them. Returns null when the image cannot be fetched.
  static Future<Uint8List?> bytes(String url) =>
      _inflight.putIfAbsent(url, () => _fetch(url).whenComplete(() { _inflight.remove(url); }));

  static Future<Uint8List?> _fetch(String url) async {
    File? file;
    try {
      file = File('${(await _folder()).path}/${_name(url)}');
      if (await file.exists()) {
        final saved = await file.readAsBytes();
        if (saved.isNotEmpty) return saved;
      }
    } catch (_) {/* fall through to the network */}
    try {
      final res = await http.get(Uri.parse(url)).timeout(const Duration(seconds: 20));
      if (res.statusCode != 200 || res.bodyBytes.isEmpty) return null;
      if (file != null) {
        try {
          final tmp = File('${file.path}.tmp');
          await tmp.writeAsBytes(res.bodyBytes, flush: true);
          await tmp.rename(file.path);
        } catch (_) {/* a full disk must not stop the image from showing */}
      }
      return res.bodyBytes;
    } catch (_) {
      return null;
    }
  }

  /// Drops every saved image (used by "Clear saved data" and sign-out).
  static Future<void> clearAll() async {
    try {
      final dir = await _folder();
      if (await dir.exists()) await dir.delete(recursive: true);
      _dir = null;
    } catch (_) {}
  }

  @override
  Future<DiskCachedImage> obtainKey(ImageConfiguration configuration) => SynchronousFuture<DiskCachedImage>(this);

  @override
  ImageStreamCompleter loadImage(DiskCachedImage key, ImageDecoderCallback decode) {
    return MultiFrameImageStreamCompleter(
      codec: _load(key, decode),
      scale: key.scale,
      debugLabel: key.url,
    );
  }

  Future<ui.Codec> _load(DiskCachedImage key, ImageDecoderCallback decode) async {
    final data = await bytes(key.url);
    if (data == null) {
      // Not cached anywhere and the download failed: let Flutter drop this key so the next build retries.
      PaintingBinding.instance.imageCache.evict(key);
      throw Exception('Could not load ${key.url}');
    }
    final buffer = await ui.ImmutableBuffer.fromUint8List(data);
    return decode(buffer);
  }

  @override
  bool operator ==(Object other) => other is DiskCachedImage && other.url == url && other.scale == scale;

  @override
  int get hashCode => Object.hash(url, scale);
}

/// Drop-in for `Image.network` with the disk cache, a fade-in and no layout jump.
class CachedNetImage extends StatelessWidget {
  final String url;
  final double? width, height;
  final BoxFit fit;
  final Widget Function(BuildContext)? placeholder;
  final Widget Function(BuildContext)? error;
  final int? cacheWidth;
  const CachedNetImage(this.url, {super.key, this.width, this.height, this.fit = BoxFit.cover, this.placeholder, this.error, this.cacheWidth});

  @override
  Widget build(BuildContext context) {
    ImageProvider provider = DiskCachedImage(url);
    if (cacheWidth != null) provider = ResizeImage(provider, width: cacheWidth);
    return Image(
      image: provider,
      width: width,
      height: height,
      fit: fit,
      gaplessPlayback: true,
      frameBuilder: (context, child, frame, sync) {
        if (sync) return child;
        if (frame == null) return placeholder?.call(context) ?? const SizedBox.shrink();
        return TweenAnimationBuilder<double>(tween: Tween(begin: 0, end: 1), duration: const Duration(milliseconds: 180), builder: (_, v, c) => Opacity(opacity: v, child: c), child: child);
      },
      errorBuilder: (context, _, __) => error?.call(context) ?? const SizedBox.shrink(),
    );
  }
}

/// Only http(s) addresses are fetched; anything else has no photo.
ImageProvider? avatarProvider(String? url, {int? width}) {
  final u = url?.trim();
  if (u == null || u.isEmpty || !(u.startsWith('http://') || u.startsWith('https://'))) return null;
  final ImageProvider base = DiskCachedImage(u);
  if (width == null) return base;
  return ResizeImage(base, width: width);
}
