import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import '../models/product.dart';

class LocalDatabaseService {
  static Database? _database;

  static Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDatabase();
    return _database!;
  }

  static Future<Database> _initDatabase() async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, 'fkr_cafe_offline.db');

    return await openDatabase(
      path,
      version: 1,
      onCreate: (db, version) async {
        await db.execute('''
          CREATE TABLE cached_products (
            id INTEGER PRIMARY KEY,
            name TEXT,
            description TEXT,
            price REAL,
            preparation_time_minutes INTEGER,
            image_url TEXT,
            is_available INTEGER,
            is_fasting INTEGER
          )
        ''');

        await db.execute('''
          CREATE TABLE queued_offline_orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_payload TEXT,
            created_at TEXT
          )
        ''');
      },
    );
  }

  static Future<void> cacheProducts(List<Product> products) async {
    final db = await database;
    final batch = db.batch();
    batch.delete('cached_products');
    for (final p in products) {
      batch.insert('cached_products', p.toMap());
    }
    await batch.commit(noResult: true);
  }

  static Future<List<Product>> getCachedProducts() async {
    final db = await database;
    final List<Map<String, dynamic>> maps = await db.query('cached_products');
    return maps.map((m) => Product.fromJson(m)).toList();
  }
}
