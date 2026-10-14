<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketReply;
use App\Models\User;
use App\Notifications\TicketUpdated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class TicketController extends Controller
{
    private const CATEGORIES = ['Hardware', 'Software', 'Network'];

    private const PRIORITIES = ['low', 'medium', 'high', 'urgent'];

    private const STATUSES = ['open', 'in_progress', 'resolved', 'closed'];

    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Ticket::query()->with(['owner', 'assignee'])->latest();

        if (! $user->isStaff()) {
            $query->where('user_id', $user->id);
        } elseif ($user->role === 'agent') {
            $query->where(function ($builder) use ($user) {
                $builder->where('assigned_to', $user->id)->orWhereNull('assigned_to');
            });
        }

        if ($request->filled('status') && in_array($request->status, self::STATUSES, true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $search = trim($request->string('q')->toString());
            $query->where(function ($builder) use ($search) {
                $builder->where('subject', 'like', "%{$search}%")
                    ->orWhere('id', is_numeric($search) ? (int) $search : 0);
            });
        }

        $tickets = $query->paginate(12)->withQueryString();

        return view('tickets.index', compact('tickets'));
    }

    public function create(): View
    {
        return view('tickets.create', ['ticket' => new Ticket]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:10000'],
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'priority' => ['required', Rule::in(self::PRIORITIES)],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,txt,log,zip'],
        ]);

        $ticket = $request->user()->tickets()->create(collect($data)->except('attachments')->all());
        $this->storeAttachments($request, $ticket);

        return redirect()->route('tickets.show', $ticket)->with('status', 'Your support ticket has been created.');
    }

    public function show(Request $request, Ticket $ticket): View
    {
        $this->authorizeTicket($request, $ticket);
        $ticket->load(['owner', 'assignee', 'attachments.uploader', 'replies']);

        $agents = $request->user()->isStaff()
            ? User::query()->whereIn('role', ['admin', 'agent'])->orderBy('name')->get()
            : collect();

        return view('tickets.show', compact('ticket', 'agents'));
    }

    public function edit(Request $request, Ticket $ticket): View
    {
        $this->authorizeTicket($request, $ticket);
        abort_if($ticket->status === 'closed', 403);

        $agents = $request->user()->isStaff()
            ? User::query()->whereIn('role', ['admin', 'agent'])->orderBy('name')->get()
            : collect();

        return view('tickets.edit', compact('ticket', 'agents'));
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorizeTicket($request, $ticket);
        abort_if($ticket->status === 'closed', 403);
        $user = $request->user();

        $rules = [
            'subject' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:10000'],
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'priority' => ['required', Rule::in(self::PRIORITIES)],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,txt,log,zip'],
        ];

        if ($user->isStaff()) {
            $rules['status'] = ['required', Rule::in(self::STATUSES)];
            $rules['assigned_to'] = ['nullable', 'integer', Rule::exists('users', 'id')->whereIn('role', ['admin', 'agent'])];
        }

        $data = $request->validate($rules);
        $previousStatus = $ticket->status;
        $ticket->fill(collect($data)->except(['attachments', 'status', 'assigned_to'])->all());

        if ($user->isStaff()) {
            $ticket->status = $data['status'];
            $ticket->assigned_to = $data['assigned_to'] ?? null;
            $ticket->resolved_at = in_array($ticket->status, ['resolved', 'closed'], true)
                ? ($ticket->resolved_at ?? now())
                : null;
        }

        $ticket->save();
        $this->storeAttachments($request, $ticket);

        if ($previousStatus !== $ticket->status) {
            $this->notifyParticipants(
                $ticket,
                $user,
                'Ticket status changed',
                "The status of ticket #{$ticket->id} changed from ".str_replace('_', ' ', $previousStatus).' to '.str_replace('_', ' ', $ticket->status).'.'
            );
        }

        return redirect()->route('tickets.show', $ticket)->with('status', 'Ticket details updated.');
    }

    public function reply(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorizeTicket($request, $ticket);
        abort_if(in_array($ticket->status, ['closed', 'resolved'], true) && ! $request->user()->isStaff(), 403);
        $data = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,txt,log,zip'],
        ]);

        $reply = $ticket->replies()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);
        $this->storeAttachments($request, $ticket, $reply);

        $this->notifyParticipants(
            $ticket,
            $request->user(),
            'New reply on your support ticket',
            "{$request->user()->name} added a reply to ticket #{$ticket->id}."
        );

        return redirect()->route('tickets.show', $ticket)->with('status', 'Your reply has been sent.');
    }

    public function download(Request $request, TicketAttachment $attachment)
    {
        $ticket = $attachment->ticket;
        $this->authorizeTicket($request, $ticket);

        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }

    private function authorizeTicket(Request $request, Ticket $ticket): void
    {
        $user = $request->user();
        abort_unless(
            $user->isStaff() || $ticket->user_id === $user->id,
            403
        );
        if ($user->role === 'agent') {
            abort_unless($ticket->assigned_to === null || $ticket->assigned_to === $user->id, 403);
        }
    }

    private function storeAttachments(Request $request, Ticket $ticket, ?TicketReply $reply = null): void
    {
        foreach ($request->file('attachments', []) as $file) {
            $path = $file->store('ticket-attachments', 'local');
            if ($path === false) {
                throw new RuntimeException('Unable to store the uploaded ticket attachment.');
            }

            TicketAttachment::create([
                'ticket_id' => $ticket->id,
                'reply_id' => $reply?->id,
                'user_id' => $request->user()->id,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
            ]);
        }
    }

    private function notifyParticipants(Ticket $ticket, User $actor, string $subject, string $details): void
    {
        $recipients = collect([$ticket->owner, $ticket->assignee])
            ->filter()
            ->merge(
                ! $actor->isStaff() && ! $ticket->assignee
                    ? User::query()->whereIn('role', ['admin', 'agent'])->get()
                    : collect()
            )
            ->unique('id')
            ->reject(fn (User $recipient) => $recipient->id === $actor->id);

        foreach ($recipients as $recipient) {
            $recipient->notify(new TicketUpdated($ticket, $subject, $details));
        }
    }
}
