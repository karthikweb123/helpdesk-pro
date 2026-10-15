<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\KnowledgeArticle;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use App\Notifications\RegistrationVerificationCode;
use App\Notifications\TicketUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class HelpdeskTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_requires_email_verification_before_creating_a_customer_account(): void
    {
        Notification::fake();
        $registration = [
            'name' => 'Jamie Customer',
            'username' => 'jamie-customer',
            'email' => 'jamie@example.test',
            'password' => 'A-long-test-password-1',
            'password_confirmation' => 'A-long-test-password-1',
        ];

        $this->from(route('register'))->post(route('register.store'), $registration)
            ->assertRedirect(route('register'))
            ->assertSessionHas('verification_sent', true);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'jamie@example.test']);

        $code = null;
        Notification::assertSentOnDemand(
            RegistrationVerificationCode::class,
            function (RegistrationVerificationCode $notification, array $channels, $notifiable) use (&$code): bool {
                $code = $notification->code;

                return in_array('mail', $channels, true)
                    && $notifiable->routeNotificationFor('mail') === 'jamie@example.test';
            }
        );
        $this->assertNotNull($code);

        $this->post(route('register.store'), $registration + ['verification_code' => $code])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->get(route('dashboard'))->assertOk()->assertSee('Good to see you');
        $this->assertDatabaseHas('users', [
            'email' => 'jamie@example.test',
            'role' => 'customer',
        ]);
        $this->assertNotNull(User::where('email', 'jamie@example.test')->value('email_verified_at'));
    }

    public function test_registration_rejects_an_invalid_email_verification_code(): void
    {
        Notification::fake();
        $registration = [
            'name' => 'Jamie Customer',
            'username' => 'jamie-customer',
            'email' => 'jamie@example.test',
            'password' => 'A-long-test-password-1',
            'password_confirmation' => 'A-long-test-password-1',
        ];

        $this->post(route('register.store'), $registration);

        $this->from(route('register'))->post(route('register.store'), $registration + ['verification_code' => '000000'])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('verification_code')
            ->assertSessionHas('verification_sent', true);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'jamie@example.test']);
    }

    public function test_customers_can_only_view_their_own_tickets(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $ownTicket = $customer->tickets()->create([
            'subject' => 'My request',
            'description' => 'My description',
            'category' => 'Hardware',
            'priority' => 'low',
        ]);
        $ticket = $otherCustomer->tickets()->create([
            'subject' => 'Private request',
            'description' => 'Private description',
            'category' => 'Software',
            'priority' => 'medium',
        ]);

        $this->actingAs($customer)
            ->get(route('tickets.show', $ownTicket))
            ->assertOk()
            ->assertSee('My request');

        $this->actingAs($customer)
            ->get(route('tickets.show', $ticket))
            ->assertForbidden();

        $this->actingAs($customer)
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertDontSee('Private request');
    }

    public function test_agents_can_update_ticket_status_and_notify_the_customer(): void
    {
        Notification::fake();
        $customer = User::factory()->create();
        $agent = User::factory()->create(['role' => 'agent']);
        $ticket = $customer->tickets()->create([
            'subject' => 'Cannot access app',
            'description' => 'The app fails to load.',
            'category' => 'Software',
            'priority' => 'high',
        ]);

        $this->actingAs($agent)->get(route('tickets.edit', $ticket))->assertOk();

        $response = $this->actingAs($agent)->patch(route('tickets.update', $ticket), [
            'subject' => $ticket->subject,
            'description' => $ticket->description,
            'category' => $ticket->category,
            'priority' => $ticket->priority,
            'status' => 'in_progress',
            'assigned_to' => $agent->id,
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'in_progress',
            'assigned_to' => $agent->id,
        ]);
        Notification::assertSentTo($customer, TicketUpdated::class);
    }

    public function test_customer_reply_notifies_the_assigned_agent(): void
    {
        Notification::fake();
        $customer = User::factory()->create();
        $agent = User::factory()->create(['role' => 'agent']);
        $ticket = $customer->tickets()->create([
            'subject' => 'Network issue',
            'description' => 'Wi-Fi drops frequently.',
            'category' => 'Network',
            'priority' => 'medium',
            'assigned_to' => $agent->id,
        ]);

        $this->actingAs($customer)->post(route('tickets.reply', $ticket), [
            'body' => 'The issue is still happening.',
        ])->assertRedirect(route('tickets.show', $ticket));

        $this->assertDatabaseHas('ticket_replies', [
            'ticket_id' => $ticket->id,
            'user_id' => $customer->id,
            'body' => 'The issue is still happening.',
        ]);
        Notification::assertSentTo($agent, TicketUpdated::class);
    }

    public function test_knowledge_base_is_public_and_admin_analytics_is_restricted(): void
    {
        $article = KnowledgeArticle::create([
            'title' => 'Reset an application',
            'category' => 'Software',
            'summary' => 'Basic recovery steps.',
            'body' => 'Restart the application.',
        ]);

        $this->get(route('knowledge.show', $article))->assertOk()->assertSee('Restart the application.');
        $customer = User::factory()->create();
        $this->actingAs($customer)->get(route('analytics'))->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('analytics'))->assertOk();
    }

    public function test_unassigned_customer_reply_notifies_support_staff(): void
    {
        Notification::fake();
        $customer = User::factory()->create();
        $agent = User::factory()->create(['role' => 'agent']);
        $ticket = $customer->tickets()->create([
            'subject' => 'Need help',
            'description' => 'Something is not working.',
            'category' => 'Hardware',
            'priority' => 'low',
        ]);

        $this->actingAs($customer)->post(route('tickets.reply', $ticket), [
            'body' => 'Adding more information.',
        ])->assertRedirect(route('tickets.show', $ticket));

        Notification::assertSentTo($agent, TicketUpdated::class);
    }

    public function test_ticket_attachments_are_private_and_download_authorized(): void
    {
        Storage::fake('local');
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();

        $this->actingAs($customer)->post(route('tickets.store'), [
            'subject' => 'Application error',
            'description' => 'The application shows an error.',
            'category' => 'Software',
            'priority' => 'medium',
            'attachments' => [UploadedFile::fake()->createWithContent('error.txt', 'Error details')],
        ])->assertRedirect();

        $attachment = TicketAttachment::firstOrFail();
        Storage::disk('local')->assertExists($attachment->path);
        $this->actingAs($customer)->get(route('attachments.download', $attachment))->assertOk();
        $this->actingAs($otherCustomer)->get(route('attachments.download', $attachment))->assertForbidden();
    }

    public function test_admin_creation_command_hashes_password_and_requires_first_login_change(): void
    {
        $temporaryPassword = Str::password(20);

        $this->artisan('admin:create', ['username' => 'admin'])
            ->expectsQuestion('Admin email address', 'admin@example.test')
            ->expectsQuestion('Initial password', $temporaryPassword)
            ->expectsQuestion('Confirm initial password', $temporaryPassword)
            ->expectsOutputToContain('Change the initial password')
            ->assertExitCode(0);

        $admin = User::where('username', 'admin')->firstOrFail();
        $this->assertSame('admin', $admin->role);
        $this->assertTrue($admin->must_change_password);
        $this->assertTrue(Hash::check($temporaryPassword, $admin->password));
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.created', 'subject_id' => $admin->id]);
    }

    public function test_admin_first_login_requires_password_change_before_dashboard_access(): void
    {
        $temporaryPassword = Str::password(20);
        $admin = User::factory()->create([
            'username' => 'admin',
            'role' => 'admin',
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => true,
        ]);

        $this->post(route('login.store'), ['login' => 'admin', 'password' => $temporaryPassword])
            ->assertRedirect(route('account.password.edit'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('account.password.edit'));
        $this->get(route('account.password.edit'))->assertOk()->assertSee('temporary password');

        $this->put(route('account.password.update'), [
            'current_password' => $temporaryPassword,
            'password' => 'A-Strong-Admin-Password-2026!',
            'password_confirmation' => 'A-Strong-Admin-Password-2026!',
        ])->assertRedirect(route('dashboard'));

        $this->assertFalse($admin->fresh()->must_change_password);
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Dashboard overview');
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $admin->id,
            'action' => 'password.changed',
        ]);
    }

    public function test_admin_routes_are_forbidden_to_customers_and_agents(): void
    {
        $customer = User::factory()->create();
        $agent = User::factory()->create(['role' => 'agent']);

        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($agent)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_existing_accounts_can_still_sign_in_with_email_and_inactive_accounts_cannot(): void
    {
        $legacyUser = User::factory()->create(['username' => null]);

        $this->post(route('login.store'), ['login' => $legacyUser->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($legacyUser);

        $this->post(route('logout'))->assertRedirect(route('home'));
        $legacyUser->update(['is_active' => false]);
        $this->post(route('login.store'), ['login' => $legacyUser->email, 'password' => 'password'])
            ->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_admin_can_manage_users_but_cannot_remove_the_last_admin(): void
    {
        $admin = User::factory()->create(['username' => 'primary-admin', 'role' => 'admin']);
        $this->actingAs($admin);

        $this->post(route('admin.users.store'), [
            'name' => 'New Support Agent',
            'username' => 'new-agent',
            'email' => 'new-agent@example.test',
            'role' => 'agent',
            'password' => 'Strong-Agent-Password-2026!',
            'password_confirmation' => 'Strong-Agent-Password-2026!',
            'is_active' => '1',
            'must_change_password' => '1',
        ])->assertRedirect();

        $agent = User::where('username', 'new-agent')->firstOrFail();
        $this->assertSame('agent', $agent->role);
        $this->assertTrue($agent->must_change_password);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.created', 'subject_id' => $agent->id]);

        $this->patch(route('admin.users.active', $admin))->assertForbidden();
        $this->patch(route('admin.users.active', $agent))->assertSessionHas('status', 'User account deactivated.');
        $this->assertFalse($agent->fresh()->is_active);
        $this->patch(route('admin.users.active', $agent))->assertSessionHas('status', 'User account activated.');
        $this->assertTrue($agent->fresh()->is_active);

        $this->put(route('admin.users.update', $agent), [
            'name' => $agent->name,
            'username' => $agent->username,
            'email' => $agent->email,
            'role' => 'admin',
            'is_active' => '1',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect();
        $this->assertTrue($agent->fresh()->must_change_password);
        $this->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'username' => 'primary-admin',
            'email' => $admin->email,
            'role' => 'customer',
            'is_active' => '1',
        ])->assertSessionHasErrors('role');
    }

    public function test_admin_can_view_ticket_reports_export_csv_and_review_audit_log(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $ticket = $customer->tickets()->create([
            'subject' => 'CSV export request',
            'description' => 'Testing ticket reports.',
            'category' => 'Software',
            'priority' => 'high',
        ]);
        AuditLog::create([
            'actor_id' => $admin->id,
            'action' => 'ticket.updated',
            'subject_type' => Ticket::class,
            'subject_id' => $ticket->id,
            'description' => 'Reviewed a ticket.',
        ]);

        $this->actingAs($admin)->get(route('admin.tickets.index'))->assertOk()->assertSee('CSV export request');
        $this->get(route('admin.agents.index'))->assertOk();
        $this->get(route('admin.reports.index'))->assertOk()->assertSee('Ticket reports');
        $this->get(route('admin.reports.export'))->assertDownload('helpdesk-ticket-report.csv');
        $this->get(route('admin.audit.index'))->assertOk()->assertSee('Reviewed a ticket.');
        $this->get(route('admin.profile.edit'))->assertOk();
    }
}
