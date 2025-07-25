# Task Management – COP4813 Project

## Overview

This is the final version of the Task Management System for our semester project in COP4813.  
The application allows users to create, view, edit, delete, and organize tasks through  
a drag-and-drop interface. It includes a responsive front-end interface,  
server-side PHP scripts, and a MySQL database to store persistent user and task data.

There are also tools for administrators to manage user and task data as well as  
view analytics for tracking platform usage.

---

## Functional Front-End Pages

The task webpage includes multiple front-end PHP and HTML pages that allow users to interact
with the system:

- `create-task.php` – A form to submit a new task
- `edit-task.php` – A form to edit existing tasks
- `dashboard.php` – A dynamic task board that displays all user tasks by status with drag-and-drop support
- `create-user.html` – A form to create a new user account

These pages are styled using Bootstrap, include interactive icons for editing and deleting tasks,  
and support real user input.

---

## End-to-End Flow Example

- A user visits `create-task.php`
- They fill out the form and click "Create Task"
- The task data is submitted to the server and saved in the database
- The task is then displayed on `dashboard.php` under the appropriate status column

Users can:
- Drag tasks between "To Do", "In Progress", and "Done"
- Edit tasks using the pencil icon
- Delete tasks securely
- View live task updates

This demonstrates full communication between the front-end UI, PHP back-end, and MySQL database.

### Admin Capabilities 
The system now includes a dedicated admin interface for managing users and tasks.
Admins can log into the website as normal and access the admin tools through the navigation bar. 
Admins also have the ability to edit or delete any task in the system (regardless of owner) and flag tasks that violate guidelines so that their contents is hidden on a normal user dashboard.
They can also view and edit all registered users, grant other users admin privileges, and delete accounts altogether.

There are also pages for user and task analytics as well as a page with information on page traffic.

These features provide oversight, information, and control, supporting administrative intervention when needed.

---

## Admin Capabilities

The system includes a secure admin-only dashboard for managing users and overseeing content.

Admins can:
- Access the admin dashboard through the nav bar
- View and manage all registered users
- Grant admin privileges or delete accounts
- View and moderate all tasks in the system
- Censor task content to hide inappropriate submissions

This gives admins full oversight and the ability to enforce platform guidelines.

---

### Admin Analytics Tools

Available through `admin-analytics.php`, the analytics dashboard includes:

- **User Statistics**
    - Total users
    - Active vs. inactive accounts
    - New user registrations over time (by day, week, or month)

- **Task Statistics**
    - Total task count
    - Completion rate
    - Average tasks per user
    - Blocked/censored tasks

- **Interactive Graphs**
    - New user registration trends
    - Tasks created over time
    - Tasks by status (pie chart: Completed, In Progress, Not Started)

All graphs are filterable and responsive, making data easy to explore.

### Most Visited Pages Report

A separate tool, `admin-page-traffic.php`, analyzes real Apache access logs and visualizes  
the **top 10 most visited pages** across the platform.

Features:
- Automatically filters out irrelevant file paths and noise from the logs
- Converts route names into **friendly page labels** (e.g., "Admin Dashboard" instead of `admin-dashboard.php`)
- Shows only real pages users or admins can visit
- Light blue bar chart using Chart.js
- Optional filters (by time frame or user role – in progress)

This provides admins with a clear picture of what parts of the app get the most use.

---

## Database

We used a MySQL database hosted on an AWS Lightsail server.

- **Database name:** `taskmanagement`
- **Tables:**
    - `users`: Contains `user_id`, `name`, `email`, `pw_hash`, `created`, `active`, `admin`
    - `tasks`: Contains `task_id`, `user_id`, `title`, `description`, `status`, `estimated_hr`, `due`, `priority`, `created`, `updated`, `censor`, and `overwrite`

Accounts are created with hashed passwords and stored securely.  
Admin access is managed via the `admin` field.

---




