import 'package:flutter/material.dart';

class LocaleProvider with ChangeNotifier {
  Locale _locale = const Locale('am'); // Default Amharic

  Locale get locale => _locale;
  bool get isAmharic => _locale.languageCode == 'am';

  void setLocale(Locale loc) {
    _locale = loc;
    notifyListeners();
  }

  void toggleLanguage() {
    if (_locale.languageCode == 'am') {
      _locale = const Locale('en');
    } else {
      _locale = const Locale('am');
    }
    notifyListeners();
  }
}
