<?php
session_start();
include("../../connection/connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['user_username'])) {
        echo "Please log in to comment.";
        exit();
    }

    $event_id = intval($_POST['event_id']);
    $username = $_SESSION['user_username'];
    $comment = htmlspecialchars($_POST['comment']);

    if (!empty($comment)) {
        // Verify if the username exists in the users table
        $user_check_sql = "SELECT * FROM users WHERE Username = ?";
        $user_stmt = $conn->prepare($user_check_sql);
        $user_stmt->bind_param("s", $username);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();

        if ($user_result->num_rows > 0) {
            $sql = "INSERT INTO eventcomment (EventID, Comment, UserUsername, CommentDateTime) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iss", $event_id, $comment, $username);

            if ($stmt->execute()) {
                echo "Comment added successfully.";
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Error: Username does not exist.";
        }

        $user_stmt->close();
    } else {
        echo "Please fill in all fields.";
    }

    $conn->close();
    header("Location: EventDetail.php?event_id=$event_id");
    exit();
} else {
    echo "Invalid request method.";
}
?>
