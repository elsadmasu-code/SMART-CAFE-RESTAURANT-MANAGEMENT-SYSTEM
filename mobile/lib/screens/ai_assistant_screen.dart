import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/locale_provider.dart';
import '../services/api_service.dart';

class AiAssistantScreen extends StatefulWidget {
  const AiAssistantScreen({super.key});

  @override
  State<AiAssistantScreen> createState() => _AiAssistantScreenState();
}

class _AiAssistantScreenState extends State<AiAssistantScreen> {
  final TextEditingController _textController = TextEditingController();
  final List<Map<String, String>> _messages = [
    {
      'sender': 'assistant',
      'text': 'ሰላም! እኔ የፍቅር ካፌ (ኮምቦልቻ) AI ረዳት ነኝ። ስለ ምግብ ምርጫ፣ የጾም ምግቦች ወይም የቡና አይነቶች ማንኛውንም ጥያቄ ይጠይቁኝ።',
    }
  ];
  bool _loading = false;

  Future<void> _sendMessage() async {
    final text = _textController.text.trim();
    if (text.isEmpty || _loading) return;

    final locale = Provider.of<LocaleProvider>(context, listen: false).locale.languageCode;

    setState(() {
      _messages.add({'sender': 'user', 'text': text});
      _loading = true;
    });
    _textController.clear();

    final reply = await ApiService.chatWithAi(text, locale);

    if (mounted) {
      setState(() {
        _messages.add({'sender': 'assistant', 'text': reply});
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Fkr Cafe AI Assistant'),
        backgroundColor: const Color(0xFF64301B),
        foregroundColor: Colors.white,
      ),
      body: Column(
        children: [
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: _messages.length,
              itemBuilder: (context, index) {
                final msg = _messages[index];
                final isUser = msg['sender'] == 'user';
                return Align(
                  alignment: isUser ? Alignment.centerRight : Alignment.centerLeft,
                  child: Container(
                    margin: const EdgeInsets.symmetric(vertical: 4),
                    padding: const EdgeInsets.all(12),
                    constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.8),
                    decoration: BoxDecoration(
                      color: isUser ? const Color(0xFF64301B) : Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: Colors.black12),
                    ),
                    child: Text(
                      msg['text']!,
                      style: TextStyle(
                        color: isUser ? Colors.white : Colors.black87,
                        fontSize: 13,
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
          if (_loading)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 8),
              child: Text('AI is thinking...', style: TextStyle(fontSize: 11, color: Colors.grey)),
            ),
          Container(
            padding: const EdgeInsets.all(12),
            color: Colors.white,
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _textController,
                    decoration: const InputDecoration(
                      hintText: 'በአማርኛ ወይም በእንግሊዝኛ ይጠይቁ...',
                      border: OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(24))),
                      contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                IconButton(
                  icon: const Icon(Icons.send, color: Color(0xFF64301B)),
                  onPressed: _sendMessage,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
