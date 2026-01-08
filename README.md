# 📚 Koatendance - QR Code Attendance System

<div align="center">

**A modern, innovative attendance management system with QR code scanning, integrated grading, and automated guardian alerts.**

[![Laravel](https://img.shields.io/badge/Laravel-12-red.svg)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue-3-green.svg)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-v2-purple.svg)](https://inertiajs.com)
[![PHP](https://img.shields.io/badge/PHP-8.5-blue.svg)](https://php.net)

</div>

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Tech Stack](#-tech-stack)
- [Key Features](#-key-features)
- [System Architecture](#-system-architecture)
- [Database Schema](#-database-schema)
- [Data Flow](#-data-flow)
- [API Reference](#-api-reference)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Testing](#-testing)
- [License](#-license)

---

## 🎯 Overview

Koatendance is a comprehensive attendance management system designed for educational institutions. The system streamlines attendance tracking through QR code technology where **teachers scan student QR codes** during class sessions. 

### Core Capabilities

- **QR Code Attendance**: Each student has a unique, encrypted QR code that teachers scan
- **Integrated Grading**: Complete grading system with multiple assessment types
- **Seat Plan Management**: Dynamic seating arrangements with visual layouts
- **Automated Alerts**: Guardian notifications for absences via email
- **Analytics Dashboard**: Real-time insights into attendance patterns and academic performance
- **Multi-Role Support**: Admin, Teacher, Student, and Guardian roles

---

## 🛠 Tech Stack

| Category | Technology |
|----------|-----------|
| **Backend** | PHP 8.5 + Laravel 12 |
| **Frontend** | Vue 3 + Inertia.js v2 |
| **Styling** | Tailwind CSS v4 |
| **Authentication** | Laravel Fortify (2FA support) |
| **Database** | SQLite (configurable) |
| **Testing** | Pest v4 |

---

## ✨ Key Features

### 1. QR Code-Based Attendance

- **Student QR Codes**: Each student receives a unique, encrypted QR code containing their ID
- **Teacher Scanning**: Teachers use their device camera to scan student QR codes
- **Real-time Validation**: Instant verification of student identity and enrollment
- **Duplicate Prevention**: System blocks multiple scans in the same session
- **Manual Override**: Teachers can manually adjust attendance records

### 2. Automated Guardian Alerts

- **Smart Detection**: Multiple alert rules (threshold, consecutive, pattern-based)
- **Email Notifications**: Automatic emails to guardians when students are absent
- **Customizable Preferences**: Guardians control notification frequency and channels
- **Alert History**: Complete audit trail of all notifications sent

### 3. Comprehensive Grading System

- **Multi-type Assessments**: Assignments, quizzes, exams, projects, participation
- **Weighted Grading**: Configurable weights for different assessment types
- **Letter Grades**: Automatic conversion using customizable grade scales
- **GPA Calculation**: Real-time GPA tracking for students
- **Teacher Feedback**: Detailed feedback attached to each grade entry

### 4. Advanced Analytics

- **Student Analytics**: Attendance rates, grade trends, absence patterns
- **Section Analytics**: Class performance, at-risk student identification
- **Institutional Insights**: Grade distribution, course popularity, effectiveness metrics

---

## 🏗 System Architecture

### User Role Hierarchy

```mermaid
graph TD
    A[Users] --> B[Admin]
    A --> C[Teacher]
    A --> D[Student]
    A --> E[Guardian]
    
    B -->|Manages| F[System Configuration]
    B -->|Oversees| G[All Users & Data]
    
    C -->|Teaches| H[Class Sessions]
    C -->|Scans| I[Student QR Codes]
    C -->|Records| J[Grades & Attendance]
    
    D -->|Owns| K[QR Code]
    D -->|Enrolled In| L[Sections/Classes]
    D -->|Receives| M[Grades]
    
    E -->|Receives| N[Absence Alerts]
    E -->|Monitors| D
    
    style B fill:#ff6b6b
    style C fill:#4ecdc4
    style D fill:#45b7d1
    style E fill:#96ceb4
```

### Core System Components

```mermaid
graph LR
    A[Authentication Layer] --> B[Application Core]
    B --> C[QR Code Module]
    B --> D[Attendance Module]
    B --> E[Grading Module]
    B --> F[Analytics Module]
    B --> G[Alert Module]
    
    C --> H[(Database)]
    D --> H
    E --> H
    F --> H
    G --> H
    
    G --> I[Email Service]
    F --> J[Dashboard UI]
    
    style A fill:#feca57
    style B fill:#48dbfb
    style H fill:#ff9ff3
```

---

## 🗄 Database Schema

### Entity Relationship Overview

```mermaid
erDiagram
    USERS ||--o{ STUDENTS : "has profile"
    USERS ||--o{ TEACHERS : "has profile"
    USERS ||--o{ GUARDIANS : "has profile"
    
    GUARDIANS ||--o{ STUDENTS : "monitors"
    STUDENTS ||--o{ ATTENDANCE_RECORDS : "has"
    STUDENTS ||--o{ GRADES : "receives"
    STUDENTS ||--o{ SEAT_ALLOCATIONS : "assigned"
    STUDENTS ||--o{ QR_SCAN_EVENTS : "generates"
    
    TEACHERS ||--o{ CLASS_SESSIONS : "conducts"
    TEACHERS ||--o{ SECTIONS : "advises"
    TEACHERS ||--o{ QR_SCAN_EVENTS : "performs"
    
    COURSES ||--o{ CLASS_SESSIONS : "scheduled as"
    SECTIONS ||--o{ STUDENTS : "contains"
    SECTIONS ||--o{ CLASS_SESSIONS : "has"
    SECTIONS ||--o{ SEAT_PLANS : "uses"
    
    CLASS_SESSIONS ||--o{ ATTENDANCE_RECORDS : "tracks"
    CLASS_SESSIONS ||--o{ QR_SCAN_EVENTS : "records"
    
    SEAT_PLANS ||--o{ SEAT_ALLOCATIONS : "defines"
    
    QR_SCAN_EVENTS ||--|| ATTENDANCE_RECORDS : "creates"
    
    GUARDIANS ||--o{ ALERT_LOGS : "receives"
    STUDENTS ||--o{ ALERT_LOGS : "triggers"
```

### Core Models

#### 👤 User Model

```mermaid
classDiagram
    class User {
        +UUID id
        +string name
        +string email
        +datetime email_verified_at
        +string password
        +string two_factor_secret
        +json two_factor_recovery_codes
        +datetime two_factor_confirmed_at
        +enum role
        +string profile_photo
        +timestamps
    }
    
    class Student {
        +UUID id
        +UUID user_id
        +string student_id
        +string first_name
        +string last_name
        +string middle_name
        +date birth_date
        +enum gender
        +text address
        +string phone
        +UUID guardian_id
        +int current_grade_level
        +UUID section_id
        +enum status
        +date enrollment_date
        +string qr_code_data
        +boolean qr_code_active
        +datetime qr_code_regenerated_at
        +timestamps
    }
    
    class Teacher {
        +UUID id
        +UUID user_id
        +string employee_id
        +string first_name
        +string last_name
        +string email
        +string phone
        +string department
        +enum status
        +timestamps
    }
    
    class Guardian {
        +UUID id
        +UUID user_id
        +string first_name
        +string last_name
        +string email
        +string phone
        +enum relationship
        +boolean is_primary
        +json alert_preferences
        +timestamps
    }
    
    User "1" --> "*" Student
    User "1" --> "*" Teacher
    User "1" --> "*" Guardian
```

#### 📚 Academic Models

```mermaid
classDiagram
    class Course {
        +UUID id
        +string code
        +string name
        +text description
        +int grade_level
        +int credits
        +enum type
        +boolean is_active
        +timestamps
    }
    
    class Section {
        +UUID id
        +string name
        +int grade_level
        +string academic_year
        +enum semester
        +UUID advisor_id
        +int max_students
        +json schedule_template
        +timestamps
    }
    
    class ClassSession {
        +UUID id
        +UUID course_id
        +UUID section_id
        +UUID teacher_id
        +string room
        +date scheduled_date
        +time start_time
        +time end_time
        +enum status
        +enum attendance_mode
        +text notes
        +timestamps
    }
    
    Course "1" --> "*" ClassSession
    Section "1" --> "*" ClassSession
    Teacher "1" --> "*" ClassSession
```

#### ✅ Attendance System

```mermaid
classDiagram
    class QrScanEvent {
        +UUID id
        +UUID session_id
        +UUID teacher_id
        +UUID student_id
        +datetime scanned_at
        +json device_info
        +json location
        +enum status
        +string error_message
        +timestamps
    }
    
    class AttendanceRecord {
        +UUID id
        +UUID session_id
        +UUID student_id
        +enum status
        +datetime timestamp
        +UUID recorded_by
        +UUID scan_event_id
        +json device_info
        +text notes
        +timestamps
    }
    
    QrScanEvent "1" --> "1" AttendanceRecord
    ClassSession "1" --> "*" QrScanEvent
    ClassSession "1" --> "*" AttendanceRecord
```

#### 📊 Grading System

```mermaid
classDiagram
    class Grade {
        +UUID id
        +UUID student_id
        +UUID course_id
        +UUID class_session_id
        +enum grade_type
        +string grade_item
        +decimal score
        +decimal max_score
        +decimal percentage
        +string grade_letter
        +decimal weight
        +string academic_year
        +enum semester
        +enum grading_period
        +text feedback
        +UUID recorded_by
        +timestamps
    }
    
    class GradeScale {
        +UUID id
        +string name
        +text description
        +boolean is_default
        +string academic_year
        +boolean is_active
        +timestamps
    }
    
    class GradeScaleItem {
        +UUID id
        +UUID grade_scale_id
        +string letter_grade
        +decimal min_percentage
        +decimal max_percentage
        +decimal gpa_points
        +string description
        +timestamps
    }
    
    GradeScale "1" --> "*" GradeScaleItem
```

#### 🔔 Alert System

```mermaid
classDiagram
    class AlertConfiguration {
        +UUID id
        +string name
        +text description
        +enum alert_type
        +json condition
        +json notification_channels
        +boolean is_active
        +int priority
        +timestamps
    }
    
    class AlertLog {
        +UUID id
        +UUID alert_config_id
        +UUID student_id
        +UUID guardian_id
        +enum alert_type
        +text message
        +json data
        +enum status
        +datetime sent_at
        +datetime acknowledged_at
        +timestamps
    }
    
    AlertConfiguration "1" --> "*" AlertLog
    Student "1" --> "*" AlertLog
    Guardian "1" --> "*" AlertLog
```

### Field Descriptions

<details>
<summary><b>User Role Types</b></summary>

| Role | Description |
|------|-------------|
| `admin` | Full system access, manages all users and configurations |
| `teacher` | Conducts classes, scans QR codes, records grades |
| `student` | Owns QR code, enrolled in sections, receives grades |
| `guardian` | Receives alerts, monitors student(s) |

</details>

<details>
<summary><b>Attendance Status Types</b></summary>

| Status | Description |
|--------|-------------|
| `present` | Student attended the class |
| `absent` | Student did not attend |
| `late` | Student arrived after threshold time |
| `excused` | Absence is excused with reason |
| `early_departure` | Student left before class ended |

</details>

<details>
<summary><b>Class Session Status</b></summary>

| Status | Description |
|--------|-------------|
| `scheduled` | Class is scheduled but not started |
| `in_progress` | Class is currently ongoing |
| `completed` | Class has finished |
| `cancelled` | Class was cancelled |
| `makeup` | Makeup class for missed session |

</details>

<details>
<summary><b>Grade Types</b></summary>

| Type | Description |
|------|-------------|
| `assignment` | Regular assignment |
| `quiz` | Short assessment |
| `exam` | Major examination |
| `project` | Long-term project |
| `participation` | Class participation score |
| `final` | Final grade for the course |

</details>

<details>
<summary><b>Alert Types</b></summary>

| Type | Description |
|------|-------------|
| `absence_threshold` | Alert after X absences |
| `consecutive_absence` | Alert for consecutive absences |
| `pattern_detected` | Unusual attendance pattern |
| `daily_digest` | Daily summary of absences |

</details>

---

## 🔄 Data Flow

### QR Code Attendance Workflow

```mermaid
sequenceDiagram
    participant S as Student
    participant T as Teacher
    participant SYS as System
    participant DB as Database
    participant G as Guardian
    
    Note over S: Student has QR code
    
    T->>SYS: Start attendance session
    SYS->>DB: Create ClassSession
    
    loop For each student
        T->>SYS: Scan student QR code
        SYS->>SYS: Decrypt & validate QR
        
        alt Valid QR code
            SYS->>DB: Create QrScanEvent
            SYS->>DB: Create AttendanceRecord (present)
            SYS-->>T: ✓ Success
        else Invalid/Duplicate
            SYS-->>T: ✗ Error message
        end
    end
    
    T->>SYS: Finalize attendance
    SYS->>DB: Mark remaining students absent
    
    SYS->>SYS: Run alert rules
    
    alt Student is absent
        SYS->>DB: Check alert configurations
        SYS->>DB: Create AlertLog
        SYS->>G: Send email notification
    end
```

### Grading Workflow

```mermaid
sequenceDiagram
    participant T as Teacher
    participant SYS as System
    participant DB as Database
    participant S as Student
    
    T->>SYS: Enter grade for assessment
    SYS->>SYS: Validate score & parameters
    SYS->>DB: Store Grade record
    
    SYS->>DB: Calculate percentage
    SYS->>DB: Lookup grade scale
    SYS->>DB: Assign letter grade
    SYS->>DB: Update student GPA
    
    SYS-->>T: Grade recorded successfully
    
    S->>SYS: View grades
    SYS->>DB: Fetch student grades
    SYS-->>S: Display grades & GPA
```

### Alert System Workflow

```mermaid
flowchart TD
    A[Attendance Finalized] --> B{Check for Absences}
    B -->|Found| C[Evaluate Alert Rules]
    B -->|None| Z[End]
    
    C --> D{Threshold Met?}
    D -->|Yes| E[Retrieve Student Info]
    D -->|No| Z
    
    E --> F[Lookup Guardian]
    F --> G{Guardian Exists?}
    G -->|Yes| H[Check Preferences]
    G -->|No| Z
    
    H --> I{Email Enabled?}
    I -->|Yes| J[Create Alert Log]
    I -->|No| Z
    
    J --> K[Queue Email Job]
    K --> L[Send Email]
    L --> M[Update Alert Status]
    M --> Z
    
    style A fill:#4ecdc4
    style L fill:#ff6b6b
    style Z fill:#95e1d3
```

---

## 🌐 API Reference

### Authentication Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/login` | User login with credentials |
| POST | `/logout` | User logout |
| POST | `/register` | New user registration |

### Attendance Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/attendance/sessions` | List all class sessions |
| POST | `/api/attendance/sessions` | Create new session |
| GET | `/api/attendance/sessions/{id}` | Get session details |
| POST | `/api/attendance/sessions/{id}/start` | Start attendance taking |
| GET | `/api/attendance/sessions/{id}/students` | List enrolled students |
| GET | `/api/attendance/sessions/{id}/scanned` | List scanned students |
| GET | `/api/attendance/sessions/{id}/pending` | List pending students |
| POST | `/api/attendance/scan` | Scan student QR code |
| GET | `/api/attendance/records/{sessionId}` | Get attendance records |
| PUT | `/api/attendance/records/{id}` | Update attendance record |
| POST | `/api/attendance/records/{id}/finalize` | Finalize and mark absences |

### QR Code Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/students/{id}/qr-code` | Get QR code image |
| POST | `/api/students/{id}/regenerate-qr` | Regenerate QR code |
| GET | `/api/students/{id}/qr-code-data` | Get raw QR data |

### Grading System

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/grades/student/{studentId}` | Get student grades |
| GET | `/api/grades/course/{courseId}` | Get course grades |
| POST | `/api/grades` | Create grade entry |
| PUT | `/api/grades/{id}` | Update grade |
| GET | `/api/grades/transcript/{studentId}` | Generate transcript |

### Seat Plan Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/seat-plans/section/{sectionId}` | Get section seat plans |
| POST | `/api/seat-plans` | Create seat plan |
| PUT | `/api/seat-plans/{id}` | Update seat plan |
| POST | `/api/seat-allocations` | Assign seats |
| GET | `/api/seat-allocations/session/{sessionId}` | Get session seating |

### Analytics & Reporting

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/analytics/dashboard` | Dashboard overview data |
| GET | `/api/analytics/student/{studentId}` | Student analytics |
| GET | `/api/analytics/section/{sectionId}` | Section analytics |
| GET | `/api/analytics/attendance-trends` | Attendance trends |
| GET | `/api/analytics/grade-distribution` | Grade distribution |

### Alert Configuration

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/alerts/configurations` | List alert configs |
| POST | `/api/alerts/configurations` | Create alert config |
| GET | `/api/alerts/logs/student/{studentId}` | Student alert history |

---

## 📦 Installation

### Prerequisites

- PHP 8.5 or higher
- Composer
- Node.js 18+ and NPM
- SQLite (or preferred database)

### Setup Steps

```bash
# 1. Clone the repository
git clone <repository-url>
cd Koatendance

# 2. Install PHP dependencies
composer install

# 3. Install NPM dependencies
npm install

# 4. Configure environment
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Run database migrations
php artisan migrate

# 7. (Optional) Seed the database with sample data
php artisan db:seed

# 8. Build frontend assets
npm run build

# 9. Start development server
composer run dev
```

### Development Mode

```bash
# Terminal 1: Start Laravel development server
php artisan serve

# Terminal 2: Start Vite dev server for hot reloading
npm run dev

# Terminal 3: Start queue worker for jobs
php artisan queue:work

# Terminal 4: Start scheduler for automated tasks
php artisan schedule:work
```

---

## ⚙️ Configuration

### Environment Variables

Create a `.env` file with the following key configurations:

```env
# Application
APP_NAME=Koatendance
APP_ENV=local
APP_URL=http://localhost
APP_DEBUG=true

# Database
DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database.sqlite

# Mail Configuration (for alerts)
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-email@example.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=alerts@koatendance.com
MAIL_FROM_NAME="${APP_NAME}"

# QR Code Settings
QR_CODE_REGENERATION_DAYS=90
QR_CODE_ENCRYPTION_KEY="${APP_KEY}"

# Attendance Settings
ATTENDANCE_LATE_THRESHOLD_MINUTES=15
ATTENDANCE_AUTO_FINALIZE=true

# Alert Settings
ABSENT_ALERT_ENABLED=true
ALERT_THRESHOLD_DEFAULT=3
ALERT_CONSECUTIVE_DEFAULT=2
```

### Alert Configuration Options

| Setting | Default | Description |
|---------|---------|-------------|
| `ABSENT_ALERT_ENABLED` | `true` | Enable/disable alert system |
| `ALERT_THRESHOLD_DEFAULT` | `3` | Default absence count for alerts |
| `ALERT_CONSECUTIVE_DEFAULT` | `2` | Default consecutive absences for alerts |

### Queue Configuration

The system uses Laravel queues for:
- Email notifications
- Analytics calculations
- Report generation

Ensure your queue worker is running:

```bash
php artisan queue:work --tries=3
```

---

## 🧪 Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run with compact output
php artisan test --compact

# Run specific test suite
php artisan test --testsuite=Feature

# Run specific test file
php artisan test tests/Feature/AttendanceTest.php

# Run with coverage report
php artisan test --coverage

# Run with minimum coverage threshold
php artisan test --coverage --min=80
```

### Test Structure

```
tests/
├── Feature/
│   ├── AttendanceTest.php
│   ├── GradingTest.php
│   ├── QrCodeTest.php
│   └── AlertTest.php
└── Unit/
    ├── Models/
    ├── Services/
    └── Helpers/
```

---

## 📄 License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

## 📞 Support

For issues, questions, or suggestions, please open an issue on the repository.

---

<div align="center">

**Built with ❤️ using Laravel, Vue, and Inertia.js**

</div>
