# OTU Educational Management System - Student API Documentation

## Overview
This document provides comprehensive documentation for the Student API endpoints in the OTU Educational Management System. All student routes require authentication and student role privileges.

## Base URL
```
https://your-domain.com/api/student/
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

## 1. Course Management

### Get All Student Courses
**GET** `/api/student/courses`

**Description:** 
Retrieves all courses available to the authenticated student based on their group assignment.

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/student/courses" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 1,
            "name": "مقدمة في البرمجة",
            "code": "CS101",
            "description": "مقرر أساسي في البرمجة باستخدام لغة Python",
            "credit_hours": 3,
            "semester": "2024-1",
            "active": true,
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

### Get Course Details
**GET** `/api/student/courses/{course}`

**Description:** 
Retrieves detailed information about a specific course including syllabus, teachers, and schedule.

**Example Response:**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "name": "مقدمة في البرمجة",
        "code": "CS101",
        "description": "مقرر شامل يغطي أساسيات البرمجة",
        "credit_hours": 3,
        "teachers": [
            {
                "id": 2,
                "name": "د. سارة أحمد",
                "email": "sarah@otu.edu"
            }
        ],
        "schedules": [
            {
                "id": 1,
                "day": "Sunday",
                "start_time": "08:00:00",
                "end_time": "10:00:00",
                "room": "A101"
            }
        ],
        "total_students": 25,
        "my_grade": {
            "assignment_grade": 85,
            "midterm_grade": 78,
            "final_grade": 82,
            "total": 245,
            "percentage": 81.7,
            "letter_grade": "B+"
        }
    }
}
```

---

## 2. Fee Management

### Get Student Fees
**GET** `/api/student/fees`

**Description:** 
Retrieves all fees assigned to the authenticated student with payment status and amounts.

**Query Parameters:**
- `status` (optional): paid, unpaid, partial, overdue
- `fee_type` (optional): tuition, registration, exam, other
- `academic_year` (optional): e.g., "2024-2025"

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/student/fees?status=unpaid" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 15,
            "title": "رسوم دراسية للفصل الدراسي الأول",
            "total_amount": 12000.00,
            "paid_amount": 8000.00,
            "remaining_amount": 4000.00,
            "status": "partial",
            "fee_type": "tuition",
            "due_date": "2024-12-31",
            "academic_year": "2024-2025",
            "description": "رسوم دراسية للفصل الدراسي الأول",
            "is_overdue": false,
            "created_at": "2024-09-01T00:00:00.000000Z"
        }
    ]
}
```

### Get Fee Statement
**GET** `/api/student/fees/statement`

**Description:** 
Generates a comprehensive financial statement showing all fees, payments, and outstanding amounts.

**Query Parameters:**
- `academic_year` (optional): Filter by academic year
- `format` (optional): json, pdf (default: json)

**Example Response:**
```json
{
    "status": "success",
    "data": {
        "student": {
            "id": 10,
            "name": "أحمد محمد",
            "student_id": "STU001"
        },
        "academic_year": "2024-2025",
        "summary": {
            "total_fees": 15000.00,
            "total_paid": 10000.00,
            "total_outstanding": 5000.00,
            "overdue_amount": 0.00
        },
        "fees": [
            {
                "id": 15,
                "title": "رسوم دراسية",
                "amount": 12000.00,
                "paid": 8000.00,
                "outstanding": 4000.00,
                "due_date": "2024-12-31",
                "status": "partial"
            }
        ],
        "payments": [
            {
                "id": 25,
                "amount": 5000.00,
                "payment_method": "bank_transfer",
                "transaction_id": "TXN123456",
                "payment_date": "2024-10-15",
                "status": "completed"
            }
        ]
    }
}
```

### Get Payment History
**GET** `/api/student/fees/payments`

**Description:** 
Retrieves the payment history for the authenticated student.

**Query Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page (default: 15)
- `from_date` (optional): Filter payments from date (YYYY-MM-DD)
- `to_date` (optional): Filter payments to date (YYYY-MM-DD)

**Example Response:**
```json
{
    "status": "success",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 25,
                "fee": {
                    "id": 15,
                    "title": "رسوم دراسية للفصل الأول"
                },
                "amount": 5000.00,
                "payment_method": "bank_transfer",
                "payment_method_name": "تحويل بنكي",
                "transaction_id": "TXN123456",
                "payment_date": "2024-10-15T10:30:00.000000Z",
                "status": "completed",
                "status_name": "مكتمل",
                "receipt_number": "RCP001234"
            }
        ],
        "total": 5,
        "per_page": 15
    }
}
```

### Get Fee Details
**GET** `/api/student/fees/{id}`

**Description:** 
Retrieves detailed information about a specific fee including payment history and installment options.

### Create Payment Transaction
**POST** `/api/student/fees/{id}/payment`

**Description:** 
Initiates a payment transaction for a specific fee.

**Request Body:**
```json
{
    "amount": 5000.00,
    "payment_method": "bank_transfer",
    "description": "دفعة جزئية للرسوم الدراسية"
}
```

**Payment Methods:**
- `cash`: نقداً
- `bank_transfer`: تحويل بنكي
- `credit_card`: بطاقة ائتمانية
- `online`: دفع إلكتروني

### Get Payment Receipt
**GET** `/api/student/fees/payment/receipt/{paymentId}`

**Description:** 
Retrieves or generates a payment receipt for a completed payment.

---

## 3. Grade Management

### Get Student Grades
**GET** `/api/student/grades`

**Description:** 
Retrieves all grades for the authenticated student across all enrolled courses.

**Query Parameters:**
- `course_id` (optional): Filter by specific course
- `semester` (optional): Filter by semester (e.g., "2024-1")
- `is_final` (optional): true/false - filter by finalized grades only

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/student/grades?is_final=true" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 45,
            "course": {
                "id": 1,
                "name": "مقدمة في البرمجة",
                "code": "CS101",
                "credit_hours": 3
            },
            "assignment_grade": 85,
            "midterm_grade": 78,
            "final_grade": 82,
            "practical_grade": 88,
            "total": 333,
            "percentage": 83.25,
            "letter_grade": "B+",
            "gpa": 3.30,
            "is_final": true,
            "submission_date": "2024-11-15T14:30:00.000000Z",
            "comments": "أداء جيد جداً، يُنصح بالتركيز على الخوارزميات"
        }
    ]
}
```

### Get Grade Details
**GET** `/api/student/grades/{grade}`

**Description:** 
Retrieves detailed information about a specific grade including component breakdown and teacher feedback.

**Example Response:**
```json
{
    "status": "success",
    "data": {
        "id": 45,
        "course": {
            "id": 1,
            "name": "مقدمة في البرمجة",
            "code": "CS101"
        },
        "grade_components": {
            "assignment_grade": {
                "score": 85,
                "max_score": 100,
                "percentage": 85.0,
                "weight": "30%"
            },
            "midterm_grade": {
                "score": 78,
                "max_score": 100,
                "percentage": 78.0,
                "weight": "25%"
            },
            "final_grade": {
                "score": 82,
                "max_score": 100,
                "percentage": 82.0,
                "weight": "35%"
            },
            "practical_grade": {
                "score": 88,
                "max_score": 100,
                "percentage": 88.0,
                "weight": "10%"
            }
        },
        "total_score": 333,
        "max_total": 400,
        "percentage": 83.25,
        "letter_grade": "B+",
        "gpa": 3.30,
        "teacher_comments": "أداء ممتاز في الجانب العملي، ينصح بمراجعة المفاهيم النظرية",
        "submission_date": "2024-11-15T14:30:00.000000Z"
    }
}
```

---

## 4. Exam Management

### Get Student Exams
**GET** `/api/student/exams`

**Description:** 
Retrieves all exams available to the authenticated student based on their enrolled courses.

**Query Parameters:**
- `course_id` (optional): Filter by specific course
- `status` (optional): pending, active, completed
- `upcoming` (optional): true - show only upcoming exams

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/student/exams?status=active" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 8,
            "title": "امتحان منتصف الفصل - مقدمة في البرمجة",
            "description": "اختبار شامل لمفاهيم البرمجة الأساسية",
            "course": {
                "id": 1,
                "name": "مقدمة في البرمجة",
                "code": "CS101"
            },
            "duration": 120,
            "total_marks": 100,
            "start_time": "2024-12-01T10:00:00.000000Z",
            "end_time": "2024-12-01T12:00:00.000000Z",
            "status": "active",
            "is_published": true,
            "is_open": true,
            "my_attempt": null,
            "remaining_time": 115,
            "can_attempt": true
        }
    ]
}
```

### Get Exam Details
**GET** `/api/student/exams/{exam}`

**Description:** 
Retrieves detailed information about a specific exam including questions (if active) and previous attempts.

**Example Response:**
```json
{
    "status": "success",
    "data": {
        "id": 8,
        "title": "امتحان منتصف الفصل - مقدمة في البرمجة",
        "description": "اختبار شامل يغطي الوحدات 1-5",
        "course": {
            "id": 1,
            "name": "مقدمة في البرمجة",
            "code": "CS101"
        },
        "duration": 120,
        "total_marks": 100,
        "status": "active",
        "instructions": "اقرأ كل سؤال بعناية قبل الإجابة. لديك محاولة واحدة فقط.",
        "questions": [
            {
                "id": 15,
                "question_text": "ما هو الهدف من استخدام الحلقات في البرمجة؟",
                "question_type": "multiple_choice",
                "marks": 5,
                "options": [
                    {"id": 1, "text": "تكرار تنفيذ مجموعة من التعليمات"},
                    {"id": 2, "text": "إنشاء متغيرات جديدة"},
                    {"id": 3, "text": "حذف البيانات من الذاكرة"},
                    {"id": 4, "text": "ربط البرنامج بالإنترنت"}
                ]
            }
        ],
        "my_attempts": [],
        "can_attempt": true,
        "remaining_time": 120
    }
}
```

### Submit Exam Attempt
**POST** `/api/student/exams/{exam}/submit`

**Description:** 
Submits answers for an exam attempt.

**Request Body:**
```json
{
    "answers": [
        {
            "question_id": 15,
            "selected_option_id": 1,
            "answer_text": "تكرار تنفيذ مجموعة من التعليمات"
        },
        {
            "question_id": 16,
            "answer_text": "هنا إجابة السؤال المقالي..."
        }
    ],
    "time_taken": 85
}
```

**Example Response:**
```json
{
    "status": "success",
    "message": "تم تسليم الامتحان بنجاح",
    "data": {
        "attempt_id": 25,
        "score": 85,
        "total_marks": 100,
        "percentage": 85.0,
        "grade": "B+",
        "submitted_at": "2024-12-01T11:25:00.000000Z",
        "time_taken": 85,
        "auto_graded": true
    }
}
```

### Get Exam Results
**GET** `/api/student/exams/{exam}/results`

**Description:** 
Retrieves the results of completed exam attempts for the authenticated student.

**Example Response:**
```json
{
    "status": "success",
    "data": {
        "exam": {
            "id": 8,
            "title": "امتحان منتصف الفصل - مقدمة في البرمجة",
            "total_marks": 100
        },
        "attempt": {
            "id": 25,
            "score": 85,
            "percentage": 85.0,
            "grade": "B+",
            "submitted_at": "2024-12-01T11:25:00.000000Z",
            "time_taken": 85,
            "is_graded": true
        },
        "answers": [
            {
                "question": {
                    "id": 15,
                    "question_text": "ما هو الهدف من استخدام الحلقات؟",
                    "marks": 5
                },
                "my_answer": "تكرار تنفيذ مجموعة من التعليمات",
                "correct_answer": "تكرار تنفيذ مجموعة من التعليمات",
                "is_correct": true,
                "marks_obtained": 5
            }
        ]
    }
}
```

---

## 5. Schedule Management

### Get Student Schedules
**GET** `/api/student/schedules`

**Description:** 
Retrieves the class schedule for the authenticated student based on their group assignment.

**Query Parameters:**
- `day` (optional): Filter by specific day (Monday, Tuesday, etc.)
- `week` (optional): Filter by specific week (YYYY-MM-DD format for week start)

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/student/schedules?day=Sunday" \
  -H "Authorization: Bearer {token}"
```

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
                "name": "مقدمة في البرمجة",
                "code": "CS101"
            },
            "group": {
                "id": 1,
                "name": "المجموعة الأولى"
            },
            "teacher": {
                "id": 2,
                "name": "د. سارة أحمد"
            }
        }
    ]
}
```

### Get Schedule Details
**GET** `/api/student/schedules/{schedule}`

**Description:** 
Retrieves detailed information about a specific class schedule.

### Get Group Schedules
**GET** `/api/student/groups/{group}/schedules`

**Description:** 
Retrieves all schedules for a specific group (useful for group representatives).

---

## 6. Notification Management

### Get All Notifications
**GET** `/api/notifications`

**Description:** 
Retrieves all notifications for the authenticated student including system announcements, grade updates, fee reminders, and course notifications.

**Query Parameters:**
- `page` (optional): Page number for pagination (default: 1)
- `per_page` (optional): Items per page (default: 15)
- `type` (optional): Filter by notification type (system, grade, fee, course, exam)
- `read` (optional): true/false - filter by read status
- `date_from` (optional): Filter notifications from date (YYYY-MM-DD)
- `date_to` (optional): Filter notifications to date (YYYY-MM-DD)

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/notifications?read=false&per_page=10" \
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
                "id": 45,
                "title": "تم نشر درجات امتحان منتصف الفصل",
                "message": "تم نشر درجات امتحان منتصف الفصل لمقرر مقدمة في البرمجة. يمكنك مراجعة درجتك من خلال النظام.",
                "type": "grade",
                "priority": "medium",
                "is_read": false,
                "data": {
                    "course_id": 1,
                    "course_name": "مقدمة في البرمجة",
                    "exam_id": 8,
                    "grade": 85
                },
                "created_at": "2024-11-24T14:30:00.000000Z",
                "read_at": null
            },
            {
                "id": 44,
                "title": "تذكير دفع الرسوم الدراسية",
                "message": "يُرجى دفع الرسوم الدراسية المستحقة قبل تاريخ 2024-12-31 لتجنب الرسوم الإضافية.",
                "type": "fee",
                "priority": "high",
                "is_read": true,
                "data": {
                    "fee_id": 15,
                    "amount": 4000.00,
                    "due_date": "2024-12-31"
                },
                "created_at": "2024-11-20T09:00:00.000000Z",
                "read_at": "2024-11-21T10:15:00.000000Z"
            }
        ],
        "total": 25,
        "per_page": 10,
        "last_page": 3,
        "from": 1,
        "to": 10
    }
}
```

### Get Unread Notifications Count
**GET** `/api/notifications/unread-count`

**Description:** 
Retrieves the count of unread notifications for the authenticated student. Useful for displaying notification badges in UI.

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/notifications/unread-count" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
    "status": "success",
    "data": {
        "unread_count": 8,
        "by_type": {
            "system": 2,
            "grade": 3,
            "fee": 2,
            "course": 1,
            "exam": 0
        },
        "priority_breakdown": {
            "high": 2,
            "medium": 4,
            "low": 2
        }
    }
}
```

### Get Notification Details
**GET** `/api/notifications/{notification}`

**Description:** 
Retrieves detailed information about a specific notification and automatically marks it as read.

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/notifications/45" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
    "status": "success",
    "data": {
        "id": 45,
        "title": "تم نشر درجات امتحان منتصف الفصل",
        "message": "تم نشر درجات امتحان منتصف الفصل لمقرر مقدمة في البرمجة. يمكنك مراجعة درجتك من النظام والاطلاع على التفاصيل والملاحظات من الأستاذ المشرف.",
        "type": "grade",
        "priority": "medium",
        "is_read": true,
        "sender": {
            "type": "system",
            "name": "نظام إدارة التعليم"
        },
        "data": {
            "course_id": 1,
            "course_name": "مقدمة في البرمجة",
            "course_code": "CS101",
            "exam_id": 8,
            "exam_title": "امتحان منتصف الفصل",
            "grade": 85,
            "total_marks": 100,
            "percentage": 85.0,
            "letter_grade": "B+",
            "teacher_comments": "أداء ممتاز، استمر على هذا المستوى"
        },
        "actions": [
            {
                "label": "عرض الدرجة",
                "type": "redirect",
                "url": "/api/student/grades/45"
            },
            {
                "label": "عرض تفاصيل الامتحان",
                "type": "redirect", 
                "url": "/api/student/exams/8/results"
            }
        ],
        "created_at": "2024-11-24T14:30:00.000000Z",
        "read_at": "2024-11-24T15:45:00.000000Z"
    }
}
```

### Mark Notification as Read
**POST** `/api/notifications/{notification}/read`

**Description:** 
Manually marks a specific notification as read without retrieving its full details.

**Example Request:**
```bash
curl -X POST "https://your-domain.com/api/notifications/45/read" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
    "status": "success",
    "message": "تم تحديد الإشعار كمقروء",
    "data": {
        "notification_id": 45,
        "is_read": true,
        "read_at": "2024-11-24T15:45:00.000000Z"
    }
}
```

### Mark All Notifications as Read
**POST** `/api/notifications/mark-all-read`

**Description:** 
Marks all unread notifications for the authenticated student as read.

**Request Body (Optional):**
```json
{
    "type": "grade",
    "before_date": "2024-11-01"
}
```

**Example Request:**
```bash
curl -X POST "https://your-domain.com/api/notifications/mark-all-read" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"type": "grade"}'
```

**Example Response:**
```json
{
    "status": "success",
    "message": "تم تحديد جميع الإشعارات كمقروءة",
    "data": {
        "marked_count": 8,
        "total_notifications": 25,
        "remaining_unread": 0
    }
}
```

### Delete Notification
**DELETE** `/api/notifications/{notification}`

**Description:** 
Deletes a specific notification for the authenticated student.

**Example Request:**
```bash
curl -X DELETE "https://your-domain.com/api/notifications/45" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
    "status": "success",
    "message": "تم حذف الإشعار بنجاح",
    "data": {
        "deleted_notification_id": 45
    }
}
```

### Delete All Notifications
**DELETE** `/api/notifications`

**Description:** 
Deletes all notifications for the authenticated student with optional filtering.

**Request Body (Optional):**
```json
{
    "type": "grade",
    "read_only": true,
    "before_date": "2024-10-01"
}
```

**Example Request:**
```bash
curl -X DELETE "https://your-domain.com/api/notifications" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"read_only": true}'
```

**Example Response:**
```json
{
    "status": "success",
    "message": "تم حذف الإشعارات بنجاح",
    "data": {
        "deleted_count": 15,
        "remaining_count": 10
    }
}
```

### Notification Types

The system supports the following notification types:

| Type | Description | Priority Levels |
|------|-------------|-----------------|
| `system` | إشعارات النظام العامة والإعلانات | high, medium, low |
| `grade` | إشعارات الدرجات والنتائج | medium, low |
| `fee` | إشعارات الرسوم والمدفوعات | high, medium |
| `course` | إشعارات المقررات والمحاضرات | medium, low |
| `exam` | إشعارات الامتحانات والاختبارات | high, medium |
| `attendance` | إشعارات الحضور والغياب | medium, low |
| `schedule` | إشعارات الجدول الدراسي | low |

### Notification Priority Levels

| Priority | Description | Use Cases |
|----------|-------------|-----------|
| `high` | إشعارات عاجلة ومهمة | رسوم مستحقة، امتحانات قريبة، إعلانات مهمة |
| `medium` | إشعارات مهمة غير عاجلة | درجات جديدة، تحديثات المقررات |
| `low` | إشعارات معلوماتية | تذكيرات عامة، نصائح دراسية |

---

## 7. Student Attendance Management

### Get Student Attendance
**GET** `/api/student/attendance`

**Description:** 
Retrieves attendance records for the authenticated student.

**Query Parameters:**
- `course_id` (optional): Filter by specific course
- `date_from` (optional): Filter from date (YYYY-MM-DD)
- `date_to` (optional): Filter to date (YYYY-MM-DD)
- `status` (optional): present, absent, late, excused

**Example Request:**
```bash
curl -X GET "https://your-domain.com/api/student/attendance?course_id=1" \
  -H "Authorization: Bearer {token}"
```

**Example Response:**
```json
{
    "status": "success",
    "data": [
        {
            "id": 150,
            "schedule": {
                "id": 1,
                "day": "Sunday",
                "start_time": "08:00:00",
                "end_time": "10:00:00",
                "course": {
                    "id": 1,
                    "name": "مقدمة في البرمجة",
                    "code": "CS101"
                }
            },
            "attendance_date": "2024-11-24",
            "status": "present",
            "status_name": "حاضر",
            "notes": "حضور منتظم",
            "marked_at": "2024-11-24T08:05:00.000000Z"
        }
    ]
}
```

### Get Attendance Details
**GET** `/api/student/attendance/{attendance}`

**Description:** 
Retrieves detailed information about a specific attendance record.

### Get Attendance Statistics
**GET** `/api/student/attendance/stats`

**Description:** 
Retrieves comprehensive attendance statistics for the authenticated student.

**Query Parameters:**
- `course_id` (optional): Filter statistics by specific course
- `period` (optional): week, month, semester (default: semester)

**Example Response:**
```json
{
    "status": "success",
    "data": {
        "overall_stats": {
            "total_classes": 48,
            "attended": 44,
            "absent": 3,
            "late": 1,
            "excused": 2,
            "attendance_rate": 91.7
        },
        "course_stats": [
            {
                "course": {
                    "id": 1,
                    "name": "مقدمة في البرمجة",
                    "code": "CS101"
                },
                "total_classes": 16,
                "attended": 15,
                "absent": 1,
                "late": 0,
                "attendance_rate": 93.8
            }
        ],
        "monthly_breakdown": [
            {
                "month": "سبتمبر 2024",
                "total": 12,
                "attended": 11,
                "rate": 91.7
            },
            {
                "month": "أكتوبر 2024", 
                "total": 16,
                "attended": 15,
                "rate": 93.8
            }
        ],
        "warnings": [
            "نسبة الحضور في مقرر الرياضيات أقل من 75%"
        ]
    }
}
```

---

## Error Handling

### Common Error Responses

**Validation Error (422):**
```json
{
    "status": "error",
    "message": "Validation error",
    "errors": {
        "amount": ["The amount field is required."],
        "payment_method": ["The selected payment method is invalid."]
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
    "message": "Access denied. Student role required."
}
```

**Not Found (404):**
```json
{
    "status": "error",
    "message": "Resource not found"
}
```

**Course Access Denied (403):**
```json
{
    "status": "error",
    "message": "You are not enrolled in this course"
}
```

**Exam Not Available (400):**
```json
{
    "status": "error",
    "message": "This exam is not currently available for taking"
}
```

**Payment Processing Error (400):**
```json
{
    "status": "error",
    "message": "Payment processing failed. Please try again."
}
```

---

## Rate Limiting
API requests are limited to 60 requests per minute per student.

## Data Privacy
- Students can only access their own data
- Grade information is only available for courses they are enrolled in
- Payment information is strictly confidential and encrypted

## Testing
Use the provided test credentials:
- **Student**: student@test.com / password
- **Student 2**: student2@otu.edu / password

## Support
For technical support regarding the Student API, contact:
- Email: support@otu.edu
- Phone: +966-XX-XXXXXXX
- Student Portal: https://student.otu.edu/support

## Mobile App Integration
This API is designed to work seamlessly with the OTU Student Mobile App. For mobile-specific considerations:
- Use appropriate pagination for mobile data usage
- Implement proper caching for offline access
- Handle network timeouts gracefully
- Use push notifications for important updates (exams, grades, fees)

## Changelog
- **v1.0** - Initial release with basic student functionality
- **v1.1** - Added payment processing and attendance statistics
- **v1.2** - Enhanced exam system with real-time features
- **v1.3** - Added comprehensive grade analytics and reporting 