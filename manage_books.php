<?php
include 'db_connect.php'; // Kết nối CSDL

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $tenSach = $_POST['tenSach'];
                $giaBan = $_POST['giaBan'];
                $soLuong = $_POST['soLuong'];
                $moTa = $_POST['moTa'];
                $theLoai = $_POST['theLoai'];
                $soTrang = $_POST['soTrang'];
                $khoiLuong = $_POST['khoiLuong'];
                $kichThuoc = $_POST['kichThuoc'];
                $tacGia = $_POST['tacGia'];
                $dichGia = $_POST['dichGia'];
                $nxbID = $_POST['NXBID'];

                $fileName = basename($_FILES["hinhAnh"]["name"]);
                $targetDir = "uploads/";
                $targetFilePath = $targetDir . $fileName;
                move_uploaded_file($_FILES["hinhAnh"]["tmp_name"], $targetFilePath);

                $sql = "INSERT INTO sach (TenSach, GiaBan, SoLuong, MoTa, TheLoai, SoTrang, KhoiLuong, KichThuoc, TacGia, DichGia, NXBID, HinhAnh) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sdisssssssis", $tenSach, $giaBan, $soLuong, $moTa, $theLoai, $soTrang, $khoiLuong, $kichThuoc, $tacGia, $dichGia, $nxbID, $targetFilePath);
                $stmt->execute();
                header("Location: manage_books.php"); // Redirect
                exit();
                break;

            case 'edit':
                $sachID = $_POST['sachID'];
                $sql = "SELECT * FROM sach WHERE SachID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $sachID);
                $stmt->execute();
                $result = $stmt->get_result();
                $book = $result->fetch_assoc();
                break;

            case 'update':
                $sachID = $_POST['sachID'];
                $tenSach = $_POST['tenSach'];
                $giaBan = $_POST['giaBan'];
                $soLuong = $_POST['soLuong'];
                $moTa = $_POST['moTa'];
                $theLoai = $_POST['theLoai'];
                $soTrang = $_POST['soTrang'];
                $khoiLuong = $_POST['khoiLuong'];
                $kichThuoc = $_POST['kichThuoc'];
                $tacGia = $_POST['tacGia'];
                $dichGia = $_POST['dichGia'];
                $nxbID = $_POST['NXBID'];

                $fileName = basename($_FILES["hinhAnh"]["name"]);
                if (!empty($fileName)) {
                    $targetDir = "uploads/";
                    $targetFilePath = $targetDir . $fileName;
                    move_uploaded_file($_FILES["hinhAnh"]["tmp_name"], $targetFilePath);
                } else {
                    $targetFilePath = $_POST['hinhAnhCu'];
                }

                $sql = "UPDATE sach SET TenSach = ?, GiaBan = ?, SoLuong = ?, MoTa = ?, TheLoai = ?, SoTrang = ?, KhoiLuong = ?, KichThuoc = ?, TacGia = ?, DichGia = ?, NXBID = ?, HinhAnh = ? WHERE SachID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sdisssssssisi", $tenSach, $giaBan, $soLuong, $moTa, $theLoai, $soTrang, $khoiLuong, $kichThuoc, $tacGia, $dichGia, $nxbID, $targetFilePath, $sachID);
                $stmt->execute();
                header("Location: manage_books.php"); // Redirect
                exit();
                break;

                // case 'delete':
                //     $sachID = $_POST['sachID'];
                //     $sql = "DELETE FROM sach WHERE SachID = ?";
                //     $stmt = $conn->prepare($sql);
                //     $stmt->bind_param("i", $sachID);
                //     $stmt->execute();
                //     header("Location: manage_books.php"); // Redirect
                //     exit();
            case 'delete':
                $sachID = $_POST['sachID'];
                // Xóa trong chitietdonhang trước
                $sql = "DELETE FROM chitietdonhang WHERE SachID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $sachID);
                if (!$stmt->execute()) {
                    echo "Lỗi khi xóa từ chitietdonhang: " . $stmt->error;
                    exit();
                }
                // Xóa trong giohang nếu cần
                $sql = "DELETE FROM giohang WHERE SachID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $sachID);
                if (!$stmt->execute()) {
                    echo "Lỗi khi xóa từ giohang: " . $stmt->error;
                    exit();
                }
                // Cuối cùng, xóa trong sach
                $sql = "DELETE FROM sach WHERE SachID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $sachID);
                if (!$stmt->execute()) {
                    echo "Lỗi khi xóa từ sach: " . $stmt->error;
                    exit();
                }
                header("Location: manage_books.php");
                exit();
                break;
        }
    }
}

$searchKeyword = '';
if (isset($_GET['search'])) {
    $searchKeyword = $_GET['search'];
    $result = $conn->query("SELECT * FROM sach WHERE TenSach LIKE '%$searchKeyword%' OR TacGia LIKE '%$searchKeyword%' OR TheLoai LIKE '%$searchKeyword%'");
} else {
    $result = $conn->query("SELECT * FROM sach");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sách</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f0f2f5;
        }

        .container-fluid {
            margin-top: 30px;
        }

        .form-inline input[type="search"] {
            width: calc(100% - 120px);
        }

        .form-inline button {
            width: 100px;
        }

        .table img {
            max-width: 100px;
            height: auto;
        }

        .form-group img {
            max-width: 150px;
            height: auto;
            margin-top: 10px;
        }

        /* .card-header {
            background-color: #28a745;
            color: white;
        } */
        .card-header {
            background-color: #28a745;
            color: white;
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            right: 0;
        }

        .card {
            margin-bottom: 20px;
        }

        .btn-primary,
        .btn-success,
        .btn-warning,
        .btn-danger {
            margin-right: 5px;
        }

        .btn-success,
        .btn-outline-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-success:hover,
        .btn-outline-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        .sticky-form {
            position: -webkit-sticky;
            position: sticky;
            top: 20px;
        }

        .search-bar {
            margin-bottom: 20px;
        }

        .card-body {
            max-height: 85vh;
            /* Đặt chiều cao tối đa bằng chiều cao của màn hình */
            overflow-y: auto;
            /* Kích hoạt thanh cuộn dọc khi cần thiết */
        }
    </style>


</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <button class="btn btn-primary btn-sm" onclick="window.location.href='admin_dashboard.php'">
                    <i class="fas fa-home"></i>
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
                </button>
                <!-- Form tìm kiếm sách -->
                <div class="search-bar">
                    <form class="form-inline" method="get" action="manage_books.php">
                        <input class="form-control mr-sm-2" type="search" name="search" placeholder="Tìm kiếm sách" aria-label="Search" value="<?php echo $searchKeyword; ?>">
                        <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Tìm kiếm</button>
                    </form>
                </div>
                <!-- Form thêm và cập nhật thông tin sách -->
                <div class="card sticky-form">
                    <div class="card-header">
                        <?php echo isset($book) ? 'Cập Nhật Sách' : 'Thêm Sách Mới'; ?>
                    </div>
                    <div class="card-body">
                        <form method="post" action="manage_books.php" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo isset($book) ? 'update' : 'add'; ?>">
                            <input type="hidden" name="sachID" value="<?php echo $book['SachID'] ?? ''; ?>">

                            <div class="form-group">
                                <label for="tenSach">Tên Sách</label>
                                <input type="text" class="form-control" id="tenSach" name="tenSach" placeholder="Tên Sách" value="<?php echo $book['TenSach'] ?? ''; ?>" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-5">
                                    <label for="giaBan">Giá Bán</label>
                                    <input type="number" class="form-control" id="giaBan" name="giaBan" placeholder="Giá Bán" value="<?php echo $book['GiaBan'] ?? ''; ?>" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="soLuong">Số Lượng</label>
                                    <input type="number" class="form-control" id="soLuong" name="soLuong" placeholder="Số Lượng" value="<?php echo $book['SoLuong'] ?? ''; ?>" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="theLoai">Thể Loại</label>
                                    <input type="text" class="form-control" id="theLoai" name="theLoai" placeholder="Thể Loại" value="<?php echo $book['TheLoai'] ?? ''; ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="moTa">Mô Tả</label>
                                <textarea class="form-control" id="moTa" name="moTa" placeholder="Mô Tả" rows="3"><?php echo $book['MoTa'] ?? ''; ?></textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-5">
                                    <label for="soTrang">Số Trang</label>
                                    <input type="number" class="form-control" id="soTrang" name="soTrang" placeholder="Số Trang" value="<?php echo $book['SoTrang'] ?? ''; ?>" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="khoiLuong">Khối Lượng</label>
                                    <input type="text" class="form-control" id="khoiLuong" name="khoiLuong" placeholder="Khối Lượng" value="<?php echo $book['KhoiLuong'] ?? ''; ?>" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="kichThuoc">Kích Thước</label>
                                    <input type="text" class="form-control" id="kichThuoc" name="kichThuoc" placeholder="Kích Thước" value="<?php echo $book['KichThuoc'] ?? ''; ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="tacGia">Tác Giả</label>
                                    <input type="text" class="form-control" id="tacGia" name="tacGia" placeholder="Tác Giả" value="<?php echo $book['TacGia'] ?? ''; ?>">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="dichGia">Dịch Giả</label>
                                    <input type="text" class="form-control" id="dichGia" name="dichGia" placeholder="Dịch Giả" value="<?php echo $book['DichGia'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="NXBID">Nhà Xuất Bản</label>
                                <select class="form-control" id="NXBID" name="NXBID">
                                    <!-- Lấy danh sách NXB từ database -->
                                    <?php
                                    $nxbs = $conn->query("SELECT NXBID, TenNXB FROM nhaxuatban");
                                    while ($nxb = $nxbs->fetch_assoc()) {
                                        echo "<option value='{$nxb['NXBID']}'" . ($book['NXBID'] == $nxb['NXBID'] ? " selected" : "") . ">{$nxb['TenNXB']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="hinhAnh">Hình Ảnh</label>
                                <input type="file" class="form-control-file" id="hinhAnh" name="hinhAnh">
                                <?php if (isset($book['HinhAnh']) && $book['HinhAnh']) : ?>
                                    <img src="<?php echo $book['HinhAnh']; ?>" alt="Hình Ảnh">
                                    <input type="hidden" name="hinhAnhCu" value="<?php echo $book['HinhAnh']; ?>">
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-success"><?php echo isset($book) ? 'Cập nhật' : 'Thêm'; ?></button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Bảng hiển thị thông tin sách -->
                <div class="card">
                    <div class="card-header">
                        Danh sách Sách
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Tên Sách</th>
                                    <th>Giá Bán</th>
                                    <th>Số Lượng</th>
                                    <th>Mô Tả</th>
                                    <th>Thể Loại</th>
                                    <th>Số Trang</th>
                                    <th>Khối Lượng</th>
                                    <th>Kích Thước</th>
                                    <th>Tác Giả</th>
                                    <th>Dịch Giả</th>
                                    <th>NXB</th>
                                    <th>Hình Ảnh</th>
                                    <th>Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()) : ?>
                                    <tr>
                                        <td><?php echo $row['SachID']; ?></td>
                                        <td><?php echo $row['TenSach']; ?></td>
                                        <td><?php echo $row['GiaBan']; ?></td>
                                        <td><?php echo $row['SoLuong']; ?></td>
                                        <td><?php echo substr($row['MoTa'], 0, 100) . (strlen($row['MoTa']) > 100 ? '...' : ''); ?></td>
                                        <td><?php echo $row['TheLoai']; ?></td>
                                        <td><?php echo $row['SoTrang']; ?></td>
                                        <td><?php echo $row['KhoiLuong']; ?></td>
                                        <td><?php echo $row['KichThuoc']; ?></td>
                                        <td><?php echo $row['TacGia']; ?></td>
                                        <td><?php echo $row['DichGia']; ?></td>
                                        <td><?php echo $row['NXBID']; ?></td> <!-- Đổi thành tên NXB nếu cần -->
                                        <td><img src="<?php echo $row['HinhAnh']; ?>" alt="Hình Ảnh"></td>
                                        <td>
                                            <form action="manage_books.php" method="post" class="d-inline">
                                                <input type="hidden" name="action" value="edit">
                                                <input type="hidden" name="sachID" value="<?php echo $row['SachID']; ?>">
                                                <button type="submit" class="btn btn-warning btn-sm">Sửa</button>
                                            </form>
                                            <form action="manage_books.php" method="post" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sách này?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="sachID" value="<?php echo $row['SachID']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                                            </form>

                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>