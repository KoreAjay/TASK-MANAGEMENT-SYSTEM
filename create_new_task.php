<?php
// create_new_task.php - Create New Task Page

session_start();
require_once 'db/connection.php'; // Include database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Redirect to login if not authenticated
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$user_role = $_SESSION['role'];

// Initialize message variables
$message = '';
$message_type = ''; // 'success' or 'error'

// Fetch data for dropdowns
$categories = [];
$departments = [];
$users_in_departments = []; // To store users grouped by department for dynamic dropdown

try {
    // Fetch Task Categories
    $stmt_cat = $pdo->query("SELECT category_id, category_name FROM task_categories ORDER BY category_name");
    $categories = $stmt_cat->fetchAll();

    // Fetch Departments
    $stmt_dept = $pdo->query("SELECT department_id, department_name FROM departments ORDER BY department_name");
    $departments = $stmt_dept->fetchAll();

    // Fetch all users (only regular users for assignment) and group by department for JS
    $stmt_users = $pdo->query("SELECT u.user_id, u.full_name, u.department_id, d.department_name
                               FROM users u JOIN departments d ON u.department_id = d.department_id
                               WHERE u.role = 'user' AND u.is_active = TRUE ORDER BY u.full_name");
    $all_users = $stmt_users->fetchAll();

    // Group users by department for easier JavaScript handling
    foreach ($all_users as $user) {
        $users_in_departments[$user['department_id']][] = [
            'user_id' => $user['user_id'],
            'full_name' => $user['full_name']
        ];
    }

} catch (PDOException $e) {
    error_log("Error fetching dropdown data: " . $e->getMessage());
    $message = 'Error loading page data. Please try again later.';
    $message_type = 'error';
}

// Handle task creation form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_name = $_POST['task_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $category_id = $_POST['category_id'] ?? null;
    $start_date = $_POST['start_date'] ?? '';
    $deadline_date = $_POST['deadline_date'] ?? '';
    $department_id = $_POST['department_id'] ?? null;
    $allocated_to_user_id = $_POST['allocated_to_user_id'] ?? null;
    $priority = $_POST['priority'] ?? 'Medium';

    // Basic Validation
    if (empty($task_name) || empty($start_date) || empty($deadline_date) || empty($department_id) || empty($allocated_to_user_id)) {
        $message = 'Please fill in all required fields.';
        $message_type = 'error';
    } elseif (strtotime($start_date) > strtotime($deadline_date)) {
        $message = 'Deadline date cannot be before start date.';
        $message_type = 'error';
    } else {
        try {
            $pdo->beginTransaction();

            // Generate a unique Task ID (UUID-like)
            // For a true UUID v4 in PHP, you might use random_bytes() or a library.
            // For this example, we'll use uniqid() for a simple unique string.
            $task_id = uniqid('task_', true); // Generates something like 'task_654be0f0b4d458.74235312'

            $stmt = $pdo->prepare("INSERT INTO tasks (task_id, task_name, description, category_id, start_date, deadline_date, assigned_by_user_id, assigned_to_user_id, current_department_id, priority, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
            $stmt->execute([
                $task_id,
                $task_name,
                $description,
                $category_id,
                $start_date,
                $deadline_date,
                $user_id, // The user creating/assigning the task
                $allocated_to_user_id,
                $department_id,
                $priority
            ]);

            // --- Handle File Attachments (Simplified due to folder constraints) ---
            if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
                $attached_files = $_FILES['attachments'];
                $file_count = count($attached_files['name']);

                for ($i = 0; $i < $file_count; $i++) {
                    $file_name = basename($attached_files['name'][$i]);
                    $file_type = $attached_files['type'][$i];
                    $file_size_kb = round($attached_files['size'][$i] / 1024);
                    $tmp_name = $attached_files['tmp_name'][$i]; // Temporary path on server

                    // IMPORTANT:
                    // In a real application, you would move the file from $tmp_name
                    // to a permanent, secure location (e.g., an 'uploads/' folder,
                    // or cloud storage like AWS S3).
                    // Example: move_uploaded_file($tmp_name, "uploads/" . $file_name);
                    // The $file_path below would then be the path to that permanent location.
                    // Due to your strict folder constraints, we are not performing the actual move here.
                    $file_path_for_db = 'path/to/uploads/' . $file_name; // Placeholder path for DB

                    $stmt_attach = $pdo->prepare("INSERT INTO task_attachments (task_id, file_name, file_path, file_type, file_size_kb, uploaded_by_user_id) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_attach->execute([$task_id, $file_name, $file_path_for_db, $file_type, $file_size_kb, $user_id]);
                }
            }

            $pdo->commit();
            $message = 'Task "' . htmlspecialchars($task_name) . '" created successfully!';
            $message_type = 'success';
            // Clear form fields after successful submission (or redirect)
            $_POST = []; // Clears POST data to reset form
            // Redirect to dashboard after success
            // header('Location: user_dashboard.php');
            // exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Task creation error: " . $e->getMessage());
            $message = 'Error creating task: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Task - NEXUS TaskHub</title>
    <style>
        /* Inline CSS for Create New Task Page */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: #f0f2f5;
            color: #333;
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: #3f51b5; /* User panel blue */
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
        .message {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            font-size: 14px;
            display: <?php echo empty($message) ? 'none' : 'block'; ?>;
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

        .task-form-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            max-width: 900px;
            margin: 0 auto;
        }
        .task-form-container h2 {
            margin-top: 0;
            margin-bottom: 25px;
            color: #3f51b5;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
            font-size: 15px;
        }
        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group textarea,
        .form-group select {
            width: calc(100% - 22px); /* Account for padding and border */
            padding: 12px 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        .form-group input[type="file"] {
            padding: 10px 0;
        }
        .form-group input[type="text"]:focus,
        .form-group input[type="date"]:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: #3f51b5;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(63, 81, 181, 0.25);
        }
        button[type="submit"] {
            width: 100%;
            padding: 15px;
            background-color: #3f51b5;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
        }
        button[type="submit"]:hover {
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
            .task-form-container {
                padding: 20px;
            }
            .form-group input[type="text"],
            .form-group input[type="date"],
            .form-group textarea,
            .form-group select {
                width: 100%;
            }
        }
        @media (max-width: 480px) {
            .sidebar h2 {
                font-size: 20px;
            }
            .header h1 {
                font-size: 24px;
            }
            button[type="submit"] {
                font-size: 16px;
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>NEXUS User</h2>
        <ul>
            <li><a href="user_dashboard.php">My Dashboard</a></li>
            <li><a href="create_new_task.php" class="active">Create New Task</a></li>
            <li><a href="index.php?logout=true" style="margin-top: 30px;">Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="header">
            <h1>Create New Task</h1>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="task-form-container">
            <h2>New Task Details</h2>
            <form action="create_new_task.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="task_name">Task Name:</label>
                    <input type="text" id="task_name" name="task_name" value="<?php echo htmlspecialchars($_POST['task_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Task Description:</label>
                    <textarea id="description" name="description"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="category_id">Task Category:</label>
                    <select id="category_id" name="category_id">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['category_id']); ?>"
                                <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($_POST['start_date'] ?? date('Y-m-d')); ?>" required>
                </div>
                <div class="form-group">
                    <label for="deadline_date">Deadline Date:</label>
                    <input type="date" id="deadline_date" name="deadline_date" value="<?php echo htmlspecialchars($_POST['deadline_date'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="department_id">Department:</label>
                    <select id="department_id" name="department_id" required>
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?php echo htmlspecialchars($department['department_id']); ?>"
                                <?php echo (isset($_POST['department_id']) && $_POST['department_id'] == $department['department_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($department['department_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="allocated_to_user_id">Allocated To Person:</label>
                    <select id="allocated_to_user_id" name="allocated_to_user_id" required>
                        <option value="">Select Department First</option>
                        </select>
                </div>
                <div class="form-group">
                    <label for="priority">Priority:</label>
                    <select id="priority" name="priority">
                        <option value="Low" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'Low') ? 'selected' : ''; ?>>Low</option>
                        <option value="Medium" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'Medium') ? 'selected' : 'selected'; ?>>Medium</option>
                        <option value="High" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'High') ? 'selected' : ''; ?>>High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="attachments">Attachments:</label>
                    <input type="file" id="attachments" name="attachments[]" multiple>
                    <small>Select one or more files to attach.</small>
                </div>
                <button type="submit">Create Task</button>
            </form>
        </div>
    </div>

    <script>
        // Inline JavaScript for Create New Task Page
        console.log("Create New Task page loaded.");

        // Data for dynamic user dropdown, passed from PHP
        const usersInDepartments = <?php echo json_encode($users_in_departments); ?>;
        const departmentSelect = document.getElementById('department_id');
        const allocatedToUserSelect = document.getElementById('allocated_to_user_id');

        function updateAllocatedToUsers() {
            const selectedDepartmentId = departmentSelect.value;
            allocatedToUserSelect.innerHTML = ''; // Clear current options

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Select Person';
            allocatedToUserSelect.appendChild(defaultOption);

            if (selectedDepartmentId && usersInDepartments[selectedDepartmentId]) {
                usersInDepartments[selectedDepartmentId].forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.user_id;
                    option.textContent = user.full_name;
                    allocatedToUserSelect.appendChild(option);
                });
            }
            // Retain previously selected value if form was submitted with errors
            const previousSelectedUser = "<?php echo htmlspecialchars($_POST['allocated_to_user_id'] ?? ''); ?>";
            if (previousSelectedUser) {
                allocatedToUserSelect.value = previousSelectedUser;
            }
        }

        // Event listener for department change
        departmentSelect.addEventListener('change', updateAllocatedToUsers);

        // Initial call to populate users if a department was pre-selected (e.g., after form submission with error)
        document.addEventListener('DOMContentLoaded', () => {
            updateAllocatedToUsers();

            // Set current date as default for start_date if not set
            const startDateInput = document.getElementById('start_date');
            if (!startDateInput.value) {
                const today = new Date();
                const yyyy = today.getFullYear();
                const mm = String(today.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
                const dd = String(today.getDate()).padStart(2, '0');
                startDateInput.value = `${yyyy}-${mm}-${dd}`;
            }
        });
    </script>
</body>
</html>