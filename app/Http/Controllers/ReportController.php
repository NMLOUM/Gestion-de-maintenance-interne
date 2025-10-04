<?php
// app/Http/Controllers/ReportController.php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Service;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TicketsExport;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        // Statistiques générales
        $stats = [
            'created' => Ticket::whereMonth('created_at', Carbon::now()->month)->count(),
            'resolved' => Ticket::whereMonth('resolved_at', Carbon::now()->month)->count(),
            'avg_time' => round(Ticket::whereNotNull('resolved_at')
                ->whereMonth('resolved_at', Carbon::now()->month)
                ->avg(DB::raw('TIMESTAMPDIFF(HOUR, created_at, resolved_at)')), 1),
            'active_users' => User::whereMonth('last_login_at', Carbon::now()->month)->count(),
        ];

        $stats['resolution_rate'] = $stats['created'] > 0
            ? round(($stats['resolved'] / $stats['created']) * 100, 1)
            : 0;

        // Tickets par statut
        $byStatus = Ticket::whereMonth('created_at', Carbon::now()->month)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Tickets par priorité
        $byPriority = Ticket::whereMonth('created_at', Carbon::now()->month)
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority');

        // Tickets par service
        $byService = Service::withCount(['tickets' => function ($query) {
                $query->whereMonth('created_at', Carbon::now()->month);
            }])
            ->having('tickets_count', '>', 0)
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'count' => $service->tickets_count
                ];
            });

        // Top techniciens
        $topTechnicians = User::technicians()
            ->withCount([
                'assignedTickets as resolved_count' => function ($query) {
                    $query->where('status', 'resolved')
                          ->whereMonth('resolved_at', Carbon::now()->month);
                },
                'assignedTickets as total_count' => function ($query) {
                    $query->whereMonth('created_at', Carbon::now()->month);
                }
            ])
            ->get()
            ->map(function ($tech) {
                $tech->success_rate = $tech->total_count > 0
                    ? round(($tech->resolved_count / $tech->total_count) * 100, 1)
                    : 0;
                return $tech;
            })
            ->sortByDesc('resolved_count')
            ->take(5)
            ->values();

        return Inertia::render('Reports/Index', [
            'stats' => $stats,
            'byStatus' => $byStatus,
            'byPriority' => $byPriority,
            'byService' => $byService,
            'topTechnicians' => $topTechnicians,
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:summary,detailed,technician,service',
            'format' => 'required|in:pdf,excel',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'service_id' => 'nullable|exists:services,id',
            'category_id' => 'nullable|exists:categories,id',
            'technician_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:pending,in_progress,resolved,closed,cancelled',
        ]);

        $data = $this->getReportData($request);

        if ($request->format === 'pdf') {
            return $this->generatePDF($request->type, $data, $request);
        } else {
            return $this->generateExcel($request->type, $data, $request);
        }
    }

    private function getReportData($request)
    {
        $query = Ticket::with(['requester', 'assignedUser', 'service', 'category'])
                      ->whereBetween('created_at', [
                          Carbon::parse($request->date_from)->startOfDay(),
                          Carbon::parse($request->date_to)->endOfDay()
                      ]);

        if ($request->service_id) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->technician_id) {
            $query->where('assigned_to', $request->technician_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $tickets = $query->orderBy('created_at', 'desc')->get();

        // Statistiques générales
        $stats = [
            'total' => $tickets->count(),
            'by_status' => $tickets->groupBy('status')->map->count(),
            'by_priority' => $tickets->groupBy('priority')->map->count(),
            'by_service' => $tickets->groupBy('service.name')->map->count(),
            'by_category' => $tickets->groupBy('category.name')->map->count(),
            'avg_resolution_time' => $this->calculateAverageResolutionTime($tickets),
        ];

        return [
            'tickets' => $tickets,
            'stats' => $stats,
            'filters' => $request->only(['date_from', 'date_to', 'service_id', 'category_id', 'technician_id', 'status']),
        ];
    }

    private function calculateAverageResolutionTime($tickets)
    {
        $resolvedTickets = $tickets->where('status', 'resolved')->where('resolved_at');
        
        if ($resolvedTickets->isEmpty()) {
            return 0;
        }

        $totalHours = $resolvedTickets->sum(function ($ticket) {
            return Carbon::parse($ticket->created_at)->diffInHours(Carbon::parse($ticket->resolved_at));
        });

        return round($totalHours / $resolvedTickets->count(), 2);
    }

    private function generatePDF($type, $data, $request)
    {
        $view = match($type) {
            'summary' => 'reports.summary',
            'detailed' => 'reports.detailed',
            'technician' => 'reports.technician',
            'service' => 'reports.service',
        };

        $pdf = PDF::loadView($view, $data);
        
        $filename = $type . '_report_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    private function generateExcel($type, $data, $request)
    {
        $filename = $type . '_report_' . date('Y-m-d') . '.xlsx';
        
        return Excel::download(new TicketsExport($data['tickets']), $filename);
    }
}
