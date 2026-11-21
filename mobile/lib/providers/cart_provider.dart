import 'package:flutter/foundation.dart';
import '../models/product.dart';
import '../models/cart_item.dart';

class CartProvider with ChangeNotifier {
  final List<CartItem> _items = [];
  String _orderType = 'dine_in';
  int _tableId = 1;
  String _customerName = 'Guest';
  String _customerPhone = '';

  List<CartItem> get items => _items;
  String get orderType => _orderType;
  int get tableId => _tableId;
  String get customerName => _customerName;
  String get customerPhone => _customerPhone;

  int get itemCount => _items.fold(0, (sum, item) => sum + item.quantity);
  double get subtotal => _items.fold(0.0, (sum, item) => sum + item.totalPrice);
  double get vatAmount => (subtotal * 0.15);
  double get serviceAmount => (subtotal * 0.05);
  double get totalAmount => subtotal + vatAmount + serviceAmount;

  void setOrderType(String type) {
    _orderType = type;
    notifyListeners();
  }

  void setTableId(int id) {
    _tableId = id;
    notifyListeners();
  }

  void setCustomerDetails(String name, String phone) {
    _customerName = name;
    _customerPhone = phone;
    notifyListeners();
  }

  void addItem(Product product, {int quantity = 1, String? instructions}) {
    final index = _items.indexWhere((i) => i.product.id == product.id);
    if (index >= 0) {
      _items[index].quantity += quantity;
    } else {
      _items.add(CartItem(
        product: product,
        quantity: quantity,
        specialInstructions: instructions,
      ));
    }
    notifyListeners();
  }

  void updateQuantity(int productId, int quantity) {
    if (quantity <= 0) {
      _items.removeWhere((i) => i.product.id == productId);
    } else {
      final item = _items.firstWhere((i) => i.product.id == productId);
      item.quantity = quantity;
    }
    notifyListeners();
  }

  void clearCart() {
    _items.clear();
    notifyListeners();
  }
}
