<?php
session_start();
include 'db_connect.php'; // Ensure the database connection is included

if (!isset($_SESSION['userid'])) {
    header("Location: login.php"); // Redirect if the user is not logged in
    exit;
}

$userID = $_SESSION['userid'];

// Truy vấn để lấy tất cả đơn hàng
$sql_all_orders = "SELECT dh.DHID, dh.TrangThai, dh.DiaChiGiaoHang, dh.PhuongThucThanhToan, s.SachID, s.TenSach, ctdh.SoLuong, ctdh.DonGia
        FROM donhang dh 
        JOIN chitietdonhang ctdh ON ctdh.DHID = dh.DHID 
        JOIN sach s ON s.SachID = ctdh.SachID
        WHERE dh.ID = ?";
$stmt_all_orders = $conn->prepare($sql_all_orders);

if (!$stmt_all_orders) {
    die('Lỗi chuẩn bị câu lệnh: ' . $conn->error);
}

$stmt_all_orders->bind_param("i", $userID);
if (!$stmt_all_orders->execute()) {
    die('Lỗi thực thi câu lệnh: ' . $stmt_all_orders->error);
}

$result_all_orders = $stmt_all_orders->get_result();

if (!$result_all_orders) {
    die('Lỗi lấy kết quả: ' . $stmt_all_orders->error);
}

// Truy vấn để tính tổng tiền cho các đơn hàng trạng thái Completed
$sql_total_price = "SELECT SUM(ctdh.SoLuong * ctdh.DonGia) AS total_price
        FROM donhang dh 
        JOIN chitietdonhang ctdh ON ctdh.DHID = dh.DHID
        WHERE dh.ID = ? AND dh.TrangThai = 'Completed'";
$stmt_total_price = $conn->prepare($sql_total_price);

if (!$stmt_total_price) {
    die('Lỗi chuẩn bị câu lệnh tổng tiền: ' . $conn->error);
}

$stmt_total_price->bind_param("i", $userID);
if (!$stmt_total_price->execute()) {
    die('Lỗi thực thi câu lệnh tổng tiền: ' . $stmt_total_price->error);
}

$result_total_price = $stmt_total_price->get_result();
if (!$result_total_price) {
    die('Lỗi lấy kết quả tổng tiền: ' . $stmt_total_price->error);
}

$row_total_price = $result_total_price->fetch_assoc();
$total = $row_total_price['total_price'];
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng của bạn</title>
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            background-color: #f3f4f6;
            color: #333;
        }

        .cart-container {

            max-width: 1200px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table thead th {
            background-color: #20c997;
            /* Màu xanh ngọc */
            color: white;
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

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-danger {
            background-color: #f44336;
            color: white;
        }

        .alert-success {
            background-color: #4CAF50;
            color: white;
        }

        .home-button {
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

        .home-button:hover {
            background-color: #17a2b8;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .table-responsive {
            overflow-x: auto;
            /* Thêm thanh cuộn ngang nếu bảng quá rộng */
        }

        .table {
            width: 100%;
            /* Đảm bảo bảng chiếm toàn bộ chiều rộng của khung chứa */
            margin-bottom: 1rem;
            color: #212529;
        }

        .table thead th {
            text-align: center;
        }

        .table th,
        .table td {
            white-space: nowrap;
            /* Ngăn chặn việc cắt bớt nội dung */
        }

        .editable {
            cursor: pointer;
        }

        .editable:hover {
            background-color: #f1f1f1;
        }

        .hidden {
            display: none;
        }

        .btn-yellow {
            background-color: #f1c40f;
            /* Màu vàng */
            color: white;
            border: none;
            padding: 5px 5px;
            font-size: 14px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
            transition: background-color 0.3s;
        }

        .btn-yellow:hover {
            background-color: #d4ac0d;
        }
    </style>
    <style>
        /* Thay đổi màu sắc thông báo thành công */
        .toast-success {
            background-color: #28a745 !important;
            /* Màu xanh lá cây */
            color: white !important;
        }

        /* Thay đổi màu sắc thông báo thất bại */
        .toast-error {
            background-color: #dc3545 !important;
            /* Màu đỏ */
            color: white !important;
        }
    </style>
</head>

<body>
    <div class="container cart-container">
        <h1 class="text-center">Đơn hàng của bạn</h1>

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
            echo '<div class="alert alert-success">';
            echo '<p>' . htmlspecialchars($_SESSION['success']) . '</p>';
            echo '</div>';
            unset($_SESSION['success']);
        }
        ?>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="cart-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sách ID</th>
                        <th>Tên sách</th>
                        <th>Số lượng</th>
                        <th>Giá</th>
                        <th>Trạng thái</th>
                        <th>Hình thức thanh toán</th>
                        <th>Địa chỉ giao hàng</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_all_orders->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['DHID']); ?></td>
                            <td><?php echo htmlspecialchars($row['SachID']); ?></td>
                            <td><?php echo htmlspecialchars($row['TenSach']); ?></td>
                            <td><?php echo htmlspecialchars($row['SoLuong']); ?></td>
                            <td><?php echo htmlspecialchars($row['DonGia']); ?></td>
                            <td><?php echo htmlspecialchars($row['TrangThai']); ?></td>
                            <td><?php echo htmlspecialchars($row['PhuongThucThanhToan']); ?></td>
                            <td>
                                <span class="editable" data-id="<?php echo htmlspecialchars($row['DHID']); ?>">
                                    <?php echo htmlspecialchars(trim($row['DiaChiGiaoHang'])); ?>
                                </span>
                                <input type="text" class="form-control hidden" value="<?php echo htmlspecialchars(trim($row['DiaChiGiaoHang'])); ?>">
                            </td>
                            <td>
                                <?php if (trim($row['TrangThai']) === 'Pending') : ?>
                                    <a href="javascript:void(0);" class="btn btn-primary btn-sm edit-btn" data-id="<?php echo htmlspecialchars($row['DHID']); ?>">Sửa</a>
                                    <a href="javascript:void(0);" class="btn btn-primary btn-sm update-btn" data-id="<?php echo htmlspecialchars($row['DHID']); ?>">Cập nhật</a>
                                <?php endif; ?>
                                <?php if (trim($row['TrangThai']) === 'Completed') : ?>
                                    <a href="user_review.php?order_id=<?php echo htmlspecialchars($row['DHID']); ?>" class="btn btn-yellow btn-sm">Đánh giá</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>

            </table>
        </div>

        <div class="button-container">
            <button class="btn home-button" onclick="window.location.href='index.php';">Quay lại trang chủ</button>
            <div class="cart-summary">
                <p>Tổng tiền GD thành công: <span id="total-price"><?php echo number_format($total, 2); ?> VND</span></p>
            </div>
        </div>
    </div>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Khi nhấn nút "Sửa", tự động kích hoạt chỉnh sửa ô địa chỉ
            $('.edit-btn').on('click', function() {
                var $this = $(this);
                var orderID = $this.data('id');
                var $row = $this.closest('tr');

                // Hiển thị ô nhập liệu và ẩn ô hiển thị địa chỉ
                $row.find('.editable').addClass('hidden').next('input').removeClass('hidden').focus();
                $row.find('input').val($row.find('.editable').text().trim());
                $row.find('.update-btn').removeClass('hidden'); // Hiển thị nút cập nhật
                $this.addClass('hidden'); // Ẩn nút "Sửa"
            });

            // Khi nhấn nút "Cập nhật", lưu thay đổi
            $(document).on('click', '.update-btn', function() {
                var $this = $(this);
                var orderID = $this.data('id');
                var newAddress = $this.closest('tr').find('input').val().trim();

                $.ajax({
                    url: 'update_cart.php',
                    type: 'POST',
                    data: {
                        id: orderID,
                        address: newAddress
                    },
                    success: function(response) {
                        var trimmedResponse = response.trim();
                        if (trimmedResponse === 'success') {
                            toastr.success('Cập nhật địa chỉ giao hàng thành công.', '', {
                                closeButton: true,
                                progressBar: true,
                                toastClass: 'toast toast-success' // Sử dụng lớp tùy chỉnh cho thông báo thành công
                            });
                            $this.closest('tr').find('.editable').text(newAddress).removeClass('hidden');
                            $this.closest('tr').find('input').addClass('hidden');
                            $this.addClass('hidden'); // Ẩn nút cập nhật
                            $this.closest('tr').find('.edit-btn').removeClass('hidden'); // Hiển thị lại nút "Sửa"
                        } else if (trimmedResponse === 'no_change') {
                            toastr.info('Không có thay đổi nào.', '', {
                                closeButton: true,
                                progressBar: true
                            });
                        } else if (trimmedResponse.startsWith('error:')) {
                            toastr.error('Lỗi: ' + trimmedResponse.substring(6), '', {
                                closeButton: true,
                                progressBar: true,
                                toastClass: 'toast toast-error' // Sử dụng lớp tùy chỉnh cho thông báo thất bại
                            });
                            setTimeout(function() {
                                window.location.href = 'user_order.php'; // Tải lại trang user_order.php
                            }, 2000); // Đợi 2 giây trước khi tải lại trang
                        } else {
                            toastr.success('Cập nhật địa chỉ giao hàng thành công.', '', {
                                closeButton: true,
                                progressBar: true,
                                toastClass: 'toast toast-success' // Sử dụng lớp tùy chỉnh cho thông báo thất bại
                            });
                            setTimeout(function() {
                                window.location.href = 'user_order.php'; // Tải lại trang user_order.php
                            }, 2000); // Đợi 2 giây trước khi tải lại trang
                        }
                    },
                    error: function() {
                        toastr.error('Có lỗi xảy ra.', '', {
                            closeButton: true,
                            progressBar: true,
                            toastClass: 'toast toast-error' // Sử dụng lớp tùy chỉnh cho thông báo thất bại
                        });
                        setTimeout(function() {
                            window.location.href = 'user_order.php'; // Tải lại trang user_order.php
                        }, 2000); // Đợi 2 giây trước khi tải lại trang
                    }
                });
            });
        });
    </script>
</body>

</html>