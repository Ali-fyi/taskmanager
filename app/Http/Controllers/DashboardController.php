<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        $myTasks = Task::where('assigned_to', $user->id)
            ->with(['project.workspace', 'status'])
            ->latest()
            ->get();

        $stats = [
            'workspaces' => $user->workspaces()->count(),
            'assigned'   => $myTasks->count(),
            'overdue'    => $myTasks
                ->filter(fn(Task $t) => $t->due_date && $t->due_date->isPast())
                ->count(),
        ];

        return view('dashboard', compact('myTasks', 'stats'));
    }
}
