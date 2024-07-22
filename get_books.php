<?php
include 'db_connect.php';

$sql = "SELECT * FROM sach";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $books = array();
    while ($row = $result->fetch_assoc()) {
        array_push($books, $row);
    }
    echo json_encode($books);
} else {
    echo "0 results";
}

$conn->close();
