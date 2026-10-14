# Helpdesk Pro

A Laravel 12 helpdesk with customer, support-agent, and administrator roles.

## Setup

1. Install PHP dependencies with `composer install`.
2. Copy `.env.example` to `.env`, configure the database and mail transport, and run `php artisan key:generate`.
3. Run `php artisan migrate --seed` to create the helpdesk tables and starter knowledge-base guides.
4. Register your first account, then promote it to administrator:

   ```powershell
   php artisan helpdesk:set-role you@example.com admin
   ```

5. Run the app with `php artisan serve`.

New accounts always receive the customer role. Set trusted support accounts to `agent` with `php artisan helpdesk:set-role agent@example.com agent`; only administrators can open analytics. Never grant roles from public registration.

## Features

- Customers can create and update tickets, attach diagnostic files, reply, and track status.
- Agents can view unassigned tickets and their assigned queue, assign work, set priority, and resolve or close tickets.
- Ticket status changes and replies email the customer and relevant assigned agent. When a customer replies to an unassigned ticket, the support team is notified.
- Ticket attachments are stored on Laravel's private local disk and downloaded only after ticket access is authorized.
- The public knowledge base is searchable; agents and administrators can publish and edit articles.
- Administrator analytics report ticket counts, average time to resolve, and assigned-ticket counts per support user.

Set `MAIL_MAILER` and the appropriate `MAIL_*` values in `.env` to deliver email. The example environment uses Laravel's log mailer, which records messages locally instead of delivering them.

Run the feature and unit tests with `php artisan test`.
# helpdesk-pro
