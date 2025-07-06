# NEXUS TaskHub

## Project Overview

NEXUS TaskHub is a web-based task management system designed to streamline task assignment, tracking, and collaboration within an organization. It supports user roles (admin, regular user) and allows for the creation, allocation, and management of tasks across different departments. The system facilitates efficient workflow by providing features like task prioritization, deadline tracking, and file attachments.

## Features

* **User Authentication:** Secure login for administrators and regular users.
* **Role-Based Access:** Different functionalities and views based on user roles (e.g., admin dashboard, user dashboard).
* **Task Creation:** Easily create new tasks with details such as name, description, category, start/deadline dates, priority, and assigned department/person.
* **Dynamic User Assignment:** Assign tasks to specific users within selected departments dynamically.
* **Task Management:** (Based on `user_dashboard.php` and `admin_dashboard.php`)
    * **User Dashboard:** Users can view their assigned tasks, update task status (e.g., Accept, Reject, Complete), and request task transfers.
    * **Admin Dashboard:** Administrators can oversee all tasks, view summaries, and manage various aspects of the system.
* **Task Categorization:** Organize tasks using predefined categories.
* **File Attachments:** Attach relevant files to tasks (note: actual file storage needs to be configured).
* **Responsive Design:** (Based on the recent design changes) The interface adapts to various screen sizes (desktop, tablet, mobile) and is optimized for landscape viewing.

## Technologies Used

* **Backend:** PHP
* **Database:** MySQL (or compatible, based on `task_management.sql`)
* **Frontend:** HTML, CSS, JavaScript
* **Database Connection:** PDO for secure database interactions.

## Installation Guide

To set up NEXUS TaskHub on your local machine, follow these steps:

### Prerequisites

* Web server (e.g., Apache, Nginx)
* PHP (version 7.4 or higher recommended)
* MySQL/MariaDB database
* Composer (optional, if you plan to add PHP dependencies)

### Setup Steps

1.  **Clone the Repository:**
    ```bash
    git clone [https://github.com/YourUsername/nexus-taskhub.git](https://github.com/YourUsername/nexus-taskhub.git)
    cd nexus-taskhub
    ```
    (Replace `https://github.com/YourUsername/nexus-taskhub.git` with your actual GitHub repository URL)

2.  **Database Setup:**
    * Create a new MySQL database (e.g., `task_management`).
    * Import the provided SQL schema:
        ```bash
        mysql -u your_db_user -p task_management < task_management.sql
        ```
        (Replace `your_db_user` and `task_management.sql` path as necessary)

3.  **Database Connection Configuration:**
    * Locate the `db/connection.php` file (or create it if it doesn't exist based on your project structure).
    * Update the database connection details:
        ```php
        <?php
        $host = 'localhost';
        $db = 'task_management';
        $user = 'your_db_user'; // Your database username
        $pass = 'your_db_password'; // Your database password
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("Database connection failed: " . $e->getMessage());
        }
        ?>
        ```
        (Replace `your_db_user` and `your_db_password`)

4.  **Web Server Configuration:**
    * Point your web server's document root to the `nexus-taskhub` directory (or a subdirectory if you prefer).
    * Ensure that PHP is correctly configured and enabled for your web server.

5.  **File Uploads (Important):**
    * The `create_new_task.php` mentions a placeholder path for file uploads: `'path/to/uploads/'`.
    * **You MUST create a dedicated, secure directory** (e.g., `uploads/`) outside your web server's public document root, or configure a secure path within it, to store uploaded attachments.
    * Update the `$file_path_for_db` variable and the `move_uploaded_file()` function (if implemented) in `create_new_task.php` to use your actual, secure upload directory.
    * Ensure your web server has write permissions to this directory.

6.  **Access the Application:**
    * Open your web browser and navigate to the URL where you've deployed the application (e.g., `http://localhost/nexus-taskhub/index.php`).

## Usage

1.  **Login:** Use your credentials to log in. Default admin credentials (if any) or create new users through database insertion.
2.  **Admin Users:** Can create new tasks, manage departments, and oversee all system activities.
3.  **Regular Users:** Can view and manage tasks assigned to them, update status, and request transfers.
4.  **Create Task:** Navigate to the "Create New Task" page to fill in the task details, assign it, set priority, and attach files.
5.  **Dashboard:** Check your dashboard for an overview of tasks.

## Contributing

If you'd like to contribute to NEXUS TaskHub, please follow these steps:

1.  Fork the repository.
2.  Create a new branch (`git checkout -b feature/YourFeatureName`).
3.  Make your changes and commit them (`git commit -m 'Add new feature'`).
4.  Push to the branch (`git push origin feature/YourFeatureName`).
5.  Open a Pull Request.

## License

This project is licensed under the [MIT License](LICENSE). (Create a `LICENSE` file in your repository with the MIT License text if you choose this license).

## Contact

For questions or feedback, please open an issue in the GitHub repository or contact [Your Name/Email].
