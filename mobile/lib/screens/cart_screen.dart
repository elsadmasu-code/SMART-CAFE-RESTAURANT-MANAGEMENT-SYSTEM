import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/cart_provider.dart';
import '../providers/locale_provider.dart';
import '../services/api_service.dart';
import 'order_tracking_screen.dart';

class CartScreen extends StatefulWidget {
  const CartScreen({super.key});

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  String _selectedPayment = 'telebirr';
  final TextEditingController _refController = TextEditingController();
  bool _submitting = false;

  @override
  void dispose() {
    _refController.dispose();
    super.dispose();
  }

  Future<void> _submitOrder(CartProvider cart) async {
    setState(() => _submitting = true);

    final payload = {
      'order_type': cart.orderType,
      'table_id': cart.orderType == 'dine_in' ? cart.tableId : null,
      'guest_name': cart.customerName,
      'guest_phone': cart.customerPhone,
      'items': cart.items.map((i) => {
            'product_id': i.product.id,
            'quantity': i.quantity,
          }).toList(),
    };

    final res = await ApiService.placeOrder(payload);

    setState(() => _submitting = false);

    if (res != null && res['success'] == true) {
      final orderUuid = res['data']['order_uuid'];
      cart.clearCart();

      if (mounted) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (_) => OrderTrackingScreen(orderUuid: orderUuid),
          ),
        );
      }
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Could not place order. Please check local connection.')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final cart = Provider.of<CartProvider>(context);
    final locale = Provider.of<LocaleProvider>(context);
    final isAm = locale.isAmharic;

    return Scaffold(
      appBar: AppBar(
        title: Text(isAm ? 'የእርስዎ ቅርጫት' : 'Your Cart'),
        backgroundColor: const Color(0xFF64301B),
        foregroundColor: Colors.white,
      ),
      body: cart.items.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.shopping_bag_outlined, size: 64, color: Colors.grey),
                  const SizedBox(height: 12),
                  Text(isAm ? 'ቅርጫትዎ ባዶ ነው' : 'Your cart is empty'),
                ],
              ),
            )
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Order Type
                  Card(
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(isAm ? 'የትዕዛዝ አይነት' : 'Order Type', style: const TextStyle(fontWeight: FontWeight.bold)),
                          const SizedBox(height: 12),
                          Row(
                            children: [
                              _buildTypeChoice(cart, 'dine_in', isAm ? 'ጠረጴዛ ላይ' : 'Dine-In'),
                              const SizedBox(width: 8),
                              _buildTypeChoice(cart, 'takeaway', isAm ? 'ይዞ መሄድ' : 'Takeaway'),
                              const SizedBox(width: 8),
                              _buildTypeChoice(cart, 'delivery', isAm ? 'ዴሊቨሪ' : 'Delivery'),
                            ],
                          ),
                          if (cart.orderType == 'dine_in') ...[
                            const SizedBox(height: 12),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(isAm ? 'ጠረጴዛ ይምረጡ:' : 'Select Table:'),
                                DropdownButton<int>(
                                  value: cart.tableId,
                                  items: List.generate(15, (index) => index + 1)
                                      .map((i) => DropdownMenuItem(value: i, child: Text('Table $i')))
                                      .toList(),
                                  onChanged: (val) {
                                    if (val != null) cart.setTableId(val);
                                  },
                                ),
                              ],
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Cart Items
                  ListView.separated(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: cart.items.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 8),
                    itemBuilder: (context, index) {
                      final item = cart.items[index];
                      return Card(
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        child: Padding(
                          padding: const EdgeInsets.all(12),
                          child: Row(
                            children: [
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(item.product.name, style: const TextStyle(fontWeight: FontWeight.bold)),
                                    Text('${item.product.price} ETB', style: const TextStyle(color: Colors.grey, fontSize: 12)),
                                  ],
                                ),
                              ),
                              IconButton(
                                icon: const Icon(Icons.remove_circle_outline, size: 20),
                                onPressed: () => cart.updateQuantity(item.product.id, item.quantity - 1),
                              ),
                              Text('${item.quantity}', style: const TextStyle(fontWeight: FontWeight.bold)),
                              IconButton(
                                icon: const Icon(Icons.add_circle_outline, size: 20),
                                onPressed: () => cart.updateQuantity(item.product.id, item.quantity + 1),
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                  const SizedBox(height: 16),

                  // Payment Options
                  Card(
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(isAm ? 'የመክፈያ ዘዴ' : 'Payment Method', style: const TextStyle(fontWeight: FontWeight.bold)),
                          RadioListTile<String>(
                            title: const Text('Telebirr (ቴሌብር)'),
                            subtitle: const Text('Merchant: 0911000001 (Fkr Cafe)'),
                            value: 'telebirr',
                            groupValue: _selectedPayment,
                            onChanged: (val) => setState(() => _selectedPayment = val!),
                          ),
                          RadioListTile<String>(
                            title: const Text('CBE Birr (ሲቢኢ ብር)'),
                            subtitle: const Text('Account: 1000123456789'),
                            value: 'cbe_birr',
                            groupValue: _selectedPayment,
                            onChanged: (val) => setState(() => _selectedPayment = val!),
                          ),
                          RadioListTile<String>(
                            title: const Text('Cash (በጥሬ ገንዘብ)'),
                            subtitle: const Text('Pay to waiter or cashier'),
                            value: 'cash',
                            groupValue: _selectedPayment,
                            onChanged: (val) => setState(() => _selectedPayment = val!),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Summary
                  Card(
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        children: [
                          _buildSummaryRow('Subtotal', '${cart.subtotal.toStringAsFixed(2)} ETB'),
                          _buildSummaryRow('VAT (15%)', '${cart.vatAmount.toStringAsFixed(2)} ETB'),
                          _buildSummaryRow('Service (5%)', '${cart.serviceAmount.toStringAsFixed(2)} ETB'),
                          const Divider(),
                          _buildSummaryRow('Total Amount', '${cart.totalAmount.toStringAsFixed(2)} ETB', isBold: true),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),

                  ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF64301B),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    ),
                    onPressed: _submitting ? null : () => _submitOrder(cart),
                    child: _submitting
                        ? const CircularProgressIndicator(color: Colors.white)
                        : Text(isAm ? 'ትዕዛዝ አስተላልፍ' : 'Place Order Now', style: const TextStyle(fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            ),
    );
  }

  Widget _buildTypeChoice(CartProvider cart, String type, String label) {
    final isSelected = cart.orderType == type;
    return Expanded(
      child: InkWell(
        onTap: () => cart.setOrderType(type),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 8),
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: isSelected ? const Color(0xFF64301B) : Colors.grey.shade100,
            borderRadius: BorderRadius.circular(10),
          ),
          child: Text(
            label,
            style: TextStyle(
              color: isSelected ? Colors.white : Colors.black87,
              fontSize: 12,
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSummaryRow(String label, String value, {bool isBold = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(fontWeight: isBold ? FontWeight.bold : FontWeight.normal)),
          Text(value, style: TextStyle(fontWeight: isBold ? FontWeight.bold : FontWeight.normal)),
        ],
      ),
    );
  }
}
