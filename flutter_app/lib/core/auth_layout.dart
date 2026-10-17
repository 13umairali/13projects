import 'package:flutter/material.dart';
import 'brand.dart';
import 'theme.dart';

/// One scrollable surface keeps the header and form reachable with the keyboard open.
class AuthLayout extends StatelessWidget {
  final String title, subtitle;
  final List<Widget> children;
  final bool showBack;
  const AuthLayout(
      {super.key,
      required this.title,
      required this.subtitle,
      required this.children,
      this.showBack = false});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      resizeToAvoidBottomInset: true,
      body: SafeArea(
        child: Stack(
          children: [
            Theme(
              data: theme.copyWith(
                textTheme: theme.textTheme.copyWith(
                    bodyLarge: theme.textTheme.bodyLarge?.copyWith(fontSize: 15)),
                inputDecorationTheme: theme.inputDecorationTheme.copyWith(
                    contentPadding:
                        const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                    labelStyle:
                        TextStyle(fontSize: 14, color: AppColors.mutedOf(context)),
                    helperStyle:
                        TextStyle(fontSize: 12, color: AppColors.mutedOf(context))),
              ),
              child: SingleChildScrollView(
                keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
                padding: const EdgeInsets.fromLTRB(20, 20, 20, 28),
                child: Center(
                    child: ConstrainedBox(
                        constraints: const BoxConstraints(maxWidth: 420),
                        child: Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              const Center(child: BrandLogo(size: 68)),
                              const SizedBox(height: 12),
                              Text(Brand.name,
                                  textAlign: TextAlign.center,
                                  style: TextStyle(
                                      fontSize: 21,
                                      height: 1.2,
                                      fontWeight: FontWeight.w700,
                                      color: theme.colorScheme.onSurface)),
                              const SizedBox(height: 6),
                              Text(Brand.tagline,
                                  textAlign: TextAlign.center,
                                  style: TextStyle(
                                      fontSize: 13,
                                      height: 1.4,
                                      color: AppColors.mutedOf(context))),
                              const SizedBox(height: 24),
                              Container(
                                  padding: const EdgeInsets.all(20),
                                  decoration: BoxDecoration(
                                      color: theme.colorScheme.surface,
                                      borderRadius: BorderRadius.circular(20),
                                      border: Border.all(
                                          color: AppColors.borderOf(context))),
                                  child: Material(
                                      type: MaterialType.transparency,
                                      child: Column(
                                          crossAxisAlignment:
                                              CrossAxisAlignment.stretch,
                                          children: [
                                            Text(title,
                                                style: TextStyle(
                                                    fontSize: 22,
                                                    height: 1.25,
                                                    fontWeight: FontWeight.w700,
                                                    color:
                                                        theme.colorScheme.onSurface)),
                                            const SizedBox(height: 6),
                                            Text(subtitle,
                                                style: TextStyle(
                                                    fontSize: 13,
                                                    height: 1.4,
                                                    color:
                                                        AppColors.mutedOf(context))),
                                            const SizedBox(height: 20),
                                            ...children,
                                          ]))),
                            ]))),
              ),
            ),
            if (showBack)
              Positioned(
                top: 4,
                left: 8,
                child: IconButton(
                  icon: const Icon(Icons.arrow_back_rounded),
                  tooltip: 'Back',
                  onPressed: () => Navigator.maybePop(context),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
