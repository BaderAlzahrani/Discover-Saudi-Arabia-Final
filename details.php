<?php
require 'db.php';
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM regions WHERE id = ?");
$stmt->execute([$id]);
$region = $stmt->fetch();


$landmark_list = explode('،', $region['landmarks']); 
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?php echo $region['name']; ?> - التفاصيل</title>
    <link rel="stylesheet" href="styles_test.css">
    <script>
        function toggleNightMode() {
            const isNight = document.body.classList.toggle('night-mode');
            localStorage.setItem('theme', isNight ? 'dark' : 'light');
        }
        window.onload = function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.classList.add('night-mode');
            }
        };
    </script>
</head>
<body>
    <header>
        <div class="logo">اكتشف السعودية</div>
        <nav><a href="index.php">الرئيسية</a> | <a href="gallery.php">المعرض</a> <button onclick="toggleNightMode()">الوضع الليلي 🌙</button></nav>
    </header>

    <main class="container">
        <img src="img/<?php echo $region['main_image']; ?>" class="detail-hero-img">
        
        <h1><?php echo $region['name']; ?></h1>
        <p class="description-text"><?php echo $region['description']; ?></p>

        <div class="info-box">
            <h3>معلومات سريعة</h3>
            <ul>
                <li><strong>الموقع:</strong> المنطقة <?php echo $region['category']; ?></li>
                <li><strong>أهم المميزات:</strong> <?php echo $region['features']; ?></li>
                <li><strong>أبرز الأنشطة:</strong> <?php echo $region['activities']; ?></li>
            </ul>
        </div>

        <div class="landmarks-section">
            <h3>أبرز المعالم</h3>
            <ul>
                <?php foreach($landmark_list as $item): ?>
                    <li><?php echo trim($item); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <h3>معرض الصور</h3>
        <div class="gallery">
            <img src="img/<?php echo $region['gallery_img1']; ?>" class="gallery-thumb">
            <?php if($region['gallery_img2']): ?> <img src="img/<?php echo $region['gallery_img2']; ?>" class="gallery-thumb"> <?php endif; ?>
            <?php if($region['gallery_img3']): ?> <img src="img/<?php echo $region['gallery_img3']; ?>" class="gallery-thumb"> <?php endif; ?>
        </div>
    </main>
</body>
</html>