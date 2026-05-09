<?php
// Set session settings BEFORE starting session (must be at the very top, before any output)
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);
session_start();
$is_admin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اكتشف السعودية - الرئيسية</title>
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

    <main>
        <section class="hero">
            <h1>أهلاً بك <span>👋</span></h1>
            <h2>موقع ثقافي تفاعلي للتعريف بالمملكة</h2>
            <p>استكشف مناطق المملكة العربية السعودية وتعرف على أهم المعالم التاريخية والثقافية. اختر منطقة من المعرض للانتقال إلى صفحة التفاصيل.</p>
            <a href="gallery.php"><button>ابدأ الاستكشاف</button></a>
        </section>

        <section class="home-showcase" aria-label="صورة من معالم السعودية">
            <img src="img/riyadh.jpg" alt="صورة بانورامية لمدينة الرياض" class="home-showcase-image">
        </section>

        <section class="intro">
            <h3>مقدمة عن السعودية</h3>
            <p>المملكة العربية السعودية تقع في قلب شبه الجزيرة العربية، وهي أكبر دولة في الشرق الأوسط من حيث المساحة. تتميز السعودية بتاريخها العريق وتراثها الإسلامي العميق، إضافة إلى تنوعها الجغرافي بين الصحراء الشاسعة، الجبال الشاهقة، والسواحل البحرية الجميلة على البحر الأحمر والخليج العربي.</p>
            <ul>
                <li>تعرّف على أهم المعالم التاريخية والثقافية في المملكة.</li>
                <li>استكشف المناطق السياحية والطبيعية والأثرية في كل إقليم.</li>
                <li>اكتشف كيف تجمع السعودية بين التراث القديم والنهضة الحديثة.</li>
                <li>استخدم الموقع للتنقل بين الأماكن مع تفاصيل وصور واضحة.</li>
            </ul>
        </section>
    </main>

    <script src="script.js"></script>
</body>
</html>