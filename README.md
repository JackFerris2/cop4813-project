# Task Management – COP4813 Project

## Overview

This is the updated Task Management System for our semester project in COP4813.  
The application allows users to create, view, edit, delete, and organize tasks through  
a drag-and-drop interface. It includes a responsive front-end interface,  
server-side PHP scripts, and a MySQL database to store persistent user and task data.

This updated version also features a secure admin dashboard with user management,  
task moderation tools, and full analytics for tracking platform usage.

The current version goes beyond the initial MVP and delivers on the design and goals  
outlined in our system proposal.

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

This shows full communication between the front-end, back-end, and MySQL database.

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

## New: Admin Analytics Features

We introduced an entire **admin analytics** to support platform
monitoring.

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
- Automatically filters out irrelevant file paths and noise
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
    - `users`: Contains `user_id`, `name`, `email`, `pw_hash`, `created`, `is_admin`
    - `tasks`: Contains `task_id`, `user_id`, `title`, `description`, `status`,
    - `estimated_hr`, `due`, `priority`, `created`, `updated`, `censor`

Accounts are created with hashed passwords and stored securely.  
Admin access is managed via the `is_admin` field.

---




