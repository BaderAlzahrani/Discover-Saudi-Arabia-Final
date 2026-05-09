<?php
// Set session settings BEFORE starting session
ini_set('session.cookie_lifetime', 3600);
ini_set('session.gc_maxlifetime', 3600);
session_start();
require 'db.php';

// 1. Protect page using sessions [cite: 34]
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // If not, kick them back to the login page 
    header("Location: admin_login.php");
    exit();
}

$message = '';

// Helper function to reorder IDs
function reorderIDs($pdo) {
    try {
        // Get all data first
        $result = $pdo->query("SELECT * FROM regions ORDER BY id");
        $regions = $result->fetchAll(PDO::FETCH_ASSOC);
        
        // Delete all records
        $pdo->exec("DELETE FROM regions");

        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'pgsql') {
            // PostgreSQL: reset id sequence before explicit id inserts (see regions_id_seq for SERIAL PK)
            $pdo->exec("ALTER SEQUENCE regions_id_seq RESTART WITH 1");
        } else {
            // MySQL
            $pdo->exec("ALTER TABLE regions AUTO_INCREMENT = 1");
        }

        // Re-insert with new sequential IDs
        $newId = 1;
        foreach ($regions as $region) {
            $stmt = $pdo->prepare("INSERT INTO regions (id, name, category, description, main_image, features, activities, landmarks, gallery_img1, gallery_img2, gallery_img3) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $newId,
                $region['name'],
                $region['category'],
                $region['description'],
                $region['main_image'],
                $region['features'],
                $region['activities'],
                $region['landmarks'],
                $region['gallery_img1'],
                $region['gallery_img2'],
                $region['gallery_img3']
            ]);
            $newId++;
        }

        if ($driver === 'pgsql') {
            // Keep SERIAL in sync after explicit IDs (otherwise the next INSERT may fail)
            $pdo->exec(
                "SELECT setval(pg_get_serial_sequence('regions', 'id'), COALESCE((SELECT MAX(id) FROM regions), 1), true)"
            );
        }
    } catch (Exception $e) {
        // Silently catch errors to avoid breaking the page
        error_log("ID reordering error: " . $e->getMessage());
    }
}

// 2. Handle Delete Operation [cite: 47]
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $del_stmt = $pdo->prepare("DELETE FROM regions WHERE id = ?");
    if ($del_stmt->execute([$delete_id])) {
        // Reorder IDs to remove gaps
        reorderIDs($pdo);
        // Confirmation message displayed on dashboard (not message box) [cite: 49-50]
        $message = "تم حذف السجل بنجاح.";
    }
}

// 3. Fetch all content items [cite: 46]
$stmt = $pdo->query("SELECT * FROM regions");
$regions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم المشرف - إدارة المحتوى</title>
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
        <div class="logo">لوحة التحكم</div>
        <nav>
            <a href="index.php">معاينة الموقع</a>
            <a href="logout.php">تسجيل الخروج</a> <button onclick="toggleNightMode()">الوضع الليلي 🌙</button>
        </nav>
    </header>

    <div class="admin-container">
        <h2>إدارة المحتوى</h2>
        
        <?php if (!empty($message)) echo "<div class='alert'>$message</div>"; ?>
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class='alert'><?php echo $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?></div>
        <?php endif; ?>

        <div class="admin-actions">
            <a href="add_content.php" class="btn btn-add-new">إضافة محتوى جديد</a>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>المنطقة</th>
                    <th>التصنيف</th>
                    <th>الوصف</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($regions as $region): ?>
                <tr>
                    <td><?php echo $region['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($region['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($region['category']); ?></td>
                    <td><?php echo mb_strimwidth(htmlspecialchars($region['description']), 0, 60, '...'); ?></td>
                    <td>
                        <div class="btn-group">
                            <a href="update_content.php?id=<?php echo $region['id']; ?>" class="btn btn-edit">تعديل</a>
                            
                            <form method="POST" style="display:inline;" onsubmit="return confirm('هل تريد حذف هذا السجل؟');">
                                <input type="hidden" name="delete_id" value="<?php echo $region['id']; ?>">
                                <button type="submit" class="btn btn-delete">حذف</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="script.js"></script>
</body>
</html>