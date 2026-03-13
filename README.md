# 🎓 Assam Level College Counselling System

<div align="center">

![Assam Level College Counselling System Logo](https://img.shields.io/badge/Project-Live-green?style=for-the-badge&logo=appveyor) <!-- TODO: Add project logo -->

[![GitHub stars](https://img.shields.io/github/stars/bca-college-project/assam_level_college_counselling?style=for-the-badge)](https://github.com/bca-college-project/assam_level_college_counselling/stargazers)

[![GitHub forks](https://img.shields.io/github/forks/bca-college-project/assam_level_college_counselling?style=for-the-badge)](https://github.com/bca-college-project/assam_level_college_counselling/network)

[![GitHub issues](https://img.shields.io/github/issues/bca-college-project/assam_level_college_counselling?style=for-the-badge)](https://github.com/bca-college-project/assam_level_college_counselling/issues)

[![GitHub license](https://img.shields.io/badge/License-No%20License%20Specified-lightgrey?style=for-the-badge)](LICENSE)

**A web-based admission platform for Assam colleges, featuring OTP registration, document upload, and merit-based seat allotment, inspired by the SAMARTH eGov Portal.**

[Live Demo](https://demo-link.com) <!-- TODO: Add live demo link --> |
[Documentation](https://docs-link.com) <!-- TODO: Add documentation link -->

</div>

## 📖 Overview

The Assam Level College Counselling System is a dedicated web application designed to streamline the college admission process for students in Assam. It facilitates admissions based on Class 12 percentages from both the Assam Higher Secondary Education Council (AHSEC) and the Central Board of Secondary Education (CBSE). The system provides a secure and efficient platform for students to register, upload necessary documents, and apply for colleges, utilizing a merit-based seat allotment logic. Inspired by the functionality of the SAMARTH eGov Portal, it also includes robust administrative tools for super admins and institution-level administrators to manage the counselling process effectively.

## ✨ Features

-   🎯 **OTP-Based User Registration & Verification**: Secure new user onboarding through one-time password verification.
-   🔐 **Role-Based Access Control**: Distinct dashboards and functionalities for Students, Institution Administrators, and Super Administrators.
-   📄 **Student Document Upload**: Facilitates submission of academic and supporting documents by applicants.
-   📈 **Merit-Based Seat Allotment**: Implements a fair and transparent system for allocating seats based on Class 12 academic performance.
-   🏫 **Institution Management**: Admin interface for colleges to manage their offered courses, seat availability, and applicant data.
-   ⚙️ **System Configuration**: Super admin tools for managing global settings, colleges, courses, and overall counselling parameters.
-   📊 **Application Tracking**: Students can track the status of their applications and seat allotment.
-   📧 **Email/SMS Notifications**: (Implied by OTP) Automated communication for registration, verification, and updates.

## 🖥️ Screenshots

<!-- TODO: Add actual screenshots of the application (e.g., Home page, Registration, Login, Student Dashboard, Admin Dashboard) -->

![Screenshot 1 - Homepage/Login](path-to-screenshot-1.png)
_Overview of the login/registration interface._

![Screenshot 2 - Student Dashboard](path-to-screenshot-2.png)
_Example of a student's application dashboard._

![Screenshot 3 - Admin Panel](path-to-screenshot-3.png)
_Screenshot of an administrative interface._

## 🛠️ Tech Stack

**Backend:**
-   <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Badge"/>
-   <img src="https://img.shields.io/badge/Apache-D42029?style=for-the-badge&logo=apache&logoColor=white" alt="Apache Badge"/> (or Nginx)

**Frontend:**
-   <img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white" alt="HTML5 Badge"/>
-   <img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white" alt="CSS3 Badge"/>
-   <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript Badge"/> (Vanilla/jQuery likely)

**Database:**
-   <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL Badge"/>

## 🚀 Quick Start

Follow these steps to get the Assam Level College Counselling System up and running on your local machine.

### Prerequisites
Before you begin, ensure you have the following installed:
-   **Web Server**: Apache, Nginx, or any compatible server capable of serving PHP applications.
-   **PHP**: Version 7.x or higher (check `check_mysql_version.php` for potential specific requirements, but typically modern PHP versions are compatible).
-   **MySQL Database**: Version 5.7 or higher, or MariaDB equivalent.
-   **phpMyAdmin** (Optional, but recommended for database management).
-   **Composer** (If any PHP dependencies were to be introduced later, though not explicitly detected now).

### Installation

1.  **Clone the repository**
    ```bash
    git clone https://github.com/bca-college-project/assam_level_college_counselling.git
    cd assam_level_college_counselling
    ```

2.  **Web Server Setup**
    *   Place the project directory (`assam_level_college_counselling`) into your web server's document root (e.g., `htdocs` for Apache, `www` for Nginx).
    *   Ensure your web server is configured to process `.php` files.

3.  **Environment setup**
    *   Create a `.env` file by copying the provided example:
        ```bash
        cp .env.example .env
        ```
    *   Open `.env` and configure your environment variables:
        ```ini
        # Database Configuration
        DB_HOST=127.0.0.1
        DB_USER=root
        DB_PASSWORD=
        DB_NAME=assam_counselling

        # Other configurations (e.g., API keys for OTP, etc.)
        # ... refer to .env.example for full list
        ```
        **Note**: The `.env.example` file contains placeholders like `SMTP_HOST`, `SMTP_USERNAME`, `TWILIO_SID`, `TWILIO_TOKEN`, `TWILIO_NUMBER`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`, etc. Make sure to fill these with your actual credentials for email and SMS services if you intend to use them.

4.  **Database setup**
    *   Create a new MySQL database (e.g., `assam_counselling`) using phpMyAdmin or your MySQL client.
    *   Import the `database.sql` file into your newly created database:
        ```bash
        # Via MySQL command line (adjust path and credentials)
        mysql -u your_db_user -p your_db_name < database.sql
        ```
        Alternatively, use phpMyAdmin to import the SQL file.

5.  **Start development server**
    *   Ensure your web server (Apache/Nginx) and MySQL database server are running.
    *   No specific command is needed to "start" the development server for traditional PHP; it's served by your configured web server.

6.  **Open your browser**
    *   Visit the URL configured for your web server, e.g., `http://localhost/assam_level_college_counselling` or `http://localhost` if deployed to the root.

## 📁 Project Structure

```
assam_level_college_counselling/
├── .env.example              # Example environment variables for configuration
├── .gitignore                # Files/directories to ignore in Git
├── check_mysql_version.php   # Utility to check MySQL version
├── config/                   # Configuration files (e.g., database connection, constants)
├── css/                      # Stylesheets for the frontend
├── database.sql              # SQL dump for the database schema and initial data
├── index.php                 # Main entry point / homepage
├── init.php                  # Application initialization script (e.g., session, DB connection)
├── instadmin/                # Module for Institution Administrator functionalities
├── js/                       # JavaScript files for frontend interactivity
├── login.php                 # User login page and logic
├── logout.php                # User logout logic
├── register.php              # User registration page and logic
├── settings.php              # Application settings management (likely for admins)
├── student/                  # Module for Student user functionalities
├── superadmin/               # Module for Super Administrator functionalities
├── test_admins.csv           # Sample data for administrators
├── test_courses.csv          # Sample data for courses
├── test_institutions.csv     # Sample data for institutions
└── verify_otp.php            # OTP verification page and logic
```

## ⚙️ Configuration

### Environment Variables
The application relies on `.env` file for sensitive configuration. Copy `.env.example` to `.env` and populate the values.

| Variable          | Description                                         | Default      | Required |

|-------------------|-----------------------------------------------------|--------------|----------|

| `DB_HOST`         | Database host                                       | `127.0.0.1`  | Yes      |

| `DB_USER`         | Database username                                   | `root`       | Yes      |

| `DB_PASSWORD`     | Database password                                   | (empty)      | Yes      |

| `DB_NAME`         | Database name                                       | `assam_counselling` | Yes      |

| `SMTP_HOST`       | SMTP server host for email notifications            | -            | No       |

| `SMTP_USERNAME`   | SMTP username                                       | -            | No       |

| `SMTP_PASSWORD`   | SMTP password                                       | -            | No       |

| `SMTP_PORT`       | SMTP port                                           | `587`        | No       |

| `SMTP_FROM_EMAIL` | Email address to send from                          | -            | No       |

| `TWILIO_SID`      | Twilio Account SID for SMS (OTP)                    | -            | No       |

| `TWILIO_TOKEN`    | Twilio Auth Token                                   | -            | No       |

| `TWILIO_NUMBER`   | Twilio Phone Number                                 | -            | No       |

| `ADMIN_EMAIL`     | Default Super Admin email                           | -            | No       |

| `ADMIN_PASSWORD`  | Default Super Admin password                        | -            | No       |

### Configuration Files
-   `config/`: This directory likely contains PHP files that define constants, database connection settings, or other global application configurations. Review these files for specific settings not covered by environment variables.

## 🔧 Development

This project is a traditional PHP web application. Development primarily involves editing PHP, HTML, CSS, and JavaScript files directly.

### Development Workflow
1.  Ensure your web server (Apache/Nginx) and MySQL database are running.
2.  Make changes to the relevant `.php`, `.css`, or `.js` files.
3.  Refresh your browser to see the changes.

## 🧪 Testing

No explicit automated testing framework (like PHPUnit) was detected in the repository structure. Manual testing is the primary method for verifying functionality.
The `.csv` files (`test_admins.csv`, `test_courses.csv`, `test_institutions.csv`) appear to be sample data for populating the database or for manual testing scenarios rather than automated test scripts.

## 🚀 Deployment

Deployment involves moving the project files to a production web server environment configured with PHP and MySQL.

### Production Build
There is no "build" step in the modern sense for this traditional PHP application. The project files themselves are the deployable artifacts.

### Deployment Options
-   **Shared/VPS Hosting**: Upload the entire project directory to your web server's public HTML directory. Configure your web server (Apache/Nginx) to point to the project root.
-   **Cloud Platforms**: Deploy to platforms that support PHP (e.g., AWS EC2, DigitalOcean Droplet, Google Cloud Compute Engine) by setting up a LAMP or LEMP stack.
-   **Docker**: While not explicitly configured, a `Dockerfile` could be created to containerize the application for easier deployment and scalability.

## 📚 API Reference

This application does not expose a public API in the RESTful sense. It is a monolithic web application where frontend requests are directly handled by PHP scripts rendering dynamic HTML.

## 🤝 Contributing

We welcome contributions to improve the Assam Level College Counselling System!

### Development Setup for Contributors
The development setup is as described in the [Quick Start](#🚀-quick-start) section. Ensure you have a working PHP/MySQL environment.

### Contribution Guidelines
1.  Fork the repository.
2.  Create a new branch for your feature or bug fix: `git checkout -b feature/your-feature-name` or `bugfix/issue-description`.
3.  Make your changes, ensuring code quality and adherence to existing style.
4.  Commit your changes: `git commit -m "feat: Add new feature"`.
5.  Push to your fork: `git push origin feature/your-feature-name`.
6.  Open a Pull Request to the `main` branch of this repository.

## 📄 License

This project currently has **no explicit license specified**. Please contact the repository owner for licensing information regarding commercial or public use.

## 🙏 Acknowledgments

-   Inspired by the **SAMARTH eGov Portal** for its comprehensive educational administration features.
-   The development team and contributors to the project. <!-- TODO: Add actual names if known -->

## 📞 Support & Contact

-   📧 Email: [contact@example.com] <!-- TODO: Add primary contact email for the project -->
-   🐛 Issues: [GitHub Issues](https://github.com/bca-college-project/assam_level_college_counselling/issues)
-   💬 Discussions: (Not enabled by default, can be added via GitHub Discussions)

---

<div align="center">

**⭐ Star this repo if you find it helpful!**

Made with ❤️ by [Author Name] <!-- TODO: Add author name or project team name -->

</div>
```

