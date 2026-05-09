<?php
// Set session settings BEFORE starting session
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: admin_login.php"); exit(); }
require 'db.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM regions WHERE id = ?");
$stmt->execute([$id]);
$region = $stmt->fetch();

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

    foreach (['main_image', 'g1', 'g2', 'g3'] as $field) {
        if (!empty($_FILES[$field]['name'])) {
            if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'حدث خطأ أثناء رفع ' . $field . '. يرجى المحاولة مرة أخرى.';
            } else {
                $mime = $finfo->file($_FILES[$field]['tmp_name']);
                if (!in_array($mime, $allowed_types)) {
                    $errors[] = 'الملف ' . $field . ' يجب أن يكون صورة صالحاً (jpg, png, gif, webp).';
                }
            }
        }
    }

    $main_img = $region['main_image'];
    $g1 = $region['gallery_img1'];
    $g2 = $region['gallery_img2'];
    $g3 = $region['gallery_img3'];

    if (empty($errors)) {
        if (!empty($_FILES['main_image']['name'])) {
            $main_img = makeSafeFilename($_FILES['main_image']['name']);
            if (!move_uploaded_file($_FILES['main_image']['tmp_name'], "img/" . $main_img)) {
                $errors[] = 'فشل رفع الصورة الرئيسية.';
            }
        }

        if (!empty($_FILES['g1']['name'])) {
            $g1 = makeSafeFilename($_FILES['g1']['name']);
            if (!move_uploaded_file($_FILES['g1']['tmp_name'], "img/" . $g1)) {
                $errors[] = 'فشل رفع صورة المعرض الأولى.';
            }
        }

        if (!empty($_FILES['g2']['name'])) {
            $g2 = makeSafeFilename($_FILES['g2']['name']);
            if (!move_uploaded_file($_FILES['g2']['tmp_name'], "img/" . $g2)) {
                $errors[] = 'فشل رفع صورة المعرض الثانية.';
            }
        }

        if (!empty($_FILES['g3']['name'])) {
            $g3 = makeSafeFilename($_FILES['g3']['name']);
            if (!move_uploaded_file($_FILES['g3']['tmp_name'], "img/" . $g3)) {
                $errors[] = 'فشل رفع صورة المعرض الثالثة.';
            }
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE regions SET name=?, category=?, description=?, main_image=?, features=?, activities=?, landmarks=?, gallery_img1=?, gallery_img2=?, gallery_img3=? WHERE id=?";
        $update = $pdo->prepare($sql);
        
        if ($update->execute([$name, $category, $description, $main_img, $features, $activities, $landmarks, $g1, $g2, $g3, $id])) {
            $_SESSION['success_msg'] = "تم تحديث السجل بنجاح"; // Requirement: Display on dashboard [cite: 69-70]
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
    <title>تحديث المكان - <?php echo $region['name']; ?></title>
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
        <div class="logo">تحديث المحتوى</div>
        <nav><a href="admin_dashboard.php">العودة للوحة التحكم</a> | <a href="logout.php">تسجيل الخروج</a> <button onclick="toggleNightMode()">الوضع الليلي 🌙</button></nav>
    </header>

    <div class="form-container">
        <h2>تعديل بيانات: <?php echo $region['name']; ?></h2>
        <?php if (!empty($errors)): ?>
            <div class="alert error"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>اسم المكان</label>
                <input type="text" name="name" value="<?php echo $region['name']; ?>" required>
            </div>
            <div class="form-group">
                <label>الوصف</label>
                <textarea name="description" rows="4"><?php echo $region['description']; ?></textarea>
            </div>
            <div class="form-group">
                <label>الموقع (التصنيف)</label>
                <select name="category">
                    <option value="وسطى" <?php if($region['category']=='وسطى') echo 'selected'; ?>>وسطى</option>
                    <option value="غربية" <?php if($region['category']=='غربية') echo 'selected'; ?>>غربية</option>
                    <option value="شرقية" <?php if($region['category']=='شرقية') echo 'selected'; ?>>شرقية</option>
                    <option value="جنوبية" <?php if($region['category']=='جنوبية') echo 'selected'; ?>>جنوبية</option>
                    <option value="شمالية" <?php if($region['category']=='شمالية') echo 'selected'; ?>>شمالية</option>
                </select>
            </div>
            <div class="form-group">
                <label>تحديث الصورة الرئيسية (اختياري)</label>
                <input type="file" name="main_image" accept="image/*">
                <p><small>الحالية: <?php echo $region['main_image']; ?></small></p>
            </div>
            <div class="form-group">
                <label>المميزات</label>
                <input type="text" name="features" value="<?php echo $region['features']; ?>" placeholder="مثال: معالم طبيعية، تاريخ عريق">
            </div>
            <div class="form-group">
                <label>الأنشطة</label>
                <input type="text" name="activities" value="<?php echo $region['activities']; ?>" placeholder="مثال: جولات سياحية، تخييم">
            </div>
            <div class="form-group">
                <label>المعالم (افصل بينها بفاصلة)</label>
                <input type="text" name="landmarks" value="<?php echo $region['landmarks']; ?>" placeholder="مثال: مدائن صالح، جبل الفيل">
            </div>
            <hr>
            <h3>صور المعرض</h3>
            <div class="form-group">
                <label>صورة العرض الأولى (اختياري)</label>
                <input type="file" name="g1" accept="image/*">
                <p><small>الحالية: <?php echo $region['gallery_img1']; ?></small></p>
            </div>
            <div class="form-group">
                <label>صورة العرض الثانية (اختياري)</label>
                <input type="file" name="g2" accept="image/*">
                <p><small>الحالية: <?php echo $region['gallery_img2'] ? $region['gallery_img2'] : 'لا توجد'; ?></small></p>
            </div>
            <div class="form-group">
                <label>صورة العرض الثالثة (اختياري)</label>
                <input type="file" name="g3" accept="image/*">
                <p><small>الحالية: <?php echo $region['gallery_img3'] ? $region['gallery_img3'] : 'لا توجد'; ?></small></p>
            </div>
            <button type="submit" class="btn btn-add-new">حفظ التغييرات</button>
        </form>
    </div>
</body>
</html>