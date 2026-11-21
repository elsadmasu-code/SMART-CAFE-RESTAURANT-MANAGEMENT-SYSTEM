class Product {
  final int id;
  final String name;
  final String description;
  final double price;
  final int preparationTimeMinutes;
  final String? imageUrl;
  final bool isAvailable;
  final bool isFasting;

  Product({
    required this.id,
    required this.name,
    required this.description,
    required this.price,
    required this.preparationTimeMinutes,
    this.imageUrl,
    required this.isAvailable,
    required this.isFasting,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: json['id'] as int,
      name: json['name'] as String? ?? 'Item',
      description: json['description'] as String? ?? '',
      price: (json['price'] as num?)?.toDouble() ?? 0.0,
      preparationTimeMinutes: json['preparation_time_minutes'] as int? ?? 10,
      imageUrl: json['image_url'] as String?,
      isAvailable: json['is_available'] as bool? ?? true,
      isFasting: json['is_fasting'] as bool? ?? false,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'name': name,
      'description': description,
      'price': price,
      'preparation_time_minutes': preparationTimeMinutes,
      'image_url': imageUrl,
      'is_available': isAvailable ? 1 : 0,
      'is_fasting': isFasting ? 1 : 0,
    };
  }
}
