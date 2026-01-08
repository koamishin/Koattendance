# Koatendance - QR Code Attendance System

A modern, innovative attendance management system built with Laravel 12, Vue 3, and Inertia.js. Features student QR code-based attendance (teachers scan student QR codes), integrated grading system, seat plan management, automated absent alerts to guardians via email, and a comprehensive analytics dashboard.

## Tech Stack

- **PHP 8.5** with Laravel 12
- **Vue 3** with Inertia.js v2
- **Tailwind CSS v4**
- **Laravel Fortify** for authentication
- **SQLite** database (configurable)
- **Pest v4** for testing

## System Overview

This system is designed to automate attendance tracking using student QR codes scanned by teachers, while providing additional features for educational institutions. Each student has a unique QR code containing their encrypted ID, and teachers scan these codes during class to mark attendance. The system supports multiple roles (teachers, students, guardians, admins) and provides real-time analytics and automated notifications.

## Data Structure

### Core Entities

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              USER MODEL                                      │
├─────────────────────────────────────────────────────────────────────────────┤
│ id: UUID                    │ Primary key                                   │
│ name: string                │ User's full name                              │
│ email: string               │ Unique email address                          │
│ email_verified_at: datetime │ When email was verified                       │
│ password: string            │ Hashed password                               │
│ two_factor_secret: string   │ 2FA secret key (optional)                     │
│ two_factor_recovery_codes   │ Recovery codes for 2FA (optional)             │
│ two_factor_confirmed_at     │ When 2FA was enabled                          │
│ role: enum                  │ 'admin', 'teacher', 'student', 'guardian'     │
│ profile_photo: string|null  │ Path to profile photo                         │
│ created_at, updated_at      │ Timestamps                                    │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Student Management

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              STUDENT MODEL                                   │
├─────────────────────────────────────────────────────────────────────────────┤
│ id: UUID                    │ Primary key                                   │
│ user_id: UUID               │ FK to users table                             │
│ student_id: string          │ Unique student number (e.g., 2024-001)        │
│ first_name: string          │ First name                                    │
│ last_name: string           │ Last name                                     │
│ middle_name: string|null    │ Middle name                                   │
│ birth_date: date            │ Date of birth                                 │
│ gender: enum                │ 'male', 'female', 'other'                     │
│ address: text               │ Full address                                  │
│ phone: string               │ Contact number                                │
│ guardian_id: UUID|null      │ FK to guardians table                         │
│ current_grade_level: int    │ Current grade level (1-12)                    │
│ section_id: UUID|null       │ FK to sections table                          │
│ status: enum                │ 'active', 'inactive', 'graduated', 'transferred'│
│ enrollment_date: date       │ Date of enrollment                            │
│ qr_code_data: string        │ Encrypted QR code data (student ID + salt)    │
│ qr_code_active: boolean     │ Whether QR code is active                     │
│ qr_code_regenerated_at: datetime │ Last QR code regeneration time           │
│ created_at, updated_at      │ Timestamps                                    │
└─────────────────────────────────────────────────────────────────────────────┘
│ RELATIONS:                                                                  │
│ - belongsTo(User)                                                           │
│ - belongsTo(Guardian) via guardian_id                                       │
│ - belongsTo(Section) via section_id                                         │
│ - hasMany(AttendanceRecords)                                                │
│ - hasMany(Grades)                                                           │
│ - hasMany(SeatAllocations)                                                  │
│ - hasMany(QrScanEvents)                                                     │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Guardian Model

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                             GUARDIAN MODEL                                   │
├─────────────────────────────────────────────────────────────────────────────┤
│ id: UUID                    │ Primary key                                   │
│ user_id: UUID               │ FK to users table                             │
│ first_name: string          │ First name                                    │
│ last_name: string           │ Last name                                     │
│ email: string               │ Email for alerts (may differ from login)      │
│ phone: string               │ Phone number                                  │
│ relationship: enum          │ 'parent', 'guardian', 'other'                 │
│ is_primary: boolean         │ Primary contact flag                          │
│ alert_preferences: json     │ Notification preferences                      │
│   - email_enabled: boolean  │   Email notifications enabled                 │
│   - sms_enabled: boolean    │   SMS notifications (future)                  │
│   - daily_digest: boolean   │   Daily absent summary                        │
│   - immediate_alert: boolean│   Immediate alert on absence                  │
│   - threshold_alerts: int   │   Alert after X absences                      │
│ created_at, updated_at      │ Timestamps                                    │
└─────────────────────────────────────────────────────────────────────────────┘
│ RELATIONS:                                                                  │
│ - belongsTo(User)                                                           │
│ - hasMany(Students)                                                         │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Course/Subject Management

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              COURSE MODEL                                    │
├─────────────────────────────────────────────────────────────────────────────┤
│ id: UUID                    │ Primary key                                   │
│ code: string                │ Unique course code (e.g., MATH101)            │
│ name: string                │ Full course name                              │
│ description: text|null      │ Course description                            │
│ grade_level: int            │ Applicable grade level (1-12 or 7-12)         │
│ credits: int                │ Credit hours                                  │
│ type: enum                  │ 'core', 'elective', 'special'                 │
│ is_active: boolean          │ Whether course is offered                     │
│ created_at, updated_at      │ Timestamps                                    │
└─────────────────────────────────────────────────────────────────────────────┘
│ RELATIONS:                                                                  │
│ - hasMany(ClassSessions)                                                    │
│ - hasMany(Grades)                                                           │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Section Model

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              SECTION MODEL                                   │
├─────────────────────────────────────────────────────────────────────────────┤
│ id: UUID                    │ Primary key                                   │
│ name: string                │ Section name (e.g., "Rose", "Magnet")         │
│ grade_level: int            │ Grade level                                   │
│ academic_year: string       │ Academic year (e.g., "2024-2025")             │
│ semester: enum              │ 'first', 'second', 'summer'                   │
│ advisor_id: UUID            │ FK to teachers table                          │
│ max_students: int           │ Maximum capacity                              │
│ schedule_template: json     │ Weekly schedule configuration                 │
│   - monday: [{start, end, course_id}]                                       │
│   - tuesday: [...]                                                          │
│   - ...                                                                     │
│ created_at, updated_at      │ Timestamps                                    │
└─────────────────────────────────────────────────────────────────────────────┘
│ RELATIONS:                                                                  │
│ - belongsTo(Teacher, 'advisor')                                             │
│ - hasMany(Students)                                                         │
│ - hasMany(ClassSessions)                                                    │
│ - hasMany(SeatPlans)                                                        │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Teacher Model

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              TEACHER MODEL                                   │
├─────────────────────────────────────────────────────────────────────────────┤
│ id: UUID                    │ Primary key                                   │
│ user_id: UUID               │ FK to users table                             │
│ employee_id: string         │ Unique employee ID                            │
│ first_name: string          │ First name                                    │
│ last_name: string           │ Last name                                     │
│ email: string               │ Contact email                                 │
│ phone: string               │ Phone number                                  │
│ department: string          │ Department/subject area                       │
│ status: enum                │ 'active', 'inactive'                          │
│ created_at, updated_at      │ Timestamps                                    │
└─────────────────────────────────────────────────────────────────────────────┘
│ RELATIONS:                                                                  │
│ - belongsTo(User)                                                           │
│ - hasMany(AssignedCourses)                                                  │
│ - hasMany(ClassSessions)                                                    │
│ - hasMany(Sections) as advisor                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Class Session (Individual Class Meeting)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          CLASS SESSION MODEL                                 │
├─────────────────────────────────────────────────────────────────────────────┤
│ id: UUID                    │ Primary key                                   │
│ course_id: UUID             │ FK to courses table                           │
│ section_id: UUID            │ FK to sections table                          │
│ teacher_id: UUID            │ FK to teachers table                          │
│ room: string                │ Room location                                 │
│ scheduled_date: date        │ Date of class                                 │
│ start_time: time            │ Start time                                    │
│ end_time: time              │ End time                                      │
│ status: enum                │ 'scheduled', 'in_progress', 'completed',      │
│                             │ 'cancelled', 'makeup'                         │
│ attendance_mode: enum       │ 'qr_code', 'manual', 'hybrid'                 │
│ notes: text|null            │ Additional notes                              │
│ created_at, updated_at      │ Timestamps                                    │
└─────────────────────────────────────────────────────────────────────────────┘
│ RELATIONS:                                                                  │
│ - belongsTo(Course)                                                         │
│ - belongsTo(Section)                                                        │
│ - belongsTo(Teacher)                                                        │
│ - hasMany(AttendanceRecords)                                                │
│ - hasMany(QrScanEvents)                                                     │
│ - hasMany(SeatAllocations)                                                  │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Attendance Records

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          ATTENDANCE RECORD MODEL                             │
├─────────────────────────────────────────────────────────────────────────────┤
│ id: UUID                    │ Primary key                                   │
│ session_id: UUID            │ FK to class_sessions table                    │
│ student_id: UUID            │ FK to students table                          │
│ status: enum                │ 'present', 'absent', 'late', 'excused',       │
│                             │ 'early_departure'                             │
│ timestamp: datetime         │ When attendance was recorded                  │
│ recorded_by: UUID           │ FK to teachers table (who scanned/marked)     │
│ scan_event_id: UUID|null    │ FK to qr_scan_events table                    │
│ device_info: json|null      │ Device used for scanning                      │
│ notes: text|null            │ Attendance notes                              │
│ created_at, updated_at      │ Timestamps                                    │
└─────────────────────────────────────────────────────────────────────────────┘
│ RELATIONS:                                                                  │
│ - belongsTo(ClassSession)                                                   │
│ - belongsTo(Student)                                                        │
│ - belongsTo(Teacher, 'recorded_by')                                         │
│ - belongsTo(QrScanEvent, optional)                                          │
└─────────────────────────────────────────────────────────────────────────────┘
│ INDEXES:                                                                    │
│ - Unique: (session_id, student_id)                                          │
│ - Index: (student_id, timestamp)                                            │
│ - Index: (status, timestamp)                                                │
└─────────────────────────────────────────────────────────────────────────────┘
```

### QR Scan Events (Teacher Scanning Student QR Codes)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          QR SCAN EVENT MODEL                                 │
├─────────────────────────────────────────────────────────────────────────────┤
│ id: UUID                    │ Primary key                                   │
│ session_id: UUID            │ FK to class_sessions table                    │
│ teacher_id: UUID            │ FK to teachers table (who scanned)            │
│ student_id: UUID            │ FK to students table (scanned student)        │
│ scanned_at: datetime        │ When the scan occurred                        │
│ device_info: json|null      │ Device used for scanning                      │
│   - device_type: string     │   mobile, tablet, desktop                     │
│   - browser: string         │   Browser name                                │
│   - os: string              │   Operating system                            │
│ location: json|null         │ GPS coordinates (if available)                │
│ status: enum                │ 'success', 'invalid', 'already_scanned',      │
│                             │ 'expired', 'inactive'                         │
│ error_message: string|null  │ Error message if scan failed                  │
│ created_at, updated_at      │ Timestamps                                    │
└─────────────────────────────────────────────────────────────────────────────┘
│ RELATIONS:                                                                  │
│ - belongsTo(ClassSession)                                                   │
│ - belongsTo(Teacher)                                                        │
│ - belongsTo(Student)                                                        │
│ - hasOne(AttendanceRecord)                                                  │
└─────────────────────────────────────────────────────────────────────────────┘
│ INDEXES:                                                                    │
│ - Unique: (session_id, student_id) - prevent duplicate scans                │
│ - Index: (teacher_id, scanned_at)                                           │
│ - Index: (session_id, scanned_at)                                           │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Seat Plan Management

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              SEAT PLAN MODEL                                 │
├─────────────────────────────────────────────────────────────────────────────┤
│ id: UUID                    │ Primary key                                   │
│ section_id: UUID            │ FK to sections table                          │
│ name: string                │ Seat plan name                                │
│ layout: json                │ Room layout configuration                     │
│   - rows: int               │ Number of rows                                │
│   - columns: int            │ Number of columns                             │
│   - seats: [{id, row, column, type}]                                        │
│   - obstacles: [{row, column, type}]                                        │
│   - dimensions: {width, height}                                             │
│ is_active: boolean          │ Whether currently in use                      │
│ academic_year: string       │ Academic year                                 │
│ semester: enum              │ Semester                                      │
│ created_at, updated_at      │ Timestamps                                    │
└─────────────────────────────────────────────────────────────────────────────┘
│ RELATIONS:                                                                  │
│ - belongsTo(Section)                                                        │
│ - hasMany(SeatAllocations)                                                  │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                          SEAT ALLOCATION MODEL                               │
├─────────────────────────────────────────────────────────────────────────────┤
│ id: UUID                    │ Primary key                                   │
│ seat_plan_id: UUID          │ FK to seat_plans table                        │
│ student_id: UUID            │ FK to students table                          │
│ session_id: UUID|null       │ FK to class_sessions table (optional)         │
│ seat_id: string             │ Seat identifier in layout                     │
│ row: int                    │ Row number                                    │
│ column: int                 │ Column number                                 │
│ effective_from: date        │ Start date                                    │
│ effective_to: date|null     │ End date (null = permanent)                   │
│ created_at, updated_at      │ Timestamps                                    │
└─────────────────────────────────────────────────────────────────────────────┘
│ RELATIONS:                                                                  │
│ - belongsTo(SeatPlan)                                                       │
│ - belongsTo(Student)                                                        │
│ - belongsTo(ClassSession, optional)                                         │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Grading System

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              GRADE MODEL                                     │
├─────────────────────────────────────────────────────────────────────────────┤
│ id: UUID                    │ Primary key                                   │
│ student_id: UUID            │ FK to students table                          │
│ course_id: UUID             │ FK to courses table                           │
│ class_session_id: UUID|null │ FK to class_sessions table (optional)         │
│ grade_type: enum            │ 'assignment', 'quiz', 'exam', 'project',      │
│                             │ 'participation', 'final'                      │
│ grade_item: string          │ Specific item name (e.g., "Midterm Exam")    │
│ score: decimal(5,2)         │ Score achieved                                │
│ max_score: decimal(5,2)     │ Maximum possible score                        │
│ percentage: decimal(5,2)    │ Calculated percentage                         │
│ grade_letter: string|null   │ Letter grade (A, B+, etc.)                   │
│ weight: decimal(5,2)        │ Weight in final grade calculation             │
│ academic_year: string       │ Academic year                                 │
│ semester: enum              │ Semester                                      │
│ grading_period: enum        │ 'prelim', 'midterm', 'prefinal', 'final'      │
│ feedback: text|null         │ Teacher feedback                              │
│ recorded_by: UUID           │ FK to teachers table                          │
│ created_at, updated_at      │ Timestamps                                    │
└─────────────────────────────────────────────────────────────────────────────┘
│ RELATIONS:                                                                  │
│ - belongsTo(Student)                                                        │
│ - belongsTo(Course)                                                         │
│ - belongsTo(Teacher, 'recorded_by')                                         │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                           GRADE SCALE MODEL                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│ id: UUID                    │ Primary key                                   │
│ name: string                │ Scale name (e.g., "Standard Grading")         │
│ description: text|null      │ Description                                   │
│ is_default: boolean         │ Default scale for system                      │
│ academic_year: string       │ Academic year                                 │
│ is_active: boolean          │ Whether in use                                │
│ created_at, updated_at      │ Timestamps                                    │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                      GRADE SCALE ITEM MODEL                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│ id: UUID                    │ Primary key                                   │
│ grade_scale_id: UUID        │ FK to grade_scales table                      │
│ letter_grade: string        │ Letter grade (A, B+, etc.)                    │
│ min_percentage: decimal     │ Minimum percentage                            │
│ max_percentage: decimal     │ Maximum percentage                            │
│ gpa_points: decimal(3,2)    │ GPA points (e.g., 4.00)                       │
│ description: string|null    │ Description                                   │
│ created_at, updated_at      │ Timestamps                                    │
└─────────────────────────────────────────────────────────────────────────────┘
│ RELATIONS:                                                                  │
│ - belongsTo(GradeScale)                                                     │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Alert Configuration

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        ALERT CONFIGURATION MODEL                             │
├─────────────────────────────────────────────────────────────────────────────┤
│ id: UUID                    │ Primary key                                   │
│ name: string                │ Configuration name                            │
│ description: text|null      │ Description                                   │
│ alert_type: enum            │ 'absence_threshold', 'consecutive_absence',   │
│                             │ 'pattern_detected', 'daily_digest'            │
│ condition: json             │ Alert condition                               │
│   - threshold: int          │   Number of absences before alert             │
│   - consecutive: boolean    │   Check for consecutive absences             │
│   - days_window: int        │   Days to analyze                             │
│ notification_channels: json │ Channels to notify                            │
│   - email: boolean          │   Email to guardian                           │
│   - sms: boolean            │   SMS (future feature)                        │
│   - push: boolean           │   Push notification (future)                  │
│ is_active: boolean          │ Whether rule is active                        │
│ priority: int               │ Alert priority (lower = higher priority)      │
│ created_at, updated_at      │ Timestamps                                    │
└─────────────────────────────────────────────────────────────────────────────┘
│ RELATIONS:                                                                  │
│ - hasMany(AlertLogs)                                                        │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                             ALERT LOG MODEL                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│ id: UUID                    │ Primary key                                   │
│ alert_config_id: UUID       │ FK to alert_configurations table              │
│ student_id: UUID            │ FK to students table                          │
│ guardian_id: UUID           │ FK to guardians table                         │
│ alert_type: enum            │ Type of alert                                 │
│ message: text               │ Alert message                                 │
│ data: json                  │ Additional data (absence count, dates, etc.)  │
│ status: enum                │ 'pending', 'sent', 'failed', 'acknowledged'   │
│ sent_at: datetime|null      │ When alert was sent                           │
│ acknowledged_at: datetime   │ When guardian acknowledged                    │
│ created_at, updated_at      │ Timestamps                                    │
└─────────────────────────────────────────────────────────────────────────────┘
│ RELATIONS:                                                                  │
│ - belongsTo(AlertConfiguration)                                             │
│ - belongsTo(Student)                                                        │
│ - belongsTo(Guardian)                                                       │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Analytics Models

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          ANALYTICS SNAPSHOT MODEL                            │
├─────────────────────────────────────────────────────────────────────────────┤
│ id: UUID                    │ Primary key                                   │
│ entity_type: enum           │ 'student', 'section', 'course', 'teacher'     │
│ entity_id: UUID             │ ID of entity                                  │
│ date: date                  │ Date of snapshot                              │
│ period_type: enum           │ 'daily', 'weekly', 'monthly', 'term'          │
│ metrics: json               │ Calculated metrics                            │
│   - attendance_rate: float  │   Attendance percentage                       │
│   - total_sessions: int     │   Total class sessions                        │
│   - present_count: int      │   Days present                                │
│   - absent_count: int       │   Days absent                                 │
│   - late_count: int         │   Times late                                  │
│   - average_grade: float    │   Average grade (if applicable)               │
│   - gpa: float              │   GPA (if applicable)                         │
│   - rank: int|null          │   Class rank (if applicable)                  │
│ created_at, updated_at      │ Timestamps                                    │
└─────────────────────────────────────────────────────────────────────────────┘
│ INDEXES:                                                                    │
│ - Unique: (entity_type, entity_id, date, period_type)                       │
│ - Index: (entity_type, entity_id, date)                                     │
└─────────────────────────────────────────────────────────────────────────────┘
```

## Database Relationships Overview

```
USERS
 ├── STUDENTS (one-to-many)
 │    └── ATTENDANCE_RECORDS (one-to-many)
 │    └── GRADES (one-to-many)
 │    └── SEAT_ALLOCATIONS (one-to-many)
 │    └── QR_SCAN_EVENTS (one-to-many)
 │
 ├── TEACHERS (one-to-many)
 │    └── CLASS_SESSIONS (one-to-many)
 │    └── SECTIONS as advisor (one-to-many)
 │    └── GRADES recorded_by (one-to-many)
 │    └── QR_SCAN_EVENTS (one-to-many)
 │    └── ATTENDANCE_RECORDS recorded_by (one-to-many)
 │
 ├── GUARDIANS (one-to-many)
 │    └── STUDENTS (one-to-many)
 │    └── ALERT_LOGS (one-to-many)

COURSES
 ├── CLASS_SESSIONS (one-to-many)
 └── GRADES (one-to-many)

SECTIONS
 ├── STUDENTS (one-to-many)
 ├── CLASS_SESSIONS (one-to-many)
 ├── SEAT_PLANS (one-to-many)
 └── TEACHER as advisor (belongsTo)

CLASS_SESSIONS
 ├── ATTENDANCE_RECORDS (one-to-many)
 ├── QR_SCAN_EVENTS (one-to-many)
 ├── SEAT_ALLOCATIONS (one-to-many)
 ├── COURSE (belongsTo)
 ├── SECTION (belongsTo)
 └── TEACHER (belongsTo)

SEAT_PLANS
 └── SEAT_ALLOCATIONS (one-to-many)

QR_SCAN_EVENTS
 └── ATTENDANCE_RECORD (hasOne)
 ├── CLASS_SESSION (belongsTo)
 ├── TEACHER (belongsTo)
 └── STUDENT (belongsTo)
```

## Key Features Data Flow

### QR Code Attendance Flow

1. **Student QR Code Generation**
    - Each student is assigned a unique QR code
    - QR code contains encrypted student ID and a salt for security
    - QR code can be regenerated (e.g., if compromised)
    - Students can print or save their QR code (ID card, mobile app)

2. **Teacher Scanning Process**
    - Teacher opens attendance session on their device
    - Teacher selects the class/section for the session
    - Teacher scans each student's QR code using device camera
    - System validates:
        - Student exists and is active
        - Student belongs to this section/course
        - QR code is valid and not expired
        - Student hasn't been scanned already for this session
    - On successful scan:
        - QR Scan Event is recorded
        - Attendance record created with `status = 'present'`
        - Timestamp and teacher who scanned are recorded
    - Late arrivals can be marked based on class start time

3. **Handling Non-Scanned Students**
    - After scanning period, teacher views list of enrolled students
    - Students without attendance records are marked as `absent`
    - Teacher can manually override any status
    - Excused absences can be recorded for specific students

4. **Duplicate Scan Prevention**
    - System prevents same student from being scanned twice in one session
    - Returns error: "Student already scanned"
    - Teacher can view scanned students in real-time

### Absent Alert System

1. **Detection**
    - System runs scheduled job after each class session
    - Checks students with `status = 'absent'`
    - Evaluates alert rules

2. **Rule Evaluation**
    - Threshold alerts: X absences in Y days
    - Consecutive alerts: X days in a row
    - Pattern detection: irregular attendance

3. **Notification**
    - System looks up student's guardian(s)
    - Checks guardian preferences
    - Sends email with:
        - Student name
        - Date(s) of absence
        - Course(s) missed
        - Total absence count
        - School contact information

4. **Logging**
    - All alerts logged in `alert_logs` table
    - Tracks delivery status
    - Acknowledgment tracking

### Analytics Dashboard Data

1. **Student Analytics**
    - Attendance rate over time
    - Grade trends
    - Absence patterns
    - Rank in class

2. **Class/Section Analytics**
    - Overall attendance rate
    - Average grade per course
    - At-risk students identification
    - Perfect attendance tracking

3. **Institutional Analytics**
    - Grade distribution
    - Course popularity
    - Teacher effectiveness metrics
    - Attendance trends by day/time

## API Endpoints Structure

### Authentication

- `POST /login` - User login
- `POST /logout` - User logout
- `POST /register` - New user registration

### Attendance

- `GET /api/attendance/sessions` - List class sessions
- `POST /api/attendance/sessions` - Create session
- `GET /api/attendance/sessions/{id}` - Session details
- `POST /api/attendance/sessions/{id}/start` - Start attendance taking
- `GET /api/attendance/sessions/{id}/students` - List students in session
- `GET /api/attendance/sessions/{id}/scanned` - List scanned students
- `GET /api/attendance/sessions/{id}/pending` - List pending students
- `POST /api/attendance/scan` - Scan student QR code (teacher)
- `GET /api/attendance/records/{sessionId}` - Session attendance records
- `PUT /api/attendance/records/{id}` - Update record (teacher)
- `POST /api/attendance/records/{id}/finalize` - Finalize and mark absent

### Student QR Code

- `GET /api/students/{id}/qr-code` - Get student's QR code (as image)
- `POST /api/students/{id}/regenerate-qr` - Regenerate QR code
- `GET /api/students/{id}/qr-code-data` - Get raw QR code data

### Grades

- `GET /api/grades/student/{studentId}` - Student grades
- `GET /api/grades/course/{courseId}` - Course grades
- `POST /api/grades` - Create grade entry
- `PUT /api/grades/{id}` - Update grade
- `GET /api/grades/transcript/{studentId}` - Generate transcript

### Seat Plans

- `GET /api/seat-plans/section/{sectionId}` - Section seat plans
- `POST /api/seat-plans` - Create seat plan
- `PUT /api/seat-plans/{id}` - Update seat plan
- `POST /api/seat-allocations` - Assign seats
- `GET /api/seat-allocations/session/{sessionId}` - Session seating

### Analytics

- `GET /api/analytics/dashboard` - Dashboard data
- `GET /api/analytics/student/{studentId}` - Student analytics
- `GET /api/analytics/section/{sectionId}` - Section analytics
- `GET /api/analytics/attendance-trends` - Attendance trends
- `GET /api/analytics/grade-distribution` - Grade distribution

### Alerts

- `GET /api/alerts/configurations` - List alert configs
- `POST /api/alerts/configurations` - Create config
- `GET /api/alerts/logs/student/{studentId}` - Student alert history

## Installation

```bash
# Clone the repository
git clone <repository-url>
cd Koatendance

# Install PHP dependencies
composer install

# Install NPM dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Build frontend assets
npm run build

# Start development server
composer run dev
```

## Environment Variables

Key variables in `.env`:

```env
APP_NAME=Koatendance
APP_URL=http://localhost

DB_CONNECTION=sqlite
# DB_DATABASE=/home/library/Koatendance/database.sqlite

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=alerts@koatendance.com
MAIL_FROM_NAME=Koatendance

QR_CODE_REGENERATION_DAYS=90  # Days before QR code auto-regenerates
ABSENT_ALERT_ENABLED=true
ATTENDANCE_LATE_THRESHOLD_MINUTES=15  # Minutes after start time for late
```

## Queue Workers

For email notifications and scheduled tasks:

```bash
# Start queue worker
php artisan queue:work

# Start scheduler
php artisan schedule:work
```

## Testing

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --compact tests/Feature/AttendanceTest.php

# Run tests with coverage
php artisan test --coverage
```

## License

This project is licensed under the MIT License.
