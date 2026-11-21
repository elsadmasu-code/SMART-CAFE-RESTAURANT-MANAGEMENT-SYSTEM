class OrderSummary {
  final String orderUuid;
  final String orderNumber;
  final double totalAmount;
  final String orderStatus;
  final String paymentStatus;
  final String createdAt;

  OrderSummary({
    required this.orderUuid,
    required this.orderNumber,
    required this.totalAmount,
    required this.orderStatus,
    required this.paymentStatus,
    required this.createdAt,
  });

  factory OrderSummary.fromJson(Map<String, dynamic> json) {
    return OrderSummary(
      orderUuid: json['order_uuid'] as String? ?? '',
      orderNumber: json['order_number'] as String? ?? '',
      totalAmount: (json['total_amount'] as num?)?.toDouble() ?? 0.0,
      orderStatus: json['order_status'] as String? ?? 'submitted',
      paymentStatus: json['payment_status'] as String? ?? 'pending',
      createdAt: json['created_at'] as String? ?? '',
    );
  }
}
