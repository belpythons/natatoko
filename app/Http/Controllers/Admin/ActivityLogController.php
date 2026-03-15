<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the activity logs.
     */
    public function index(Request $request): Response
    {
        $logs = ActivityLog::with('user:id,name') // Optimize eager load
            ->latest()
            ->paginate(50)
            ->through(function ($log) {
            return [
            'id' => $log->id,
            'user' => $log->user ? $log->user->name : 'Sistem', // Handle deleted users or system events
            'action' => $log->action,
            'description' => $log->description,
            'properties' => $log->properties,
            'ip_address' => $log->ip_address,
            'created_at' => $log->created_at->format('Y-m-d H:i:s'),
            'human_time' => $log->created_at->diffForHumans(),
            ];
        });

        return Inertia::render('Admin/ActivityLogs/Index', [
            'logs' => $logs
        ]);
    }
}