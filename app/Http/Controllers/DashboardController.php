<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $tickets = Ticket::query()->with(['owner', 'assignee'])->latest();

        if (! $user->isStaff()) {
            $tickets->where('user_id', $user->id);
        } elseif ($user->role === 'agent') {
            $tickets->where(function ($query) use ($user) {
                $query->where('assigned_to', $user->id)->orWhereNull('assigned_to');
            });
        }

        $openCount = (clone $tickets)->whereNotIn('status', ['resolved', 'closed'])->count();
        $closedCount = (clone $tickets)->whereIn('status', ['resolved', 'closed'])->count();
        $recentTickets = (clone $tickets)->limit(6)->get();

        return view('dashboard', compact('user', 'openCount', 'closedCount', 'recentTickets'));
    }
}
