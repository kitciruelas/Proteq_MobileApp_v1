import 'package:flutter/material.dart';
import 'screens/dashboard.dart';
import 'screens/report_incident.dart';
import 'screens/welfare_check.dart';
import 'screens/safety_protocols.dart';
import 'screens/evacuation_centers.dart';


void main() {
  runApp(const ProteqApp());
}


class ProteqApp extends StatelessWidget {
  const ProteqApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'PROTEQ',
      theme: ThemeData(
        primarySwatch: Colors.red,
        scaffoldBackgroundColor: Colors.grey[50],
        inputDecorationTheme: InputDecorationTheme(
          filled: true,
          fillColor: Colors.white,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide(color: Colors.grey.shade300),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: BorderSide(color: Colors.grey.shade300),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: Colors.red),
          ),
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            padding: const EdgeInsets.symmetric(vertical: 16),
            textStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
          ),
        ),
      ),
      initialRoute: '/',
      routes: {
        '/': (context) => const SplashScreen(),
        '/dashboard': (context) => const DashboardScreen(),
        '/report': (context) => const ReportIncidentScreen(),
        '/welfare': (context) => const WelfareCheckScreen(),
        '/protocols': (context) => const SafetyProtocolsScreen(),
        '/evacuation': (context) => const EvacuationCentersScreen(),

      },
    );
  }
}

class SplashScreen extends StatelessWidget {
  const SplashScreen({super.key});

  @override
  Widget build(BuildContext context) {
    Future.delayed(const Duration(seconds: 2), () {
      Navigator.pushReplacementNamed(context, '/dashboard');
    });
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Colors.red, Colors.white],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
        ),
        child: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.8),
                  shape: BoxShape.circle,
                  boxShadow: [BoxShadow(color: Colors.black12, blurRadius: 16)],
                ),
                padding: const EdgeInsets.all(32),
                child: Icon(Icons.shield, size: 100, color: Colors.red),
              ),
              const SizedBox(height: 32),
              const Text('PROTEQ', style: TextStyle(fontSize: 36, fontWeight: FontWeight.bold, color: Colors.red)),
            ],
          ),
        ),
      ),
    );
  }
}
