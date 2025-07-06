# Task Management – COP4813 Project

## Overview

This is the updated Task Management System for our semester project in COP4813.
The application allows users to create, view, edit, delete, and organize tasks through
a drag-and-drop interface. It includes a responsive front-end interface,
server-side PHP scripts, and a MySQL database to persist user and task data.
This updated version also features and admin dashboard which is described below.

The current version goes beyond the initial MVP and delivers on the design and goals
outlined in our system proposal.

---

### Functional Front-End Pages
The task webpage includes multiple front-end PHP and HTML pages that allow users to interact 
with the system:
- create-task.php – A form to submit a new task
- edit-task.php – A form to edit existing tasks
- dashboard.php – A dynamic task board that displays all user tasks by status with drag-and-drop support
- create-user.html – A form to create a new user account 

These pages are styled using Bootstrap, include interactive icons for editing and deleting tasks,
and support real user input.

### End-to-End Flow Example
- User opens create-task.php
- They fill out the form and hit Create Task
- The form submits to the backend to save the task
- dashboard.php displays all user tasks sorted by status (To Do, In Progress, Done)

Users can:
- Drag tasks between status columns
- Edit tasks via an icon
- Delete tasks securely via a POST form
- Visually select tasks by clicking or dragging them

This demonstrates full communication between the front-end UI, PHP back-end, and MySQL database.

### Admin Capabilities 
The system now includes a dedicated admin interface for managing users and tasks.
Admins can log in via a provided admin email and password, view all registered users,
edit or delete any task in the system (regardless of owner), flag tasks that violate guidelines,
give admin privileges to another account, and delete accounts altogether.

These features provide oversight and control, supporting administrative intervention when needed.

---

### Database

We used a MySQL database hosted on an AWS lightsail server.

- The database is named: `taskmanagement`
- The main tables used are: `users` and `tasks`
- Users contain `user_id`, `name`, `email`, `pw_hash`, and a `created` timestamp
- Tasks contain `task_id`, `user_id`, `title`, `description`, `status`, `estimated_hr`, `due`, `priority`, `created` and `updated` timestamps

A sample dummy users (`taskmanager` and `webuser`) were created with restricted access to allow 
form submissions without compromising root credentials.

---


 


