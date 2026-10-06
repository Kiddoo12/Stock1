<?php
$host = '127.0.0.1';
$dbname = 'import_goods_db';
$username = 'root';
$password = '123456789';
$success_msg = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // បង្កើតតារាង orders ស្វ័យប្រវត្តិប្រសិនបើទើបប្រើប្រាស់ដំបូង
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        order_id INT AUTO_INCREMENT PRIMARY KEY,
        customer_name VARCHAR(255) NOT NULL,
        order_item VARCHAR(255) NOT NULL,
        quantity INT NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// គ្រប់គ្រងការបញ្ចូលការបញ្ជាទិញថ្មី
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = $_POST['customer_name'] ?? '';
    $order_item = $_POST['order_item'] ?? '';
    $quantity = $_POST['quantity'] ?? 1;
    $total_price = $_POST['total_price'] ?? 0;

    if(!empty($customer_name) && !empty($order_item)) {
        $stmt = $pdo->prepare("INSERT INTO orders (customer_name, order_item, quantity, total_price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$customer_name, $order_item, $quantity, $total_price]);
        $success_msg = "បានបង្កើតការបញ្ជាទិញថ្មីដោយជោគជ័យ!";
        header("Refresh: 1; url=orders.php");
    }
}

// មុខងារលុបការបញ្ជាទិញ
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM orders WHERE order_id=?");
    $stmt->execute([$id]);
    header("Location: orders.php");
    exit();
}

$orders = $pdo->query("SELECT * FROM orders ORDER BY order_id DESC")->fetchAll(PDO::FETCH_ASSOC);
$total_orders = count($orders);
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>គ្រប់គ្រងការបញ្ជាទិញ - Orders</title>
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
                <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-sm">🛒</div>
                <div>
                    <h1 class="font-bold text-slate-900 text-sm leading-tight">Import System</h1>
                    <span class="text-[11px] text-slate-400">គ្រប់គ្រងការបញ្ជាទិញ</span>
                </div>
            </div>
            <nav class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-medium">
                <a href="db.php" class="text-slate-600 px-3 py-1.5 hover:text-slate-900">ទូទៅ</a>
                <a href="products.php" class="text-slate-600 px-3 py-1.5 hover:text-slate-900">ទំនិញ</a>
                <a href="orders.php" class="bg-white text-blue-600 px-3 py-1.5 rounded-lg shadow-xs">បញ្ជាទិញ</a>
            </nav>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="max-w-xl mx-auto px-4 py-6 w-full flex-1 space-y-6">

        <?php if(!empty($success_msg)): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2 shadow-xs">
            <span>✅</span> <?= $success_msg ?>
        </div>
        <?php endif; ?>

        <!-- Statistics Widget -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 text-white p-4 rounded-2xl shadow-sm">
                <p class="text-xs text-blue-100 font-medium">ការបញ្ជាទិញសរុប</p>
                <h3 class="text-2xl font-bold mt-1"><?= $total_orders ?> <span class="text-xs font-normal">កម្មង់</span></h3>
            </div>
            <div class="bg-white border border-slate-200 p-4 rounded-2xl shadow-xs flex flex-col justify-between">
                <p class="text-xs text-slate-400 font-medium">ស្ថានភាពប្រព័ន្ធ</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse"></span>
                    <span class="text-xs font-semibold text-slate-700">ដំណើរការធម្មតា</span>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-100">
                <h2 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <span>✨</span>
                    <span>បង្កើតការបញ្ជាទិញថ្មី</span>
                </h2>
            </div>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">ឈ្មោះអតិថិជន <span class="text-rose-500">*</span></label>
                    <input type="text" name="customer_name" required 
                           placeholder="បញ្ចូលឈ្មោះអតិថិជន..."
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">មុខទំនិញបញ្ជាទិញ <span class="text-rose-500">*</span></label>
                    <input type="text" name="order_item" required 
                           placeholder="ឧ. ខ្សែកាប់ពីតាល, ថ្ម Dzi..."
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">ចំនួន (Qty) <span class="text-rose-500">*</span></label>
                        <input type="number" name="quantity" value="1" min="1" required 
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">តម្លៃសរុប ($) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" name="total_price" required 
                               placeholder="0.00"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium py-3.5 rounded-xl shadow-md shadow-blue-500/20 transition text-sm">
                    រក្សាទុកការបញ្ជាទិញ
                </button>
            </form>
        </div>

        <!-- Orders List Section -->
        <div class="space-y-3">
            <div class="flex justify-between items-center px-1">
                <h3 class="font-bold text-slate-800 text-sm">បញ្ជីការបញ្ជាទិញទាំងអស់</h3>
                <span class="text-xs text-slate-400 font-medium"><?= $total_orders ?> កម្មង់</span>
            </div>

            <div class="space-y-2.5">
                <?php if(empty($orders)): ?>
                    <div class="bg-white p-8 rounded-2xl text-center border border-slate-200 text-slate-400 text-sm">
                        មិនទាន់មានការបញ្ជាទិញនៅឡើយទេ
                    </div>
                <?php else: ?>
                    <?php foreach($orders as $o): ?>
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between gap-3 hover:border-blue-200 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-12 h-12 bg-blue-50 text-blue-600 font-bold rounded-xl border border-blue-100 flex items-center justify-center shrink-0 text-sm">
                                🛒
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-slate-900 text-sm truncate"><?= htmlspecialchars($o['customer_name']) ?></h4>
                                <p class="text-[11px] text-slate-500 mt-0.5 truncate">ទំនិញ៖ <?= htmlspecialchars($o['order_item']) ?> (ចំនួន: <?= $o['quantity'] ?>)</p>
                                <span class="inline-block mt-1 text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">
                                    $<?= number_format($o['total_price'], 2) ?>
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <a href="orders.php?delete=<?= $o['order_id'] ?>" onclick="return confirm('តើអ្នកពិតជាចង់លុបការបញ្ជាទិញនេះមែនទេ?')" class="p-2 bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-600 rounded-xl text-xs transition" title="លុប">
                                🗑
                            </a>
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