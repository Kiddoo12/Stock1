<?php
$host = '127.0.0.1';
$dbname = 'import_goods_db';
$username = 'root';
$password = '123456789';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ទាញយកទិន្នន័យស្ថិតិ និងទំនិញចុងក្រោយ
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?? 0;
$recent_products = $pdo->query("SELECT * FROM products ORDER BY product_id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ផ្ទាំងគ្រប់គ្រងទូទៅ - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Kantumruy Pro', sans-serif; } 
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col justify-between">

    <!-- Top Header / Navigation -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-xs">
        <div class="max-w-xl mx-auto px-4 py-3.5 flex justify-between items-center">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-sm">📊</div>
                <div>
                    <h1 class="font-bold text-slate-900 text-sm leading-tight">Import System</h1>
                    <span class="text-[11px] text-slate-400">ផ្ទាំងគ្រប់គ្រងទូទៅ</span>
                </div>
            </div>
            <nav class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-medium">
                <a href="index.php" class="bg-white text-blue-600 px-3 py-1.5 rounded-lg shadow-xs">ទិដ្ឋភាពទូទៅ</a>
                <a href="products.php" class="text-slate-600 px-3 py-1.5 hover:text-slate-900">ទំនិញ</a>
                <a href="orders.php" class="text-slate-600 px-3 py-1.5 hover:text-slate-900">បញ្ជាទិញ</a>
            </nav>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="max-w-xl mx-auto px-4 py-6 w-full flex-1 space-y-6">

        <!-- Welcome Banner -->
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white p-5 rounded-2xl shadow-sm">
            <h2 class="text-lg font-bold">សួស្តី, អ្នកគ្រប់គ្រង! 👋</h2>
            <p class="text-xs text-blue-100 mt-1 leading-relaxed">សូមស្វាគមន៍មកកាន់ប្រព័ន្ធគ្រប់គ្រងការនាំចូលទំនិញ និងស្តុករបស់អ្នក។</p>
        </div>

        <!-- Statistics Grid -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white border border-slate-200 p-4 rounded-2xl shadow-xs flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <p class="text-xs text-slate-400 font-medium">ទំនិញសរុប</p>
                    <span class="p-2 bg-blue-50 text-blue-600 rounded-xl text-xs">📦</span>
                </div>
                <h3 class="text-2xl font-bold text-slate-900 mt-3"><?= $total_products ?> <span class="text-xs font-normal text-slate-500">មុខ</span></h3>
            </div>
            <div class="bg-white border border-slate-200 p-4 rounded-2xl shadow-xs flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <p class="text-xs text-slate-400 font-medium">ការបញ្ជាទិញ</p>
                    <span class="p-2 bg-amber-50 text-amber-600 rounded-xl text-xs">🛒</span>
                </div>
                <h3 class="text-2xl font-bold text-slate-900 mt-3"><?= $total_orders ?> <span class="text-xs font-normal text-slate-500">កម្មង់</span></h3>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3">
            <h3 class="font-bold text-slate-900 text-sm">សកម្មភាពរហ័ស</h3>
            <div class="grid grid-cols-2 gap-2.5">
                <a href="products.php" class="flex items-center gap-3 p-3 bg-slate-50 hover:bg-blue-50 hover:border-blue-200 border border-slate-200 rounded-xl transition text-xs font-semibold text-slate-700">
                    <span class="text-base">➕</span> បន្ថែមទំនិញថ្មី
                </a>
                <a href="orders.php" class="flex items-center gap-3 p-3 bg-slate-50 hover:bg-blue-50 hover:border-blue-200 border border-slate-200 rounded-xl transition text-xs font-semibold text-slate-700">
                    <span class="text-base">📝</span> រៀបចំបញ្ជាទិញ
                </a>
            </div>
        </div>

        <!-- Recent Products Section (Updated to match Products card layout) -->
        <div class="space-y-3">
            <div class="flex justify-between items-center px-1">
                <h3 class="font-bold text-slate-800 text-sm">ទំនិញទើបបញ្ចូលថ្មីៗ</h3>
                <a href="products.php" class="text-xs text-blue-600 font-medium hover:underline">មើលទាំងអស់ →</a>
            </div>

            <div class="space-y-2.5">
                <?php if(empty($recent_products)): ?>
                    <div class="bg-white p-6 rounded-2xl text-center border border-slate-200 text-slate-400 text-xs">គ្មានទំនិញ</div>
                <?php else: ?>
                    <?php foreach($recent_products as $p): 
                        // គណនាតម្លៃសរុប (តម្លៃដើម + ពន្ធ + សេវាដឹក)
                        $base_price = $p['unit_price_foreign'];
                        $tax_amount = $p['tax_rate'];
                        $delivery = $p['delivery_fee'] ?? 0;
                        $grand_total = $base_price + $tax_amount + $delivery;
                    ?>
                    <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between gap-3 hover:border-blue-200 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <?php if(!empty($p['product_image']) && file_exists('uploads/' . $p['product_image'])): ?>
                                <img src="uploads/<?= $p['product_image'] ?>" class="w-16 h-16 object-cover rounded-xl border border-slate-100 shrink-0">
                            <?php else: ?>
                                <div class="w-16 h-16 bg-slate-100 rounded-xl border border-slate-100 flex items-center justify-center text-[11px] text-slate-400 shrink-0 font-medium">គ្មានរូប</div>
                            <?php endif; ?>
                            <div class="min-w-0">
                                <h4 class="font-bold text-slate-900 text-sm truncate"><?= htmlspecialchars($p['product_name']) ?></h4>
                                <p class="text-[11px] text-slate-400 mt-0.5 truncate">ប្រភេទ៖ <?= htmlspecialchars($p['category'] ?: 'ទូទៅ') ?></p>
                                
                                <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                    <span class="text-xs font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">
                                        តម្លៃ: <?= number_format($base_price, 2) ?> <?= $p['currency'] ?>
                                    </span>
                                    <?php if(($p['tax_rate'] ?? 0) > 0): ?>
                                    <span class="text-[10px] text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded">ពន្ធ: <?= $p['tax_rate'] ?>$</span>
                                    <?php endif; ?>
                                    <?php if(($p['delivery_fee'] ?? 0) > 0): ?>
                                    <span class="text-[10px] text-purple-600 bg-purple-50 px-1.5 py-0.5 rounded">ដឹក: <?= $p['delivery_fee'] ?>$</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-right shrink-0">
                            <span class="text-[10px] text-slate-400 block">សរុបទាំងអស់</span>
                            <span class="text-xs font-bold text-emerald-600"><?= number_format($grand_total, 2) ?> <?= $p['currency'] ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="text-center py-6 text-xs text-slate-400 border-t border-slate-200 mt-8">
        Import Management System &copy; 2026
    </footer>

</body>
</html>