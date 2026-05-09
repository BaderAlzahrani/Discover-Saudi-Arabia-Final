<?php
// Set session settings BEFORE starting session
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);
session_start();

require 'db.php';
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

$stmt = $pdo->query("SELECT * FROM regions");
$regions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معرض المناطق - اكتشف السعودية</title>
    <link rel="stylesheet" href="styles_test.css">
</head>
<body>
    <header>
        <div class="logo">اكتشف السعودية</div>
        <nav>
            <a href="index.php">الرئيسية</a>
            <a href="gallery.php">معرض المناطق</a>
            <?php if ($is_admin): ?>
                <a href="admin_dashboard.php">لوحة التحكم</a>
                <a href="logout.php">تسجيل الخروج</a>
            <?php else: ?>
                <a href="admin_login.php">دخول المشرف</a>
            <?php endif; ?>
            <button onclick="toggleNightMode()">الوضع الليلي 🌙</button>
        </nav>
    </header>

    <div class="container">
        <h2>معرض المناطق</h2>
        <div class="filter-sec">
            <label for="regionFilter">تصفية حسب المنطقة: </label>
            <select id="regionFilter" onchange="filterRegions()">
                <option value="all">الكل</option>
                <option value="وسطى">وسطى</option>
                <option value="غربية">غربية</option>
                <option value="شرقية">شرقية</option>
                <option value="جنوبية">جنوبية</option>
                <option value="شمالية">شمالية</option>
            </select>
        </div>

        <div class="gallery" id="galleryContainer">
            <?php foreach ($regions as $region): ?>
                <div class="card" data-category="<?php echo htmlspecialchars($region['category']); ?>" onclick="window.location.href='details.php?id=<?php echo $region['id']; ?>'">
                    <img src="img/<?php echo htmlspecialchars($region['main_image']); ?>" alt="صورة <?php echo htmlspecialchars($region['name']); ?>">
                    <h3><?php echo htmlspecialchars($region['name']); ?></h3>
                    <p><?php echo htmlspecialchars($region['category']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <script src="script.js"></script>
</body>
</html>