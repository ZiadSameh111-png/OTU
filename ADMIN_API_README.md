# OTU Educational Management System - Admin API Documentation

## Overview
This document provides comprehensive documentation for the Admin API endpoints in the OTU Educational Management System. All admin routes require authentication and admin role privileges.

## Base URL
```
https://your-domain.com/api/admin/
```

## Authentication
All requests must include a Bearer token in the Authorization header:
```
Authorization: Bearer {your-access-token}
```

## Response Format
All API responses follow this standard format:
```json
{
    "status": "success|error",
    "message": "Response message",
    "data": {},
    "errors": {}
}
```

---

## 1. User Management

### Get All Users
**GET** `/api/admin/users`

**Query Parameters:**
- `role` (optional): Filter by role (Admin, Teacher, Student)
- `group_id` (optional): Filter by group ID
- `search` (optional): Search by name or email
- `per_page` (optional): Items per page (default: 15)

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/admin/users?role=Student&per_page=20" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
    "status": "success",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "name": "أحمد محمد",
                "email": "ahmed@example.com",
                "role": "Student",
                "group_id": 1,
                "group": {
                    "id": 1,
                    "name": "المجموعة الأولى"
                },
                "created_at": "2024-01-01T00:00:00.000000Z"
            }
        ],
        "total": 25,
        "per_page": 20
    }
}
```

### Create User
**POST** `/api/admin/users`

**Request Body:**
```json
{
    "name": "محمد أحمد",
    "email": "mohammed@example.com",
    "password": "password123",
    "role": "Student",
    "group_id": 1,
    "phone": "0501234567",
    "address": "الرياض، السعودية"
}
```

**Example Response:**
```json
{
    "status": "success",
    "message": "تم إنشاء المستخدم بنجاح",
    "data": {
        "id": 26,
        "name": "محمد أحمد",
        "email": "mohammed@example.com",
        "role": "Student",
        "group_id": 1
    }
}
```

### Get User Details
**GET** `/api/admin/users/{id}`

### Update User
**PUT** `/api/admin/users/{id}`

### Delete User
**DELETE** `/api/admin/users/{id}`

---

## 2. Course Management

### Get All Courses
**GET** `/api/admin/courses`

**Query Parameters:**
- `teacher_id` (optional): Filter by teacher
- `group_id` (optional): Filter by group
- `search` (optional): Search by course name or code

**Example Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "مقدمة في البرمجة",
            "code": "CS101",
            "description": "مقرر أساسي في البرمجة",
            "credits": 3,
            "teachers": [
                {
                    "id": 2,
                    "name": "د. سارة أحمد"
                }
            ],
            "groups": [
                {
                    "id": 1,
                    "name": "المجموعة الأولى"
                }
            ]
        }
    ]
}
```

### Create Course
**POST** `/api/admin/courses`

**Request Body:**
```json
{
    "name": "هياكل البيانات",
    "code": "CS201",
    "description": "مقرر متقدم في هياكل البيانات والخوارزميات",
    "credits": 4,
    "teacher_ids": [2, 3],
    "group_ids": [1, 2]
}
```

---

## 3. Fee Management

### Get All Fees
**GET** `/api/admin/fees`

**Query Parameters:**
- `student_id` (optional): Filter by student
- `status` (optional): paid, unpaid, partial, overdue
- `fee_type` (optional): tuition, registration, exam, other
- `academic_year` (optional): e.g., "2024-2025"

**Example Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "title": "رسوم دراسية للفصل الدراسي الحالي",
            "total_amount": 12000.00,
            "paid_amount": 8000.00,
            "remaining_amount": 4000.00,
            "status": "partial",
            "due_date": "2024-12-31",
            "student": {
                "id": 10,
                "name": "أحمد محمد"
            }
        }
    ]
}
```

### Create Fee
**POST** `/api/admin/fees`

**Request Body:**
```json
{
    "user_id": 10,
    "title": "رسوم امتحانات نهاية الفصل",
    "total_amount": 500.00,
    "due_date": "2024-06-30",
    "fee_type": "exam",
    "academic_year": "2024-2025",
    "description": "رسوم امتحانات نهاية الفصل الدراسي"
}
```

---

## 4. Group Management

### Get All Groups
**GET** `/api/admin/groups`

**Example Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "المجموعة الأولى",
            "description": "مجموعة طلاب السنة الأولى",
            "active": true,
            "students_count": 25,
            "courses_count": 6
        }
    ]
}
```

### Create Group
**POST** `/api/admin/groups`

**Request Body:**
```json
{
    "name": "المجموعة الثالثة",
    "description": "مجموعة طلاب السنة الثالثة",
    "active": true
}
```

### Get Group Students
**GET** `/api/admin/groups/{group}/students`

### Get Group Courses
**GET** `/api/admin/groups/{group}/courses`

---

## 5. Grade Management

### Get All Grades
**GET** `/api/admin/grades`

**Query Parameters:**
- `student_id` (optional): Filter by student
- `course_id` (optional): Filter by course
- `group_id` (optional): Filter by group
- `is_final` (optional): true/false

**Example Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "student": {
                "id": 10,
                "name": "أحمد محمد"
            },
            "course": {
                "id": 1,
                "name": "مقدمة في البرمجة",
                "code": "CS101"
            },
            "midterm_grade": 85,
            "assignment_grade": 90,
            "final_grade": 88,
            "score": 87.5,
            "grade": "A-",
            "gpa": 3.70,
            "is_final": true
        }
    ]
}
```

### Get Course Report
**GET** `/api/admin/courses/{course}/report`

### Get Group Report
**GET** `/api/admin/groups/{group}/report`

---

## 6. Exam Management

### Get All Exams
**GET** `/api/admin/exams`

**Query Parameters:**
- `course_id` (optional): Filter by course
- `group_id` (optional): Filter by group
- `teacher_id` (optional): Filter by teacher
- `status` (optional): pending, active, completed

**Example Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "title": "امتحان منتصف الفصل - مقدمة في البرمجة",
            "description": "امتحان منتصف الفصل الدراسي",
            "start_time": "2024-06-15T10:00:00.000000Z",
            "end_time": "2024-06-15T12:00:00.000000Z",
            "duration": 120,
            "status": "active",
            "course": {
                "id": 1,
                "name": "مقدمة في البرمجة"
            },
            "group": {
                "id": 1,
                "name": "المجموعة الأولى"
            }
        }
    ]
}
```

### Get Exam Results
**GET** `/api/admin/exams/{exam}/results`

---

## 7. Schedule Management

### Get All Schedules
**GET** `/api/admin/schedules`

**Query Parameters:**
- `group_id` (optional): Filter by group
- `course_id` (optional): Filter by course
- `day` (optional): Monday, Tuesday, etc.

**Example Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "day": "Sunday",
            "start_time": "08:00:00",
            "end_time": "10:00:00",
            "room": "A101",
            "course": {
                "id": 1,
                "name": "مقدمة في البرمجة"
            },
            "group": {
                "id": 1,
                "name": "المجموعة الأولى"
            }
        }
    ]
}
```

### Create Schedule
**POST** `/api/admin/schedules`

**Request Body:**
```json
{
    "course_id": 1,
    "group_id": 1,
    "day": "Monday",
    "start_time": "10:00:00",
    "end_time": "12:00:00",
    "room": "B202"
}
```

---

## 8. Teacher Attendance Management

### Get Teacher Attendance
**GET** `/api/admin/teacher-attendance`

**Query Parameters:**
- `teacher_id` (optional): Filter by teacher
- `date` (optional): Filter by date (YYYY-MM-DD)
- `status` (optional): present, absent, late, sick_leave

**Example Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "teacher": {
                "id": 2,
                "name": "د. سارة أحمد"
            },
            "attendance_date": "2024-05-31",
            "check_in": "2024-05-31T08:15:00.000000Z",
            "check_out": "2024-05-31T16:30:00.000000Z",
            "status": "present",
            "notes": "حضور منتظم"
        }
    ]
}
```

### Get Teacher Attendance Stats
**GET** `/api/admin/teachers/{teacher}/attendance/stats`

---

## 9. Student Attendance Management

### Get Student Attendance
**GET** `/api/admin/student-attendance`

**Query Parameters:**
- `student_id` (optional): Filter by student
- `course_id` (optional): Filter by course
- `group_id` (optional): Filter by group
- `date` (optional): Filter by date

**Example Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "student": {
                "id": 10,
                "name": "أحمد محمد"
            },
            "schedule": {
                "id": 1,
                "course": {
                    "name": "مقدمة في البرمجة"
                }
            },
            "attendance_date": "2024-05-31",
            "status": "present",
            "notes": "حضور منتظم"
        }
    ]
}
```

### Get Student Attendance Stats
**GET** `/api/admin/students/{student}/attendance/stats`

### Get Group Attendance Stats
**GET** `/api/admin/groups/{group}/student-attendance/stats`

---

## 10. Notification Management

### Send Notification
**POST** `/api/admin/notifications`

**Request Body:**
```json
{
    "title": "إشعار مهم",
    "content": "يرجى مراجعة الجدول الدراسي المحدث",
    "receiver_type": "role",
    "role": "Student",
    "type": "info"
}
```

**Receiver Types:**
- `user`: Send to specific user (requires `receiver_id`)
- `role`: Send to all users with specific role (requires `role`)
- `group`: Send to all students in group (requires `group_id`)
- `all`: Send to all users

---

## 11. Dashboard Management

### Get Admin Dashboard
**GET** `/api/admin/dashboard`

**Description:** 
Retrieves comprehensive dashboard statistics and overview data for administrators including user counts, financial summaries, attendance statistics, recent activities, and system notifications.

**Query Parameters:**
- `date` (optional): Filter data by specific date (YYYY-MM-DD)
- `period` (optional): Filter by period (today, week, month, year)

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/admin/dashboard?period=month" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
    "status": "success",
    "data": {
        "statistics": {
            "total_students": 156,
            "total_teachers": 24,
            "total_courses": 18,
            "active_groups": 6
        },
        "financial_summary": {
            "total_fees": 1250000.00,
            "paid_fees": 980000.00,
            "pending_fees": 270000.00,
            "overdue_fees": 45000.00,
            "payment_rate": 78.4
        },
        "attendance_summary": {
            "teacher_attendance": {
                "present_today": 22,
                "absent_today": 2,
                "late_today": 1,
                "attendance_rate": 88.0
            },
            "student_attendance": {
                "present_today": 142,
                "absent_today": 14,
                "attendance_rate": 91.0
            }
        },
        "recent_activities": [
            {
                "type": "user_created",
                "message": "تم إنشاء حساب جديد للطالب أحمد محمد",
                "timestamp": "2024-05-31T10:30:00.000000Z",
                "user": {
                    "id": 157,
                    "name": "أحمد محمد"
                }
            },
            {
                "type": "fee_payment",
                "message": "تم دفع رسوم دراسية بقيمة 5000 ريال",
                "timestamp": "2024-05-31T09:15:00.000000Z",
                "amount": 5000.00
            }
        ],
        "pending_requests": [
            {
                "id": 12,
                "type": "leave_request",
                "student": {
                    "id": 45,
                    "name": "فاطمة علي"
                },
                "title": "طلب إجازة مرضية",
                "priority": "normal",
                "created_at": "2024-05-30T14:20:00.000000Z"
            }
        ],
        "upcoming_exams": [
            {
                "id": 8,
                "title": "امتحان منتصف الفصل - البرمجة المتقدمة",
                "course": {
                    "id": 5,
                    "name": "البرمجة المتقدمة",
                    "code": "CS301"
                },
                "start_time": "2024-06-05T10:00:00.000000Z",
                "duration": 120,
                "students_count": 28
            }
        ],
        "system_notifications": [
            {
                "type": "warning",
                "message": "يوجد 3 مدرسين لم يسجلوا الحضور اليوم",
                "count": 3,
                "link": "/admin/teacher-attendance"
            },
            {
                "type": "info",
                "message": "15 طلب إداري في انتظار المراجعة",
                "count": 15,
                "link": "/admin/requests"
            }
        ],
        "charts_data": {
            "monthly_enrollment": [
                {"month": "يناير", "students": 145},
                {"month": "فبراير", "students": 148},
                {"month": "مارس", "students": 152},
                {"month": "أبريل", "students": 156}
            ],
            "fee_collection": [
                {"month": "يناير", "collected": 245000, "pending": 55000},
                {"month": "فبراير", "collected": 280000, "pending": 45000},
                {"month": "مارس", "collected": 310000, "pending": 40000},
                {"month": "أبريل", "collected": 325000, "pending": 35000}
            ],
            "attendance_trends": [
                {"date": "2024-05-27", "teacher_rate": 92, "student_rate": 89},
                {"date": "2024-05-28", "teacher_rate": 88, "student_rate": 91},
                {"date": "2024-05-29", "teacher_rate": 95, "student_rate": 87},
                {"date": "2024-05-30", "teacher_rate": 90, "student_rate": 93}
            ]
        }
    }
}
```

**Response Fields:**
- `statistics`: Basic counts of system entities
- `financial_summary`: Fee collection and payment statistics
- `attendance_summary`: Today's attendance rates for teachers and students
- `recent_activities`: Latest system activities and changes
- `pending_requests`: Administrative requests awaiting approval
- `upcoming_exams`: Scheduled exams in the near future
- `system_notifications`: Important alerts and warnings
- `charts_data`: Data for dashboard charts and graphs

---

## Error Handling

### Common Error Responses

**Validation Error (422):**
```json
{
    "status": "error",
    "message": "Validation error",
    "errors": {
        "email": ["The email field is required."],
        "name": ["The name field is required."]
    }
}
```

**Unauthorized (401):**
```json
{
    "status": "error",
    "message": "Unauthenticated"
}
```

**Forbidden (403):**
```json
{
    "status": "error",
    "message": "Unauthorized access"
}
```

**Not Found (404):**
```json
{
    "status": "error",
    "message": "Resource not found"
}
```

---

## Rate Limiting
API requests are limited to 60 requests per minute per user.

## Testing
Use the provided test credentials:
- **Admin**: admin@otu.edu / password123
- **Teacher**: teacher@test.com / password
- **Student**: student@test.com / password

## Support
For technical support, contact the development team or refer to the system documentation. 