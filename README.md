# Helpdesk Pro

**A Laravel-powered customer support and ticket management system.**

Helpdesk Pro helps businesses manage customer support requests through a centralized platform with ticket tracking, support-agent workflows, email notifications, a searchable knowledge base, and administrative reporting.

**Live Demo:** https://helpdesk.ndapps.tech/  
**Repository:** https://github.com/karthikweb123/helpdesk-pro

## Features

### Customer Portal
- Register and sign in securely.
- Create support tickets and attach diagnostic files.
- Reply to tickets and track their progress.
- Update permitted ticket information.
- Search the public knowledge base for helpful articles.

### Support Agent Portal
- View unassigned tickets and assigned work.
- Assign tickets and manage support workloads.
- Set ticket priorities and update statuses.
- Reply to customers and resolve or close tickets.
- Create, edit, and publish knowledge-base articles.

### Administrator Dashboard
- View ticket analytics and resolution metrics.
- Manage customers, support agents, and user roles.
- Activate or deactivate user accounts.
- Monitor all tickets and agent workloads.
- Export reports to CSV.
- Update administrator profile details.
- Review audit events.

### Notifications and File Security
- Email notifications for ticket replies and status changes.
- Support-team notifications for replies to unassigned tickets.
- Private ticket attachments stored on Laravel's local storage disk.
- Attachment downloads authorized against ticket access permissions.
- Public registration restricted to the customer role.
- Administrator access restricted to active administrators.
- Mandatory password change for the initial administrator account.

## Technology Stack

| Technology | Purpose |
|---|---|
| Laravel 12 | Application framework |
| PHP | Backend development |
| MySQL or compatible database | Data storage |
| Blade, HTML and CSS | User interface |
| Laravel Authentication and Authorization | Access control |
| Laravel Mail | Email notifications |
| Laravel Local Storage | Private file attachments |
| PHPUnit / Laravel Testing | Automated tests |
| Linux VPS, Nginx and HTTPS | Production deployment |

## Requirements

Before installing, make sure your environment includes:

- PHP version supported by Laravel 12, with the required PHP extensions.
- Composer.
- MySQL or another supported database.
- Node.js and npm if the project includes frontend assets that need compiling.
- A web server such as Nginx for production deployment.

Consult the Laravel 12 documentation for the exact PHP and extension requirements.

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/karthikweb123/helpdesk-pro.git
cd helpdesk-pro
```

### 2. Install dependencies

```bash
composer install
```

If the application has frontend assets:

```bash
npm install
npm run build
```

### 3. Configure the environment

Create your local environment file:

```bash
cp .env.example .env
```

Update `.env` with your database connection and mail settings. Keep production credentials private.

Generate the application key:

```bash
php artisan key:generate
```

### 4. Set up the database

Create an empty database and configure the database connection in `.env`.

Run migrations and seed the starter data:

```bash
php artisan migrate --seed
```

**Important:** Review the seeders before running them in production. Never seed default or predictable administrator credentials into a live environment.

### 5. Create the initial administrator

Run:

```bash
php artisan admin:create admin
```

Follow the interactive prompts to enter the administrator email and temporary password.

The command is designed to hash the password and require a password change after the first sign-in.

Sign in using the username `admin` and the temporary password you entered. Complete the mandatory password change before accessing the dashboard.

### 6. Configure support-agent access

Public registrations must remain customer accounts.

To grant support-agent access to a trusted account, run:

```bash
php artisan helpdesk:set-role agent@example.com agent
```

Replace the example email with the actual account email.

Only trusted accounts should receive elevated roles. Verify the account's identity before changing its permissions.

### 7. Start the application locally

For local development:

```bash
php artisan serve
```

Open the local URL shown in your terminal.

For production, configure your web server to serve the Laravel application's `public` directory. Do not expose the project root, `.env`, private storage, or application source files directly to the web.

## Email Configuration

Configure the appropriate mail transport in `.env`.

For example:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-mail-username
MAIL_PASSWORD=your-mail-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=support@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

Use the values supplied by your email provider. Do not commit real credentials to Git.

The example environment uses Laravel's log mailer, which writes email output to local logs instead of delivering messages.

## Running Tests

Run the automated test suite:

```bash
php artisan test
```

Use a separate testing database and test environment. Passing automated tests is useful, but does not by itself prove that every security control or production workflow is safe.

## Production Deployment Checklist

Before exposing a production installation to customers:

- [ ] Set `APP_ENV=production`.
- [ ] Set `APP_DEBUG=false`.
- [ ] Configure HTTPS and renew certificates as required.
- [ ] Use strong, unique passwords for administrators and support agents.
- [ ] Keep `.env`, private files, and database credentials inaccessible to the public.
- [ ] Verify role-based access control for customers, agents, and administrators.
- [ ] Validate uploaded file types, sizes, and filenames.
- [ ] Confirm ticket attachments cannot be downloaded by unauthorized users.
- [ ] Configure working email delivery and test notifications.
- [ ] Review database backups and recovery procedures.
- [ ] Review logs, audit events, and error handling.
- [ ] Run automated tests and manually verify critical workflows.
- [ ] Keep Laravel and Composer dependencies updated with compatible security fixes.

## Project Structure

A typical Laravel application uses these directories:

```text
helpdesk-pro/
├── app/             # Application logic
├── bootstrap/       # Framework bootstrap
├── config/          # Configuration files
├── database/        # Migrations, factories and seeders
├── public/          # Public web root
├── resources/        # Views
