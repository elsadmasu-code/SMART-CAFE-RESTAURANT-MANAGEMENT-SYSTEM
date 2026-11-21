import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/product.dart';

class ApiService {
  static const String baseUrl = 'http://10.0.2.2:8000/api/v1'; // Android emulator localhost

  static Future<List<Product>> fetchMenu(String locale) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/menu?locale=$locale'),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        final List<Product> products = [];
        final categories = data['data']['categories'] as List? ?? [];

        for (final cat in categories) {
          final prods = cat['products'] as List? ?? [];
          for (final p in prods) {
            products.add(Product.fromJson(p));
          }
        }
        return products;
      }
      return [];
    } catch (e) {
      return [];
    }
  }

  static Future<Map<String, dynamic>?> placeOrder(Map<String, dynamic> payload) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/orders'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Idempotency-Key': 'mobile-${DateTime.now().millisecondsSinceEpoch}',
        },
        body: json.encode(payload),
      );

      if (response.statusCode == 200 || response.statusCode == 201) {
        return json.decode(response.body);
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  static Future<Map<String, dynamic>?> fetchOrderStatus(String uuid) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/orders/$uuid'),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        return json.decode(response.body)['data'];
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  static Future<String> chatWithAi(String message, String locale) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/ai/chat'),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'message': message,
          'locale': locale,
          'role_context': 'customer',
        }),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['data']['reply'] ?? 'ሰላም! እንዴት ልርዳዎት?';
      }
      return 'AI Service currently offline.';
    } catch (e) {
      return 'Offline mode: You can browse the downloaded menu.';
    }
  }
}
