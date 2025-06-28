import 'package:flutter/material.dart';
import 'login_screen.dart';
import 'signup_step1.dart';
import 'signup_step3.dart'; // ✅ Step 3 page

class SignUpStep2 extends StatefulWidget {
  const SignUpStep2({super.key});

  @override
  State<SignUpStep2> createState() => _SignUpStep2State();
}

class _SignUpStep2State extends State<SignUpStep2> {
  bool _obscurePassword = true;
  bool _obscureConfirm = true;

  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  String? _errorText;

  bool _isPasswordValid(String password) {
    final hasUppercase = password.contains(RegExp(r'[A-Z]'));
    final hasNumber = password.contains(RegExp(r'[0-9]'));
    return password.length >= 8 && hasUppercase && hasNumber;
  }

  void _goToNextStep() {
    final password = _passwordController.text;
    final confirmPassword = _confirmPasswordController.text;

    if (password != confirmPassword) {
      setState(() {
        _errorText = "Passwords do not match.";
      });
      return;
    }

    if (!_isPasswordValid(password)) {
      setState(() {
        _errorText =
            "Password must be at least 8 characters, include one uppercase letter and one number.";
      });
      return;
    }

    // ✅ Proceed to Step 3
    setState(() {
      _errorText = null;
    });

    Navigator.push(
      context,
      MaterialPageRoute(builder: (context) => const SignUpStep3()),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Create Account")),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // 🔵 Stepper (Static)
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: const [
                StepCircle(index: 1, isActive: true, label: "General Info"),
                StepCircle(index: 2, isActive: true, label: "Security"),
                StepCircle(index: 3, isActive: false, label: "Review"),
              ],
            ),
            const SizedBox(height: 24),

            const Text("🔐 Password"),
            const SizedBox(height: 6),
            TextField(
              obscureText: _obscurePassword,
              controller: _passwordController,
              decoration: InputDecoration(
                hintText: "Enter your password",
                border: const OutlineInputBorder(),
                suffixIcon: IconButton(
                  icon: Icon(
                    _obscurePassword ? Icons.visibility_off : Icons.visibility,
                  ),
                  onPressed: () {
                    setState(() {
                      _obscurePassword = !_obscurePassword;
                    });
                  },
                ),
              ),
            ),

            const SizedBox(height: 10),

            Row(
              children: const [
                Icon(Icons.shield_outlined, size: 18),
                SizedBox(width: 6),
                Text("Password must contain:"),
              ],
            ),
            const Padding(
              padding: EdgeInsets.only(left: 24.0, top: 4),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text("• At least 8 characters"),
                  Text("• At least one uppercase letter"),
                  Text("• At least one number"),
                ],
              ),
            ),

            const SizedBox(height: 20),

            const Text("🔁 Confirm Password"),
            const SizedBox(height: 6),
            TextField(
              obscureText: _obscureConfirm,
              controller: _confirmPasswordController,
              decoration: InputDecoration(
                hintText: "Confirm your password",
                border: const OutlineInputBorder(),
                suffixIcon: IconButton(
                  icon: Icon(
                    _obscureConfirm ? Icons.visibility_off : Icons.visibility,
                  ),
                  onPressed: () {
                    setState(() {
                      _obscureConfirm = !_obscureConfirm;
                    });
                  },
                ),
              ),
            ),

            if (_errorText != null) ...[
              const SizedBox(height: 8),
              Text(
                _errorText!,
                style: const TextStyle(color: Colors.red),
              ),
            ],

            const SizedBox(height: 20),

            // 🔶 Security Tips Box
            Container(
              decoration: BoxDecoration(
                color: Colors.yellow[100],
                borderRadius: BorderRadius.circular(6),
                border: Border.all(color: Colors.yellow[700]!),
              ),
              padding: const EdgeInsets.all(12),
              child: const Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(Icons.warning_amber_rounded, color: Colors.orange),
                      SizedBox(width: 6),
                      Text(
                        "Security Tips:",
                        style: TextStyle(fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  SizedBox(height: 6),
                  Text("• Use a strong, unique password"),
                  Text("• Never share your password with anyone"),
                  Text("• Enable two-factor authentication if available"),
                ],
              ),
            ),

            const SizedBox(height: 24),

            // 🔘 Navigation Buttons
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () {
                      Navigator.pop(context); // Go back to Step 1
                    },
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                    child: const Text("Previous"),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: _goToNextStep,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.blue,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                    child: const Text("Next"),
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
}

// 🔁 Step Circle Widget
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
