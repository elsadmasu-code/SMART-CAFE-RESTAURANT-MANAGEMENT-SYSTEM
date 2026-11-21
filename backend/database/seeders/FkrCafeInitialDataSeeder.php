<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Models\Department;
use App\Models\Employee;
use App\Models\DiningTable;
use App\Models\QrCode;
use App\Models\BusinessHour;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use App\Models\ProductAddon;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\Supplier;
use App\Models\Shift;
use App\Models\ExpenseCategory;
use App\Models\KitchenStation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FkrCafeInitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Fkr Cafe Branch
        $branch = Branch::create([
            'code' => 'FKR-KMB-01',
            'name' => 'Fkr Cafe',
            'city' => 'Kombolcha',
            'region' => 'Amhara',
            'country' => 'Ethiopia',
            'address' => 'Main Street, Near Commercial Bank of Ethiopia, Kombolcha',
            'phone' => '+251 33 551 2345',
            'tin_number' => '0049281729',
            'currency_code' => 'ETB',
            'default_vat_rate' => 15.00,
            'default_service_charge' => 5.00,
            'is_active' => true,
        ]);

        // 2. Roles
        $rolesData = [
            ['slug' => 'manager', 'name_en' => 'General Manager', 'name_am' => 'ስራ አስኪያጅ', 'description' => 'Full administrative control'],
            ['slug' => 'cashier', 'name_en' => 'Cashier', 'name_am' => 'ካሺየር', 'description' => 'Order payment verification and billing'],
            ['slug' => 'chef', 'name_en' => 'Kitchen Chef', 'name_am' => 'ሼፍ / የወጥ ቤት ሰራተኛ', 'description' => 'Kitchen Display System & food preparation'],
            ['slug' => 'waiter', 'name_en' => 'Waiter / Service', 'name_am' => 'አስተናጋጅ', 'description' => 'Table delivery and guest service calls'],
            ['slug' => 'storekeeper', 'name_en' => 'Storekeeper', 'name_am' => 'እቃ ግምጃ ቤት ኃላፊ', 'description' => 'Inventory raw ingredients & purchases'],
            ['slug' => 'customer', 'name_en' => 'Customer', 'name_am' => 'ደንበኛ', 'description' => 'Public customer and registered ordering'],
            ['slug' => 'owner', 'name_en' => 'Business Owner', 'name_am' => 'የካፌው ባለቤት', 'description' => 'Executive business reports and oversight'],
        ];

        $roles = [];
        foreach ($rolesData as $rd) {
            $roles[$rd['slug']] = Role::create($rd);
        }

        // 3. Departments
        $deptManagement = Department::create(['branch_id' => $branch->id, 'name_en' => 'Management', 'name_am' => 'አስተዳደር']);
        $deptKitchen = Department::create(['branch_id' => $branch->id, 'name_en' => 'Kitchen & Food Prep', 'name_am' => 'የወጥ ቤት ክፍል']);
        $deptService = Department::create(['branch_id' => $branch->id, 'name_en' => 'Service & Floor', 'name_am' => 'የአስተናጋጅ ክፍል']);
        $deptStore = Department::create(['branch_id' => $branch->id, 'name_en' => 'Store & Inventory', 'name_am' => 'የእቃ ግምጃ ቤት']);

        // 4. Default Staff Users
        $defaultStaff = [
            [
                'name' => 'Abebe Kebede',
                'email' => 'manager@fkrcafe.com',
                'phone' => '+251911000001',
                'role' => 'manager',
                'position' => 'General Manager',
                'dept' => $deptManagement->id,
                'salary' => 18000.00,
            ],
            [
                'name' => 'Tigist Haile',
                'email' => 'cashier@fkrcafe.com',
                'phone' => '+251911000002',
                'role' => 'cashier',
                'position' => 'Senior Cashier',
                'dept' => $deptManagement->id,
                'salary' => 8500.00,
            ],
            [
                'name' => 'Mulugeta Tadesse',
                'email' => 'chef@fkrcafe.com',
                'phone' => '+251911000003',
                'role' => 'chef',
                'position' => 'Head Chef',
                'dept' => $deptKitchen->id,
                'salary' => 12000.00,
            ],
            [
                'name' => 'Sara Mohammed',
                'email' => 'waiter@fkrcafe.com',
                'phone' => '+251911000004',
                'role' => 'waiter',
                'position' => 'Lead Floor Waiter',
                'dept' => $deptService->id,
                'salary' => 6500.00,
            ],
            [
                'name' => 'Dawit Girma',
                'email' => 'store@fkrcafe.com',
                'phone' => '+251911000005',
                'role' => 'storekeeper',
                'position' => 'Inventory Officer',
                'dept' => $deptStore->id,
                'salary' => 7500.00,
            ],
        ];

        foreach ($defaultStaff as $index => $staff) {
            $user = User::create([
                'uuid' => (string) Str::uuid(),
                'branch_id' => $branch->id,
                'name' => $staff['name'],
                'email' => $staff['email'],
                'phone' => $staff['phone'],
                'password' => Hash::make('password123'),
                'status' => 'active',
                'preferred_language' => 'am',
            ]);
            $user->roles()->attach($roles[$staff['role']]->id);

            Employee::create([
                'user_id' => $user->id,
                'branch_id' => $branch->id,
                'department_id' => $staff['dept'],
                'employee_code' => 'EMP-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'position' => $staff['position'],
                'hire_date' => now()->subMonths(6)->toDateString(),
                'base_salary' => $staff['salary'],
                'employment_status' => 'active',
            ]);
        }

        // 5. Kitchen Stations
        KitchenStation::create([
            'branch_id' => $branch->id,
            'name_en' => 'Coffee & Beverages Bar',
            'name_am' => 'የቡና እና መጠጦች ባር',
            'code' => 'BARISTA-01',
        ]);
        KitchenStation::create([
            'branch_id' => $branch->id,
            'name_en' => 'Hot Traditional & Grill Kitchen',
            'name_am' => 'የባህላዊ እና የጥብስ ወጥ ቤት',
            'code' => 'GRILL-01',
        ]);
        KitchenStation::create([
            'branch_id' => $branch->id,
            'name_en' => 'Fast Food & Pizza Oven',
            'name_am' => 'የፒዛ እና ፈጣን ምግቦች ክፍል',
            'code' => 'PIZZA-01',
        ]);

        // 6. Dining Tables & QR Codes
        for ($i = 1; $i <= 15; $i++) {
            $zone = $i <= 6 ? 'Main Hall' : ($i <= 12 ? 'Garden Terrace' : 'VIP Balcony');
            $table = DiningTable::create([
                'branch_id' => $branch->id,
                'table_number' => "T-{$i}",
                'seating_capacity' => ($i % 3 === 0) ? 6 : 4,
                'zone' => $zone,
                'status' => 'vacant',
                'is_active' => true,
            ]);

            $token = hash('sha256', "fkr-cafe-table-{$i}-" . Str::random(16));
            QrCode::create([
                'dining_table_id' => $table->id,
                'signed_token' => $token,
                'qr_url' => "https://www.fkrcafe.com/menu?table={$table->id}&token={$token}",
                'scan_count' => 0,
            ]);
        }

        // 7. Business Operating Hours
        for ($day = 0; $day <= 6; $day++) {
            BusinessHour::create([
                'branch_id' => $branch->id,
                'day_of_week' => $day,
                'open_time' => '06:30:00',
                'close_time' => '22:30:00',
                'is_closed' => false,
            ]);
        }

        // 8. Shifts
        Shift::create([
            'branch_id' => $branch->id,
            'shift_name' => 'Morning Shift (የጠዋት ፈረቃ)',
            'start_time' => '06:30:00',
            'end_time' => '14:30:00',
            'grace_period_minutes' => 15,
        ]);
        Shift::create([
            'branch_id' => $branch->id,
            'shift_name' => 'Evening Shift (የማታ ፈረቃ)',
            'start_time' => '14:30:00',
            'end_time' => '22:30:00',
            'grace_period_minutes' => 15,
        ]);

        // 9. Raw Ingredients
        $ingredientsData = [
            ['name_en' => 'Organic Highland Coffee Beans', 'name_am' => 'ኦርጋኒክ የሀበሻ ቡና', 'unit' => 'kg', 'stock' => 50.0, 'min' => 10.0, 'cost' => 380.0],
            ['name_en' => 'Fresh Pasteurized Milk', 'name_am' => 'ትኩስ ወተት', 'unit' => 'liter', 'stock' => 40.0, 'min' => 15.0, 'cost' => 70.0],
            ['name_en' => 'Special White Wheat Flour', 'name_am' => 'የዳቦና የፒዛ ዱቄት', 'unit' => 'kg', 'stock' => 100.0, 'min' => 20.0, 'cost' => 85.0],
            ['name_en' => 'Prime Fresh Beef', 'name_am' => 'ትኩስ የበሬ ሥጋ', 'unit' => 'kg', 'stock' => 35.0, 'min' => 8.0, 'cost' => 650.0],
            ['name_en' => 'Authentic Shiro Powder', 'name_am' => 'የተዘጋጀ የሽሮ ዱቄት', 'unit' => 'kg', 'stock' => 25.0, 'min' => 5.0, 'cost' => 220.0],
            ['name_en' => 'Pure Vegetable Cooking Oil', 'name_am' => 'የሱፍ ዘይት', 'unit' => 'liter', 'stock' => 60.0, 'min' => 15.0, 'cost' => 210.0],
            ['name_en' => 'Fresh Mozzarella Cheese', 'name_am' => 'ሞዛሬላ አይብ', 'unit' => 'kg', 'stock' => 18.0, 'min' => 5.0, 'cost' => 580.0],
            ['name_en' => 'Fresh Ripe Hass Avocados', 'name_am' => 'ትኩስ አቮካዶ', 'unit' => 'kg', 'stock' => 45.0, 'min' => 10.0, 'cost' => 60.0],
            ['name_en' => 'Sweet Mangoes', 'name_am' => 'ማንጎ', 'unit' => 'kg', 'stock' => 40.0, 'min' => 10.0, 'cost' => 80.0],
            ['name_en' => 'Refined Sugar', 'name_am' => 'ስኳር', 'unit' => 'kg', 'stock' => 50.0, 'min' => 10.0, 'cost' => 110.0],
        ];

        $ingredients = [];
        foreach ($ingredientsData as $ing) {
            $ingredients[$ing['name_en']] = Ingredient::create([
                'branch_id' => $branch->id,
                'name_en' => $ing['name_en'],
                'name_am' => $ing['name_am'],
                'unit_of_measure' => $ing['unit'],
                'current_stock' => $ing['stock'],
                'minimum_stock_level' => $ing['min'],
                'reorder_level' => $ing['min'] * 1.5,
                'cost_per_unit' => $ing['cost'],
                'is_active' => true,
            ]);
        }

        // 10. Menu Categories
        $categoriesData = [
            [
                'slug' => 'coffee-hot-drinks',
                'name_en' => 'Coffee & Hot Drinks',
                'name_am' => 'ትኩስ መጠጦች እና ቡና',
                'icon' => 'coffee',
                'display_order' => 1,
            ],
            [
                'slug' => 'traditional-dishes',
                'name_en' => 'Traditional Ethiopian Food',
                'name_am' => 'ባህላዊ ምግቦች',
                'icon' => 'utensils',
                'display_order' => 2,
            ],
            [
                'slug' => 'pizzas-fast-food',
                'name_en' => 'Pizzas & Fast Food',
                'name_am' => 'ፒዛ እና ፈጣን ምግቦች',
                'icon' => 'pizza',
                'display_order' => 3,
            ],
            [
                'slug' => 'fresh-juices',
                'name_en' => 'Fresh Juices & Smoothies',
                'name_am' => 'ትኩስ ጭማቂዎች',
                'icon' => 'glass',
                'display_order' => 4,
            ],
            [
                'slug' => 'pastries-desserts',
                'name_en' => 'Pastries & Desserts',
                'name_am' => 'ኬኮች እና ጣፋጮች',
                'icon' => 'cake',
                'display_order' => 5,
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $c) {
            $categories[$c['slug']] = Category::create([
                'branch_id' => $branch->id,
                'slug' => $c['slug'],
                'name_en' => $c['name_en'],
                'name_am' => $c['name_am'],
                'icon' => $c['icon'],
                'display_order' => $c['display_order'],
                'is_active' => true,
            ]);
        }

        // 11. Products & Recipes
        $productsData = [
            // Coffee
            [
                'cat' => 'coffee-hot-drinks',
                'price' => 40.00,
                'prep' => 7,
                'fasting' => true,
                'name_en' => 'Traditional Jebena Coffee',
                'name_am' => 'የጀበና ቡና በጤና አዳም',
                'desc_en' => 'Slow-brewed organic highland coffee served in a traditional clay pot (ጀበና) with fresh rue herb.',
                'desc_am' => 'በባህላዊ የሸክላ ጀበና የተፈላ ልዩ የሀበሻ ቡና ከጤና አዳም ጋር።',
                'image' => '/images/jebena_coffee.jpg',
                'recipe' => [
                    ['ing' => 'Organic Highland Coffee Beans', 'qty' => 0.025], // 25g
                    ['ing' => 'Refined Sugar', 'qty' => 0.015],
                ],
            ],
            [
                'cat' => 'coffee-hot-drinks',
                'price' => 55.00,
                'prep' => 5,
                'fasting' => false,
                'name_en' => 'Special Ethiopian Macchiato',
                'name_am' => 'ልዩ የሀበሻ ማኪያቶ',
                'desc_en' => 'Rich espresso layered over steamed creamy milk with delicate froth.',
                'desc_am' => 'በደመቀ ኤስፕሬሶ እና በሞቀ ክሬሚ ወተት የተዘጋጀ ተወዳጅ ማኪያቶ።',
                'image' => '/images/macchiato.jpg',
                'recipe' => [
                    ['ing' => 'Organic Highland Coffee Beans', 'qty' => 0.020],
                    ['ing' => 'Fresh Pasteurized Milk', 'qty' => 0.150], // 150ml
                ],
            ],

            // Traditional
            [
                'cat' => 'traditional-dishes',
                'price' => 380.00,
                'prep' => 15,
                'fasting' => false,
                'name_en' => 'Special Beef Tibs with Injera',
                'name_am' => 'ልዩ የበሬ ጥብስ በእንጀራ',
                'desc_en' => 'Tender cubes of prime beef sautéed with onions, rosemary, garlic, and sliced green peppers.',
                'desc_am' => 'ከተመረጠ ለስላሳ የበሬ ሥጋ፣ ቀይ ሽንኩርት፣ ቃሪያ እና ቅመማ ቅመም ጋር በልዩ ጣዕም የተጠበሰ ጥብስ።',
                'image' => '/images/beef_tibs.jpg',
                'recipe' => [
                    ['ing' => 'Prime Fresh Beef', 'qty' => 0.350], // 350g
                    ['ing' => 'Pure Vegetable Cooking Oil', 'qty' => 0.050],
                ],
            ],
            [
                'cat' => 'traditional-dishes',
                'price' => 180.00,
                'prep' => 12,
                'fasting' => true,
                'name_en' => 'Shiro Tegamino (Fasting Special)',
                'name_am' => 'የተጋሚኖ ሽሮ (የጾም)',
                'desc_en' => 'Authentic spiced chickpea stew simmering in a traditional clay dish, served boiling hot with fresh injera.',
                'desc_am' => 'በሸክላ ድስት ፈልቶ የሚቀርብ ጣፋጭ የጾም የተጋሚኖ ሽሮ ከትኩስ እንጀራ ጋር።',
                'image' => '/images/shiro.jpg',
                'recipe' => [
                    ['ing' => 'Authentic Shiro Powder', 'qty' => 0.120],
                    ['ing' => 'Pure Vegetable Cooking Oil', 'qty' => 0.040],
                ],
            ],

            // Fast Food & Pizza
            [
                'cat' => 'pizzas-fast-food',
                'price' => 420.00,
                'prep' => 20,
                'fasting' => false,
                'name_en' => 'Fkr Special Pizza',
                'name_am' => 'ልዩ የፍቅር ፒዛ',
                'desc_en' => 'Hand-tossed crust topped with spiced tomato sauce, double mozzarella, seasoned beef, and olives.',
                'desc_am' => 'በሞዛሬላ አይብ፣ ለስላሳ የበሬ ስጋ፣ የወይራ ፍሬ እና ልዩ የቲማቲም ሶስ የተዘጋጀ ባለሙሉ ጣዕም ፒዛ።',
                'image' => '/images/fkr_special_pizza.jpg',
                'recipe' => [
                    ['ing' => 'Special White Wheat Flour', 'qty' => 0.400],
                    ['ing' => 'Fresh Mozzarella Cheese', 'qty' => 0.250],
                    ['ing' => 'Prime Fresh Beef', 'qty' => 0.150],
                    ['ing' => 'Pure Vegetable Cooking Oil', 'qty' => 0.030],
                ],
            ],
            [
                'cat' => 'pizzas-fast-food',
                'price' => 320.00,
                'prep' => 15,
                'fasting' => false,
                'name_en' => 'Gourmet Beef Burger with Crispy Fries',
                'name_am' => 'ልዩ የበሬ በርገር ከድንች ጥብስ ጋር',
                'desc_en' => 'Juicy handmade beef patty with fresh lettuce, tomato, caramelized onion, and house dressing.',
                'desc_am' => 'በጥራት ከተዘጋጀ የበሬ ፓቲ፣ ሰላጣ፣ ቲማቲም እና ትኩስ የድንች ጥብስ ጋር የቀረበ።',
                'image' => '/images/beef_burger.jpg',
                'recipe' => [
                    ['ing' => 'Prime Fresh Beef', 'qty' => 0.200],
                    ['ing' => 'Special White Wheat Flour', 'qty' => 0.100],
                    ['ing' => 'Pure Vegetable Cooking Oil', 'qty' => 0.080],
                ],
            ],

            // Fresh Juices
            [
                'cat' => 'fresh-juices',
                'price' => 120.00,
                'prep' => 8,
                'fasting' => true,
                'name_en' => 'Special Spris Layered Juice',
                'name_am' => 'ልዩ ስፕሪስ ጭማቂ (አቮካዶ እና ማንጎ)',
                'desc_en' => 'Freshly pressed organic Avocado and Sweet Mango layered with fresh lime squeeze.',
                'desc_am' => 'ከትኩስ አቮካዶ እና ማንጎ በደረጃ ተደርድሮ በሎሚ የታጀበ ምርጥ ጭማቂ።',
                'image' => '/images/spris_juice.jpg',
                'recipe' => [
                    ['ing' => 'Fresh Ripe Hass Avocados', 'qty' => 0.250],
                    ['ing' => 'Sweet Mangoes', 'qty' => 0.250],
                ],
            ],

            // Pastry
            [
                'cat' => 'pastries-desserts',
                'price' => 95.00,
                'prep' => 3,
                'fasting' => false,
                'name_en' => 'Black Forest Chocolate Pastry',
                'name_am' => 'ጥቁር ደን የቸኮሌት ኬክ',
                'desc_en' => 'Decadent chocolate sponge layered with whipped cream and cherries.',
                'desc_am' => 'በቸኮሌት፣ ክሬም እና ቼሪ የተዘጋጀ እጅግ የሚጣፍጥ ኬክ።',
                'image' => '/images/black_forest.jpg',
                'recipe' => [
                    ['ing' => 'Special White Wheat Flour', 'qty' => 0.100],
                    ['ing' => 'Fresh Pasteurized Milk', 'qty' => 0.080],
                    ['ing' => 'Refined Sugar', 'qty' => 0.050],
                ],
            ],
        ];

        foreach ($productsData as $pData) {
            $product = Product::create([
                'branch_id' => $branch->id,
                'category_id' => $categories[$pData['cat']]->id,
                'price' => $pData['price'],
                'cost_price' => $pData['price'] * 0.40,
                'preparation_time_minutes' => $pData['prep'],
                'image_url' => $pData['image'],
                'is_available' => true,
                'is_featured' => true,
                'is_fasting' => $pData['fasting'],
            ]);

            ProductTranslation::create([
                'product_id' => $product->id,
                'locale' => 'en',
                'name' => $pData['name_en'],
                'description' => $pData['desc_en'],
            ]);

            ProductTranslation::create([
                'product_id' => $product->id,
                'locale' => 'am',
                'name' => $pData['name_am'],
                'description' => $pData['desc_am'],
            ]);

            // Create Recipe and Recipe Items
            $recipe = Recipe::create([
                'product_id' => $product->id,
                'recipe_name' => "Recipe for {$pData['name_en']}",
                'yield_quantity' => 1,
            ]);

            foreach ($pData['recipe'] as $rItem) {
                if (isset($ingredients[$rItem['ing']])) {
                    RecipeItem::create([
                        'recipe_id' => $recipe->id,
                        'ingredient_id' => $ingredients[$rItem['ing']]->id,
                        'quantity_required' => $rItem['qty'],
                    ]);
                }
            }
        }

        // 12. Suppliers
        Supplier::create([
            'branch_id' => $branch->id,
            'name' => 'Kombolcha Highland Coffee & Farmers Union',
            'contact_person' => 'Ato Alemayehu',
            'phone' => '+251 91 144 5566',
            'city' => 'Kombolcha',
            'address' => 'Union Warehouse, Market Zone',
        ]);
        Supplier::create([
            'branch_id' => $branch->id,
            'name' => 'Wollo Fresh Dairy & Meat Distributors',
            'contact_person' => 'Ato Solomon',
            'phone' => '+251 91 188 9900',
            'city' => 'Kombolcha',
            'address' => 'Industrial Zone Bypass',
        ]);

        // 13. Expense Categories
        ExpenseCategory::create(['branch_id' => $branch->id, 'name_en' => 'Building Rent', 'name_am' => 'የቤት ኪራይ', 'code' => 'RENT']);
        ExpenseCategory::create(['branch_id' => $branch->id, 'name_en' => 'Electricity & Water', 'name_am' => 'መብራት እና ውሃ', 'code' => 'UTILITIES']);
        ExpenseCategory::create(['branch_id' => $branch->id, 'name_en' => 'Internet & Telebirr Fee', 'name_am' => 'ኢንተርኔት እና የቴሌ አገልግሎት', 'code' => 'INTERNET']);
        ExpenseCategory::create(['branch_id' => $branch->id, 'name_en' => 'Equipment Maintenance', 'name_am' => 'የእቃዎች ጥገና', 'code' => 'MAINTENANCE']);
    }
}
