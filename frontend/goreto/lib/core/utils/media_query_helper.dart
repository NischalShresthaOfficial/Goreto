import 'package:flutter/material.dart';

class ScreenSize {
  final BuildContext context;

  ScreenSize(this.context);

  double get width => MediaQuery.of(context).size.width;
  double get height => MediaQuery.of(context).size.height;
  double get textScale => MediaQuery.of(context).textScaleFactor;

  // Width percent (0–100)
  double widthP(double percent) => width * (percent / 100);

  // Height percent (0–100)
  double heightP(double percent) => height * (percent / 100);
}
