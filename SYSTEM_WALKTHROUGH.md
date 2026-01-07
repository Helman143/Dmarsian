# D'Marsians Taekwondo Gym - System Walkthrough & Flow Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [System Architecture](#system-architecture)
3. [User Roles & Access](#user-roles--access)
4. [System Flow Diagrams](#system-flow-diagrams)
5. [Feature Walkthrough](#feature-walkthrough)
6. [Database Schema](#database-schema)
7. [Technical Stack](#technical-stack)

---

## System Overview

**D'Marsians Taekwondo Gym Management System** is a comprehensive web-based application designed to manage a Taekwondo gym's operations, including student enrollment, payment tracking, post management (achievements and events), and administrative functions.

### Key Features
- **Public Website**: Showcase gym information, achievements, events, and registration
- **Admin Dashboard**: Complete management system for posts, students, enrollments, and payments
- **Student Registration**: Online enrollment and trial session booking
- **Payment Management**: Track student payments, dues, and discounts
- **Post Management**: Create and manage achievement and event posts
- **Archive System**: Historical view of all posts with filtering capabilities

---

## System Architecture

### Frontend Architecture
```
┌─────────────────────────────────────────────────────────┐
│                    PUBLIC WEBSITE                        │
│  (webpage.php)                                          │
│  ├── Hero Section with Video                            │
│  ├── Achievements Slider (Coverflow Carousel)           │
│  ├── Events Slider (Coverflow Carousel)                 │
│  ├── Instructor Profile                                 │
│  ├── Services/Offers Section                            │
│  ├── About Us & Schedule                                │
│  ├── Registration Form                                   │
│  └── Contact & Footer                                    │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                    ADMIN PANEL                           │
│  ├── Admin Login (admin_login.php)                      │
│  ├── Dashboard (admin_dashboard.php)                    │
│  ├── Post Management (post_management.php)               │
│  ├── Student Management (admin_student_management.php)  │
│  ├── Enrollment Management (admin_enrollment.php)        │
│  ├── Payment Management (admin_payment.php)             │
│  ├── Trial Session Management (admin_trial_session.php) │
│  └── Settings & Profile                                  │
└─────────────────────────────────────────────────────────┘
```

### Backend Architecture
```
┌─────────────────────────────────────────────────────────┐
│                    PHP BACKEND                           │
│  ├── Database Layer (db_connect.php, config.php)        │
│  ├── Post Operations (post_operations.php)              │
│  ├── API Endpoints (get_posts.php, get_students.php)    │
│  ├── Form Handlers (save_student.php, submit_*.php)     │
│  ├── Authentication (auth_helpers.php)                  │
│  └── Email Services (SMTP2GO Integration)               │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                    DATABASE (MySQL)                      │
│  ├── posts (achievements, events)                       │
│  ├── students (enrolled students)                       │
│  ├── enrollment_requests (pending enrollments)          │
│  ├── payments (payment records)                         │
│  ├── registrations (trial sessions)                     │
│  ├── admin_accounts (admin users)                        │
│  └── Supporting tables (dues_reminders, logs)          │
└─────────────────────────────────────────────────────────┘
```

---

## User Roles & Access

### 1. Public Users (Visitors)
- **Access**: Public website (webpage.php)
- **Capabilities**:
  - View gym information, achievements, and events
  - Browse archive of posts
  - Submit registration form (enrollment or trial session)
  - View instructor profile and gym schedule

### 2. Admin Users
- **Access**: Admin panel (requires authentication)
- **Capabilities**:
  - Manage posts (create, edit, archive)
  - Manage students (add, edit, deactivate)
  - Approve/reject enrollment requests
  - Record and track payments
  - Manage trial sessions
  - View dashboard statistics
  - Configure system settings

---

## System Flow Diagrams

### 1. User Registration Flow

```
┌─────────────┐
│   Visitor   │
│  (Website)  │
└──────┬──────┘
       │
       │ Fills Registration Form
       │ (Student Name, Parent Info, Class, Belt Rank, etc.)
       │
       ▼
┌─────────────────────┐
│  Registration Form  │
│  (webpage.php)      │
└──────┬──────────────┘
       │
       │ Submit Form
       │
       ├─────────────────┬─────────────────┐
       │                 │                 │
       ▼                 ▼                 ▼
┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│   Enroll     │  │ Trial Session │  │   Error      │
│   Option     │  │   Option      │  │   Handling   │
└──────┬───────┘  └──────┬───────┘  └──────────────┘
       │                 │
       │                 │
       ▼                 ▼
┌─────────────────────────────────────┐
│  submit_enrollment_request.php       │
│  OR                                  │
│  register_trial_session.php          │
└──────────────┬──────────────────────┘
               │
               │ Save to Database
               │ (enrollment_requests OR registrations)
               │
               ▼
┌─────────────────────────────────────┐
│  Send Confirmation Email            │
│  (via SMTP2GO)                      │
└──────────────┬──────────────────────┘
               │
               │
               ▼
┌─────────────────────────────────────┐
│  Show Success Popup                 │
│  "Please proceed to gym to continue" │
└─────────────────────────────────────┘
```

### 2. Admin Post Management Flow

```
┌─────────────┐
│   Admin     │
│   User      │
└──────┬──────┘
       │
       │ Login to Admin Panel
       │
       ▼
┌─────────────────────┐
│  Admin Dashboard    │
└──────┬──────────────┘
       │
       │ Navigate to Post Management
       │
       ▼
┌─────────────────────┐
│  post_management.php│
│  (View All Posts)   │
└──────┬──────────────┘
       │
       ├─────────────────┬─────────────────┐
       │                 │                 │
       ▼                 ▼                 ▼
┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│  Create Post │  │  Edit Post   │  │ Archive Post │
└──────┬───────┘  └──────┬───────┘  └──────┬───────┘
       │                 │                 │
       │                 │                 │
       ▼                 ▼                 ▼
┌─────────────────────────────────────────────────┐
│  Post Modal Form                                 │
│  ├── Upload Image                                │
│  ├── Enter Title                                 │
│  ├── Select Category (Achievement/Event/Both)    │
│  ├── Set Date                                    │
│  └── Enter Description                           │
└──────────────┬──────────────────────────────────┘
               │
               │ Submit
               │
               ▼
┌─────────────────────────────────────┐
│  post_operations.php                │
│  (Save/Update to Database)          │
└──────────────┬──────────────────────┘
               │
               │ Update posts table
               │
               ▼
┌─────────────────────────────────────┐
│  Post Appears on:                   │
│  ├── Public Website (webpage.php)   │
│  ├── Archive Page (archive.php)      │
│  └── Admin Post Management          │
└─────────────────────────────────────┘
```

### 3. Enrollment Approval Flow

```
┌─────────────────────┐
│  Enrollment Request  │
│  (Submitted Online)  │
└──────────┬───────────┘
           │
           │ Saved to enrollment_requests table
           │ Status: 'pending'
           │
           ▼
┌─────────────────────┐
│  Admin Dashboard     │
│  (View Pending)      │
└──────────┬───────────┘
           │
           │ Admin Reviews Request
           │
           ├─────────────────┬─────────────────┐
           │                 │                 │
           ▼                 ▼                 ▼
┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│   Approve    │    │    Reject    │    │   View       │
│              │    │              │    │   Details    │
└──────┬───────┘    └──────┬───────┘    └──────────────┘
       │                   │
       │                   │
       ▼                   ▼
┌─────────────────────────────────────┐
│  approve_enrollment.php             │
└──────────┬──────────────────────────┘
           │
           ├─────────────────┬─────────────────┐
           │                 │                 │
           ▼                 ▼                 ▼
┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│ Create       │    │ Update       │    │ Send         │
│ Student      │    │ Request      │    │ Notification │
│ Record       │    │ Status       │    │ Email        │
│ (students)   │    │ (rejected)   │    │              │
└──────┬───────┘    └──────────────┘    └──────────────┘
       │
       │ Generate JEJA Number (STD-XXXXX)
       │
       ▼
┌─────────────────────┐
│  Student Active     │
│  (Can make payments)│
└─────────────────────┘
```

### 4. Payment Processing Flow

```
┌─────────────────────┐
│  Active Student     │
│  (Has JEJA Number)  │
└──────────┬───────────┘
           │
           │ Admin Records Payment
           │
           ▼
┌─────────────────────┐
│  Payment Form       │
│  (admin_payment.php)│
└──────────┬───────────┘
           │
           │ Enter Payment Details:
           │ - Amount
           │ - Payment Type (Full/Partial)
           │ - Discount (if applicable)
           │ - Date
           │
           ▼
┌─────────────────────┐
│  save_payment.php   │
└──────────┬───────────┘
           │
           │ Save to payments table
           │
           ├─────────────────┬─────────────────┐
           │                 │                 │
           ▼                 ▼                 ▼
┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│ Update       │    │ Record       │    │ Generate      │
│ Student      │    │ Payment      │    │ Receipt      │
│ Status       │    │ History      │    │              │
└──────────────┘    └──────────────┘    └──────────────┘
```

### 5. Post Display Flow (Public Website)

```
┌─────────────────────┐
│  webpage.php        │
│  (Page Load)        │
└──────────┬───────────┘
           │
           │ JavaScript Fetch
           │
           ├─────────────────┬─────────────────┐
           │                 │                 │
           ▼                 ▼                 ▼
┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│ Fetch        │    │ Fetch        │    │ Display      │
│ Achievements │    │ Events       │    │ Static       │
│              │    │              │    │ Content      │
└──────┬───────┘    └──────┬───────┘    └──────────────┘
       │                   │
       │                   │
       ▼                   ▼
┌─────────────────────────────────────┐
│  get_posts.php?category=achievement │
│  get_posts.php?category=event       │
└──────────┬──────────────────────────┘
           │
           │ Query Database
           │ SELECT * FROM posts
           │ WHERE category = ? AND status = 'active'
           │
           ▼
┌─────────────────────┐
│  Return JSON        │
│  (Post Data)        │
└──────────┬───────────┘
           │
           │ Render Sliders
           │
           ▼
┌─────────────────────────────────────┐
│  Coverflow Carousel                 │
│  ├── Achievements Slider            │
│  └── Events Slider                  │
│                                     │
│  User Interaction:                  │
│  ├── Click Card → Open Modal        │
│  ├── Click Arrow → Navigate         │
│  └── Click "See More" → Archive     │
└─────────────────────────────────────┘
```

---

## Feature Walkthrough

### 1. Public Website (webpage.php)

#### Homepage Sections:
1. **Hero Section**
   - Background video with holographic frame effect
   - Floating logo animation
   - Main headline with glitch effect
   - Call-to-action button
   - Stats ticker (animated counters)

2. **Achievements Section**
   - Infinite coverflow carousel
   - Displays posts with category "achievement" or "achievement_event"
   - Click card to view full details in modal
   - "See More" link to archive page

3. **Events Section**
   - Same carousel functionality as achievements
   - Displays posts with category "event" or "achievement_event"

4. **Instructor Profile**
   - Holographic card design
   - Parallax effect on hover
   - Animated statistics (years, dan rank, black belts)
   - Profile photo and bio

5. **Services/Offers**
   - Grid of service cards
   - Staggered entrance animations
   - 6 main offerings displayed

6. **About Us & Schedule**
   - Gym mission statement
   - Class schedule by rank
   - Membership pricing
   - Opening hours

7. **Registration Form**
   - Student and parent information
   - Class selection (Poomsae/Kyorugi)
   - Belt rank selection
   - Enrollment type (Enroll/Trial Session)
   - Form validation and submission

8. **Contact & Footer**
   - Map with location pin
   - Contact information
   - Social media links
   - Partner badges

### 2. Archive Page (archive.php)

- **Filtering Options**:
  - Category filter (All/Achievement/Event)
  - Year filter (current year and past 5 years)
  - Search by title or date

- **Display**:
  - Grid layout of post cards
  - Image, title, date, category badge
  - Responsive design

### 3. Admin Dashboard

#### Main Features:
1. **Dashboard Statistics**
   - Total students (active/inactive)
   - Pending enrollments
   - Recent payments
   - System overview

2. **Post Management**
   - Create new posts (achievement/event)
   - Edit existing posts
   - Archive posts
   - Filter by year and category
   - Image upload with preview

3. **Student Management**
   - View all students
   - Add new students manually
   - Edit student information
   - Deactivate students
   - View student payment history

4. **Enrollment Management**
   - View pending enrollment requests
   - Approve/reject enrollments
   - Convert to student record
   - Generate JEJA numbers

5. **Payment Management**
   - Record payments
   - Track payment history
   - Apply discounts
   - View dues and reminders

6. **Trial Session Management**
   - View trial session requests
   - Complete trial sessions
   - Convert to full enrollment

---

## Database Schema

### Core Tables

#### 1. `posts`
Stores achievement and event posts
```sql
- id (INT, PRIMARY KEY)
- title (VARCHAR)
- description (TEXT)
- image_path (VARCHAR)
- category (ENUM: 'achievement', 'event', 'achievement_event')
- post_date (DATE)
- status (ENUM: 'active', 'archived')
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

#### 2. `students`
Stores enrolled student information
```sql
- id (INT, PRIMARY KEY)
- jeja_no (VARCHAR, UNIQUE) - Student ID (STD-XXXXX)
- full_name (VARCHAR)
- address (TEXT)
- phone (VARCHAR)
- email (VARCHAR)
- school (VARCHAR)
- parent_name (VARCHAR)
- parent_phone (VARCHAR)
- parent_email (VARCHAR)
- belt_rank (VARCHAR)
- discount (DECIMAL)
- schedule (VARCHAR)
- date_enrolled (DATE)
- status (ENUM: 'Active', 'Inactive')
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

#### 3. `enrollment_requests`
Stores pending enrollment requests from website
```sql
- id (INT, PRIMARY KEY)
- full_name (VARCHAR)
- phone (VARCHAR)
- school (VARCHAR)
- belt_rank (VARCHAR)
- address (VARCHAR)
- email (VARCHAR)
- class (VARCHAR)
- parent_name (VARCHAR)
- parent_phone (VARCHAR)
- parent_email (VARCHAR)
- status (ENUM: 'pending', 'approved', 'rejected')
- created_at (TIMESTAMP)
```

#### 4. `payments`
Stores payment records
```sql
- id (INT, PRIMARY KEY)
- jeja_no (VARCHAR) - Links to students table
- fullname (VARCHAR)
- date_paid (DATE)
- amount_paid (DECIMAL)
- payment_type (VARCHAR)
- discount (VARCHAR)
- date_enrolled (DATE)
- status (VARCHAR)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

#### 5. `registrations`
Stores trial session registrations
```sql
- id (INT, PRIMARY KEY)
- student_name (VARCHAR)
- address (VARCHAR)
- parents_name (VARCHAR)
- phone (VARCHAR)
- email (VARCHAR)
- parent_phone (VARCHAR)
- school (VARCHAR)
- class (VARCHAR)
- parent_email (VARCHAR)
- belt_rank (VARCHAR)
- enroll_type (VARCHAR)
- date_registered (TIMESTAMP)
- status (VARCHAR)
- trial_payment (DECIMAL)
```

#### 6. `admin_accounts`
Stores admin user accounts
```sql
- id (INT, PRIMARY KEY)
- email (VARCHAR, UNIQUE)
- username (VARCHAR, UNIQUE)
- password (VARCHAR) - Hashed with bcrypt
```

#### 7. `dues_reminders`
Tracks reminder state for monthly dues
```sql
- id (INT, PRIMARY KEY)
- jeja_no (VARCHAR)
- due_month (DATE)
- last_reminder_at (DATETIME)
- reminder_count (INT)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

#### 8. `admin_password_resets`
Tracks OTP-based password resets
```sql
- id (INT, PRIMARY KEY)
- email (VARCHAR)
- admin_id (INT)
- otp_hash (VARCHAR)
- otp_expires_at (DATETIME)
- attempt_count (INT)
- last_sent_at (DATETIME)
- consumed (BOOLEAN)
- created_at (TIMESTAMP)
```

---

## Technical Stack

### Frontend
- **HTML5/CSS3**: Modern semantic markup and styling
- **JavaScript (ES6+)**: Interactive features and animations
- **Bootstrap 5.3.3**: Responsive framework
- **Font Awesome 6.5.1**: Icons
- **Custom CSS**: Advanced animations (glitch effects, holographic UI, coverflow carousel)

### Backend
- **PHP 7.4+**: Server-side logic
- **MySQL/MariaDB**: Database management
- **mysqli**: Database connectivity

### External Services
- **SMTP2GO**: Email delivery service
- **DigitalOcean Spaces**: File storage (optional, for production)
- **Google Fonts**: Typography (Orbitron, Teko, Montserrat, Rajdhani)

### Development Environment
- **XAMPP**: Local development server
- **Git**: Version control
- **DigitalOcean App Platform**: Production hosting

---

## System Flow Summary

### User Journey (Public)

1. **Visit Website** → View hero section, achievements, events
2. **Browse Content** → Click on posts to view details in modal
3. **View Archive** → Filter and search historical posts
4. **Register Interest** → Fill out registration form
5. **Submit Form** → Receive confirmation, wait for admin approval
6. **Visit Gym** → Complete enrollment process in person

### Admin Journey

1. **Login** → Authenticate with username/password
2. **Dashboard** → View system statistics and overview
3. **Manage Posts** → Create/edit/archive posts for website
4. **Review Enrollments** → Approve or reject pending requests
5. **Manage Students** → Add/edit student records
6. **Record Payments** → Track student payments and dues
7. **Monitor System** → View logs and system status

---

## Key System Features

### 1. Infinite Coverflow Carousel
- Smooth 3D card transitions
- Infinite loop navigation
- Touch/swipe support
- Keyboard navigation support

### 2. Responsive Design
- Mobile-first approach
- Adaptive layouts for all screen sizes
- Touch-optimized interactions
- Offcanvas navigation for mobile

### 3. Image Management
- Upload and store post images
- Automatic placeholder for missing images
- Image validation and error handling
- Support for multiple image formats

### 4. Email Notifications
- Confirmation emails for registrations
- OTP emails for password reset
- Payment reminders
- Admin notifications

### 5. Security Features
- Password hashing (bcrypt)
- SQL injection prevention (prepared statements)
- XSS protection (htmlspecialchars)
- Session management
- OTP-based password reset

---

## File Structure Overview

```
Dmarsian/
├── webpage.php              # Main public website
├── archive.php              # Post archive page
├── admin_*.php              # Admin panel pages
├── post_management.php      # Admin post management
├── get_posts.php            # API endpoint for posts
├── post_operations.php      # Post CRUD operations
├── db_connect.php           # Database connection
├── config.php               # Configuration & environment
├── Scripts/
│   └── webpage.js          # Frontend JavaScript
├── Styles/
│   ├── webpage.css         # Main stylesheet
│   └── admin_*.css         # Admin panel styles
├── Picture/                 # Static images
├── Video/                   # Video assets
├── uploads/                 # User-uploaded files
└── Database/
    └── db.sql              # Database schema
```

---

## Conclusion

The D'Marsians Taekwondo Gym Management System provides a complete solution for managing gym operations, from public-facing content display to comprehensive administrative functions. The system is designed with modern web technologies, responsive design principles, and user-friendly interfaces to ensure smooth operation for both visitors and administrators.

---

**Document Version**: 1.0  
**Last Updated**: 2025  
**System Version**: Production Ready







