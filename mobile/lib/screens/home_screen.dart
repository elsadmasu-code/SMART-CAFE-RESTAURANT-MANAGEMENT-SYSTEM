import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/locale_provider.dart';
import 'menu_screen.dart';
import 'ai_assistant_screen.dart';

class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final localeProvider = Provider.of<LocaleProvider>(context);
    final isAm = localeProvider.isAmharic;

    return Scaffold(
      appBar: AppBar(
        title: Text(isAm ? 'ፍቅር ካፌ (ኮምቦልቻ)' : 'Fkr Cafe (Kombolcha)'),
        backgroundColor: const Color(0xFF64301B),
        foregroundColor: Colors.white,
        actions: [
          TextButton(
            onPressed: () => localeProvider.toggleLanguage(),
            child: Text(
              isAm ? 'EN' : 'አማርኛ',
              style: const TextStyle(color: Color(0xFFFED100), fontWeight: FontWeight.bold),
            ),
          ),
        ],
      ),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Hero Banner
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 36),
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [Color(0xFF2A1108), Color(0xFF64301B)],
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                ),
              ),
              child: Column(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Text(
                      'KOMBLCHA • ETHIOPIA',
                      style: TextStyle(color: Color(0xFFFED100), fontSize: 11, fontWeight: FontWeight.bold),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    isAm ? 'እንኳን ወደ ፍቅር ካፌ በደህና መጡ' : 'Welcome to Fkr Cafe',
                    textAlign: TextAlign.center,
                    style: const TextStyle(color: Colors.white, fontSize: 26, fontWeight: FontWeight.extrabold),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    isAm
                        ? 'የኮምቦልቻው ልዩ የጀበና ቡና፣ ባህላዊ ምግቦች እና ጣፋጭ ፒዛዎች በዘመናዊ አሰራር።'
                        : 'Authentic Ethiopian Highland Coffee, Traditional dishes, and Gourmet Pizzas.',
                    textAlign: TextAlign.center,
                    style: const TextStyle(color: Colors.white70, fontSize: 13),
                  ),
                  const SizedBox(height: 24),
                  ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFFED100),
                      foregroundColor: Colors.black87,
                      padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    ),
                    onPressed: () {
                      Navigator.push(context, MaterialPageRoute(builder: (_) => const MenuScreen()));
                    },
                    icon: const Icon(Icons.restaurant_menu),
                    label: Text(
                      isAm ? 'ሜኑ ተመልከትና እዘዝ' : 'View Menu & Order',
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                  ),
                ],
              ),
            ),

            // Features Grid
            Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    isAm ? 'አገልግሎቶቻችን' : 'Our Services',
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      _buildActionCard(
                        context,
                        icon: Icons.coffee,
                        color: const Color(0xFFB86F3E),
                        title: isAm ? 'የጀበና ቡና' : 'Jebena Coffee',
                        subtitle: 'Traditional Brew',
                        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const MenuScreen())),
                      ),
                      const SizedBox(width: 12),
                      _buildActionCard(
                        context,
                        icon: Icons.auto_awesome,
                        color: Colors.purple,
                        title: isAm ? 'AI ረዳት' : 'AI Assistant',
                        subtitle: 'Gemini Assistant',
                        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const AiAssistantScreen())),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // Google Discovery info
                  Card(
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                    elevation: 1,
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: const [
                              Icon(Icons.location_on, color: Color(0xFF64301B)),
                              SizedBox(width: 8),
                              Text('Fkr Cafe - Kombolcha', style: TextStyle(fontWeight: FontWeight.bold)),
                            ],
                          ),
                          const SizedBox(height: 6),
                          const Text(
                            'Main Street, Near CBE, Kombolcha, Amhara, Ethiopia\nOpen Daily: 06:30 AM - 10:30 PM (22:30)',
                            style: TextStyle(fontSize: 12, color: Colors.black54),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildActionCard(
    BuildContext context, {
    required IconData icon,
    required Color color,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return Expanded(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(20),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
            border: Border.parseBorder('border: 1px solid #E5E7EB'),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              CircleAvatar(
                backgroundColor: color.withOpacity(0.15),
                foregroundColor: color,
                child: Icon(icon),
              ),
              const SizedBox(height: 12),
              Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
              Text(subtitle, style: const TextStyle(fontSize: 11, color: Colors.grey)),
            ],
          ),
        ),
      ),
    );
  }
}
