import 'package:flutter/material.dart';
import 'signup_step2.dart';
import 'login_screen.dart';

class SignUpStep3 extends StatefulWidget {
  const SignUpStep3({super.key});

  @override
  State<SignUpStep3> createState() => _SignUpStep3State();
}

class _SignUpStep3State extends State<SignUpStep3> {
  bool isPolicyAgreed = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Create Account")),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Stepper
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: const [
                StepCircle(index: 1, isActive: true, label: "General Info"),
                StepCircle(index: 2, isActive: true, label: "Security"),
                StepCircle(index: 3, isActive: true, label: "Review"),
              ],
            ),
            const SizedBox(height: 24),

            const Text(
              "🔍 Review Your Information",
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),

            const SizedBox(height: 16),

            // General Info Card
            _buildInfoCard(
              title: "📁 General Information",
              content: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: const [
                  Text("Name: Sharmaine Abrenica"),
                  Text("Email: 22-35294@g.batstate-u.edu.ph"),
                  Text("Department: CICS"),
                  Text("College: Bachelor of Science in Information Technology"),
                  Text("Profile Picture: No file selected"),
                ],
              ),
            ),

            const SizedBox(height: 10),

            // Security Info Card
            _buildInfoCard(
              title: "🔒 Security Information",
              content: const Text("Password: ********"),
            ),

            const SizedBox(height: 10),

            // Role Info Card
            _buildInfoCard(
              title: "👤 Role Information",
              content: const Text("I am a: Student"),
            ),

            const SizedBox(height: 10),

            // Privacy Policy Consent
            _buildInfoCard(
              title: "📜 Privacy Policy Consent",
              content: CheckboxListTile(
                controlAffinity: ListTileControlAffinity.leading,
                contentPadding: EdgeInsets.zero,
                value: isPolicyAgreed,
                onChanged: (value) => setState(() => isPolicyAgreed = value!),
                title: const Text.rich(
                  TextSpan(
                    text: "I have read and agree to the ",
                    children: [
                      TextSpan(
                        text: "Privacy Policy",
                        style: TextStyle(
                          color: Colors.blue,
                          decoration: TextDecoration.underline,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),

            const SizedBox(height: 10),

            // Final Notes
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.blue.shade50,
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text("✅ Final Step:", style: TextStyle(fontWeight: FontWeight.bold)),
                  SizedBox(height: 6),
                  Text("• Please review all information carefully"),
                  Text("• Ensure all details are accurate"),
                  Text("• Read and accept the Privacy Policy"),
                ],
              ),
            ),

            const SizedBox(height: 24),

            // Buttons
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () {
                      Navigator.pop(context); // Go back to Step 2
                    },
                    child: const Text("Previous"),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: ElevatedButton(
                    onPressed: isPolicyAgreed
                        ? () {
                            // ✅ Submit the form or show confirmation
                            showDialog(
                              context: context,
                              builder: (context) => AlertDialog(
                                title: const Text("Account Created"),
                                content: const Text("Your account has been successfully created."),
                                actions: [
                                  TextButton(
                                    onPressed: () {
                                      Navigator.pushAndRemoveUntil(
                                        context,
                                        MaterialPageRoute(
                                            builder: (context) => const LoginScreen()),
                                        (route) => false,
                                      );
                                    },
                                    child: const Text("Go to Login"),
                                  ),
                                ],
                              ),
                            );
                          }
                        : null,
                    child: const Text("Confirm & Sign Up"),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 16),
            Center(
              child: TextButton(
                onPressed: () {
                  Navigator.pushReplacement(
                    context,
                    MaterialPageRoute(builder: (context) => const LoginScreen()),
                  );
                },
                child: const Text.rich(
                  TextSpan(
                    text: "Already have an account? ",
                    children: [
                      TextSpan(
                        text: "Log In here",
                        style: TextStyle(fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // Card builder
  Widget _buildInfoCard({required String title, required Widget content}) {
    return Card(
      elevation: 0,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(8),
        side: const BorderSide(color: Colors.grey),
      ),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: const TextStyle(fontWeight: FontWeight.bold)),
            const Divider(),
            content,
          ],
        ),
      ),
    );
  }
}

// Reusable Step Circle
class StepCircle extends StatelessWidget {
  final int index;
  final bool isActive;
  final String label;

  const StepCircle({
    super.key,
    required this.index,
    required this.isActive,
    required this.label,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        CircleAvatar(
          backgroundColor: isActive ? Colors.blue : Colors.grey[300],
          child: Text(
            index.toString(),
            style: TextStyle(
              color: isActive ? Colors.white : Colors.black,
            ),
          ),
        ),
        const SizedBox(height: 4),
        Text(label),
      ],
    );
  }
}
