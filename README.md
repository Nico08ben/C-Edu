# C-Edu

## Project Overview

C-Edu is an educational platform designed to streamline school administration and enhance the teaching process. Its primary purpose is to provide a centralized system for managing various academic and administrative tasks. The platform is targeted towards school administrators and teachers, offering tools and features to support their daily activities and improve overall efficiency.

## Features

- **User Management:** Separate interfaces and functionalities for Administrators and Teachers.
- **Calendar:** Event management for scheduling and tracking.
- **Chat:** Real-time communication capabilities.
- **Task Assignment:** Tools for creating and managing tasks.
- **User Profiles:** Sections for users to manage their profile information.
- **Notifications:** System for user notifications.

## Tech Stack

- **Backend:** PHP
- **Frontend:** HTML, CSS, JavaScript
- **Database:** MySQL (Typically managed with phpMyAdmin in a XAMPP environment)
- **Development Environment:** XAMPP (Recommended). Alternatively, any Apache, MySQL, PHP stack (WAMP, MAMP, LAMP) can be used.

## Installation and Setup

Follow these steps to set up the C-Edu platform on your local machine:

1.  **Prerequisites:**
    *   Install XAMPP (or a similar AMP stack like WAMP, MAMP, LAMP). XAMPP is recommended as it provides Apache (web server), MySQL (database), and PHP (scripting language) in one package. Download from [https://www.apachefriends.org](https://www.apachefriends.org).
    *   Ensure your XAMPP services (Apache and MySQL) are running.

2.  **Download the Code:**
    *   The primary method is to clone the repository. Open your command line interface (Terminal, Command Prompt, or PowerShell).
    *   Navigate to the `htdocs` directory within your XAMPP installation. This directory is the web server's root (e.g., `C:\xampp\htdocs`, `/Applications/XAMPP/htdocs/`, or `/opt/lampp/htdocs/`).
    *   Clone the C-Edu repository:
        ```sh
        git clone https://github.com/Nico08ben/C-Edu.git
        ```
    *   This will create a `C-Edu` folder inside `htdocs`. **The entire C-Edu project folder must reside within `htdocs` for the application to be accessible via `http://localhost/`**.

3.  **Database Setup:**
    *   Open your web browser and navigate to phpMyAdmin, usually at `http://localhost/phpmyadmin`.
    *   Click "New" on the left sidebar to create a database.
    *   Enter `cedu` as the database name and click "Create".
    *   Select the `cedu` database from the sidebar, then click the "Import" tab.
    *   Click "Choose File" and select the `cedu.sql` file located in the root of the cloned C-Edu project (e.g., `C:\xampp\htdocs\C-Edu\cedu.sql`).
    *   Click "Go" to import the database structure and data.

4.  **Application Configuration:**
    *   Database connection settings are in `conexion.php`, located in the project's root directory.
    *   The default settings are for a standard XAMPP installation:
        *   Server: `"localhost"`
        *   User: `"root"`
        *   Password: `""` (empty)
        *   Database: `"cedu"`
    *   If your MySQL setup uses different credentials (e.g., a password for `root`), update them in `conexion.php`.

5.  **Accessing the Application:**
    *   Open your web browser and navigate to `http://localhost/C-Edu/`.
    *   You should see the C-Edu login page.

## Development Environment

### Opening the Project in a Code Editor

To edit the project files:

1.  Ensure your preferred code editor (e.g., Visual Studio Code) is installed.
2.  Open the project folder (`C-Edu` located in your `htdocs` directory) using your editor's "Open Folder" or equivalent functionality.
3.  For Visual Studio Code, if you have the `code` command in your system's PATH, you can navigate to the project directory in your terminal and type:
    ```sh
    cd C:\xampp\htdocs\C-Edu
    code .
    ```
    This opens the project in VS Code.

## Directory Structure

An overview of the main directories in the C-Edu project:

-   **`Administrador/`**: Modules and features for the Administrator role (user management, settings, etc.).
-   **`Docente/`**: Modules and features for the Teacher (Docente) role (task creation, student interaction, etc.).
-   **`Inicio/`**: Contains the main landing/login page.
-   **`PHP/`**: Backend PHP scripts, including API endpoints (`PHP/api/`) and related JavaScript (`PHP/js/`). Core database connection (`conexion.php`) is in the project root.
-   **`SIDEBAR/`**: Navigation sidebar components for Admin (`SIDEBAR/Admin/`) and Teacher (`SIDEBAR/Docente/`) roles.
-   **`uploads/`**: Default directory for file uploads (e.g., profile pictures, chat files).

## Usage

Detailed instructions on how to use the different features of C-Edu will be added here. This includes navigating the admin and teacher dashboards, managing users, creating tasks, using the calendar, and interacting with the chat.

## Contributing

Contributions to C-Edu are welcome. If you'd like to contribute, please follow these general guidelines:
1. Fork the repository.
2. Create a new branch for your feature or bug fix.
3. Make your changes.
4. Submit a pull request with a clear description of your changes.

More detailed contribution guidelines will be provided later.

## License

This project is currently pending license information. Details will be added soon.

## Cloning the Repository (Alternative Methods)

The HTTPS method shown in the "Installation and Setup" section is generally recommended. Here are other methods for reference:

Using SSH:
```sh
# Navigate to your htdocs directory first
# cd C:\xampp\htdocs  (or your OS equivalent)
git clone git@github.com:Nico08ben/C-Edu.git
```

---
*Note: This README assumes a local development setup using XAMPP. Deployment to a live server may require additional steps.*