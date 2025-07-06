<?php
// get_users_by_department.php
// This file serves as an AJAX endpoint.

session_start();
require_once 'db/connection.php'; // Path to db connection

// Ensure only logged-in users (or specifically admins/users) can access this
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$department_id = $_GET['department_id'] ?? null;

header('Content-Type: application/json');

if (!$department_id) {
    echo json_encode(['error' => 'Department ID is required.']);
    exit();
}

try {
    // Fetch only regular users in the specified department
    $stmt = $pdo->prepare("SELECT user_id, full_name FROM users WHERE department_id = ? AND role = 'user' AND is_active = TRUE ORDER BY full_name");
    $stmt->execute([$department_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users);

} catch (PDOException $e) {
    error_log("Error fetching users by department: " . $e->getMessage());
    echo json_encode(['error' => 'Database error fetching users.']);
}
?>