<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Company;
use App\Notifications\NewAnnouncementNotification;
use Illuminate\Http\Request;

class AnnouncementsController extends Controller
{
    public function index(Request $request)
    {
        $announcements = Announcement::query()
            ->with('createdByUser:id,name')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        $companies = Company::orderBy('company_name')->get(['id', 'company_name']);
        return view('admin.announcements.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'target_type' => ['required', 'in:all,selected'],
            'target_company_ids' => ['nullable', 'array'],
            'target_company_ids.*' => ['integer', 'exists:companies,id'],
        ]);

        $announcement = Announcement::create([
            'title' => $data['title'],
            'body' => $data['body'],
            'target_type' => $data['target_type'],
            'target_company_ids' => $data['target_type'] === 'selected' ? ($data['target_company_ids'] ?? []) : null,
            'created_by' => auth()->id(),
            'is_published' => true,
            'published_at' => now(),
        ]);

        // Notify target companies
        if ($data['target_type'] === 'all') {
            $companies = Company::where('status', 'active')->get();
        } else {
            $companies = Company::whereIn('id', $data['target_company_ids'] ?? [])->get();
        }
        foreach ($companies as $company) {
            $company->notify(new NewAnnouncementNotification($announcement));
        }

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', __('admin_dashboard.announcement_created'));
    }

    public function edit(Announcement $announcement)
    {
        $companies = Company::orderBy('company_name')->get(['id', 'company_name']);
        return view('admin.announcements.edit', compact('announcement', 'companies'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
            'target_type' => ['required', 'in:all,selected'],
            'target_company_ids' => ['nullable', 'array'],
            'target_company_ids.*' => ['integer', 'exists:companies,id'],
            'is_published' => ['boolean'],
        ]);

        $announcement->update([
            'title' => $data['title'],
            'body' => $data['body'],
            'target_type' => $data['target_type'],
            'target_company_ids' => $data['target_type'] === 'selected' ? ($data['target_company_ids'] ?? []) : null,
            'is_published' => $data['is_published'] ?? true,
        ]);

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', __('admin_dashboard.announcement_updated'));
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();
        return back()->with('success', __('admin_dashboard.announcement_deleted'));
    }
}
