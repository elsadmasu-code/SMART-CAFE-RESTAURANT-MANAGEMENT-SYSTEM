import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'providers/cart_provider.dart';
import 'providers/locale_provider.dart';
import 'screens/home_screen.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => CartProvider()),
        ChangeNotifierProvider(create: (_) => LocaleProvider()),
      ],
      child: const FkrCafeApp(),
    ),
  );
}

class FkrCafeApp extends StatelessWidget {
  const FkrCafeApp({super.key});

  @override
  Widget build(BuildContext context) {
    final localeProvider = Provider.of<LocaleProvider>(context);

    return MaterialApp(
      title: 'Fkr Cafe Kombolcha',
      debugShowCheckedModeBanner: false,
      locale: localeProvider.locale,
      supportedLocales: const [
        Locale('am', 'ET'), // Amharic
        Locale('en', 'US'), // English
      ],
      localizationsDelegates: const [
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF64301B),
          primary: const Color(0xFF64301B),
          secondary: const Color(0xFFFED100),
        ),
        scaffoldBackgroundColor: const Color(0xFFFAF9F6),
        useMaterial3: true,
      ),
      home: const HomeScreen(),
    );
  }
}
