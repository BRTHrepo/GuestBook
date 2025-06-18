
# Guest Book Application

## Overview
This project is a Guest Book application built using PHP, JavaScript, and SQL. It allows users to leave messages, rate their experience, and provides an admin interface for moderation.

## Features
- User message submission with name, email, and rating.
- Admin moderation for approving or rejecting messages.
- Email notifications for message confirmation.
- Secure database connection using PDO.
- Responsive design with JavaScript interactivity.

## Technologies Used
- **Languages**: PHP, JavaScript, SQL
- **Database**: MySQL
- **Composer**: Dependency management
- **SMTP**: Email handling via Brevo

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/BRTHrepo/guest-book.git
   ```

2. Navigate to the project directory:
   ```bash
   cd guest-book
   ```

3. Install dependencies using Composer:
   ```bash
   composer install
   ```

4. Set up the `.env` file:
   - Configure database and email settings as per your environment.

5. Import the database schema:
   ```bash
   mysql -u [DB_USER] -p [DB_NAME] < database/schema.sql
   ```

## Configuration
Update the `.env` file with your database and email credentials:
```dotenv
DB_HOST=localhost
DB_PORT=3306
DB_NAME=guest_book
DB_USER=your_db_user
DB_PASS=your_db_password

MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_email_password
MAIL_FROM_ADDRESS=your_from_email
MAIL_FROM_NAME=Your Name
MAIL_MODERATOR=


## Usage
- Open the application in your browser 
- Submit a message via the form.
- Admins can log in to moderate messages.

## License
This project is licensed under the MIT License.
```
