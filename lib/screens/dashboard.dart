import 'package:flutter/material.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  bool _showAlert = true;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        automaticallyImplyLeading: false,
        title: const Text('PROTEQ', style: TextStyle(color: Colors.white)),
        backgroundColor: Colors.red,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.settings),
            onPressed: () {
              // TODO: Navigate to settings
            },
          ),
        ],
      ),
      body: SafeArea(
        child: Column(
          children: [
            if (_showAlert)
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 12.0, vertical: 8.0),
                child: Material(
                  elevation: 2,
                  borderRadius: BorderRadius.circular(12),
                  child: Container(
                    width: double.infinity,
                    decoration: BoxDecoration(
                      color: Colors.orange[200],
                      borderRadius: BorderRadius.circular(12),
                    ),
                    padding: const EdgeInsets.all(12),
                    child: Row(
                      children: [
                        const Icon(Icons.warning, color: Colors.red, size: 28),
                        const SizedBox(width: 10),
                        const Expanded(
                          child: Text(
                            'ALERT: Earthquake Drill at 9:30 AM',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                          ),
                        ),
                        IconButton(
                          icon: const Icon(Icons.close, size: 20),
                          onPressed: () => setState(() => _showAlert = false),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            const SizedBox(height: 16),
            Expanded(
              child: LayoutBuilder(
                builder: (context, constraints) {
                  final isWide = constraints.maxWidth > 500;
                  return GridView.count(
                    crossAxisCount: isWide ? 3 : 2,
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                    crossAxisSpacing: 20,
                    mainAxisSpacing: 20,
                    children: [
                      _DashboardButton(
                        icon: Icons.report,
                        label: 'Report Incident',
                        color: Colors.redAccent,
                        onTap: () => Navigator.pushNamed(context, '/report'),
                      ),
                      _DashboardButton(
                        icon: Icons.people,
                        label: 'Welfare Check',
                        color: Colors.blueAccent,
                        onTap: () => Navigator.pushNamed(context, '/welfare'),
                      ),
                      _DashboardButton(
                        icon: Icons.health_and_safety,
                        label: 'Safety Protocols',
                        color: Colors.green,
                        onTap: () => Navigator.pushNamed(context, '/protocols'),
                      ),
                      _DashboardButton(
                        icon: Icons.info,
                        label: 'Evacuation Centers',
                        color: Colors.orange,
                        onTap: () => Navigator.pushNamed(context, '/evacuation'),
                      ),
                    ],
                  );
                },
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(16.0),
              child: Material(
                elevation: 2,
                borderRadius: BorderRadius.circular(16),
                child: Container(
                  height: 120,
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: Colors.grey[200],
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: const Center(
                    child: Text('Map Preview Placeholder', style: TextStyle(color: Colors.black54)),
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

class _DashboardButton extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;
  const _DashboardButton({required this.icon, required this.label, required this.color, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(20),
      elevation: 2,
      child: InkWell(
        borderRadius: BorderRadius.circular(20),
        splashColor: color.withOpacity(0.2),
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 28, horizontal: 8),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              CircleAvatar(
                backgroundColor: color.withOpacity(0.15),
                radius: 32,
                child: Icon(icon, size: 38, color: color),
              ),
              const SizedBox(height: 18),
              Text(
                label,
                textAlign: TextAlign.center,
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
              ),
            ],
          ),
        ),
      ),
    );
  }
} 