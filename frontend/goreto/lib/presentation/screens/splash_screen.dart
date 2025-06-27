import 'package:flutter/material.dart';
import 'package:flutter_spinkit/flutter_spinkit.dart';
import 'package:page_transition/page_transition.dart';

import '../../core/utils/media_query_helper.dart';
import '../../routes/app_routes.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  void initState() {
    super.initState();
    Future.delayed(const Duration(seconds: 8), () {
      Navigator.pushReplacement(
        context,
        PageTransition(
          type: PageTransitionType.fade,
          duration: const Duration(milliseconds: 550),
          child: AppRoutes.getPage(AppRoutes.story), // ✅ centrally controlled
          settings: const RouteSettings(name: AppRoutes.story), // optional
        ),
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    final screen = ScreenSize(context);

    return Scaffold(
      backgroundColor: Colors.white,
      body: LayoutBuilder(
        builder: (context, constraints) {
          return ConstrainedBox(
            constraints: BoxConstraints(
              minHeight: constraints.maxHeight,
              minWidth: constraints.maxWidth,
            ),
            child: IntrinsicHeight(
              child: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    // App Logo
                    SizedBox(
                      width: screen.widthP(60),
                      height: screen.widthP(60),
                      child: Image.asset(
                        'assets/logos/goreto.png', // Add your logo here
                        fit: BoxFit.contain,
                      ),
                    ),
                    const SizedBox(height: 42),

                    // Progress Indicator
                    const SpinKitThreeBounce(
                      color: Color(0xFF192639),
                      size: 24.0,
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}
