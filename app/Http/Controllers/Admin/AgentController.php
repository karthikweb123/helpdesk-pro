<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class AgentController extends Controller
{
    public function index(): View
    {
        $agents = User::query()
            ->where('role', 'agent')
            ->withCount([
                'assignedTickets as open_tickets_count' => fn ($query) => $query->whereNotIn('status', ['resolved', 'closed']),
                'assignedTickets as resolved_tickets_count' => fn ($query) => $query->whereIn('status', ['resolved', 'closed']),
            ])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.agents.index', compact('agents'));
    }
}
