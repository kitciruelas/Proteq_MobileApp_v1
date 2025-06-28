import 'package:flutter/material.dart';
import 'signup_step1.dart';
import '../screens/dashboard.dart';
import '../responders_screen/r_dashboard.dart';


class LoginScreen extends StatelessWidget {
  const LoginScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      // App background color
      backgroundColor: Colors.white,

      body: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 24.0),
        child: Center(
          child: SingleChildScrollView(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                // 🔻 Logo at the top
                Image.asset(
                  'assets/images/logo-r.png',
                  height: 120, // Adjust as needed
                ),
                const SizedBox(height: 40),

                // 🔻 App Title
                const Text(
                  "Proteq",
                  style: TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: Colors.red,
                  ),
                ),
                const SizedBox(height: 30),

                // 🔻 Email Field
                TextField(
                  decoration: InputDecoration(
                    labelText: "Email",
                    border: OutlineInputBorder(),
                    prefixIcon: Icon(Icons.email),
                  ),
                ),
                const SizedBox(height: 20),

                // 🔻 Password Field
                TextField(
                  obscureText: true,
                  decoration: InputDecoration(
                    labelText: "Password",
                    border: OutlineInputBorder(),
                    prefixIcon: Icon(Icons.lock),
                  ),
                ),
                const SizedBox(height: 30),

                // 🔻 Login Button
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () {
                      // Show dialog to choose between users and responders screen
                      showDialog(
                        context: context,
                        builder: (BuildContext context) {
                          return AlertDialog(
                            title: const Text('Choose User Type'),
                            content: const Text('Are you a regular user or a responder?'),
                            actions: [
                              TextButton(
                                onPressed: () {
                                  Navigator.pop(context); // Close dialog
                                  Navigator.pushReplacement(
                                    context,
                                    MaterialPageRoute(builder: (context) => const DashboardScreen()),
                                  );
                                },
                                child: const Text('Regular User'),
                              ),
                              TextButton(
                                onPressed: () {
                                  Navigator.pop(context); // Close dialog
                                  Navigator.pushReplacement(
                                    context,
                                    MaterialPageRoute(builder: (context) => const ResponderDashboardScreen()),
                                  );
                                },
                                child: const Text('Responder'),
                              ),
                            ],
                          );
                        },
                      );
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.red,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                    ),
                    child: const Text(
                      "Login",
                      style: TextStyle(
                        color: Colors.white, // 🔴 Change text color here
                        fontSize: 16,
                        fontWeight: FontWeight.bold),
                    ),
                  ),
                ),

                const SizedBox(height: 20),

                // 🔻 Register Prompt
                TextButton(
                  onPressed: () {
                    //Navigate to Signup Step 1
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (context) => const SignUpStep1()),
                    );
                  },
                  child: const Text("Don't have an account? Register here"),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
