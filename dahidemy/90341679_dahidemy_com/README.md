<table align="center">
    <tr>
        <td>
            <a href="#" target="_blank">
                <img src="./public/uploads/logo.png" width="200">
            </a>
        </td>
    </tr>
</table>

## Laravel School ERP Software Project

School ERP Software is a comprehensive platform built with Laravel 11, designed to streamline and manage all aspects of school administration.

[Features]

    ## Key Features
        -   Session Manage
        -   Multi Language Manage (English, Bengali)
        -   Reports (Students, Attendance, Accounts , Fees , Fine , Deposit, Expense)
        -   Books CSV Upload
        -   Books Barcode Generate & Print Label

    ## Features
        -   Students Management 
        -   Migration Pull-Push back Students
        -   Staffs Management
        -   Staffs Attendance
        -   Student Attendance
        -   General Accounts (Core Settings, Payment, Receipt, Contra, Journal, Fund Transfer, Chart OF Accounts, Reports)
        -   Layouts & Certificates (General Recommendation Letter, Testimonial, TC, HSC Rec. , Attendance Cer. , Abroad Letter)
        -   Academic ID Card (Students, Teachers, Staffs)
        -   Exam Essentials (Admit Card & Seat Plan)
        -   Library Management (Books, Members, Barcode Books Issue, Fines Collect, Books Issues Report)
        -   SMS Modules
        -   Notifications
        -   Students Feedback

    ## Core Settings

        -   Global Settings (Role, Permission, General Setting)
        -   Academic Permissions
        -   Academic Configurations
        -   Smart Routine (Syllabus, Assignment, Class Routine, Exam Routine)
        -   Master Configurations (Extra - Principal Signature)
        -   User Logs (user-activity)
        -   Exam Module
        -   E-Learning 
        -   Inventory POS (Books, Pens, Notes , Others, Sales, Purchases, Stock, POS, Reports)
        -   Offline Attendance & Action Perform.


[Modules]
        
    ## Key Modules
        - Authentication -> Web & API Authentication with Role Permissions
        - Student
        - Staff
        - Attendance
        - Fees
        - Accounts
        - Certificate
        - Library
        - SMS
        - Notification
        - Routine
        - Academic
        - Configuration
        - Core
        - Exam
        - Essential (Admit, Seat Plan)

    ## New Features
       QR Code Attendance
       Pages Dynamic Page and Frontend Showing.
       


[Requirements]| [Install] | [How to setting] | [License] |

    Documentation for School Software
    Installation Requirements
    Before installing the School Software, ensure your server meets the following requirements:

    Server Requirements
    PHP: >= 8.2.0
    MySQL: 5.x or higher
    Required PHP Extensions:
            Fileinfo
            GD2
            JSON
            OpenSSL
            PDO
            XML

    Key Features
    The School Software is built using Laravel Framework 11 and comes with a variety of powerful features to enhance your business operations. Below are the key features included in this system:

    1. Laravel Framework 11
    Utilizing the latest version of Laravel ensures that your application benefits from the latest features, security updates, and performance improvements.

    2. Laravel Breeze
    Implement user authentication with ease using Laravel Breeze. This lightweight authentication system provides a simple and efficient way to manage user registration, login, and password recovery.

    3. Passport Authentication
    Secure your API with Passport for full OAuth2 server implementation. This allows you to authenticate users and manage access tokens effectively.

    4. CSV Upload with Maatwebsite
    Easily import and export data using the Maatwebsite Excel package. This feature allows users to upload CSV files for bulk data entry, making it easier to manage inventory and sales data.

    5. Barcode Generation
    Generate barcode effortlessly using the milon/barcode package. This feature allows you to create and print barcodes for your products, streamlining the sales process.

    6. Sweet Alert Notifications
    Improve user experience with realrashid/sweet-alert integration. This package allows for beautiful and customizable alert notifications for various actions within the application.

    7. Role-Based Permissions
    Implement role-based access control using spatie/laravel-permission. This feature allows you to define user roles and permissions for both web and application access, enhancing security and user management.

    8. Monitoring with Telescope
    Monitor your application's performance and debug issues effectively using Laravel Telescope. This tool provides insights into requests, exceptions, database queries, and more.

    ## Requirements
        "php": "^8.2",
        "browner12/helpers": "^3.6",
        "laravel/framework": "^11.9",
        "laravel/passport": "^12.0",
        "laravel/sanctum": "^4.0",
        "laravel/tinker": "^2.9",
        "maatwebsite/excel": "^3.1",
        "realrashid/sweet-alert": "*",
        "spatie/laravel-medialibrary": "^11.0",
        "spatie/laravel-pdf": "^1.5",
        "spatie/laravel-permission": "^6.9"
