<?php
session_start();
include 'db_connect.php'; // Ensure the database connection is included

if (!isset($_SESSION['userid'])) {
    header("Location: login.php"); // Redirect if the user is not logged in
    exit;
}

$userID = $_SESSION['userid'];
$sql = "SELECT s.SachID, s.TenSach, s.GiaBan, g.SoLuong, s.SoLuong AS SoLuongTonKho
        FROM giohang g 
        JOIN sach s ON g.SachID = s.SachID 
        WHERE g.ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            background-color: #f3f4f6;
            color: #333;
        }

        .cart-container {
            margin-top: 40px;
            max-width: 1200px;
            background: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            border-left: 5px solid #20c997;
            
            /* Màu xanh ngọc */
        }
        .table thead th {
            background-color: #20c997;
            /* Màu xanh ngọc */
            color: white;
        }

        .cart-summary {
            margin-top: 20px;
            text-align: right;
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
    </style>
</head>

<body>
    <div class="container cart-container">
        <h1 class="text-center">Giỏ hàng của bạn</h1>

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
        <table class="table table-bordered table-hover" id="cart-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên Sách</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Tổng tiền</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $subtotal = $row['GiaBan'] * $row['SoLuong'];
                        $total += $subtotal;
                        echo "<tr id='row_{$row['SachID']}'>";
                        echo "<td>" . htmlspecialchars($row['SachID']) . "</td>"; // Display SachID
                        echo "<td>" . htmlspecialchars($row['TenSach']) . "</td>";
                        echo "<td id='price_{$row['SachID']}'>" . number_format($row['GiaBan'], 2) . " VND</td>";
                        echo "<td><input type='number' value='{$row['SoLuong']}' id='quantity_{$row['SachID']}' min='1' max='{$row['SoLuongTonKho']}' class='quantity form-control' data-sachid='{$row['SachID']}' style='width: 80px;'></td>";
                        echo "<td id='subtotal_{$row['SachID']}'>" . number_format($subtotal, 2) . " VND</td>";
                        echo "<td><button class='btn btn-danger delete-button' data-sachid='{$row['SachID']}'>Xóa</button></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>Giỏ hàng của bạn trống.</td></tr>"; // Adjust colspan to match the number of columns
                }
                ?>
            </tbody>
        </table>

        <div class="button-container">
            <button class="btn home-button" onclick="window.location.href='index.php';">Quay lại trang chủ</button>
            <div class="cart-summary">
                <p>Tổng cộng: <span id="total-price"><?php echo number_format($total, 2); ?> VND</span></p>
                <button class="btn btn-custom" onclick="checkout()">Mua Ngay</button>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cập nhật số lượng
            document.querySelectorAll('.quantity').forEach(input => {
                input.addEventListener('change', function() {
                    const sachID = this.dataset.sachid;
                    const quantity = parseInt(this.value);
                    const maxQuantity = parseInt(this.max);
                    if (quantity > maxQuantity) {
                        alert(`Số lượng sách không được vượt quá ${maxQuantity}`);
                        this.value = maxQuantity;
                        return;
                    }
                    const pricePerUnit = parseFloat(document.getElementById(`price_${sachID}`).textContent.replace(/[, VND]/g, ''));
                    const newSubtotal = quantity * pricePerUnit;

                    document.getElementById(`subtotal_${sachID}`).textContent = newSubtotal.toFixed(2) + ' VND';
                    updateCartTotal();

                    // Cập nhật số lượng trong giỏ hàng qua AJAX
                    fetch('update_cart.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `action=update&bookId=${sachID}&quantity=${quantity}`
                        })
                        .then(response => response.text())
                        .then(data => console.log('Updated:', data))
                        .catch(error => console.error('Error:', error));
                });
            });

            // Xóa sản phẩm
            document.querySelectorAll('.delete-button').forEach(button => {
                button.addEventListener('click', function() {
                    const sachID = this.dataset.sachid;
                    if (confirm("Bạn có chắc chắn muốn xóa sản phẩm này không?")) {
                        deleteItem(sachID);
                    }
                });
            });

            // Cập nhật tổng giá trị giỏ hàng
            function updateCartTotal() {
                let total = 0;
                document.querySelectorAll('[id^="subtotal_"]').forEach(item => {
                    total += parseFloat(item.textContent.replace(/[, VND]/g, ''));
                });
                document.getElementById('total-price').textContent = `${total.toFixed(2)} VND`;
            }

            // Xóa mục giỏ hàng
            function deleteItem(sachID) {
                fetch('update_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=delete&bookId=${sachID}`
                    })
                    .then(response => response.text())
                    .then(data => {
                        console.log('Deleted:', data);
                        document.getElementById(`row_${sachID}`).remove();
                        updateCartTotal();
                    })
                    .catch(error => console.error('Error:', error));
            }
        });

        function checkout() {
            window.location.href = 'checkout.php';
        }
    </script>
</body>

</html>