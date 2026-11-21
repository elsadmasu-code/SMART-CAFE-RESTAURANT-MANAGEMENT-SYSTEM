import 'product.dart';

class CartItem {
  final Product product;
  int quantity;
  String? specialInstructions;

  CartItem({
    required this.product,
    this.quantity = 1,
    this.specialInstructions,
  });

  double get totalPrice => product.price * quantity;
}
