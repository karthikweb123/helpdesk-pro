<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $userCounts = User::query()->selectRaw('role, COUNT(*) as total')->groupBy('role')->pluck('total', 'role');
        $statusCounts = Ticket::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $totalTickets = (int) $statusCounts->sum();
        $resolvedCount = (int) $statusCounts->get('resolved', 0) + (int) $statusCounts->get('closed', 0);

        $labels = collect(range(6, 0))->map(fn (int $daysAgo) => now()->subDays($daysAgo)->toDateString());
        $ticketCountsByDay = Ticket::query()
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');
        $chartLabels = $labels->map(fn (string $day) => Carbon::parse($day)->format('D'));
        $chartValues = $labels->map(fn (string $day) => (int) $ticketCountsByDay->get($day, 0));

        $averageResolutionMinutes = Ticket::query()
            ->whereNotNull('resolved_at')
            ->selectRaw('AVG('.(DB::getDriverName() === 'sqlite'
                ? '(julianday(resolved_at) - julianday(created_at)) * 1440'
                : 'TIMESTAMPDIFF(MINUTE, created_at, resolved_at)').') as average_minutes')
            ->value('average_minutes');

        $recentTickets = Ticket::with(['owner', 'assignee'])->latest()->limit(6)->get();
        $recentActivity = AuditLog::with('actor')->latest()->limit(8)->get();
        $activeAgents = User::query()->where('role', 'agent')->where('is_active', true)->count();

        return view('admin.dashboard', compact(
            'userCounts',
            'statusCounts',
            'totalTickets',
            'resolvedCount',
            'chartLabels',
            'chartValues',
            'averageResolutionMinutes',
            'recentTickets',
            'recentActivity',
            'activeAgents',
        ));
    }
}
