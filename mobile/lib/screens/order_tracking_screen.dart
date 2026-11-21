import 'package:flutter/material.dart';
import '../services/api_service.dart';

class OrderTrackingScreen extends StatefulWidget {
  final String orderUuid;

  const OrderTrackingScreen({super.key, required this.orderUuid});

  @override
  State<OrderTrackingScreen> createState() => _OrderTrackingScreenState();
}

class _OrderTrackingScreenState extends State<OrderTrackingScreen> {
  Map<String, dynamic>? _orderData;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _fetchStatus();
  }

  Future<void> _fetchStatus() async {
    final data = await ApiService.fetchOrderStatus(widget.orderUuid);
    if (mounted) {
      setState(() {
        _orderData = data;
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Live Order Status'),
        backgroundColor: const Color(0xFF64301B),
        foregroundColor: Colors.white,
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF64301B)))
          : Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Card(
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                    child: Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        children: [
                          const Icon(Icons.check_circle, size: 54, color: Colors.green),
                          const SizedBox(height: 12),
                          Text(
                            _orderData?['order_number'] ?? 'FKR-ORDER',
                            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Status: ${_orderData?['order_status'] ?? 'Submitted'}',
                            style: const TextStyle(color: Color(0xFF64301B), fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Total Amount: ${_orderData?['total_amount'] ?? 0} ETB',
                            style: const TextStyle(color: Colors.black54),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),
                  ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF64301B),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    onPressed: _fetchStatus,
                    child: const Text('Refresh Status'),
                  ),
                ],
              ),
            ),
    );
  }
}
