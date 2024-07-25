<?php
session_start();
include 'db_connect.php';

// Thiết lập phân trang
$limit = 15; // Số sách mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Hiển thị thông báo từ phiên (session)
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-info">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']);
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$results = null;

// Tạo và thực thi truy vấn SQL tìm kiếm sách
if (!empty($search)) {
    $sql = "SELECT * FROM sach WHERE TenSach LIKE CONCAT('%', ?, '%') LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $search, $offset, $limit);
    $stmt->execute();
    $results = $stmt->get_result();
} else {
    // Nếu không có từ khóa tìm kiếm, lấy tất cả sách
    $sql = "SELECT * FROM sach LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $offset, $limit);
    $stmt->execute();
    $results = $stmt->get_result();
}

// Đếm tổng số sách để phân trang
$totalBooks = $conn->query("SELECT COUNT(*) as total FROM sach")->fetch_assoc()['total'];
$totalPages = ceil($totalBooks / $limit);

// Kiểm tra thông báo chưa xem
$soLuongThongBao = 0;
if (isset($_SESSION['userid'])) {
    $sql = "SELECT COUNT(*) AS SoLuong FROM ThongBao WHERE UserID = ? AND DaXem = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['userid']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $soLuongThongBao = $row['SoLuong'];
}

$cartItemCount = 0;
if (isset($_SESSION['userid'])) {
    $sql = "SELECT SUM(SoLuong) as total FROM giohang WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['userid']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $cartItemCount = $row['total'] ? $row['total'] : 0;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chủ</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        var isLoggedIn = <?php echo isset($_SESSION['loggedin']) && $_SESSION['loggedin'] ? 'true' : 'false'; ?>;
    </script>
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
            <a class="navbar-brand" href="#"><i class="fa fa-book" aria-hidden="true"></i> Bansach.com</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <form class="form-inline" method="get" id="searchForm">
                            <input class="form-control mr-sm-2" type="text" name="search" id="search" placeholder="Tìm kiếm sách..." aria-label="Search books" value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-outline-success my-2 my-sm-0" type="submit" aria-label="Search"><i class="fa fa-search" aria-hidden="true"></i></button>
                        </form>
                    </li>
                </ul>
                <ul class="navbar-nav ml-auto">
                    <?php if (isset($_SESSION['userid'])) : ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-bell"></i> Thông báo
                                <?php if ($soLuongThongBao > 0) : ?>
                                    <span class="notification-dot"></span>
                                <?php endif; ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right notification-popup" id="notificationPopup">
                                <h6 class="dropdown-header">Thông báo</h6>
                                <div id="notificationContent">
                                    <!-- Thông báo sẽ được tải vào đây -->
                                </div>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-center" href="#">Xem tất cả thông báo</a>
                            </div>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="user_cart.php">
                            <i class="fa fa-shopping-cart"></i> Giỏ hàng
                            <span class="badge badge-pill badge-danger"><?php echo $cartItemCount; ?></span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) : ?>
                            <a class="nav-link dropdown-toggle" href="#" id="accountDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="accountDropdown">
                                <a class="dropdown-item" href="user_order.php"> Đơn hàng</a>
                                <a class="dropdown-item" href="user_profile.php"> Cập nhật thông tin</a>
                                <a class="dropdown-item" href="user_changePassword.php"> Đổi mật khẩu</a>
                                <a class="dropdown-item" href="user_logout.php">Đăng xuất</a>
                            </div>
                        <?php else : ?>
                            <a class="nav-link" href="user_login.php"><i class="fa fa-user"></i> Đăng nhập/Đăng ký</a>
                        <?php endif; ?>
                    </li>
                    <li class="nav-item">
                        <select class="form-control small-select">
                            <option value="vie">VIE</option>
                            <option value="eng">ENG</option>
                        </select>
                    </li>
                </ul>
            </div>
        </nav>
        </nav>
        <div id="carouselExampleIndicators" class="carousel slide container" data-ride="carousel">
            <ol class="carousel-indicators">
                <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
            </ol>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="images/banner1.jpg" class="d-block w-100" alt="Banner 1">
                </div>
                <div class="carousel-item">
                    <img src="images/banner2.jpg" class="d-block w-100" alt="Banner 2">
                </div>
                <div class="carousel-item">
                    <img src="images/banner3.jpg" class="d-block w-100" alt="Banner 3">
                </div>
            </div>
            <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
        <div class="container-fluid mt-4">
            <div class="row">
                <div class="col text-center">
                    <button class="btn btn-outline-primary mb-2 filter-btn" data-filter="all">
                        <i class="fa fa-graduation-cap" aria-hidden="true"></i>
                        Tất cả
                    </button>
                </div>
                <div class="col text-center">
                    <button class="btn btn-outline-primary mb-2 filter-btn" data-filter="Học Tập">
                        <i class="fa fa-book" aria-hidden="true"></i>
                        Học Tập
                    </button>
                </div>
                <div class="col text-center">
                    <button class="btn btn-outline-primary mb-2 filter-btn" data-filter="Truyện Tranh">
                        <i class="fa fa-book-open" aria-hidden="true"></i>
                        Truyện Tranh
                    </button>
                </div>
                <div class="col text-center">
                    <button class="btn btn-outline-primary mb-2 filter-btn" data-filter="Văn Học">
                        <i class="fa fa-book-reader" aria-hidden="true"></i>
                        Văn Học
                    </button>
                </div>
                <div class="col text-center">
                    <button class="btn btn-outline-primary mb-2 filter-btn" data-filter="Tiểu Thuyết">
                        <i class="fa fa-scroll" aria-hidden="true"></i>
                        Tiểu Thuyết
                    </button>
                </div>
                <div class="col text-center">
                    <button class="btn btn-outline-primary mb-2 filter-btn" data-filter="Lịch Sử">
                        <i class="fa fa-landmark" aria-hidden="true"></i>
                        Lịch Sử
                    </button>
                </div>
            </div>
        </div>
        <div class="container-fluid mt-4">
            <div class="row book-container">
                <?php
                if ($results && $results->num_rows > 0) {
                    while ($row = $results->fetch_assoc()) {
                        $sachID = $row['SachID'];

                        // Truy vấn số sao trung bình từ bảng ratings chỉ với các đánh giá đã được phê duyệt
                        $rating_sql = "SELECT AVG(r.rating) as avg_rating FROM ratings r WHERE r.SachID = ? AND r.status = 'Approved'";
                        $rating_stmt = $conn->prepare($rating_sql);
                        $rating_stmt->bind_param("i", $sachID);
                        $rating_stmt->execute();
                        $rating_result = $rating_stmt->get_result();
                        $rating_data = $rating_result->fetch_assoc();
                        $average_rating = round($rating_data['avg_rating'], 1);

                ?>
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12 mb-4 book-item" data-category="<?= htmlspecialchars($row['TheLoai']) ?>">
                            <div class="card h-100">
                                <div class="card-body d-flex flex-column">
                                    <a href="user_book_detail.php?SachID=<?= $sachID ?>" class="card-link">
                                        <img src="<?= htmlspecialchars($row['HinhAnh']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['TenSach']) ?>">
                                        <h5 class="card-title"><?= htmlspecialchars($row['TenSach']) ?></h5>
                                        <p class="card-text"><?= number_format($row['GiaBan']) ?>đ</p>
                                    </a>
                                    <div class="mt-auto d-flex justify-content-between align-items-center">
                                        <button class="btn btn-secondary" style="width: 40px; height: 40px;" onclick="event.preventDefault(); addToCart(<?= $sachID ?>);"><i class="fa fa-shopping-cart"></i></button>
                                        <div class="rating text-right">
                                            <p class="average-rating"><?php echo $average_rating; ?> ★</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                <?php
                    }
                } else {
                    echo "<p>Không tìm thấy sách nào.</p>";
                }
                ?>
            </div>
        </div>

        <!-- Liên kết phân trang -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1) : ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?><?= !empty($search) ? "&search=" . htmlspecialchars($search) : '' ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= !empty($search) ? "&search=" . htmlspecialchars($search) : '' ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages) : ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?><?= !empty($search) ? "&search=" . htmlspecialchars($search) : '' ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <footer class="pt-4">
            <div class="container">
                <div class="row">
                    <div class="col d-flex mb-4">
                        <div class="flex-fill p-3">
                            <h5 class="text-uppercase text-highlight">Bansach.com</h5>
                            <p>Chào mừng bạn đến với Bansach.com,<br>nơi bạn có thể tìm thấy hàng ngàn đầu sách thuộc mọi thể loại.</p>
                        </div>
                    </div>
                    <div class="col d-flex mb-4">
                        <div class="flex-fill p-3">
                            <h5 class="text-uppercase text-highlight">Liên kết</h5>
                            <ul class="list-unstyled">
                                <li><a href="Index.php" class="text-white">Trang chủ</a></li>
                                <li><a href="#" class="text-white">Giới thiệu</a></li>
                                <li><a href="#" class="text-white">Liên hệ</a></li>
                                <li><a href="#" class="text-white">Điều khoản dịch vụ</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col d-flex mb-4">
                        <div class="flex-fill p-3">
                            <h5 class="text-uppercase text-highlight">Liên hệ</h5>
                            <ul class="list-unstyled">
                                <li>Email: tienshop@gmail.com</li>
                                <li>Điện thoại: 039 210 9580</li>
                                <li>Địa chỉ: Xã Chí Minh, huyện Tứ Kỳ, tỉnh Hải Dương.</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col d-flex mb-4">
                        <div class="flex-fill p-3">
                            <h5 class="text-uppercase text-highlight">Theo dõi chúng tôi</h5>
                            <div class="d-flex social-links">
                                <a href="https://www.facebook.com/ntienyt" class="text-white" target="_blank"><i class="fab fa-facebook-f"></i></a>
                                <a href="https://www.twitter.com" class="text-white" target="_blank"><i class="fab fa-twitter"></i></a>
                                <a href="https://www.instagram.com" class="text-white" target="_blank"><i class="fab fa-instagram"></i></a>
                                <a href="https://www.linkedin.com" class="text-white" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                            </div>

                        </div>
                    </div>
                    <style>
                        .social-links a {
                            margin-right: 10px;
                            /* Điều chỉnh khoảng cách giữa các biểu tượng */
                            color: white;
                            /* Đảm bảo màu của biểu tượng */
                        }

                        .social-links a:last-child {
                            margin-right: 0;
                            /* Loại bỏ khoảng cách cho biểu tượng cuối cùng */
                        }

                        .card-body {
                            display: flex;
                            flex-direction: column;
                            height: 100%;
                            /* Đảm bảo rằng card-body có chiều cao đầy đủ */
                        }

                        .mt-auto {
                            margin-top: auto;
                            /* Đẩy các phần tử đến dưới cùng của card-body */
                        }

                        .rating {
                            font-size: 1.2rem;
                            color: #ffcc00;
                            /* Màu vàng cho sao */
                        }

                        .average-rating {
                            margin: 0;
                            /* Đảm bảo rằng không có khoảng cách không cần thiết */
                        }
                    </style>

                </div>
            </div>

            </div>
            </div>
            <div class="footer-bottom bg-highlight text-center py-3 mt-4">
                &copy; Bansach.com. Cảm ơn bạn đã ủng hộ
            </div>
        </footer>



    </header>

    <script>
        $(document).ready(function() {
            $("#search").keyup(function() {
                var query = $(this).val();
                if (query.length > 0) {
                    $.ajax({
                        url: "user_search_suggestions.php",
                        data: {
                            search: query
                        },
                        success: function(data) {
                            $("#suggestionBox").fadeIn();
                            $("#suggestionBox").html(data);
                        }
                    });
                } else {
                    $("#suggestionBox").fadeOut();
                    $("#suggestionBox").html("");
                }
            });

            $(document).on("click", "#suggestionBox li", function() {
                $("#search").val($(this).text());
                $("#suggestionBox").fadeOut();
                $("#searchForm").submit();
            });

            // Load notifications when the dropdown is clicked
            $('#notificationsDropdown').on('click', function() {
                if (isLoggedIn) {
                    $.ajax({
                        url: 'user_get_notifications.php',
                        method: 'GET',
                        success: function(data) {
                            $('#notificationContent').html(data);
                        }
                    });
                }
            });
        });

        function addToCart(sachID) {
            if (!isLoggedIn) {
                alert("Bạn cần đăng nhập để sử dụng chức năng này.");
                window.location.href = 'user_login.php';
                return;
            }
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "user_book_add_to_cart.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function() {
                if (this.status === 200) {
                    showNotification('Sách đã được thêm vào giỏ hàng!', 'success');
                }
            };
            xhr.send("sachID=" + sachID + "&quantity=1");
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.classList.add('notification', type);
            notification.textContent = message;
            document.body.appendChild(notification);
            notification.classList.add('show');

            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 500);
            }, 3000);
        }
    </script>
    <script src="scripts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const bookItems = document.querySelectorAll('.book-item');

            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const filter = this.getAttribute('data-filter');

                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');

                    bookItems.forEach(item => {
                        if (filter === 'all' || item.getAttribute('data-category') === filter) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>