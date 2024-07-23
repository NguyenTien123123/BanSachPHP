<?php
include 'db_connect.php'; // Kết nối CSDL

// Thêm phần xử lý khi có hành động update
if (isset($_POST['action'])) {
    $ratingID = $_POST['ratingID'];
    switch ($_POST['action']) {
        case "update":
            $newStatus = $_POST['status'];
            $sql = "UPDATE ratings SET status = ? WHERE id_rat = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $newStatus, $ratingID);
            $stmt->execute();
            break;
    }
    // Chuyển hướng để tránh re-post khi refresh
    header("Location: manager_ratings.php");
    exit();
}

// Lấy dữ liệu từ form lọc
$date_filter_start = '';
$date_filter_end = '';
$status_filter = '';

if (isset($_POST['filter_date_start']) && isset($_POST['filter_date_end'])) {
    $date_filter_start = $_POST['filter_date_start'];
    $date_filter_end = $_POST['filter_date_end'];
}

if (isset($_POST['status_filter'])) {
    $status_filter = $_POST['status_filter'];
}

// Lấy dữ liệu từ bảng ratings
$query = "SELECT r.id_rat, s.TenSach, r.rating, r.created_at, r.comment, r.status 
          FROM ratings r 
          JOIN sach s ON r.SachID = s.SachID 
          WHERE 1";

if ($date_filter_start && $date_filter_end) {
    $query .= " AND DATE(r.created_at) BETWEEN '$date_filter_start' AND '$date_filter_end'";
}

if ($status_filter) {
    $query .= " AND r.status = '$status_filter'";
}

$query .= " ORDER BY r.created_at DESC LIMIT 30";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đánh Giá</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            background-color: #f4f4f4;
            padding-top: 20px;
        }

        .container {
            margin-top: 40px;
            margin-bottom: 40px;
            max-width: 90%;
            background: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            border-left: 5px solid #20c997;
            /* Màu xanh ngọc */
        }

        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            margin-bottom: 20px;
        }

        th,
        td {
            text-align: center;
        }

        th {
            background-color: #007bff;
            color: #fff;
        }

        .btn {
            margin: 5px;
        }

        .form-inline {
            justify-content: center;
        }

        .form-control {
            margin: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Quản Lý Đánh Giá</h2>
        <button class="btn btn-primary" onclick="window.location.href='admin_dashboard.php'">Quay lại Dashboard</button>
        <form method="post" class="form-inline mb-3">
            <label for="filter_date_start" class="mr-2">Từ ngày:</label>
            <input type="date" id="filter_date_start" name="filter_date_start" class="form-control mr-2" value="<?php echo htmlspecialchars($date_filter_start); ?>">
            <label for="filter_date_end" class="mr-2">Đến ngày:</label>
            <input type="date" id="filter_date_end" name="filter_date_end" class="form-control mr-2" value="<?php echo htmlspecialchars($date_filter_end); ?>">
            <label for="status_filter" class="mr-2">Trạng thái:</label>
            <select id="status_filter" name="status_filter" class="form-control mr-2">
                <option value="">Tất cả</option>
                <option value="Pending" <?php echo $status_filter == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="Approved" <?php echo $status_filter == 'Approved' ? 'selected' : ''; ?>>Approved</option>
            </select>
            <button type="submit" class="btn btn-primary">Lọc</button>
        </form>
        <div class="table-container">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID Đánh giá</th>
                        <th>Tên sách</th>
                        <th>Đánh giá</th>
                        <th>Ngày đánh giá</th>
                        <th>Ghi chú</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id_rat']); ?></td>
                            <td><?php echo htmlspecialchars($row['TenSach']); ?></td>
                            <td><?php echo htmlspecialchars($row['rating']); ?></td>
                            <td><?php echo date("d-m-Y", strtotime($row['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($row['comment']); ?></td>
                            <td>
                                <form action="manager_ratings.php" method="post" class="d-inline">
                                    <input type="hidden" name="ratingID" value="<?php echo htmlspecialchars($row['id_rat']); ?>">
                                    <input type="hidden" name="action" value="update">
                                    <select name="status" class="form-control form-control-sm d-inline" style="width: auto;">
                                        <option value="Pending" <?php echo $row['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Approved" <?php echo $row['status'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                    </select>
                                    <button type="submit" class="btn btn-success btn-sm">Cập nhật</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
