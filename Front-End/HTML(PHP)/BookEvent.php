<?php
session_start();
include("../../connection/connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id = intval($_POST['event_id']);
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    
    if (!isset($_SESSION['user_username'])) {
        echo "Please log in to book the event.";
        exit();
    }

    $user_username = $_SESSION['user_username'];

    // Fetch UserID from users table
    $user_sql = "SELECT UserID FROM users WHERE Username = ?";
    $user_stmt = $conn->prepare($user_sql);
    if ($user_stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $user_stmt->bind_param("s", $user_username);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user_stmt->close();

    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        $user_id = $user['UserID'];
        echo "User ID: " . htmlspecialchars($user_id) . "<br>"; // Debugging output
    } else {
        echo "User not found.";
        exit();
    }

    $sql = "INSERT INTO eventbooking (EventID, UserID, Name, Email, Phone) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("issss", $event_id, $user_id, $name, $email, $phone);

    if ($stmt->execute()) {
        echo "Booking successful!";
        header("Location: eventDetail.php?event_id=" . $event_id);
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }

    $stmt->close();
}

$conn->close();
?>
