<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('actor')->latest();
        if ($request->filled('q')) {
            $search = trim($request->string('q')->toString());
            $query->where(fn ($builder) => $builder
                ->where('description', 'like', "%{$search}%")
                ->orWhere('action', 'like', "%{$search}%")
                ->orWhereHas('actor', fn ($actor) => $actor->where('name', 'like', "%{$search}%")->orWhere('username', 'like', "%{$search}%")));
        }

        return view('admin.audit.index', ['logs' => $query->paginate(25)->withQueryString()]);
    }
}
