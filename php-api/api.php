<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    if ($method == 'OPTIONS') {
        exit;
    }

    if (empty($action)) {
        http_response_code(400);
        echo json_encode(["error" => "لم يتم تحديد إجراء"]);
        exit;
    }

switch ($action) {
    case 'products':
        if ($method == 'GET') {
            $stmt = $conn->prepare("SELECT * FROM products");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = array_map(function($r) {
                return [
                    "id" => $r['id'],
                    "name" => ["ar" => $r['name_ar'], "en" => $r['name_en']],
                    "description" => ["ar" => $r['description_ar'], "en" => $r['description_en']],
                    "images" => json_decode($r['images']),
                    "category" => $r['category'],
                    "price" => $r['price']
                ];
            }, $rows);
            echo json_encode($result);
        } elseif ($method == 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $conn->exec("DELETE FROM products");
            $stmt = $conn->prepare("INSERT INTO products (id, name_ar, name_en, description_ar, description_en, images, category, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($data as $p) {
                $stmt->execute([$p['id'], $p['name']['ar'], $p['name']['en'], $p['description']['ar'], $p['description']['en'], json_encode($p['images']), $p['category'], $p['price']]);
            }
            echo json_encode(["success" => true]);
        }
        break;

    case 'articles':
        if ($method == 'GET') {
            $stmt = $conn->prepare("SELECT * FROM articles");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = array_map(function($r) {
                return [
                    "id" => $r['id'],
                    "title" => ["ar" => $r['title_ar'], "en" => $r['title_en']],
                    "content" => ["ar" => $r['content_ar'], "en" => $r['content_en']],
                    "image" => $r['image'],
                    "date" => $r['date']
                ];
            }, $rows);
            echo json_encode($result);
        } elseif ($method == 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $conn->exec("DELETE FROM articles");
            $stmt = $conn->prepare("INSERT INTO articles (id, title_ar, title_en, content_ar, content_en, image, date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($data as $a) {
                $stmt->execute([$a['id'], $a['title']['ar'], $a['title']['en'], $a['content']['ar'], $a['content']['en'], $a['image'], $a['date']]);
            }
            echo json_encode(["success" => true]);
        }
        break;

    case 'settings':
        if ($method == 'GET') {
            $stmt = $conn->prepare("SELECT * FROM settings WHERE id = 1 LIMIT 1");
            $stmt->execute();
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$r) {
                echo json_encode([]);
                break;
            }
            echo json_encode([
                "phone" => $r['phone'],
                "whatsapp" => $r['whatsapp'],
                "logo" => $r['logo'],
                "address" => ["ar" => $r['address_ar'], "en" => $r['address_en']],
                "heroTitle" => ["ar" => $r['heroTitle_ar'], "en" => $r['heroTitle_en']],
                "heroSub" => ["ar" => $r['heroSub_ar'], "en" => $r['heroSub_en']],
                "heroImage" => $r['heroImage']
            ]);
        } elseif ($method == 'POST') {
            $s = json_decode(file_get_contents("php://input"), true);
            // Check if settings exist
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM settings WHERE id = 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                // Update existing settings
                $stmt = $conn->prepare("UPDATE settings SET phone = ?, whatsapp = ?, logo = ?, address_ar = ?, address_en = ?, heroTitle_ar = ?, heroTitle_en = ?, heroSub_ar = ?, heroSub_en = ?, heroImage = ? WHERE id = 1");
                $stmt->execute([$s['phone'], $s['whatsapp'], $s['logo'], $s['address']['ar'], $s['address']['en'], $s['heroTitle']['ar'], $s['heroTitle']['en'], $s['heroSub']['ar'], $s['heroSub']['en'], $s['heroImage']]);
            } else {
                // Insert new settings if doesn't exist
                $stmt = $conn->prepare("INSERT INTO settings (id, phone, whatsapp, logo, address_ar, address_en, heroTitle_ar, heroTitle_en, heroSub_ar, heroSub_en, heroImage) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$s['phone'], $s['whatsapp'], $s['logo'], $s['address']['ar'], $s['address']['en'], $s['heroTitle']['ar'], $s['heroTitle']['en'], $s['heroSub']['ar'], $s['heroSub']['en'], $s['heroImage']]);
            }
            echo json_encode(["success" => true]);
        }
        break;

    case 'gallery':
        if ($method == 'GET') {
            $stmt = $conn->prepare("SELECT * FROM gallery");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = array_map(function($r) {
                return [
                    "id" => $r['id'],
                    "url" => $r['url'],
                    "title" => ["ar" => $r['title_ar'], "en" => $r['title_en']],
                    "category" => $r['category']
                ];
            }, $rows);
            echo json_encode($result);
        } elseif ($method == 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $conn->exec("DELETE FROM gallery");
            $stmt = $conn->prepare("INSERT INTO gallery (id, url, title_ar, title_en, category) VALUES (?, ?, ?, ?, ?)");
            foreach ($data as $g) {
                $stmt->execute([$g['id'], $g['url'], $g['title']['ar'], $g['title']['en'], $g['category']]);
            }
            echo json_encode(["success" => true, "message" => "تم حفظ صور المعرض بنجاح"]);
        }
        break;

    case 'reviews':
        if ($method == 'GET') {
            $stmt = $conn->prepare("SELECT * FROM reviews");
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result = array_map(function($r) {
                return [
                    "id" => $r['id'],
                    "author" => $r['author'],
                    "rating" => (int)$r['rating'],
                    "comment" => ["ar" => $r['comment_ar'], "en" => $r['comment_en']],
                    "avatar" => $r['avatar']
                ];
            }, $rows);
            echo json_encode($result);
        } elseif ($method == 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            $conn->exec("DELETE FROM reviews");
            $stmt = $conn->prepare("INSERT INTO reviews (id, author, rating, comment_ar, comment_en, avatar) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($data as $r) {
                $stmt->execute([$r['id'], $r['author'], $r['rating'], $r['comment']['ar'], $r['comment']['en'], $r['avatar']]);
            }
            echo json_encode(["success" => true, "message" => "تم حفظ التقييمات بنجاح"]);
        }
        break;

    case 'inquiries':
        if ($method == 'GET') {
            $stmt = $conn->prepare("SELECT * FROM inquiries ORDER BY date DESC");
            $stmt->execute();
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } elseif ($method == 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            // Insert new inquiry without deleting existing ones
            $stmt = $conn->prepare("INSERT IGNORE INTO inquiries (id, name, email, msg, date) VALUES (?, ?, ?, ?, ?)");
            foreach ($data as $i) {
                $stmt->execute([$i['id'], $i['name'], $i['email'], $i['msg'], $i['date']]);
            }
            echo json_encode(["success" => true, "message" => "تم حفظ الاستفسار بنجاح"]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(["error" => "إجراء غير صحيح: $action"]);
        break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "خطأ: " . $e->getMessage()]);
}
?>
