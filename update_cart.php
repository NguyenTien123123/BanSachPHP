<?php
session_start();
include 'db_connect.php'; // Đảm bảo rằng file này chứa thông tin kết nối đến CSDL

if (!isset($_SESSION['userid'])) {
    echo "Not logged in";
    exit;
}

$userID = $_SESSION['userid'];
$action = $_POST['action'];
$bookId = $_POST['bookId'];
$quantity = isset($_POST['quantity']) ? $_POST['quantity'] : 1;

switch ($action) {
    case 'update':
        if ($quantity < 1) {
            echo "Quantity must be at least 1";
            exit;
        }
        $sql = "UPDATE giohang SET SoLuong = ? WHERE ID = ? AND SachID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $quantity, $userID, $bookId);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo $stmt->insert_id;
        } else {
            echo "No changes made";
        }
        $stmt->close();
        break;
    case 'delete':
        $sql = "DELETE FROM giohang WHERE ID = ? AND SachID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userID, $bookId);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo "Deleted";
        } else {
            echo "Error deleting";
        }
        $stmt->close();
        break;
    default:
        echo "Invalid action";
}

$conn->close();
?>

<?php
session_start();
include 'db_connect.php'; // Ensure the database connection is included

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['userid'])) {
        echo 'error: not_logged_in'; // Specific error message
        exit;
    }

    $orderID = $_POST['id'];
    $newAddress = $_POST['address'];
    $userID = $_SESSION['userid'];

    // Update the delivery address
    $sql = "UPDATE donhang SET DiaChiGiaoHang = ? WHERE DHID = ? AND ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $newAddress, $orderID, $userID);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo 'success'; // Send 'success' if the update was successful
        } else {
            echo 'no_change'; // If no rows were affected, send 'no_change'
        }
    } else {
        echo 'error: ' . $stmt->error; // Provide detailed error message
    }

    $stmt->close();
} else {
    echo 'error: invalid_request'; // Provide more detail about the error
}

$conn->close();
?>
