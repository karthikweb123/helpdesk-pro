<?php

namespace Tests\Feature;

use App\Models\KnowledgeArticle;
use App\Models\TicketAttachment;
use App\Models\User;
use App\Notifications\TicketUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HelpdeskTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_customer_account(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Jamie Customer',
            'email' => 'jamie@example.test',
            'password' => 'A-long-test-password-1',
            'password_confirmation' => 'A-long-test-password-1',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->get(route('dashboard'))->assertOk()->assertSee('Good to see you');
        $this->assertDatabaseHas('users', [
            'email' => 'jamie@example.test',
            'role' => 'customer',
        ]);
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
}
