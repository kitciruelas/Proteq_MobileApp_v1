import 'package:flutter/material.dart';
import 'package:file_picker/file_picker.dart';
import 'login_screen.dart';
import 'signup_step2.dart';

class SignUpStep1 extends StatefulWidget {
  const SignUpStep1({super.key});

  @override
  State<SignUpStep1> createState() => _SignUpStep1State();
}

class _SignUpStep1State extends State<SignUpStep1> {
  String? selectedRole;
  String? selectedDepartment;
  String? selectedCollege;
  PlatformFile? selectedFile;

  final _firstNameController = TextEditingController();
  final _lastNameController = TextEditingController();
  final _emailController = TextEditingController();

  final departments = ["ICT", "Engineering", "Nursing"];
  final colleges = ["BSIT", "BSCpE", "BSN"];

  Future<void> _pickFile() async {
    final result = await FilePicker.platform.pickFiles();
    if (result != null) {
      setState(() {
        selectedFile = result.files.first;
      });
    }
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
                StepCircle(index: 2, isActive: false, label: "Security"),
                StepCircle(index: 3, isActive: false, label: "Review"),
              ],
            ),
            const SizedBox(height: 24),

            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _firstNameController,
                    decoration: const InputDecoration(
                      labelText: 'First Name',
                      prefixIcon: Icon(Icons.person_outline),
                      border: OutlineInputBorder(),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: TextField(
                    controller: _lastNameController,
                    decoration: const InputDecoration(
                      labelText: 'Last Name',
                      prefixIcon: Icon(Icons.person_outline),
                      border: OutlineInputBorder(),
                    ),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 16),

            TextField(
              controller: _emailController,
              decoration: const InputDecoration(
                labelText: 'Email address',
                prefixIcon: Icon(Icons.email_outlined),
                border: OutlineInputBorder(),
              ),
            ),

            const SizedBox(height: 20),
            const Text("I am a"),
            Column(
              children: [
                RadioListTile<String>(
                  title: const Text("Student"),
                  value: "Student",
                  groupValue: selectedRole,
                  onChanged: (value) => setState(() => selectedRole = value),
                ),
                RadioListTile<String>(
                  title: const Text("Faculty"),
                  value: "Faculty",
                  groupValue: selectedRole,
                  onChanged: (value) => setState(() => selectedRole = value),
                ),
                RadioListTile<String>(
                  title: const Text("University Employee"),
                  value: "University Employee",
                  groupValue: selectedRole,
                  onChanged: (value) => setState(() => selectedRole = value),
                ),
              ],
            ),

            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(
                  child: DropdownButtonFormField<String>(
                    decoration: const InputDecoration(
                      labelText: "Department",
                      prefixIcon: Icon(Icons.apartment_outlined),
                      border: OutlineInputBorder(),
                    ),
                    items: departments.map((dep) {
                      return DropdownMenuItem(value: dep, child: Text(dep));
                    }).toList(),
                    value: selectedDepartment,
                    onChanged: (value) => setState(() => selectedDepartment = value),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: DropdownButtonFormField<String>(
                    decoration: const InputDecoration(
                      labelText: "College/Course",
                      prefixIcon: Icon(Icons.school_outlined),
                      border: OutlineInputBorder(),
                    ),
                    items: colleges.map((col) {
                      return DropdownMenuItem(value: col, child: Text(col));
                    }).toList(),
                    value: selectedCollege,
                    onChanged: (value) => setState(() => selectedCollege = value),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 16),
            const Text("Profile Picture (Optional)"),
            Row(
              children: [
                ElevatedButton.icon(
                  onPressed: _pickFile,
                  icon: const Icon(Icons.upload_file),
                  label: const Text("Choose File"),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(selectedFile?.name ?? "No file chosen"),
                )
              ],
            ),
            const SizedBox(height: 24),
            Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.blue,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                    onPressed: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (context) => const SignUpStep2()),
                        );
                      },
                      child: const Text("Next", style: TextStyle(fontSize: 16)),
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
          ],
        ),
      ),
    );
  }
}

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
