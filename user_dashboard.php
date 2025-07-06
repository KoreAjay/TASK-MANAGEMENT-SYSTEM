<?php
// user_dashboard.php - User Dashboard

session_start();
require_once 'db/connection.php'; // Include database connection

// Check if user is logged in and is a regular user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: index.php'); // Redirect to login if not authenticated or not user
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Initialize flash message for displaying success/error after actions
$flash_message = '';
$flash_message_type = '';
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    $flash_message_type = $_SESSION['flash_message_type'] ?? 'success'; // Default to success
    unset($_SESSION['flash_message']); // Clear message after display
    unset($_SESSION['flash_message_type']);
}

// Handle task actions (Accept, Reject, Complete, Transfer)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['task_id'])) {
    $taskId = $_POST['task_id'];
    $action = $_POST['action'];
    $response_message = '';
    $response_type = 'success'; // Default type for messages

    try {
        if ($action === 'accept') {
            // Logic: Pending -> In Progress
            $stmt = $pdo->prepare("UPDATE tasks SET status = 'In Progress' WHERE task_id = ? AND assigned_to_user_id = ? AND status = 'Pending'");
            $stmt->execute([$taskId, $user_id]);
            $response_message = "Task " . htmlspecialchars($taskId) . " accepted. Status changed to In Progress.";
        } elseif ($action === 'reject') {
            // Logic: Pending -> Rejected, and unassign from current user
            $reason = $_POST['reason'] ?? 'No reason provided.';
            $pdo->beginTransaction();
            $stmt_update = $pdo->prepare("UPDATE tasks SET status = 'Rejected', assigned_to_user_id = NULL WHERE task_id = ? AND assigned_to_user_id = ? AND status = 'Pending'");
            $stmt_update->execute([$taskId, $user_id]);
            $stmt_insert = $pdo->prepare("INSERT INTO task_rejections (task_id, rejected_by_user_id, reason) VALUES (?, ?, ?)");
            $stmt_insert->execute([$taskId, $user_id, $reason]);
            $pdo->commit();
            $response_message = "Task " . htmlspecialchars($taskId) . " rejected.";
        } elseif ($action === 'complete') {
            // Logic: In Progress -> Completed
            $stmt = $pdo->prepare("UPDATE tasks SET status = 'Completed' WHERE task_id = ? AND assigned_to_user_id = ? AND status = 'In Progress'");
            $stmt->execute([$taskId, $user_id]);
            $response_message = "Task " . htmlspecialchars($taskId) . " completed.";
        } elseif ($action === 'transfer') {
            // Logic: In Progress -> Transferred, and reassign to new user
            $new_department_id = $_POST['new_department_id'] ?? null;
            $new_assigned_to_user_id = $_POST['new_assigned_to_user_id'] ?? null;
            $reason = $_POST['reason'] ?? 'No reason provided.';

            if ($new_department_id && $new_assigned_to_user_id) {
                $pdo->beginTransaction();
                // Get current department for logging in transfer history
                $stmt_current_dept = $pdo->prepare("SELECT current_department_id FROM tasks WHERE task_id = ?");
                $stmt_current_dept->execute([$taskId]);
                $current_department_id_for_log = $stmt_current_dept->fetchColumn();


                // Update task for transfer
                // Set the status of the task to 'Pending' for the new assignee, and update assignee details.
                // The current user will no longer see it as their 'In Progress' or 'Pending' task.
                $stmt_update_new_assignee_status = $pdo->prepare("UPDATE tasks SET status = 'Pending', assigned_to_user_id = ?, current_department_id = ? WHERE task_id = ? AND assigned_to_user_id = ? AND status = 'In Progress'");
                $stmt_update_new_assignee_status->execute([$new_assigned_to_user_id, $new_department_id, $taskId, $user_id]);

                // Log the transfer in task_transfers table
                $stmt_insert = $pdo->prepare("INSERT INTO task_transfers (task_id, transferred_by_user_id, transferred_from_user_id, transferred_to_user_id, transferred_from_department_id, transferred_to_department_id, reason) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt_insert->execute([$taskId, $user_id, $user_id, $new_assigned_to_user_id, $current_department_id_for_log, $new_department_id, $reason]);
                $pdo->commit();
                $response_message = "Task " . htmlspecialchars($taskId) . " transferred to new person. They will see it as 'Pending'.";
            } else {
                $response_message = "Transfer failed: New department and assignee are required.";
                $response_type = 'error';
            }
        }
        $_SESSION['flash_message'] = $response_message;
        $_SESSION['flash_message_type'] = $response_type;
        header('Location: user_dashboard.php'); // Redirect to refresh page and show message
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Task action error: " . $e->getMessage());
        $_SESSION['flash_message'] = "An error occurred during task action: " . $e->getMessage();
        $_SESSION['flash_message_type'] = 'error';
        header('Location: user_dashboard.php');
        exit();
    }
}


// Fetch user's tasks
$my_tasks = [];
$pending_my_acceptance = 0;
$my_in_progress_tasks = 0;
$my_completed_tasks = 0;

try {
    // Fetch tasks explicitly assigned to the current user that are 'Pending' or 'In Progress' for *them*
    // Tasks that were 'Transferred' by *this* user will no longer show as 'Pending' or 'In Progress' for them.
    $stmt_my_tasks = $pdo->prepare("SELECT task_id, task_name, description, priority, status, start_date, deadline_date FROM tasks WHERE assigned_to_user_id = ? AND status IN ('Pending', 'In Progress') ORDER BY deadline_date ASC");
    $stmt_my_tasks->execute([$user_id]);
    $my_tasks = $stmt_my_tasks->fetchAll();

    foreach ($my_tasks as $task) {
        if ($task['status'] === 'Pending') {
            $pending_my_acceptance++;
        } elseif ($task['status'] === 'In Progress') {
            $my_in_progress_tasks++;
        }
    }
    // Also count completed tasks separately if needed for dashboard summary
    $stmt_my_completed_tasks = $pdo->prepare("SELECT COUNT(*) FROM tasks WHERE assigned_to_user_id = ? AND status = 'Completed'");
    $stmt_my_completed_tasks->execute([$user_id]);
    $my_completed_tasks = $stmt_my_completed_tasks->fetchColumn();

} catch (PDOException $e) {
    error_log("User Dashboard data fetch error: " . $e->getMessage());
    $my_tasks = [];
    $flash_message = "Error loading your tasks: " . $e->getMessage();
    $flash_message_type = 'error';
}

// Fetch departments for transfer modal
$departments = [];
try {
    $stmt_deps = $pdo->query("SELECT department_id, department_name FROM departments ORDER BY department_name");
    $departments = $stmt_deps->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching departments for transfer modal: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - NEXUS TaskHub</title>
    <style>
        /* Inline CSS for User Dashboard */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: #f0f2f5;
            color: #333;
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: #3f51b5; /* Deeper blue for user panel */
            color: white;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: sticky;
            top: 0;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #ffffff;
            font-size: 24px;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
            flex-grow: 1;
        }
        .sidebar ul li {
            margin-bottom: 15px;
        }
        .sidebar ul li a {
            display: block;
            color: #e8eaf6;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: #4b62d8;
        }
        .main-content {
            flex-grow: 1;
            padding: 30px;
            overflow-y: auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .header h1 {
            margin: 0;
            color: #3f51b5;
        }
        .welcome-message {
            font-size: 18px;
            color: #555;
        }
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        .card {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: transform 0.2s ease;
        }
        .card:hover {
                transform: translateY(-5px);
            }
        .card h3 {
            margin-top: 0;
            color: #7f8c8d;
            font-size: 16px;
        }
        .card .value {
            font-size: 3em;
            font-weight: bold;
            color: #673ab7; /* Purple for user cards */
            margin-top: 10px;
        }
        .card.pending-mine .value { color: #ff5722; }
        .card.in-progress-mine .value { color: #ffc107; }
        .card.completed-mine .value { color: #4caf50; }

        .my-tasks {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }
        .my-tasks h3 {
            margin-top: 0;
            color: #3f51b5;
            margin-bottom: 20px;
            font-size: 20px;
        }
        .my-tasks table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .my-tasks table th,
        .my-tasks table td {
            border: 1px solid #eee;
            padding: 12px 15px;
            text-align: left;
            font-size: 15px;
        }
        .my-tasks table th {
            background-color: #f8f8f8;
            font-weight: bold;
            color: #555;
        }
        .my-tasks table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
        }
        .status-badge.pending { background-color: #e74c3c; }
        .status-badge.in-progress { background-color: #f39c12; }
        .status-badge.completed { background-color: #27ae60; }
        .status-badge.rejected { background-color: #c0392b; }
        .status-badge.transferred { background-color: #8e44ad; } /* For tasks *currently* being viewed as "transferred" (not pending for current user) */

        .action-button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            margin-right: 5px;
            transition: background-color 0.2s ease;
            white-space: nowrap; /* Prevent button text from wrapping */
        }
        .action-button.accept { background-color: #28a745; }
        .action-button.accept:hover { background-color: #218838; }
        .action-button.reject { background-color: #dc3545; }
        .action-button.reject:hover { background-color: #c82333; }
        .action-button.complete { background-color: #17a2b8; }
        .action-button.complete:hover { background-color: #138496; }
        .action-button.transfer { background-color: #6c757d; }
        .action-button.transfer:hover { background-color: #5a6268; }
        .action-button.view { background-color: #007bff; }
        .action-button.view:hover { background-color: #0056b3; }

        .message {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            font-size: 14px;
            display: <?php echo empty($flash_message) ? 'none' : 'block'; ?>;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Modal styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            justify-content: center;
            align-items: center; /* Center horizontally and vertically */
        }
        .modal-content {
            background-color: #fefefe;
            margin: auto; /* For older browsers or when not using flex */
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
        }
        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
        }
        .modal-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .modal-form textarea, .modal-form select {
            width: calc(100% - 20px); /* Account for padding */
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .modal-form button {
            background-color: #3f51b5;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.2s ease;
        }
        .modal-form button:hover {
            background-color: #303f9f;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }
            .sidebar ul {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                margin-top: 10px;
            }
            .sidebar ul li {
                margin: 5px 10px;
            }
            .main-content {
                padding: 20px;
            }
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            .welcome-message {
                margin-top: 10px;
            }
            .my-tasks table {
                display: block;
                overflow-x: auto; /* Enable horizontal scroll for tables */
                white-space: nowrap; /* Prevent content wrap in cells */
            }
            .my-tasks table th,
            .my-tasks table td {
                padding: 8px 10px;
                font-size: 14px;
            }
            .action-button {
                padding: 6px 8px;
                font-size: 0.8em;
                margin-bottom: 5px; /* Stack buttons on small screens */
                display: inline-block; /* Allow stacking */
            }
            .modal-content {
                width: 95%;
            }
        }
        @media (max-width: 480px) {
            .sidebar h2 {
                font-size: 20px;
            }
            .header h1 {
                font-size: 24px;
            }
            .card .value {
                font-size: 2.5em;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>NEXUS User</h2>
        <ul>
            <li><a href="user_dashboard.php" class="active">My Dashboard</a></li>
            <li><a href="create_new_task.php">Create New Task</a></li>
            <li><a href="index.php?logout=true" style="margin-top: 30px;">Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="header">
            <h1>My Dashboard</h1>
            <span class="welcome-message">Welcome, <?php echo htmlspecialchars($username); ?>!</span>
        </div>

        <?php if (!empty($flash_message)): ?>
            <div class="message <?php echo $flash_message_type; ?>"><?php echo htmlspecialchars($flash_message); ?></div>
        <?php endif; ?>

        <div class="dashboard-cards">
            <div class="card pending-mine">
                <h3>Pending My Acceptance</h3>
                <div class="value"><?php echo $pending_my_acceptance; ?></div>
            </div>
            <div class="card in-progress-mine">
                <h3>My In Progress Tasks</h3>
                <div class="value"><?php echo $my_in_progress_tasks; ?></div>
            </div>
            <div class="card completed-mine">
                <h3>My Completed Tasks</h3>
                <div class="value"><?php echo $my_completed_tasks; ?></div>
            </div>
        </div>

        <div class="my-tasks">
            <h3>My Tasks</h3>
            <?php if (!empty($my_tasks)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Task ID</th>
                        <th>Task Name</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Deadline</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_tasks as $task): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['task_id']); ?></td>
                        <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                        <td><?php echo htmlspecialchars($task['priority']); ?></td>
                        <td><span class="status-badge <?php echo strtolower(str_replace(' ', '-', $task['status'])); ?>"><?php echo htmlspecialchars($task['status']); ?></span></td>
                        <td><?php echo htmlspecialchars($task['deadline_date']); ?></td>
                        <td>
                            <?php if ($task['status'] === 'Pending'): ?>
                                <button class="action-button accept" data-task-id="<?php echo htmlspecialchars($task['task_id']); ?>">Accept</button>
                                <button class="action-button reject" data-task-id="<?php echo htmlspecialchars($task['task_id']); ?>">Reject</button>
                            <?php elseif ($task['status'] === 'In Progress'): ?>
                                <button class="action-button complete" data-task-id="<?php echo htmlspecialchars($task['task_id']); ?>">Complete</button>
                                <button class="action-button transfer" data-task-id="<?php echo htmlspecialchars($task['task_id']); ?>">Transfer</button>
                            <?php else: /* For 'Completed', 'Rejected' (by current user), or 'Transferred' (by current user, but task status for new assignee is 'Pending') */ ?>
                                <button class="action-button view">View Details</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>No tasks currently assigned to you or in your active workflow.</p>
            <?php endif; ?>
        </div>
    </div>

    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Reject Task</h2>
            <form id="rejectForm" class="modal-form" method="POST" action="user_dashboard.php">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="task_id" id="rejectTaskId">
                <label for="rejectReason">Reason for Rejection:</label>
                <textarea id="rejectReason" name="reason" rows="4" required></textarea>
                <button type="submit">Submit Rejection</button>
            </form>
        </div>
    </div>

    <div id="transferModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Transfer Task</h2>
            <form id="transferForm" class="modal-form" method="POST" action="user_dashboard.php">
                <input type="hidden" name="action" value="transfer">
                <input type="hidden" name="task_id" id="transferTaskId">
                <label for="newDepartment">New Department:</label>
                <select id="newDepartment" name="new_department_id" required>
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept['department_id']); ?>"><?php echo htmlspecialchars($dept['department_name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="newAssignee">Allocate To Person:</label>
                <select id="newAssignee" name="new_assigned_to_user_id" required>
                    <option value="">Select Person</option>
                    </select>
                <label for="transferReason">Reason for Transfer (Optional):</label>
                <textarea id="transferReason" name="reason" rows="4"></textarea>
                <button type="submit">Transfer Task</button>
            </form>
        </div>
    </div>

    <script>
        // Inline JavaScript for User Dashboard
        console.log("User Dashboard loaded.");

        // Dynamic welcome message based on time of day
        function getGreeting() {
            const hour = new Date().getHours();
            if (hour < 12) {
                return "Good Morning";
            } else if (hour < 18) {
                return "Good Afternoon";
            } else {
                return "Good Evening";
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            const welcomeSpan = document.querySelector('.welcome-message');
            if (welcomeSpan) {
                welcomeSpan.textContent = getGreeting() + ", <?php echo htmlspecialchars($username); ?>!";
            }

            // --- Modal Logic ---
            const rejectModal = document.getElementById('rejectModal');
            const transferModal = document.getElementById('transferModal');
            const closeButtons = document.querySelectorAll('.close-button');
            let currentTaskId = null; // To store the task_id clicked

            document.querySelectorAll('.action-button').forEach(button => {
                button.addEventListener('click', function() {
                    currentTaskId = this.dataset.taskId;
                    let actionType = ''; // Initialize actionType

                    if (this.classList.contains('accept')) {
                        actionType = 'accept';
                    } else if (this.classList.contains('reject')) {
                        actionType = 'reject';
                    } else if (this.classList.contains('complete')) {
                        actionType = 'complete';
                    } else if (this.classList.contains('transfer')) {
                        actionType = 'transfer';
                    } else {
                        // Handle 'view' or other non-actionable buttons if needed
                        console.log('View Details clicked for task ' + currentTaskId);
                        return; // Exit if not an action button
                    }

                    if (actionType === 'accept' || actionType === 'complete') {
                        // Directly submit the form for accept/complete
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = 'user_dashboard.php';

                        const actionInput = document.createElement('input');
                        actionInput.type = 'hidden';
                        actionInput.name = 'action';
                        actionInput.value = actionType;
                        form.appendChild(actionInput);

                        const taskIdInput = document.createElement('input');
                        taskIdInput.type = 'hidden';
                        taskIdInput.name = 'task_id';
                        taskIdInput.value = currentTaskId;
                        form.appendChild(taskIdInput);

                        document.body.appendChild(form);
                        form.submit();

                    } else if (actionType === 'reject') {
                        document.getElementById('rejectTaskId').value = currentTaskId;
                        rejectModal.style.display = 'flex'; // Use flex for centering
                    } else if (actionType === 'transfer') {
                        document.getElementById('transferTaskId').value = currentTaskId;
                        transferModal.style.display = 'flex'; // Use flex for centering
                        // Optionally fetch users for the default selected department here or on selection change
                        loadUsersForDepartment(document.getElementById('newDepartment').value);
                    }
                });
            });

            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    rejectModal.style.display = 'none';
                    transferModal.style.display = 'none';
                });
            });

            window.addEventListener('click', function(event) {
                if (event.target === rejectModal) {
                    rejectModal.style.display = 'none';
                }
                if (event.target === transferModal) {
                    transferModal.style.display = 'none';
                }
            });

            // --- Dynamic User Loading for Transfer Modal ---
            const newDepartmentSelect = document.getElementById('newDepartment');
            const newAssigneeSelect = document.getElementById('newAssignee');

            newDepartmentSelect.addEventListener('change', function() {
                loadUsersForDepartment(this.value);
            });

            function loadUsersForDepartment(departmentId) {
                newAssigneeSelect.innerHTML = '<option value="">Loading...</option>';
                if (!departmentId) {
                    newAssigneeSelect.innerHTML = '<option value="">Select Person</option>';
                    return;
                }

                // Make a request to a PHP endpoint to get users by department
                // This assumes 'get_users_by_department.php' is in the same directory as 'user_dashboard.php'
                fetch('get_users_by_department.php?department_id=' + departmentId)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(users => {
                        newAssigneeSelect.innerHTML = '<option value="">Select Person</option>';
                        if (users.length > 0) {
                            users.forEach(user => {
                                const option = document.createElement('option');
                                option.value = user.user_id;
                                option.textContent = user.full_name;
                                newAssigneeSelect.appendChild(option);
                            });
                        } else {
                            newAssigneeSelect.innerHTML = '<option value="">No users found in this department</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching users:', error);
                        newAssigneeSelect.innerHTML = '<option value="">Error loading persons</option>';
                    });
            }
        });
    </script>
</body>
</html>