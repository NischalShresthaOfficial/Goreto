import UIKit
import Flutter
import GoogleMaps // ✅ Add this line

@main
@objc class AppDelegate: FlutterAppDelegate {
  override func application(
    _ application: UIApplication,
    didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]?
  ) -> Bool {

    android:value="AIzaSyAulCLaxFFN3fwBrOaIHB8s30UbnsFRckw"/>
    GMSServices.provideAPIKey("") // ✅ Add this line
    GeneratedPluginRegistrant.register(with: self)
    return super.application(application, didFinishLaunchingWithOptions: launchOptions)
  }
}
