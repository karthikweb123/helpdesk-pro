<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);
        $query = $this->filteredTickets($from, $to);
        $byStatus = (clone $query)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $byPriority = (clone $query)->selectRaw('priority, COUNT(*) as total')->groupBy('priority')->pluck('total', 'priority');
        $averageMinutes = (clone $query)->whereNotNull('resolved_at')
            ->selectRaw('AVG('.$this->resolutionExpression().') as average_minutes')
            ->value('average_minutes');
        $tickets = (clone $query)->with(['owner', 'assignee'])->latest()->paginate(15)->withQueryString();

        return view('admin.reports.index', compact('from', 'to', 'byStatus', 'byPriority', 'averageMinutes', 'tickets'));
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->dateRange($request);
        $tickets = $this->filteredTickets($from, $to)->with(['owner', 'assignee'])->latest();

        return response()->streamDownload(function () use ($tickets): void {
            $output = fopen('php://output', 'w');
            if ($output === false) {
                throw new \RuntimeException('Unable to open CSV output stream.');
            }
            fputcsv($output, ['Ticket ID', 'Subject', 'Customer', 'Category', 'Priority', 'Status', 'Agent', 'Created At', 'Resolved At', 'Resolution Minutes']);
            foreach ($tickets->cursor() as $ticket) {
                fputcsv($output, [
                    $ticket->id,
                    $this->safeCsvCell($ticket->subject),
                    $this->safeCsvCell($ticket->owner->name),
                    $this->safeCsvCell($ticket->category),
                    $this->safeCsvCell($ticket->priority),
                    $this->safeCsvCell($ticket->status),
                    $this->safeCsvCell($ticket->assignee?->name ?? ''),
                    $ticket->created_at->toDateTimeString(),
                    $ticket->resolved_at?->toDateTimeString() ?? '',
                    $ticket->resolved_at ? $ticket->created_at->diffInMinutes($ticket->resolved_at) : '',
                ]);
            }
            fclose($output);
        }, 'helpdesk-ticket-report.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function dateRange(Request $request): array
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return [
            isset($data['from']) ? Carbon::parse($data['from'])->startOfDay() : null,
            isset($data['to']) ? Carbon::parse($data['to'])->endOfDay() : null,
        ];
    }

    private function filteredTickets(?Carbon $from, ?Carbon $to)
    {
        return Ticket::query()
            ->when($from, fn ($query) => $query->where('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->where('created_at', '<=', $to));
    }

    private function resolutionExpression(): string
    {
        return DB::getDriverName() === 'sqlite'
            ? '(julianday(resolved_at) - julianday(created_at)) * 1440'
            : 'TIMESTAMPDIFF(MINUTE, created_at, resolved_at)';
    }

    private function safeCsvCell(string $value): string
    {
        return preg_match('/^[\t\r\n ]*[=+\-@]/', $value) ? "'{$value}" : $value;
    }
}
