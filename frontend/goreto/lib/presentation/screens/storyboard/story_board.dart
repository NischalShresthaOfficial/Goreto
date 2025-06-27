// lib/features/auth/screens/story_board_screen.dart
import 'package:flutter/material.dart';
import 'package:goreto/core/utils/media_query_helper.dart';
import 'package:goreto/routes/app_routes.dart';
import 'package:page_transition/page_transition.dart';

// Helper class for story data
class StoryItem {
  final String imagePath;
  final String title;
  final String description;

  StoryItem({
    required this.imagePath,
    required this.title,
    required this.description,
  });
}

class StoryBoardScreen extends StatefulWidget {
  const StoryBoardScreen({super.key});

  @override
  State<StoryBoardScreen> createState() => _StoryBoardScreenState();
}

class _StoryBoardScreenState extends State<StoryBoardScreen> {
  int _currentStoryIndex = 0;

  // Define your story data
  final List<StoryItem> _stories = [
    StoryItem(
      imagePath: 'assets/images/story1.jpg', // Make sure you have these assets
      title: 'Explore New Horizons',
      description:
          'Discover breathtaking places and hidden gems around the world with ease.',
    ),
    StoryItem(
      imagePath: 'assets/images/story2.jpg',
      title: 'Plan Your Perfect Trip',
      description:
          'Our AI-powered planner helps you customize your itinerary based on your preferences.',
    ),
    StoryItem(
      imagePath: 'assets/images/story3.jpg',
      title: 'Connect & Share Experiences',
      description:
          'Join groups, share reviews, and chat with fellow travelers to enhance your journey.',
    ),
  ];

  void _nextStory() {
    setState(() {
      if (_currentStoryIndex < _stories.length - 1) {
        _currentStoryIndex++;
      } else {
        // Last story, navigate to dashboard
        Navigator.pushReplacement(
          context,
          PageTransition(
            type: PageTransitionType.fade,
            duration: const Duration(milliseconds: 550),
            child: AppRoutes.getPage(AppRoutes.dashboard),
            settings: const RouteSettings(name: AppRoutes.dashboard),
          ),
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final screen = ScreenSize(context);
    final currentStory = _stories[_currentStoryIndex];

    return Scaffold(
      body: Stack(
        children: [
          // Background Image (spans throughout the screen including safe area)
          Positioned.fill(
            child: AnimatedOpacity(
              opacity:
                  1.0, // Always fully opaque for simplicity with direct image change
              duration: const Duration(milliseconds: 500),
              child: Image.asset(currentStory.imagePath, fit: BoxFit.cover),
            ),
          ),

          // Content Card
          Align(
            alignment: Alignment.bottomCenter,
            child: Padding(
              padding: EdgeInsets.only(
                bottom: screen.heightP(10),
              ), // Adjust padding as needed
              child: AnimatedSwitcher(
                duration: const Duration(milliseconds: 300),
                transitionBuilder: (Widget child, Animation<double> animation) {
                  return FadeTransition(opacity: animation, child: child);
                },
                child: Container(
                  key: ValueKey(
                    _currentStoryIndex,
                  ), // Important for AnimatedSwitcher to work
                  width: screen.widthP(85),
                  padding: const EdgeInsets.all(24.0),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.9),
                    borderRadius: BorderRadius.circular(25.0),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.1),
                        blurRadius: 10,
                        offset: const Offset(0, 5),
                      ),
                    ],
                  ),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        currentStory.title,
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: screen.widthP(5.5),
                          fontWeight: FontWeight.bold,
                          color: const Color(0xFF192639),
                        ),
                      ),
                      const SizedBox(height: 16),
                      Text(
                        currentStory.description,
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: screen.widthP(3.8),
                          color: Colors.grey[700],
                        ),
                      ),
                      const SizedBox(height: 30),
                      ElevatedButton(
                        onPressed: _nextStory,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF192639),
                          padding: EdgeInsets.symmetric(
                            horizontal: screen.widthP(10),
                            vertical: 12,
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(30),
                          ),
                        ),
                        child: Text(
                          _currentStoryIndex == _stories.length - 1
                              ? 'Get Started'
                              : 'Next',
                          style: TextStyle(
                            fontSize: screen.widthP(4),
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),

          // Dot Indicators
          Align(
            alignment: Alignment.bottomCenter,
            child: Padding(
              padding: EdgeInsets.only(
                bottom: screen.heightP(5),
              ), // Below the card
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: List.generate(
                  _stories.length,
                  (index) => Container(
                    margin: const EdgeInsets.symmetric(horizontal: 4.0),
                    width: 10.0,
                    height: 10.0,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: _currentStoryIndex == index
                          ? const Color(0xFF192639) // Active dot color
                          : Colors.grey.withOpacity(0.5), // Inactive dot color
                    ),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
