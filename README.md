# OTU - Educational Management System API

## Table of Contents
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Authentication](#authentication)
- [API Endpoints](#api-endpoints)
  - [Users](#users)
  - [Courses](#courses)
  - [Groups](#groups)
  - [Grades](#grades)
  - [Exams](#exams)
  - [Attendance](#attendance)
  - [Fees](#fees)
  - [Schedules](#schedules)
  - [Messages & Notifications](#messages--notifications)
  - [Dashboard](#dashboard)
  - [Admin Requests](#admin-requests)
  - [Grade Reports](#grade-reports)

## System Requirements

- PHP 8.0 or higher
- Laravel 9.0
- MySQL 5.7 or higher
- Composer
- Laravel Sanctum (for authentication)

## Installation

1. Clone the project:
```bash
git clone <repository-url>
cd <project-directory>
```

2. Install dependencies:
```bash
composer install
```

3. Set up environment file:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure database in .env:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. Run migrations and seeders:
```bash
php artisan migrate
php artisan db:seed
```

6. Create first admin user:
```bash
php artisan tinker
App\Models\User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => Hash::make('password')]);
$user = App\Models\User::where('email', 'admin@example.com')->first();
$role = App\Models\Role::firstOrCreate(['name' => 'Admin']);
$user->roles()->attach($role);
```

7. Start the server:
```bash
php artisan serve
```

## Authentication

### Login
```http
POST /api/login
Content-Type: application/json

{
    "email": "your_email@example.com",
    "password": "your_password",
    "device_name": "device_name"
}
```

Response:
```json
{
    "status": "success",
    "message": "User logged in successfully",
    "user": {
        "id": 1,
        "name": "User Name",
        "email": "user@example.com",
        "role": "Admin"
    },
    "token": "1|otu_pat_xxxxxxxxxxxxxxxxxxxx"
}
```

### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

## API Endpoints

### Users

#### Admin Endpoints
```http
# List all users
GET /api/admin/users

# Create new user
POST /api/admin/users
{
    "name": "string",
    "email": "string",
    "password": "string",
    "role": "string",
    "group_id": "integer|null"
}

# Show user details
GET /api/admin/users/{id}

# Update user
PUT /api/admin/users/{id}
{
    "name": "string",
    "email": "string",
    "role": "string",
    "group_id": "integer|null"
}

# Delete user
DELETE /api/admin/users/{id}

# List roles
GET /api/admin/roles

# List user groups
GET /api/admin/user-groups

# List students
GET /api/admin/students

# List teachers
GET /api/admin/teachers
```

### Courses

#### Admin Endpoints
```http
# List all courses
GET /api/admin/courses

# Create new course
POST /api/admin/courses
{
    "name": "string",
    "code": "string",
    "description": "string",
    "semester": "string",
    "year": "integer",
    "teacher_ids": "array"
}

# Show course details
GET /api/admin/courses/{course}

# Update course
PUT /api/admin/courses/{course}
{
    "name": "string",
    "code": "string",
    "description": "string",
    "semester": "string",
    "year": "integer",
    "teacher_ids": "array"
}

# Delete course
DELETE /api/admin/courses/{course}
```

#### Teacher Endpoints
```http
# List teacher's courses
GET /api/teacher/courses

# Show course details
GET /api/teacher/courses/{course}
```

#### Student Endpoints
```http
# List student's courses
GET /api/student/courses

# Show course details
GET /api/student/courses/{course}
```

### Groups

#### Admin Endpoints
```http
# List all groups
GET /api/admin/groups

# Create new group
POST /api/admin/groups
{
    "name": "string",
    "description": "string",
    "course_ids": "array"
}

# Show group details
GET /api/admin/groups/{group}

# Update group
PUT /api/admin/groups/{group}
{
    "name": "string",
    "description": "string",
    "course_ids": "array"
}

# Delete group
DELETE /api/admin/groups/{group}

# List group students
GET /api/admin/groups/{group}/students

# List group courses
GET /api/admin/groups/{group}/courses
```

### Grades

#### Teacher Endpoints
```http
# List course grades
GET /api/teacher/grades

# Add grades
POST /api/teacher/grades
{
    "student_id": "integer",
    "course_id": "integer",
    "assignment_grade": "numeric",
    "midterm_grade": "numeric",
    "final_grade": "numeric",
    "practical_grade": "numeric"
}

# Update grades
PUT /api/teacher/grades/{grade}
{
    "assignment_grade": "numeric",
    "midterm_grade": "numeric",
    "final_grade": "numeric",
    "practical_grade": "numeric"
}

# View course grade report
GET /api/teacher/courses/{course}/report
```

#### Student Endpoints
```http
# List grades
GET /api/student/grades

# Show grade details
GET /api/student/grades/{grade}
```

### Exams

#### Teacher Endpoints
```http
# List exams
GET /api/teacher/exams

# Create new exam
POST /api/teacher/exams
{
    "course_id": "integer",
    "title": "string",
    "description": "string",
    "start_time": "datetime",
    "end_time": "datetime",
    "duration": "integer",
    "total_marks": "numeric"
}

# Update exam
PUT /api/teacher/exams/{exam}

# Delete exam
DELETE /api/teacher/exams/{exam}

# View exam results
GET /api/teacher/exams/{exam}/results
```

#### Student Endpoints
```http
# List exams
GET /api/student/exams

# Submit exam attempt
POST /api/student/exams/{exam}/submit

# View exam results
GET /api/student/exams/{exam}/results
```

### Attendance

#### Teacher Endpoints
```http
# Record teacher attendance
POST /api/teacher/attendance
{
    "course_id": "integer",
    "attendance_date": "date",
    "status": "string"
}

# Record student attendance
POST /api/teacher/student-attendance
{
    "course_id": "integer",
    "attendance_date": "date",
    "student_id": "integer",
    "status": "string"
}

# Bulk record student attendance
POST /api/teacher/student-attendance/bulk
{
    "course_id": "integer",
    "attendance_date": "date",
    "attendance_data": [
        {
            "student_id": "integer",
            "status": "string"
        }
    ]
}

# View attendance statistics
GET /api/teacher/attendance/stats
```

#### Student Endpoints
```http
# View attendance record
GET /api/student/attendance

# View attendance statistics
GET /api/student/attendance/stats
```

### Fees

#### Admin Endpoints
```http
# List all fees
GET /api/admin/fees

# Create new fee
POST /api/admin/fees
{
    "student_id": "integer",
    "amount": "numeric",
    "description": "string",
    "due_date": "date"
}

# Update fee
PUT /api/admin/fees/{id}

# Delete fee
DELETE /api/admin/fees/{id}
```

#### Student Endpoints
```http
# List fees
GET /api/student/fees

# View statement
GET /api/student/fees/statement

# View payment history
GET /api/student/fees/payments

# Create payment
POST /api/student/fees/{id}/payment
{
    "amount": "numeric",
    "payment_method": "string"
}
```

### Schedules

#### Admin Endpoints
```http
# List all schedules
GET /api/admin/schedules

# Create new schedule
POST /api/admin/schedules
{
    "course_id": "integer",
    "group_id": "integer",
    "day": "string",
    "start_time": "time",
    "end_time": "time",
    "room": "string"
}

# Update schedule
PUT /api/admin/schedules/{schedule}

# Delete schedule
DELETE /api/admin/schedules/{schedule}
```

#### Teacher & Student Endpoints
```http
# View schedule
GET /api/schedules

# View schedule details
GET /api/schedules/{schedule}
```

### Messages & Notifications

```http
# List messages
GET /api/messages

# Send message
POST /api/messages
{
    "receiver_id": "integer",
    "subject": "string",
    "content": "string"
}

# List sent messages
GET /api/messages/sent

# List received messages
GET /api/messages/received

# Get unread count
GET /api/messages/unread-count

# Show message details
GET /api/messages/{message}

# Delete message
DELETE /api/messages/{message}

# Mark message as read
POST /api/messages/{message}/read

# List notifications
GET /api/notifications

# Get unread notifications count
GET /api/notifications/unread-count

# Mark all notifications as read
POST /api/notifications/mark-all-read

# Delete all notifications
DELETE /api/notifications
```

### Dashboard

```http
# Get dashboard statistics
GET /api/dashboard
```

Response varies by user role:
- Admin: System-wide statistics
- Teacher: Course and student statistics
- Student: Grades and attendance statistics

### Admin Requests

```http
# List requests
GET /api/admin-requests

# Create new request
POST /api/admin-requests
{
    "type": "string",
    "subject": "string",
    "description": "string",
    "attachments": "array"
}

# Show request details
GET /api/admin-requests/{adminRequest}

# Update request status
POST /api/admin-requests/{adminRequest}/status
{
    "status": "string",
    "response": "string"
}

# Add response to request
POST /api/admin-requests/{adminRequest}/responses
{
    "content": "string"
}
```

### Grade Reports

```http
# Student grade report
GET /api/reports/students/{student}

# Course grade report
GET /api/reports/courses/{course}

# Group grade report
GET /api/reports/groups/{group}

# Semester grade report
GET /api/reports/semester
{
    "semester": "string",
    "year": "integer"
}
```

## Important Notes

1. All requests require authentication header:
```http
Authorization: Bearer {token}
```

2. All responses follow this format:
```json
{
    "status": "success|error",
    "message": "string",
    "data": "mixed"
}
```

3. Error responses:
```json
{
    "status": "error",
    "message": "Error message",
    "errors": {
        "field": ["error messages"]
    }
}
```

4. Role-based access control:
- `/api/admin/*`: Requires Admin role
- `/api/teacher/*`: Requires Teacher role
- `/api/student/*`: Requires Student role

5. File uploads should use `multipart/form-data` content type

6. All dates and times must be in ISO 8601 format

## Frontend Development

This project is an API-only backend. For frontend development, you can use any modern framework such as:
- React.js
- Vue.js
- Angular
- React Native (for mobile apps)