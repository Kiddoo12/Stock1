<?php
$host = '127.0.0.1';
$dbname = 'import_goods_db';
$username = 'root';
$password = '123456789';
$success_msg = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // បន្ថែម Column tax_rate និង delivery_fee ស្វ័យប្រវត្តិប្រសិនបើទើបបង្កើត database
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        product_id INT AUTO_INCREMENT PRIMARY KEY,
        product_name VARCHAR(255) NOT NULL,
        category VARCHAR(100),
        product_image VARCHAR(255),
        unit_price_foreign DECIMAL(10,2) NOT NULL,
        currency VARCHAR(10) DEFAULT 'USD',
        tax_rate DECIMAL(5,2) DEFAULT 0.00,
        delivery_fee DECIMAL(10,2) DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// គ្រប់គ្រងការ Insert / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['product_name'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = $_POST['unit_price_foreign'] ?? 0;
    $currency = $_POST['currency'] ?? 'USD';
    $tax_rate = $_POST['tax_rate'] ?? 0;
    $delivery_fee = $_POST['delivery_fee'] ?? 0;
    $product_id = $_POST['product_id'] ?? '';

    $old_image = '';
    if (!empty($product_id)) {
        $stmt_old = $pdo->prepare("SELECT product_image FROM products WHERE product_id = ?");
        $stmt_old->execute([$product_id]);
        $old_image = $stmt_old->fetchColumn();
    }

    $image_name = $old_image; 

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
        $file_tmp = $_FILES['product_image']['tmp_name'];
        $original_name = $_FILES['product_image']['name'];
        $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($file_ext, $allowed_extensions)) {
            $image_name = 'prod_' . time() . '.' . $file_ext;
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            $upload_path = 'uploads/' . $image_name;

            if (move_uploaded_file($file_tmp, $upload_path)) {
                if (!empty($old_image) && file_exists('uploads/' . $old_image)) {
                    unlink('uploads/' . $old_image);
                }
            }
        }
    }

    if (!empty($product_id)) {
        $stmt = $pdo->prepare("UPDATE products SET product_name=?, category=?, product_image=?, unit_price_foreign=?, currency=?, tax_rate=?, delivery_fee=? WHERE product_id=?");
        $stmt->execute([$name, $category, $image_name, $price, $currency, $tax_rate, $delivery_fee, $product_id]);
        $success_msg = "កែប្រែទំនិញបានដោយជោគជ័យ!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (product_name, category, product_image, unit_price_foreign, currency, tax_rate, delivery_fee) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $image_name, $price, $currency, $tax_rate, $delivery_fee]);
        $success_msg = "បានបន្ថែមទំនិញថ្មីដោយជោគជ័យ!";
    }
    
    // Refresh ទៅកាន់ទំព័រដើមដើម្បីលុប Form State
    header("Refresh: 1; url=products.php");
}

// មុខងារលុប
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt_img = $pdo->prepare("SELECT product_image FROM products WHERE product_id = ?");
    $stmt_img->execute([$id]);
    $img = $stmt_img->fetchColumn();
    if (!empty($img) && file_exists('uploads/' . $img)) {
        unlink('uploads/' . $img);
    }

    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id=?");
    $stmt->execute([$id]);
    header("Location: products.php");
    exit();
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

$products = $pdo->query("SELECT * FROM products ORDER BY product_id DESC")->fetchAll(PDO::FETCH_ASSOC);
$total_products = count($products);
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ប្រព័ន្ធគ្រប់គ្រងទំនិញ</title>
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
                <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-sm">📦</div>
                <div>
                    <h1 class="font-bold text-slate-900 text-sm leading-tight">Import System</h1>
                    <span class="text-[11px] text-slate-400">គ្រប់គ្រងស្តុកទំនិញ</span>
                </div>
            </div>
            <nav class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-medium">
                <a href="db.php" class="text-slate-600 px-3 py-1.5 hover:text-slate-900">ទូទៅ</a>
                <a href="products.php" class="bg-white text-blue-600 px-3 py-1.5 rounded-lg shadow-xs">ទំនិញ</a>
                <a href="orders.php" class="text-slate-600 px-3 py-1.5 hover:text-slate-900">បញ្ជាទិញ</a>
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
                <p class="text-xs text-blue-100 font-medium">ទំនិញសរុបក្នុងស្តុក</p>
                <h3 class="text-2xl font-bold mt-1"><?= $total_products ?> <span class="text-xs font-normal">មុខ</span></h3>
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
                    <span><?= $edit_data ? '✏️' : '✨' ?></span>
                    <span><?= $edit_data ? 'កែប្រែព័ត៌មានទំនិញ' : 'បន្ថែមទំនិញថ្មី' ?></span>
                </h2>
                <?php if($edit_data): ?>
                    <a href="products.php" class="text-xs bg-rose-50 text-rose-600 px-2.5 py-1 rounded-lg font-medium">បោះបង់</a>
                <?php endif; ?>
            </div>

            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="product_id" value="<?= $edit_data['product_id'] ?? '' ?>">
                
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">ឈ្មោះទំនិញ <span class="text-rose-500">*</span></label>
                    <input type="text" name="product_name" value="<?= htmlspecialchars($edit_data['product_name'] ?? '') ?>" required 
                           placeholder="បញ្ចូលឈ្មោះទំនិញ..."
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">ប្រភេទ (Category)</label>
                        <input type="text" name="category" value="<?= htmlspecialchars($edit_data['category'] ?? '') ?>" 
                               placeholder="ឧ. គ្រឿងអលង្ការ"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">រូបិយប័ណ្ណ</label>
                        <select name="currency" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                            <option value="USD" <?= (isset($edit_data) && $edit_data['currency']=='USD')?'selected':'' ?>>USD ($)</option>
                            <option value="CNY" <?= (isset($edit_data) && $edit_data['currency']=='CNY')?'selected':'' ?>>CNY (¥)</option>
                            <option value="THB" <?= (isset($edit_data) && $edit_data['currency']=='THB')?'selected':'' ?>>THB (฿)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">តម្លៃដើម (Unit Price) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="unit_price_foreign" value="<?= $edit_data['unit_price_foreign'] ?? '' ?>" required 
                           placeholder="0.00"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>

                <!-- ផ្នែកបន្ថែម Tax និង Delivery -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">ពន្ធ (%) / Tax Rate</label>
                        <input type="number" step="0.01" name="tax_rate" value="<?= $edit_data['tax_rate'] ?? '' ?>" 
                               placeholder="0.00"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">ថ្លៃដឹកជញ្ជូន / Delivery</label>
                        <input type="number" step="0.01" name="delivery_fee" value="<?= $edit_data['delivery_fee'] ?? '' ?>" 
                               placeholder="0.00"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-3 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">រូបភាពទំនិញ</label>
                    <input type="file" name="product_image" accept="image/*" 
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100 bg-slate-50 border border-slate-200 rounded-xl">
                    <?php if(!empty($edit_data['product_image'])): ?>
                        <div class="mt-2.5 flex items-center gap-2.5 bg-slate-50 p-2 rounded-xl border border-slate-200">
                            <img src="uploads/<?= $edit_data['product_image'] ?>" class="w-10 h-10 object-cover rounded-lg">
                            <span class="text-xs text-slate-500 truncate">រូបបច្ចុប្បន្ន: <?= $edit_data['product_image'] ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium py-3.5 rounded-xl shadow-md shadow-blue-500/20 transition text-sm">
                    <?= $edit_data ? 'រក្សាទុកការកែប្រែ' : 'បញ្ចូលទំនិញថ្មី' ?>
                </button>
            </form>
        </div>

        <!-- Product List Section -->
        <div class="space-y-3">
            <div class="flex justify-between items-center px-1">
                <h3 class="font-bold text-slate-800 text-sm">បញ្ជីទំនិញទាំងអស់</h3>
                <span class="text-xs text-slate-400 font-medium"><?= $total_products ?> មុខ</span>
            </div>

            <div class="space-y-2.5">
                <?php if(empty($products)): ?>
                    <div class="bg-white p-8 rounded-2xl text-center border border-slate-200 text-slate-400 text-sm">
                        មិនទាន់មានទំនិញនៅឡើយទេ
                    </div>
                <?php else: ?>
                    <?php foreach($products as $p): 
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
                        
                        <div class="flex flex-col items-end gap-2 shrink-0">
                            <div class="text-right">
                                <span class="text-[10px] text-slate-400 block">សរុបទាំងអស់</span>
                                <span class="text-xs font-bold text-emerald-600"><?= number_format($grand_total, 2) ?> <?= $p['currency'] ?></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <a href="products.php?edit=<?= $p['product_id'] ?>" class="p-1.5 bg-slate-100 hover:bg-blue-50 hover:text-blue-600 text-slate-600 rounded-lg text-xs transition" title="កែប្រែ">
                                    ✏️
                                </a>
                                <a href="products.php?delete=<?= $p['product_id'] ?>" onclick="return confirm('តើអ្នកពិតជាចង់លុបទំនិញនេះមែនទេ?')" class="p-1.5 bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-600 rounded-lg text-xs transition" title="លុប">
                                    🗑
                                </a>
                            </div>
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