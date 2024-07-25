<?php
session_start();
include 'db_connect.php'; // Ensure the database connection is included

if (!isset($_SESSION['userid'])) {
    header("Location: user_login.php"); // Redirect if the user is not logged in
    exit;
}

$userID = $_SESSION['userid'];
$orderID = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $bookID = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;

    if ($rating > 0 && $rating <= 5 && $bookID > 0 && $comment != '') {
        // Check if the order has already been rated twice
        $sql_check_rating_count = "SELECT COUNT(*) AS rating_count FROM ratings WHERE ID = ? AND SachID = ?";
        $stmt_check_rating_count = $conn->prepare($sql_check_rating_count);
        if ($stmt_check_rating_count) {
            $stmt_check_rating_count->bind_param("ii", $userID, $bookID);
            $stmt_check_rating_count->execute();
            $result_check_rating_count = $stmt_check_rating_count->get_result();
            $row = $result_check_rating_count->fetch_assoc();

            if ($row['rating_count'] < 2) {
                // Proceed to insert the rating
                $sql_insert_rating = "INSERT INTO ratings (ID, SachID, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())";
                $stmt_insert_rating = $conn->prepare($sql_insert_rating);
                if ($stmt_insert_rating) {
                    $stmt_insert_rating->bind_param("iiis", $userID, $bookID, $rating, $comment);
                    if ($stmt_insert_rating->execute()) {
                        $_SESSION['success'] = "Thành công! Cảm ơn bạn đã góp ý cho sản phẩm của chúng tôi!";
                        header("refresh:1; url=user_order.php");
                        exit;
                    } else {
                        $_SESSION['errors'] = ["Lỗi khi lưu đánh giá: " . $stmt_insert_rating->error];
                    }
                } else {
                    $_SESSION['errors'] = ["Lỗi chuẩn bị câu lệnh: " . $conn->error];
                }
            } else {
                $_SESSION['errors'] = ["Bạn chỉ có thể đánh giá tối đa 2 lần cho mỗi hóa đơn."];
                header("refresh:2; url=user_order.php");
            }
        } else {
            $_SESSION['errors'] = ["Lỗi chuẩn bị câu lệnh: " . $conn->error];
        }
    } else {
        $_SESSION['errors'] = ["Vui lòng điền đầy đủ thông tin đánh giá."];
    }
}

// Fetch order details
$sql_admin_order_details = "SELECT dh.DHID, s.SachID, s.TenSach FROM donhang dh JOIN chitietdonhang ctdh ON ctdh.DHID = dh.DHID JOIN sach s ON s.SachID = ctdh.SachID WHERE dh.DHID = ? AND dh.ID = ? AND dh.TrangThai = 'Completed'";
$stmt_admin_order_details = $conn->prepare($sql_admin_order_details);
if ($stmt_admin_order_details) {
    $stmt_admin_order_details->bind_param("ii", $orderID, $userID);
    $stmt_admin_order_details->execute();
    $result_admin_order_details = $stmt_admin_order_details->get_result();
    $order = $result_admin_order_details->fetch_assoc();
} else {
    die("Lỗi chuẩn bị câu lệnh: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh giá đơn hàng</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            background-color: #f3f4f6;
            color: #333;
        }

        .container {
            margin-top: 40px;
            background: white;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            border-left: 5px solid #20c997;
            /* Màu xanh ngọc */
        }

        .btn-custom {
            background-color: #20c997;
            /* Màu xanh ngọc */
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
            transition: background-color 0.3s;
        }

        .btn-custom:hover {
            background-color: #17a2b8;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="text-center">Đánh giá đơn hàng</h1>

        <?php
        if (isset($_SESSION['errors'])) {
            echo '<div class="alert alert-danger">';
            foreach ($_SESSION['errors'] as $error) {
                echo '<p>' . htmlspecialchars($error) . '</p>';
            }
            echo '</div>';
            unset($_SESSION['errors']);
        }

        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success" id="success-message">';
            echo '<p>' . htmlspecialchars($_SESSION['success']) . '</p>';
            echo '</div>';
            unset($_SESSION['success']);
        }
        ?>

        <?php if ($order) : ?>
            <form action="user_order_review.php?order_id=<?php echo htmlspecialchars($orderID); ?>" method="POST">
                <div class="form-group">
                    <label for="book">Sách:</label>
                    <input type="text" class="form-control" id="book" value="<?php echo htmlspecialchars($order['TenSach']); ?>" disabled>
                    <input type="hidden" name="book_id" value="<?php echo htmlspecialchars($order['SachID']); ?>">
                </div>
                <div class="form-group">
                    <label for="rating">Đánh giá (1-5):</label>
                    <input type="number" class="form-control" id="rating" name="rating" min="1" max="5" value="5" required>
                </div>
                <div class="form-group">
                    <label for="comment">Nhận xét: </label>
                    <textarea class="form-control" placeholder="Vui lòng nhận xét sản phẩm.." id="comment" name="comment" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-custom">Gửi đánh giá</button>
            </form>
        <?php else : ?>
            <p>Không tìm thấy đơn hàng hoặc đơn hàng không hợp lệ.</p>
        <?php endif; ?>

    </div>

    <?php if (isset($_SESSION['success'])) : ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function() {
                    window.location.href = 'user_order.php';
                }, 1000); // 1 second
            });
        </script>
    <?php endif; ?>
</body>

</html>