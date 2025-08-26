# Afyacheck Solution Management System

Afyacheck is a modern PHP/MySQL-based health management system for clinics and hospitals, designed to manage patients, doctors, blood pressure readings, assignments, and reporting. It features role-based dashboards, assignment management, notifications, and a clean, responsive UI built with Tailwind CSS.

## Features

- **Role-based Access:** Admin, Doctor, and Patient modules with secure login.
- **Patient Management:** Add, edit, and view patients. Assign patients to doctors.
- **Doctor Management:** Add, edit, and view doctors. Doctor profile and dashboard.
- **BP Readings:** Record, view, and comment on patient blood pressure readings. Doctors can add notes.
- **Assignments:** Admins assign patients to doctors. Doctors see assigned patients and status.
- **Notifications:** Instant alerts for appointments, critical readings, and doctor recommendations. New assignments are highlighted for doctors and patients.
- **Reports:** Print/export patient lists, doctor lists, and individual patient records.
- **Modern UI:** Tailwind CSS, Font Awesome icons, SweetAlert2 notifications, Chart.js graphs.
- **Security:** Session-based access control, input validation, and encrypted data storage for privacy and compliance.
- **Secure Data:** All patient and doctor data is encrypted and securely stored for privacy and compliance.
- **Homepage Features:** BP Tracking, Patient Records, Doctor Monitoring, Hospital Insights, Notifications, Secure Data.

## Folder Structure

```
Afyacheck_Kombewa/
├── assets/                # Logo and static assets
├── components/            # Navbar, footer, shared UI
├── db.php                 # Database connection
├── admin_dashboard.php    # Admin dashboard
├── doctor_dashboard.php   # Doctor dashboard
├── doctor_assignments.php # Doctor's assigned patients
├── assignments.php        # Admin assignment management
├── manage_patients.php    # Patient management
├── manage_doctors.php     # Doctor management
├── add_patient.php        # Add patient form
├── edit_patient.php       # Edit patient form
├── edit_doctor.php        # Edit doctor form
├── bp_readings.php        # BP readings management
├── reports.php            # Reports and printing
├── README.md              # This documentation
└── ...                    # Other supporting files
```

## Database Schema

- **patients**: id, fullname, age, gender, email, phone
- **doctors**: id, fullname, specialty, email, phone
- **assignments**: id, doctor_id, patient_id, assigned_at
- **bp_readings**: id, patient_id, systolic, diastolic, reading_time, doctor_comment

## Setup Instructions

1. **Clone the repository** to your local server directory (e.g., `/opt/lampp/htdocs/`).
2. **Create the MySQL database** and import the schema:

```sql
import the database
```

3. **Configure database connection** in `db.php`:

```php
$pdo = new PDO('mysql:host=localhost;dbname=afyacheck', 'username', 'password');
```

4. **Start your local server** (e.g., XAMPP/LAMPP) and access the system in your browser.

## Usage Guide

### Admin Features

- Login as admin to access the dashboard.
- Add/manage patients and doctors.
- Assign patients to doctors via the assignments page.
- View/manage BP readings and reports.
- Print/export lists and records.

### Doctor Features

- Login as doctor to access the dashboard.
- View assigned patients and their status (Active/Inactive/No Data).
- Add notes/comments to BP readings.
- View BP history for individual patients.
- See notifications for new assignments (marked as read when viewed).

### BP Readings

- Both admin and doctors can view BP readings.
- Doctors can add comments/notes to readings.
- Status indicators show recent activity for each patient.

### Notifications

- New assignments are highlighted for doctors until the assignments/dashboard page is opened.

### Reports

- Print/export patient and doctor lists, individual patient records, and BP history.

## Technologies Used

- PHP 7+
- MySQL
- Tailwind CSS
- Font Awesome
- SweetAlert2
- Chart.js

## Customization & Extending

- Add more analytics, export formats, or modules as needed.
- Extend security and access control for more roles.
- Integrate with external health APIs or mobile apps.

## Support

For issues or feature requests, contact the developer or open an issue in your repository.

---

**Afyacheck** is designed for easy deployment and extension. Enjoy modern, secure, and efficient health management!

## Login Details

**Admin**

- Email admin@afyacheck.com
- Password tracy

**Doctor**

- Email tracy@afyacheck.com
- Password doctor

**Patient**

- tracy@gmail.com
- tracyy
# AfyaCheck
