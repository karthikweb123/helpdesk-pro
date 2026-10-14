<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);

        $total = Ticket::count();
        $open = Ticket::whereNotIn('status', ['resolved', 'closed'])->count();
        $closed = Ticket::whereIn('status', ['resolved', 'closed'])->count();
        $averageMinutes = Ticket::whereNotNull('resolved_at')
            ->selectRaw('AVG('.(DB::getDriverName() === 'sqlite'
                ? '(julianday(resolved_at) - julianday(created_at)) * 1440'
                : 'TIMESTAMPDIFF(MINUTE, created_at, resolved_at)').') as average_minutes')
            ->value('average_minutes');

        $agents = User::query()
            ->whereIn('role', ['admin', 'agent'])
            ->withCount([
                'assignedTickets as resolved_tickets_count' => fn ($query) => $query->whereIn('status', ['resolved', 'closed']),
                'assignedTickets as open_tickets_count' => fn ($query) => $query->whereNotIn('status', ['resolved', 'closed']),
            ])
            ->orderByDesc('resolved_tickets_count')
            ->get();
        $recentTickets = Ticket::with(['owner', 'assignee'])->latest()->limit(8)->get();

        return view('analytics', compact('total', 'open', 'closed', 'averageMinutes', 'agents', 'recentTickets'));
    }
}
