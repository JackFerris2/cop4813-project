# Task Management MVP – COP4813 Project

## Overview

This is the Minimum Viable Product (MVP) for our semester project in COP4813. 
The application is a Task Management System that allows users to create, view, 
and eventually manage tasks. It includes a front-end interface, server-side scripts,
and a working MySQL database to store and retrieve task data.

This version matches the initial design and goals laid out in our system proposal
and architecture diagram.

---

## Features Completed in the MVP

### Functional Front-End Form Pages
The MVP includes multiple front-end HTML pages that allow users to interact with the system:

- `create-task.html` – A form to submit a new task
- `edit-task.html` – A form to (eventually) edit existing tasks
- `dashboard.php` – Displays all current tasks from the database

These forms are styled using Bootstrap and support real user input.

---

### End-to-End Flow Example

One complete flow is working from start to finish:

1. User opens `create-task.html`
2. They fill out the form and hit Create Task
3. The form submits to `submit-task.php`
4. `submit-task.php` inserts the new task into the database
5. `dashboard.php` shows the updated list of tasks (including the new one)

This demonstrates full communication between the front-end, PHP back-end, and MySQL database.

---

### Database

We used a MySQL database hosted on an EC2 server.

- The database is named: `taskmanagement`
- The main table used is: `tasks`
- Tasks contain `title`, `description`, `status`, `created` and `updated` timestamps

A sample dummy user (`webuser`) was created with restricted access to allow 
form submissions without compromising root credentials.

---


 


