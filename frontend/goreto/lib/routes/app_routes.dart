import 'package:flutter/material.dart';
import 'package:page_transition/page_transition.dart';

import '../presentation/screens/dashboard_screen.dart';
import '../presentation/screens/splash_screen.dart';
import '../presentation/screens/storyboard/story_board.dart';

class AppRoutes {
  static const String splash = '/';
  static const String story = '/storyBoard';
  static const String dashboard = '/dashboard';
  // Add more named routes here as needed

  static Route<dynamic> generateRoute(RouteSettings settings) {
    switch (settings.name) {
      case splash:
        return _buildPage(const SplashScreen());
      case dashboard:
        return _buildPage(const DashboardScreen());
      case story:
        return _buildPage(const StoryBoardScreen());
      // Add more cases here

      default:
        return _errorRoute();
    }
  }

  static Widget getPage(String routeName) {
    switch (routeName) {
      case dashboard:
        return const DashboardScreen();
      case splash:
        return const SplashScreen();
      case story:
        return const StoryBoardScreen();
      default:
        return const Scaffold(body: Center(child: Text("404")));
    }
  }

  static PageTransition _buildPage(Widget child) {
    return PageTransition(
      child: child,
      type: PageTransitionType.fade,
      duration: const Duration(milliseconds: 500),
    );
  }

  static Route<dynamic> _errorRoute() {
    return MaterialPageRoute(
      builder: (_) =>
          Scaffold(body: Center(child: Text('404 - Page not found'))),
    );
  }
}
