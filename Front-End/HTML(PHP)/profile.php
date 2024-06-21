<?php
session_start();
include("../../connection/connection.php");

// Check if the user is logged in
if (!isset($_SESSION['user_username'])) {
    header("Location: SignIn.php"); // Redirect to login page if not logged in
    exit();
}

// Fetch user details
$user_username = $_SESSION['user_username'];

// Handle form submission for updating username or fullname
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_username'])) {
        $new_username = htmlspecialchars($_POST['new_username']);
        
        // Update username in database
        $update_sql = "UPDATE users SET Username = ? WHERE Username = ?";
        $update_stmt = $conn->prepare($update_sql);
        if ($update_stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }
        $update_stmt->bind_param("ss", $new_username, $user_username);
        
        if ($update_stmt->execute()) {
            // Update session variable with new username
            $_SESSION['user_username'] = $new_username;
            $user_username = $new_username; // Update local variable as well
            echo '<script>alert("Username updated successfully.");</script>';
        } else {
            echo '<script>alert("Failed to update username.");</script>';
        }
        
        $update_stmt->close();
    } elseif (isset($_POST['update_fullname'])) {
        $new_fullname = htmlspecialchars($_POST['new_fullname']);
        
        // Update fullname in database
        $update_sql = "UPDATE users SET FullName = ? WHERE Username = ?";
        $update_stmt = $conn->prepare($update_sql);
        if ($update_stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($conn->error));
        }
        $update_stmt->bind_param("ss", $new_fullname, $user_username);
        
        if ($update_stmt->execute()) {
            echo '<script>alert("Fullname updated successfully.");</script>';
        } else {
            echo '<script>alert("Failed to update fullname.");</script>';
        }
        
        $update_stmt->close();
    } elseif (isset($_POST['logout'])) {
        session_destroy();
        header("Location: SignIn.php");
        exit();
    }
}

// Fetch events liked or commented by the user
$liked_events_sql = "SELECT e.EventID, e.EventName, e.EventDateTime, e.Location
                     FROM events e
                     JOIN UserLike ul ON e.EventID = ul.EventID
                     WHERE ul.UserUsername = ?";
$liked_stmt = $conn->prepare($liked_events_sql);
if ($liked_stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$liked_stmt->bind_param("s", $user_username);
$liked_stmt->execute();
$liked_result = $liked_stmt->get_result();
$liked_stmt->close();

$commented_events_sql = "SELECT e.EventID, e.EventName, e.EventDateTime, e.Location
                         FROM events e
                         JOIN EventComment ec ON e.EventID = ec.EventID
                         WHERE ec.UserUsername = ?";
$commented_stmt = $conn->prepare($commented_events_sql);
if ($commented_stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$commented_stmt->bind_param("s", $user_username);
$commented_stmt->execute();
$commented_result = $commented_stmt->get_result();
$commented_stmt->close();

// Fetch booked events by the user
// Fetch booked events by the user (assuming UserID instead of UserUsername)
// $booked_events_sql = "SELECT e.EventID, e.EventName, e.EventDateTime, e.Location
//                       FROM events e
//                       JOIN EventBooking eb ON e.EventID = eb.EventID
//                       WHERE eb.UserID = ?";
// $booked_stmt = $conn->prepare($booked_events_sql);
// if ($booked_stmt === false) {
//     die('Prepare failed: ' . htmlspecialchars($conn->error));
// }
// $booked_stmt->bind_param("s", $user_username);
// $booked_stmt->execute();
// $booked_result = $booked_stmt->get_result();
// $booked_stmt->close();


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="../CSS/Profile.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1>Welcome, <?php echo htmlspecialchars($user_username); ?></h1>
            <form class="logout-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <button type="submit" name="logout" class="logout-button">Logout</button>
            </form>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <label for="new_username">Update Username:</label>
                <input type="text" id="new_username" name="new_username" required>
                <button type="submit" name="update_username">Update</button>
            </form>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <label for="new_fullname">Update Fullname:</label>
                <input type="text" id="new_fullname" name="new_fullname" required>
                <button type="submit" name="update_fullname">Update</button>
            </form>
        </div>
        
        <div class="liked-events">
            <h2>Events Liked:</h2>
            <div class="event-list">
                <?php while ($liked_event = $liked_result->fetch_assoc()): ?>
                    <div class="event">
                        <h3><?php echo htmlspecialchars($liked_event['EventName']); ?></h3>
                        <p><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($liked_event['EventDateTime']); ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($liked_event['Location']); ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <div class="commented-events">
            <h2>Events Commented:</h2>
            <div class="event-list">
                <?php while ($commented_event = $commented_result->fetch_assoc()): ?>
                    <div class="event">
                        <h3><?php echo htmlspecialchars($commented_event['EventName']); ?></h3>
                        <p><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($commented_event['EventDateTime']); ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($commented_event['Location']); ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- <div class="booked-events">
            <h2>Events Booked:</h2>
            <div class="event-list">
                <?php while ($booked_event = $booked_result->fetch_assoc()): ?>
                    <div class="event">
                        <h3><?php echo htmlspecialchars($booked_event['EventName']); ?></h3>
                        <p><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($booked_event['EventDateTime']); ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($booked_event['Location']); ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div> -->
    </div>
</body>
</html>
