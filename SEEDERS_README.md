# OTU Educational Management System - Database Seeders

This document explains the comprehensive database seeders created for the OTU Educational Management System. These seeders populate the database with realistic test data for all system components.

## 🚀 Quick Start

### Method 1: Using the Seeder Script (Recommended)
```bash
php seed_database.php
```

### Method 2: Using Laravel Artisan
```bash
php artisan migrate:fresh --seed
```

### Method 3: Run Seeders Individually
```bash
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=GroupSeeder
php artisan db:seed --class=AdminUserSeeder
# ... and so on
```

## 📋 Seeder Overview

### 1. **RoleSeeder**
Creates the three main user roles:
- **Admin** - Full system access
- **Teacher** - Course and student management
- **Student** - Limited access to own data

### 2. **GroupSeeder**
Creates student groups:
- مجموعة هندسة البرمجيات
- مجموعة تطوير الويب
- مجموعة الذكاء الاصطناعي
- مجموعة تحليل البيانات (inactive)

### 3. **AdminUserSeeder**
Creates the main admin user:
- **Email**: admin@otu.edu
- **Password**: password123

### 4. **UsersTableSeeder**
Creates comprehensive user data:
- **8 Teachers** with Arabic names and @otu.edu emails
- **25 Students** with Arabic names and @otu.edu emails
- **Test Users** for easy testing:
  - teacher@test.com / password
  - student@test.com / password

### 5. **CourseSeeder**
Creates 12 academic courses:
- CS101: مقدمة في البرمجة
- CS102: هياكل البيانات والخوارزميات
- CS201: البرمجة الشيئية
- CS202: قواعد البيانات
- CS301: تطوير تطبيقات الويب
- CS302: هندسة البرمجيات
- CS401: الذكاء الاصطناعي
- CS402: أمن المعلومات
- MATH101: الرياضيات للحاسوب
- ENG101: اللغة الإنجليزية التقنية
- CS501: مشروع التخرج
- CS303: الشبكات والاتصالات

### 6. **ScheduleSeeder**
Creates class schedules:
- Assigns courses to groups
- Creates time slots for each day
- Assigns classrooms
- Covers Monday to Friday

### 7. **ExamSeeder**
Creates various types of exams:
- **Midterm exams** for each course
- **Final exams** for each course
- **Quizzes** (2-4 per course)
- Different question types and durations

### 8. **GradeSeeder**
Creates comprehensive grade records:
- **Assignment grades** (2-4 per course per student)
- **Quiz grades** (3-5 per course per student)
- **Midterm exam grades**
- **Final exam grades**
- **Participation grades**
- Realistic score distributions with letter grades

### 9. **FeeSeeder**
Creates various fee types:
- **Tuition fees** (8,000-15,000 SAR)
- **Registration fees** (500-1,000 SAR)
- **Exam fees** (200-500 SAR)
- **Overdue fees** for testing
- Different payment statuses

### 10. **FeePaymentSeeder**
Creates payment records:
- **Completed payments** for paid fees
- **Pending payments** for testing
- Multiple payment methods
- Payment references and notes

### 11. **AttendanceSeeder**
Creates attendance records:
- **Teacher attendance** for last 30 days
- **Student attendance** for last 60 days
- Realistic attendance patterns
- Different status types (present, absent, late, excused)

### 12. **AdminRequestSeeder**
Creates student administrative requests:
- **Leave requests**
- **Certificate requests**
- **Group transfer requests**
- **Course withdrawal requests**
- **Absence excuse requests**
- **Transcript requests**
- Various priorities and statuses

### 13. **NotificationSeeder**
Creates system notifications:
- **System notifications** for all users
- **Academic notifications** for students
- **Financial notifications** for students
- **Exam notifications** for students
- **Teacher-specific notifications**
- Different priority levels

### 14. **MessageSeeder**
Creates internal messaging data:
- Messages between different user types
- Various message categories
- Read/unread status

## 🔑 Test Login Credentials

### Admin Access
- **Email**: admin@otu.edu
- **Password**: password123

### Teacher Access
- **Email**: teacher@test.com
- **Password**: password
- **Alternative**: Any teacher email from the list with password123

### Student Access
- **Email**: student@test.com
- **Password**: password
- **Alternative**: student1@otu.edu to student25@otu.edu with password123

## 📊 Data Statistics

After running all seeders, you'll have:
- **~35 Users** (1 admin, 9 teachers, 25 students)
- **12 Courses** with full relationships
- **4 Groups** with assigned students
- **~60 Schedules** covering all groups and courses
- **~150 Exams** (midterms, finals, quizzes)
- **~2000+ Grades** across all assessment types
- **~100 Fees** with various statuses
- **~200 Fee Payments** with different methods
- **~800 Teacher Attendance** records
- **~3000 Student Attendance** records
- **~75 Admin Requests** with various types
- **~500 Notifications** for all users
- **Messages** between users

## 🧪 Testing Scenarios

The seeders create data for testing:

### Admin Dashboard Testing
- View system statistics
- Monitor pending requests
- Check teacher attendance
- Review due fees
- Manage notifications

### Financial Management Testing
- Process fee payments
- Generate payment reports
- Handle overdue fees
- Track payment methods

### Academic Management Testing
- Manage course enrollments
- Process grade submissions
- Generate academic reports
- Handle exam scheduling

### Attendance Management Testing
- Record daily attendance
- Generate attendance reports
- Handle absence excuses
- Monitor attendance patterns

### Communication Testing
- Send system notifications
- Process admin requests
- Handle internal messaging
- Manage user communications

## 🔧 Customization

To modify the seeded data:

1. **Edit individual seeders** in `database/seeders/`
2. **Adjust data quantities** by changing loop counts
3. **Modify realistic data** by updating arrays of names, courses, etc.
4. **Add new data types** by creating additional seeders

## ⚠️ Important Notes

1. **Data Reset**: Running seeders will clear existing data
2. **Foreign Keys**: Seeders handle relationships properly
3. **Realistic Data**: All data uses Arabic names and realistic scenarios
4. **Performance**: Large datasets may take a few minutes to seed
5. **Dependencies**: Seeders run in order to maintain relationships

## 🐛 Troubleshooting

### Common Issues:
1. **Foreign Key Errors**: Ensure migrations are up to date
2. **Memory Issues**: Increase PHP memory limit for large datasets
3. **Timeout Issues**: Increase PHP execution time
4. **Database Connection**: Verify .env database settings

### Solutions:
```bash
# Reset everything
php artisan migrate:fresh --seed

# Run specific seeder
php artisan db:seed --class=SpecificSeeder

# Check seeder status
php artisan migrate:status
```

## 📈 Performance Tips

1. **Use transactions** for large data sets
2. **Disable foreign key checks** temporarily during seeding
3. **Use bulk inserts** where possible
4. **Run seeders in optimal order** to avoid constraint issues

This comprehensive seeding system ensures you have realistic, interconnected data to test every aspect of the OTU Educational Management System! 