<?php
// Set session settings BEFORE starting session
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);
session_start();
require 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // If not, kick them back to the login page 
    header("Location: admin_login.php");
    exit();
}

$errors = [];

function makeSafeFilename($name) {
    return uniqid() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', basename($name));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $features = $_POST['features'];
    $activities = $_POST['activities'];
    $landmarks = $_POST['landmarks'];

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);

    if (empty($_FILES['main_image']['name']) || $_FILES['main_image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'يجب تحميل الصورة الرئيسية للمكان.';
    } else {
        $mime = $finfo->file($_FILES['main_image']['tmp_name']);
        if (!in_array($mime, $allowed_types)) {
            $errors[] = 'الصورة الرئيسية يجب أن تكون ملف صورة صالح (jpg, png, gif, webp).';
        }
    }

    if (empty($_FILES['g1']['name']) || $_FILES['g1']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'يجب تحميل صورة المعرض الأولى.';
    } else {
        $mime = $finfo->file($_FILES['g1']['tmp_name']);
        if (!in_array($mime, $allowed_types)) {
            $errors[] = 'صورة المعرض الأولى يجب أن تكون ملف صورة صالح (jpg, png, gif, webp).';
        }
    }

    foreach (['g2', 'g3'] as $optionalFile) {
        if (!empty($_FILES[$optionalFile]['name'])) {
            if ($_FILES[$optionalFile]['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'حدث خطأ أثناء رفع ' . $optionalFile . '. يرجى المحاولة مرة أخرى.';
            } else {
                $mime = $finfo->file($_FILES[$optionalFile]['tmp_name']);
                if (!in_array($mime, $allowed_types)) {
                    $errors[] = 'يجب أن تكون ' . $optionalFile . ' ملف صورة صالح (jpg, png, gif, webp).';
                }
            }
        }
    }

    if (empty($errors)) {
        $main_img = makeSafeFilename($_FILES['main_image']['name']);
        $g1 = makeSafeFilename($_FILES['g1']['name']);
        $g2 = !empty($_FILES['g2']['name']) ? makeSafeFilename($_FILES['g2']['name']) : '';
        $g3 = !empty($_FILES['g3']['name']) ? makeSafeFilename($_FILES['g3']['name']) : '';

        if (!move_uploaded_file($_FILES['main_image']['tmp_name'], "img/" . $main_img)) {
            $errors[] = 'فشل رفع الصورة الرئيسية.';
        }
        if (!move_uploaded_file($_FILES['g1']['tmp_name'], "img/" . $g1)) {
            $errors[] = 'فشل رفع صورة المعرض الأولى.';
        }
        if (!empty($_FILES['g2']['name']) && !move_uploaded_file($_FILES['g2']['tmp_name'], "img/" . $g2)) {
            $errors[] = 'فشل رفع صورة المعرض الثانية.';
        }
        if (!empty($_FILES['g3']['name']) && !move_uploaded_file($_FILES['g3']['tmp_name'], "img/" . $g3)) {
            $errors[] = 'فشل رفع صورة المعرض الثالثة.';
        }
    }

    if (empty($errors)) {
        $sql = "INSERT INTO regions (name, category, description, main_image, features, activities, landmarks, gallery_img1, gallery_img2, gallery_img3) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$name, $category, $description, $main_img, $features, $activities, $landmarks, $g1, $g2, $g3])) {
            $_SESSION['success_msg'] = "تم إضافة المكان بنجاح"; 
            header("Location: admin_dashboard.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة مكان جديد</title>
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
        <div class="logo">لوحة المشرف</div>
        <nav><a href="admin_dashboard.php">العودة للوحة التحكم</a> | <a href="logout.php">تسجيل الخروج</a> <button onclick="toggleNightMode()">الوضع الليلي 🌙</button></nav>
    </header>

    <div class="form-container">
        <h2>إضافة مكان جديد</h2>
        <?php if (!empty($errors)): ?>
            <div class="alert error"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>اسم المكان</label>
                <input type="text" name="name" required placeholder="مثال: العلا">
            </div>
            <div class="form-group">
                <label>الصورة الرئيسية للمكان</label>
                <input type="file" name="main_image" accept="image/*" required>
            </div>
            <div class="form-group">
                <label>الوصف</label>
                <textarea name="description" rows="4" placeholder="اكتب وصفاً تفصيلياً..."></textarea>
            </div>
            <div class="form-group">
                <label>الموقع (التصنيف)</label>
                <select name="category">
                    <option value="وسطى">وسطى</option>
                    <option value="غربية">غربية</option>
                    <option value="شرقية">شرقية</option>
                    <option value="جنوبية">جنوبية</option>
                    <option value="شمالية">شمالية</option>
                </select>
            </div>
            <div class="form-group">
                <label>المميزات</label>
                <input type="text" name="features" placeholder="مثال: معالم طبيعية، تاريخ عريق">
            </div>
            <div class="form-group">
                <label>الأنشطة</label>
                <input type="text" name="activities" placeholder="مثال: جولات سياحية، تخييم">
            </div>
            <div class="form-group">
                <label>المعالم (افصل بينها بفاصلة)</label>
                <input type="text" name="landmarks" placeholder="مثال: مدائن صالح، جبل الفيل">
            </div>
            <hr>
            <h3>صور المعرض</h3>
            <div class="form-group">
                <label>صورة العرض الأولى</label>
                <input type="file" name="g1" accept="image/*" required>
            </div>
            <div class="form-group">
                <label>صورة العرض الثانية (اختياري)</label>
                <input type="file" name="g2" accept="image/*">
            </div>
            <div class="form-group">
                <label>صورة العرض الثالثة (اختياري)</label>
                <input type="file" name="g3" accept="image/*">
            </div>
            <button type="submit" class="btn btn-add-new">إضافة المكان</button>
        </form>
    </div>
</body>
</html>