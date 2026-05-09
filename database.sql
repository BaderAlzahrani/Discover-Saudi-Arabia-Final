CREATE DATABASE saudi_arabia_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE saudi_arabia_db;


CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);


INSERT INTO admins (username, password) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');


CREATE TABLE regions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    main_image VARCHAR(255) NOT NULL,
    features TEXT NOT NULL,
    activities TEXT NOT NULL,
    landmarks TEXT NOT NULL,
    gallery_img1 VARCHAR(255) NOT NULL,
    gallery_img2 VARCHAR(255) DEFAULT '',
    gallery_img3 VARCHAR(255) DEFAULT ''
);


INSERT INTO regions (
    name, category, description, main_image,
    features, activities, landmarks,
    gallery_img1, gallery_img2, gallery_img3
) VALUES (
    'الرياض',
    'وسطى',
    'عاصمة المملكة ومركز اقتصادي وثقافي، تجمع بين عمارة حديثة وتراث محلي غني.',
    'riyadh.jpg',
    'مزيج من الحداثة والتاريخ، طقس صحراوي معتدل في الشتاء، شبكة طرق وتنقل حديثة.',
    'جولات حضرية، زيارة المعالم التاريخية والتسوق، حضور فعاليات ثقافية.',
    'مركز المملكة، حصن المصمك، بوليفارد الرياض، درة الرياض',
    'istockphoto-1452888631-612x612.jpg',
    'gettyimages-2201484228-612x612.jpg',
    '360_F_305735954_R82RYvQPuBUVbkDz3dA45fmJuSNa5DOM.jpg'
);