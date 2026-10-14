<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $query = Ticket::with(['owner', 'assignee'])->latest();

        if (in_array($request->query('status'), ['open', 'in_progress', 'resolved', 'closed'], true)) {
            $query->where('status', $request->query('status'));
        }
        if (in_array($request->query('priority'), ['low', 'medium', 'high', 'urgent'], true)) {
            $query->where('priority', $request->query('priority'));
        }
        if ($request->query('assignee') === '0') {
            $query->whereNull('assigned_to');
        } elseif ($request->filled('assignee') && ctype_digit((string) $request->query('assignee'))) {
            $query->where('assigned_to', (int) $request->query('assignee'));
        }
        if ($request->filled('q')) {
            $search = trim($request->string('q')->toString());
            $query->where(fn ($builder) => $builder
                ->where('subject', 'like', "%{$search}%")
                ->orWhere('id', is_numeric($search) ? (int) $search : 0)
                ->orWhereHas('owner', fn ($owner) => $owner->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")));
        }

        return view('admin.tickets.index', [
            'tickets' => $query->paginate(15)->withQueryString(),
            'agents' => User::query()->whereIn('role', ['agent', 'admin'])->orderBy('name')->get(),
        ]);
    }
}
