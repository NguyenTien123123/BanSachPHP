<?php
session_start();
include 'db_connect.php'; // Ensure this file contains your DB connection

if (!isset($_SESSION['userid'])) {
    echo "Not logged in";
    exit;
}

$userID = $_SESSION['userid'];
$action = isset($_POST['action']) ? $_POST['action'] : '';
$bookId = isset($_POST['bookId']) ? (int)$_POST['bookId'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($bookId <= 0 || $quantity < 0) {
    echo "Invalid input";
    exit;
}

switch ($action) {
    case 'update':
        if ($quantity < 1) {
            echo "Quantity must be at least 1";
            exit;
        }

        // Check if the item exists
        $checkSql = "SELECT * FROM giohang WHERE ID = ? AND SachID = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("ii", $userID, $bookId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo "Item not found in cart";
            $stmt->close();
            exit;
        }

        // Update item quantity
        $updateSql = "UPDATE giohang SET SoLuong = ? WHERE ID = ? AND SachID = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("iii", $quantity, $userID, $bookId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo "Quantity updated";
        } else {
            echo "No changes made";
        }
        $stmt->close();
        break;

    case 'delete':
        // Check if the item exists
        $checkSql = "SELECT * FROM giohang WHERE ID = ? AND SachID = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("ii", $userID, $bookId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo "Item not found in cart";
            $stmt->close();
            exit;
        }

        // Delete item
        $deleteSql = "DELETE FROM giohang WHERE ID = ? AND SachID = ?";
        $stmt = $conn->prepare($deleteSql);
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
