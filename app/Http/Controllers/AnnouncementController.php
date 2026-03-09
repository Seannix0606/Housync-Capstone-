<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Property;
use App\Models\TenantAssignment;
use App\Models\User;
use App\Notifications\NewAnnouncement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    // ==================== LANDLORD METHODS ====================

    public function index(Request $request)
    {
        $landlordId = Auth::id();

        $query = Announcement::where('user_id', $landlordId)
            ->with('property')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at');

        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $announcements = $query->paginate(15);

        $properties = Property::where('landlord_id', $landlordId)->get();

        return view('landlord.announcements.index', compact('announcements', 'properties'));
    }

    public function create()
    {
        $landlordId = Auth::id();
        $properties = Property::where('landlord_id', $landlordId)->get();

        return view('landlord.announcements.create', compact('properties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:general,maintenance,emergency,event',
            'priority' => 'required|in:low,normal,high,urgent',
            'property_id' => 'nullable|exists:properties,id',
            'audience' => 'required|in:all_tenants,property_tenants,all_staff,everyone',
            'is_pinned' => 'nullable|boolean',
            'publish_now' => 'nullable|boolean',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $landlordId = Auth::id();

        if ($request->property_id) {
            $property = Property::where('id', $request->property_id)
                ->where('landlord_id', $landlordId)
                ->first();

            if (! $property) {
                return back()->with('error', 'Invalid property selected.');
            }
        }

        $announcement = Announcement::create([
            'user_id' => $landlordId,
            'property_id' => $request->property_id,
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type,
            'priority' => $request->priority,
            'audience' => $request->audience,
            'is_pinned' => $request->boolean('is_pinned'),
            'published_at' => $request->boolean('publish_now') ? now() : null,
            'expires_at' => $request->expires_at,
        ]);

        if ($request->boolean('publish_now')) {
            $this->notifyRecipients($announcement, $landlordId);
        }

        return redirect()->route('landlord.announcements.index')
            ->with('success', 'Announcement created successfully!');
    }

    public function show($id)
    {
        $announcement = Announcement::where('user_id', Auth::id())
            ->findOrFail($id);

        return view('landlord.announcements.show', compact('announcement'));
    }

    public function edit($id)
    {
        $landlordId = Auth::id();
        $announcement = Announcement::where('user_id', $landlordId)->findOrFail($id);
        $properties = Property::where('landlord_id', $landlordId)->get();

        return view('landlord.announcements.edit', compact('announcement', 'properties'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:general,maintenance,emergency,event',
            'priority' => 'required|in:low,normal,high,urgent',
            'property_id' => 'nullable|exists:properties,id',
            'audience' => 'required|in:all_tenants,property_tenants,all_staff,everyone',
            'is_pinned' => 'nullable|boolean',
            'expires_at' => 'nullable|date',
        ]);

        $landlordId = Auth::id();
        $announcement = Announcement::where('user_id', $landlordId)->findOrFail($id);

        $announcement->update([
            'property_id' => $request->property_id,
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type,
            'priority' => $request->priority,
            'audience' => $request->audience,
            'is_pinned' => $request->boolean('is_pinned'),
            'expires_at' => $request->expires_at,
        ]);

        return redirect()->route('landlord.announcements.show', $id)
            ->with('success', 'Announcement updated successfully!');
    }

    public function publish($id)
    {
        $landlordId = Auth::id();
        $announcement = Announcement::where('user_id', $landlordId)->findOrFail($id);

        if ($announcement->published_at) {
            return back()->with('info', 'Announcement is already published.');
        }

        $announcement->update(['published_at' => now()]);
        $this->notifyRecipients($announcement, $landlordId);

        return back()->with('success', 'Announcement published successfully!');
    }

    public function destroy($id)
    {
        $announcement = Announcement::where('user_id', Auth::id())->findOrFail($id);
        $announcement->delete();

        return redirect()->route('landlord.announcements.index')
            ->with('success', 'Announcement deleted successfully!');
    }

    // ==================== TENANT METHODS ====================

    public function tenantIndex()
    {
        $tenantId = Auth::id();

        $activeAssignment = TenantAssignment::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with('unit.property')
            ->first();

        if (! $activeAssignment) {
            return view('tenant.announcements.index', ['announcements' => collect(), 'activeAssignment' => null]);
        }

        $propertyId = $activeAssignment->unit?->property_id;
        $landlordId = $activeAssignment->landlord_id;

        $announcements = Announcement::where('user_id', $landlordId)
            ->active()
            ->where(function ($query) use ($propertyId) {
                $query->whereNull('property_id')
                    ->orWhere('property_id', $propertyId);
            })
            ->whereIn('audience', ['all_tenants', 'property_tenants', 'everyone'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->paginate(15);

        return view('tenant.announcements.index', compact('announcements', 'activeAssignment'));
    }

    public function tenantShow($id)
    {
        $tenantId = Auth::id();

        $activeAssignment = TenantAssignment::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with('unit.property')
            ->first();

        if (! $activeAssignment) {
            abort(403);
        }

        $announcement = Announcement::active()
            ->where('user_id', $activeAssignment->landlord_id)
            ->whereIn('audience', ['tenant', 'all', 'everyone'])
            ->where(function ($query) use ($activeAssignment) {
                $query->whereNull('property_id')
                    ->orWhere('property_id', $activeAssignment->unit?->property_id);
            })
            ->findOrFail($id);

        return view('tenant.announcements.show', compact('announcement'));
    }

    // ==================== STAFF METHODS ====================

    public function staffIndex()
    {
        $staffId = Auth::id();
        $staffProfile = Auth::user()->staffProfile;

        if (! $staffProfile || ! $staffProfile->created_by_landlord_id) {
            return view('staff.announcements.index', ['announcements' => collect()]);
        }

        $landlordId = $staffProfile->created_by_landlord_id;

        $announcements = Announcement::where('user_id', $landlordId)
            ->active()
            ->whereIn('audience', ['all_staff', 'everyone'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->paginate(15);

        return view('staff.announcements.index', compact('announcements'));
    }

    public function staffShow($id)
    {
        $staffProfile = Auth::user()->staffProfile;

        if (! $staffProfile || ! $staffProfile->created_by_landlord_id) {
            abort(403);
        }

        $announcement = Announcement::where('user_id', $staffProfile->created_by_landlord_id)
            ->active()
            ->findOrFail($id);

        return view('staff.announcements.show', compact('announcement'));
    }

    // ==================== HELPERS ====================

    protected function notifyRecipients(Announcement $announcement, int $landlordId): void
    {
        $recipients = collect();

        if (in_array($announcement->audience, ['all_tenants', 'property_tenants', 'everyone'])) {
            $tenantQuery = TenantAssignment::where('landlord_id', $landlordId)
                ->where('status', 'active')
                ->with('tenant');

            if ($announcement->audience === 'property_tenants' && $announcement->property_id) {
                $tenantQuery->whereHas('unit', function ($query) use ($announcement) {
                    $query->where('property_id', $announcement->property_id);
                });
            }

            $tenants = $tenantQuery->get()->pluck('tenant')->filter();
            $recipients = $recipients->merge($tenants);
        }

        if (in_array($announcement->audience, ['all_staff', 'everyone'])) {
            $staff = User::where('role', 'staff')
                ->whereHas('staffProfile', function ($query) use ($landlordId) {
                    $query->where('created_by_landlord_id', $landlordId);
                })
                ->get();
            $recipients = $recipients->merge($staff);
        }

        foreach ($recipients->unique('id') as $recipient) {
            $recipient->notify(new NewAnnouncement($announcement));
        }
    }
}
