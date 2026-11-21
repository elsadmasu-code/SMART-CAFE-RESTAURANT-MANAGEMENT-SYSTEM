import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/product.dart';
import '../providers/cart_provider.dart';
import '../providers/locale_provider.dart';
import '../services/api_service.dart';
import '../services/local_database_service.dart';
import 'cart_screen.dart';

class MenuScreen extends StatefulWidget {
  const MenuScreen({super.key});

  @override
  State<MenuScreen> createState() => _MenuScreenState();
}

class _MenuScreenState extends State<MenuScreen> {
  List<Product> _products = [];
  bool _loading = true;
  bool _fastingOnly = false;

  @override
  void initState() {
    super.initState();
    _loadMenu();
  }

  Future<void> _loadMenu() async {
    final locale = Provider.of<LocaleProvider>(context, listen: false).locale.languageCode;
    List<Product> items = await ApiService.fetchMenu(locale);

    if (items.isEmpty) {
      // Fallback to SQLite cached products
      items = await LocalDatabaseService.getCachedProducts();
    } else {
      // Cache into local database for future offline access
      await LocalDatabaseService.cacheProducts(items);
    }

    if (items.isEmpty) {
      // Default offline menu
      items = [
        Product(
          id: 1,
          name: 'Traditional Jebena Coffee (የጀበና ቡና)',
          description: 'Organic highland roast with fresh rue.',
          price: 40.0,
          preparationTimeMinutes: 7,
          isAvailable: true,
          isFasting: true,
        ),
        Product(
          id: 2,
          name: 'Special Beef Tibs (ልዩ የበሬ ጥብስ)',
          description: 'Sautéed prime beef with rosemary and injera.',
          price: 380.0,
          preparationTimeMinutes: 15,
          isAvailable: true,
          isFasting: false,
        ),
        Product(
          id: 3,
          name: 'Shiro Tegamino (የተጋሚኖ ሽሮ)',
          description: 'Traditional chickpea stew served bubbling in clay.',
          price: 180.0,
          preparationTimeMinutes: 12,
          isAvailable: true,
          isFasting: true,
        ),
        Product(
          id: 4,
          name: 'Fkr Special Pizza (ልዩ የፍቅር ፒዛ)',
          description: 'Hand tossed crust with mozzarella and seasoned beef.',
          price: 420.0,
          preparationTimeMinutes: 20,
          isAvailable: true,
          isFasting: false,
        ),
      ];
    }

    setState(() {
      _products = items;
      _loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    final cart = Provider.of<CartProvider>(context);
    final localeProvider = Provider.of<LocaleProvider>(context);
    final isAm = localeProvider.isAmharic;

    final displayedProducts = _fastingOnly
        ? _products.where((p) => p.isFasting).toList()
        : _products;

    return Scaffold(
      appBar: AppBar(
        title: Text(isAm ? 'የምግብ ሜኑ' : 'Digital Menu'),
        backgroundColor: const Color(0xFF64301B),
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: Icon(_fastingOnly ? Icons.eco : Icons.eco_outlined, color: const Color(0xFFFED100)),
            tooltip: 'Fasting filter',
            onPressed: () {
              setState(() {
                _fastingOnly = !_fastingOnly;
              });
            },
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF64301B)))
          : ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: displayedProducts.length,
              separatorBuilder: (_, __) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                final product = displayedProducts[index];
                return Card(
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  elevation: 1,
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  if (product.isFasting)
                                    Container(
                                      margin: const EdgeInsets.only(right: 6),
                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: Colors.green.shade50,
                                        borderRadius: BorderRadius.circular(6),
                                        border: Border.all(color: Colors.green.shade300),
                                      ),
                                      child: const Text('የጾም', style: TextStyle(color: Colors.green, fontSize: 10)),
                                    ),
                                  Expanded(
                                    child: Text(
                                      product.name,
                                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 4),
                              Text(
                                product.description,
                                style: const TextStyle(fontSize: 12, color: Colors.black54),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 8),
                              Text(
                                '${product.price.toStringAsFixed(0)} ETB (ብር)',
                                style: const TextStyle(
                                  fontWeight: FontWeight.extrabold,
                                  color: Color(0xFF64301B),
                                  fontSize: 14,
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 12),
                        ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF64301B),
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                          ),
                          onPressed: () {
                            cart.addItem(product);
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text('${product.name} added to cart!'),
                                duration: const Duration(milliseconds: 900),
                              ),
                            );
                          },
                          child: Text(isAm ? 'ጨምር' : 'Add'),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
      floatingActionButton: cart.itemCount > 0
          ? FloatingActionButton.extended(
              backgroundColor: const Color(0xFFFED100),
              foregroundColor: Colors.black87,
              onPressed: () {
                Navigator.push(context, MaterialPageRoute(builder: (_) => const CartScreen()));
              },
              icon: const Icon(Icons.shopping_bag),
              label: Text(
                '${cart.itemCount} items • ${cart.totalAmount.toStringAsFixed(0)} ETB',
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
            )
          : null,
    );
  }
}
