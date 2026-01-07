<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\DocumentChange;
use App\Services\RasaServerService;

class AdminController extends Controller
{
    /**
     * Admin dashboard displaying system-wide metrics, charts and recent tickets.
     */
    public function index(Request $request)
    {
        // Get admin dashboard data directly without caching
        $dashboardData = (function () {
            // KPI metrics
            $openTickets = Ticket::where('status', 'Open')->count();
            $forwardedTickets = Ticket::where('status', 'Forwarded')->count();
            // FAQ system removed - no longer counting FAQs
            $userCount = User::count();

            // Get last Rasa training timestamp
            $lastTraining = \App\Models\DocumentChange::getLastTrainingTimestamp();
            $lastTrainingFormatted = $lastTraining ? $lastTraining->format('M j, Y g:i A') : 'Never';

            // Active staff in the last 10 minutes (based on sessions table)
            // sessions.last_activity is an epoch seconds integer
            $cutoff = now()->subMinutes(10)->getTimestamp();
            $activeStaffCount = DB::table('sessions')
                ->join('users', 'sessions.user_id', '=', 'users.id')
                ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
                ->whereNotNull('sessions.user_id')
                ->where('sessions.last_activity', '>=', $cutoff)
                // exclude Primary Administrator by role name (supports migrated role_id and legacy null-role rows)
                ->where(function ($qb) {
                    $qb->whereNull('roles.name')->orWhere('roles.name', '!=', 'Primary Administrator');
                })
                ->distinct('sessions.user_id')
                ->count('sessions.user_id');

            // Build initial active staff list (name, email, last_activity)
            $activeStaff = DB::table('sessions')
                ->join('users', 'sessions.user_id', '=', 'users.id')
                ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
                ->whereNotNull('sessions.user_id')
                ->where('sessions.last_activity', '>=', $cutoff)
                ->where(function ($qb) {
                    $qb->whereNull('roles.name')->orWhere('roles.name', '!=', 'Primary Administrator');
                })
                ->groupBy('users.id', 'users.name', 'users.email')
                ->select([
                    'users.id',
                    'users.name',
                    'users.email',
                    DB::raw('MAX(sessions.last_activity) as last_activity_ts')
                ])
                ->orderByDesc('last_activity_ts')
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => (int) $row->id,
                        'name' => (string) ($row->name ?? ''),
                        'email' => (string) ($row->email ?? ''),
                        'last_activity_ts' => (int) ($row->last_activity_ts ?? 0),
                    ];
                })
                ->values()
                ->toArray();

            // Build full staff contacts with last activity and active flag
            $staffContacts = User::leftJoin('roles', 'users.role_id', '=', 'roles.id')
                ->where(function ($q) {
                    $q->whereNull('roles.name')->orWhere('roles.name', '!=', 'Primary Administrator');
                })
                ->leftJoin('sessions', 'sessions.user_id', '=', 'users.id')
                ->groupBy('users.id', 'users.name', 'users.email')
                ->select([
                    'users.id',
                    'users.name',
                    'users.email',
                    DB::raw('MAX(sessions.last_activity) as last_activity_ts')
                ])
                ->orderBy('users.name')
                ->get()
                ->map(function ($row) use ($cutoff) {
                    $ts = (int) ($row->last_activity_ts ?? 0);
                    return [
                        'id' => (int) $row->id,
                        'name' => (string) ($row->name ?? ''),
                        'email' => (string) ($row->email ?? ''),
                        'last_activity_ts' => $ts,
                        'is_active' => $ts >= $cutoff,
                    ];
                })
                ->values()
                ->toArray();

            // Weekly ticket analytics (current week Mon-Sun)
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();

            $byDay = Ticket::select(DB::raw('DATE(date_created) as d'), DB::raw('COUNT(*) as c'))
                ->whereBetween('date_created', [$startOfWeek, $endOfWeek])
                ->groupBy('d')
                ->pluck('c', 'd')
                ->toArray();

            $weekLabels = [];
            $weekData = [];
            $cursor = $startOfWeek->copy();
            for ($i = 0; $i < 7; $i++) {
                $weekLabels[] = $cursor->format('D'); // Mon, Tue, ...
                $dateKey = $cursor->toDateString();
                $weekData[] = (int)($byDay[$dateKey] ?? 0);
                $cursor->addDay();
            }

            // Tickets by Category (use category_id relation -> categories.name)
            $categoryRows = Ticket::leftJoin('categories', 'tickets.category_id', '=', 'categories.id')
                ->select(DB::raw("COALESCE(categories.name, 'Uncategorized') as category_name"), DB::raw('COUNT(*) as c'))
                ->groupBy('category_name')
                ->orderByDesc('c')
                ->get();

            $categoryLabels = $categoryRows->pluck('category_name')->toArray();
            $categoryData = $categoryRows->pluck('c')->map(fn ($v) => (int)$v)->toArray();

            // Top senders (frequent ticket creators) - top 10
            $topSenders = Ticket::select('email', DB::raw('COUNT(*) as c'))
                ->groupBy('email')
                ->orderByDesc('c')
                ->limit(10)
                ->get();

            // Show unassigned tickets (no staff assigned or assigned to Primary Administrator)
            $unassignedTickets = Ticket::with('staff')
                ->where(function ($query) {
                    $query->whereNull('staff_id')
                          ->orWhere('staff_id', 1);
                })
                ->where('status', 'Open')
                ->orderByDesc('updated_at')
                ->take(6)
                ->get();

            // Debug: Log the unassigned tickets to check for issues
            \Illuminate\Support\Facades\Log::info('Unassigned tickets count: ' . $unassignedTickets->count());
            foreach ($unassignedTickets as $ticket) {
                \Illuminate\Support\Facades\Log::info('Ticket ID: ' . $ticket->id . ', staff_id: ' . ($ticket->staff_id ?? 'null') . ', status: ' . $ticket->status . ', staff name: ' . (optional($ticket->staff)->name ?? 'none'));
            }

            return [
                'openTickets'       => $openTickets,
                'forwardedTickets' => $forwardedTickets,
                // FAQ system removed - no longer showing FAQ counts
                'userCount'         => $userCount,
                'activeStaffCount'  => $activeStaffCount,
                'lastTraining'      => $lastTrainingFormatted,
                'weekLabels'        => $weekLabels,
                'weekData'          => $weekData,
                'categoryLabels'    => $categoryLabels,
                'categoryData'      => $categoryData,
                'topSenders'        => $topSenders,
                'unassignedTickets'    => $unassignedTickets,
                'activeStaff'       => $activeStaff,
                'staffContacts'     => $staffContacts,
                'faqUpdaterSecret'  => env('RASA_SECRET'),
                'faqUpdaterUrl'     => env('RASA_SERVER_CHECKER'),
                'users'             => User::orderBy('name')->get(['id', 'name']),
            ];
        })();

        return response()->view('dashboards.admin.index', $dashboardData);
    }

    /**
     * Live data endpoint for admin dashboard auto-refresh.
     */
    public function data(Request $request)
    {
        // KPI metrics
        $openTickets = Ticket::where('status', 'Open')->count();
        $forwardedTickets = Ticket::where('status', 'Forwarded')->count();
        // FAQ system removed - no longer counting FAQs
        $userCount = User::count();

        // Get last Rasa training timestamp for live updates
        $lastTrainingLive = \App\Models\DocumentChange::getLastTrainingTimestamp();
        $lastTrainingLiveFormatted = $lastTrainingLive ? $lastTrainingLive->format('M j, Y g:i A') : 'Never';

        // Active staff in the last 10 minutes
        $cutoff = now()->subMinutes(10)->getTimestamp();
        $activeStaffCount = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->whereNotNull('sessions.user_id')
            ->where('sessions.last_activity', '>=', $cutoff)
            ->where(function ($qb) {
                $qb->whereNull('roles.name')->orWhere('roles.name', '!=', 'Primary Administrator');
            })
            ->distinct('sessions.user_id')
            ->count('sessions.user_id');

        $activeStaffArr = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->whereNotNull('sessions.user_id')
            ->where('sessions.last_activity', '>=', $cutoff)
            ->where(function ($qb) {
                $qb->whereNull('roles.name')->orWhere('roles.name', '!=', 'Primary Administrator');
            })
            ->groupBy('users.id', 'users.name', 'users.email')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                DB::raw('MAX(sessions.last_activity) as last_activity_ts')
            ])
            ->orderByDesc('last_activity_ts')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => (int) $row->id,
                    'name' => (string) ($row->name ?? ''),
                    'email' => (string) ($row->email ?? ''),
                    'last_activity_ts' => (int) ($row->last_activity_ts ?? 0),
                ];
            })
            ->values()
            ->toArray();

        // Full staff contacts list with active flag
        $staffContactsArr = User::leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->where(function ($q) {
                $q->whereNull('roles.name')->orWhere('roles.name', '!=', 'Primary Administrator');
            })
            ->leftJoin('sessions', 'sessions.user_id', '=', 'users.id')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                DB::raw('MAX(sessions.last_activity) as last_activity_ts')
            ])
            ->orderBy('users.name')
            ->get()
            ->map(function ($row) use ($cutoff) {
                $ts = (int) ($row->last_activity_ts ?? 0);
                return [
                    'id' => (int) $row->id,
                    'name' => (string) ($row->name ?? ''),
                    'email' => (string) ($row->email ?? ''),
                    'last_activity_ts' => $ts,
                    'is_active' => $ts >= $cutoff,
                ];
            })
            ->values()
            ->toArray();

        // Weekly ticket analytics (current week Mon-Sun)
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $byDay = Ticket::select(DB::raw('DATE(date_created) as d'), DB::raw('COUNT(*) as c'))
            ->whereBetween('date_created', [$startOfWeek, $endOfWeek])
            ->groupBy('d')
            ->pluck('c', 'd')
            ->toArray();

        $weekLabels = [];
        $weekData = [];
        $cursor = $startOfWeek->copy();
        for ($i = 0; $i < 7; $i++) {
            $weekLabels[] = $cursor->format('D'); // Mon, Tue, ...
            $dateKey = $cursor->toDateString();
            $weekData[] = (int)($byDay[$dateKey] ?? 0);
            $cursor->addDay();
        }

        // Tickets by Category (all) - use category_id relation
        $categoryRows = Ticket::leftJoin('categories', 'tickets.category_id', '=', 'categories.id')
            ->select(DB::raw("COALESCE(categories.name, 'Uncategorized') as category_name"), DB::raw('COUNT(*) as c'))
            ->groupBy('category_name')
            ->orderByDesc('c')
            ->get();

        $categoryLabels = $categoryRows->pluck('category_name')->values()->toArray();

        $categoryData = $categoryRows->pluck('c')->map(fn ($v) => (int)$v)->values()->toArray();

        // Top senders (frequent ticket creators) - top 10
        $topSenders = Ticket::select('email', DB::raw('COUNT(*) as c'))
            ->groupBy('email')
            ->orderByDesc('c')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                return [
                    'email' => $row->email,
                    'count' => (int) $row->c,
                ];
            })
            ->values()
            ->toArray();

        $unassignedTicketsArr = Ticket::with('staff')
            ->where(function ($query) {
                $query->whereNull('staff_id')
                      ->orWhere('staff_id', 1);
            })
            ->where('status', 'Open')
            ->orderByDesc('updated_at')
            ->take(6)
            ->get()
            ->map(function ($t) {
            return [
                'id'           => (int) $t->id,
                'status'       => (string) $t->status,
                'email'        => (string) ($t->email ?? ''),
                'category'     => (string) (is_object($t->category) ? ($t->category->name ?? '') : ($t->getAttribute('category') ?? '')) ,
                'date_created' => optional($t->date_created ?? $t->created_at)->format('Y-m-d h:i a'),
                'created_at'   => optional($t->created_at)->format('Y-m-d h:i a'),
                'updated_at'   => optional($t->updated_at)->format('Y-m-d h:i a'),
                'staff'        => $t->staff ? ['name' => (string) $t->staff->name] : null,
            ];
        })
        ->values()
        ->toArray();

        // Debug: Log the unassigned tickets for AJAX refresh
        \Illuminate\Support\Facades\Log::info('Unassigned tickets AJAX count: ' . count($unassignedTicketsArr));
        foreach ($unassignedTicketsArr as $ticket) {
            \Illuminate\Support\Facades\Log::info('AJAX Ticket ID: ' . $ticket['id'] . ', status: ' . $ticket['status'] . ', staff: ' . ($ticket['staff'] ? $ticket['staff']['name'] : 'none'));
        }

        return response()->json([
            'openTickets'       => (int) $openTickets,
            'forwardedTickets' => (int) $forwardedTickets,
            // FAQ system removed - no longer showing FAQ counts
            'userCount'         => (int) $userCount,
            'activeStaffCount'  => (int) $activeStaffCount,
            'lastTraining'      => $lastTrainingLiveFormatted,
            'weekLabels'        => $weekLabels,
            'weekData'          => $weekData,
            'categoryLabels'    => $categoryLabels,
            'categoryData'      => $categoryData,
            'topSenders'        => $topSenders,
            'unassignedTickets'    => $unassignedTicketsArr,
            'activeStaff'       => $activeStaffArr,
            'staffContacts'     => $staffContactsArr,
        ]);
    }

    /**
     * Ensure only Primary Administrator can access user management.
     *
     * Use the backwards-compatible string check to avoid analyzer/runtime issues
     * during migration (the User model exposes a getRoleAttribute accessor).
     */
    private function ensureAdmin(): void
    {
        $u = Auth::user();
        abort_unless($u && (strtolower((string) ($u->role ?? '')) === 'primary administrator'), 403, 'Unauthorized');
    }

    /**
     * List staff users (excluding Primary Administrator) with simple search + pagination.
     *
     * If called with ?include_deleted=1, shows deleted users.
     */
    public function usersIndex(Request $request)
    {
        $this->ensureAdmin();

        $q = trim((string) $request->query('q', ''));
        $isDeleted = (bool) $request->query('include_deleted', false);

        $usersQuery = User::whereHas('role', function ($qRole) {
                $qRole->where('name', '!=', 'Primary Administrator');
            })
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(function ($qq) use ($like) {
                    $qq->where('name', 'like', $like)
                       ->orWhere('email', 'like', $like)
                       ->orWhereHas('role', function ($qr) use ($like) {
                           $qr->where('name', 'like', $like);
                       })
                        ->orWhereHas('category', function ($qc) use ($like) {
                            $qc->where('name', 'like', $like);
                        });
                });
            });

        if ($isDeleted) {
            $users = $usersQuery->onlyTrashed()->orderBy('name')->paginate(10)->appends(['q' => $q, 'include_deleted' => '1']);
        } else {
            $users = $usersQuery->orderBy('name')->paginate(10)->appends(['q' => $q]);
        }

        return view('dashboards.admin.users.index', [
            'users' => $users,
            'q' => $q,
            'isDeletedView' => $isDeleted,
        ]);
    }

    /**
     * Show create staff form.
     */
    public function usersCreate()
    {
        $this->ensureAdmin();
        return view('dashboards.admin.users.create');
    }

    /**
     * Store new staff user.
     */
    public function usersStore(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            // Restrict creation to staff accounts (avoid creating another Primary Administrator)
            'role' => ['required','string','max:255','not_in:Primary Administrator','exists:roles,name'],
            'category_id' => 'nullable|integer|exists:categories,id',
            'password' => 'required|string|min:8|confirmed',
        ]);
    
        // Resolve role id from provided role name
        $roleModel = Role::where('name', $validated['role'])->first();

        // Validate category_id belongs to the role if provided
        if (!empty($validated['category_id'])) {
            if (!$roleModel || !$roleModel->categories()->where('id', $validated['category_id'])->exists()) {
                return back()->withErrors(['category_id' => 'The selected category is not valid for the chosen role.'])->withInput();
            }
        }

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role_id = $roleModel ? $roleModel->id : null;
        // Use category_id as single source of truth
        $user->category_id = $validated['category_id'] ?? null;
        // Will be auto-hashed via casts() => 'password' => 'hashed'
        $user->password = $validated['password'];
        $user->save();
    
        return redirect()->route('admin.users.index')->with('status', 'Staff created.');
    }

    /**
     * Show edit form for staff user (cannot edit Primary Administrator here).
     */
    public function usersEdit(User $user)
    {
        $this->ensureAdmin();
        if ($user->role === 'Primary Administrator') {
            abort(403, 'Cannot edit Primary Administrator.');
        }
        return view('dashboards.admin.users.edit', ['user' => $user]);
    }

    /**
     * Update staff user (email unique, optional password).
     */
    public function usersUpdate(Request $request, User $user)
    {
        $this->ensureAdmin();
        if ($user->role === 'Primary Administrator') {
            abort(403, 'Cannot modify Primary Administrator.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required','string','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'role' => ['required','string','max:255','not_in:Primary Administrator','exists:roles,name'],
            'category_id' => 'nullable|integer|exists:categories,id',
            'password' => 'nullable|string|min:8|confirmed',
        ]);
    
        // Resolve role id
        $roleModel = Role::where('name', $validated['role'])->first();

        // Validate category_id belongs to the role if provided
        if (!empty($validated['category_id'])) {
            if (!$roleModel || !$roleModel->categories()->where('id', $validated['category_id'])->exists()) {
                return back()->withErrors(['category_id' => 'The selected category is not valid for the chosen role.'])->withInput();
            }
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role_id = $roleModel ? $roleModel->id : null;
        // Use category_id as single source of truth
        $user->category_id = $validated['category_id'] ?? null;
        if (!empty($validated['password'] ?? '')) {
            // Auto-hashed via casts
            $user->password = $validated['password'];
        }
        $user->save();
    
        return redirect()->route('admin.users.index')->with('status', 'Staff updated.');
    }

    /**
     * Delete staff user (cannot delete self or Primary Administrator).
     */
    public function usersDestroy(User $user)
    {
        $this->ensureAdmin();
        $auth = Auth::user();
    
        if ($user->id === ($auth->id ?? 0)) {
            return back()->withErrors(['delete' => 'You cannot delete your own account.']);
        }
        // Use backward-compatible role accessor (string) to determine Primary Administrator
        if (strtolower((string) ($user->role ?? '')) === 'primary administrator') {
            return back()->withErrors(['delete' => 'Cannot delete Primary Administrator.']);
        }
    
        $user->delete();
    
        return redirect()->route('admin.users.index')->with('status', 'Staff deleted.');
    }

    /**
     * Deleted users page (redirect)
     *
     * Redirects to the main users index with a flag so the index will load
     * the deleted-list view.
     */
    public function usersDeletedIndex(Request $request)
    {
        $this->ensureAdmin();
        return redirect()->route('admin.users.index', ['include_deleted' => '1']);
    }

    /**
     * Restore a soft-deleted user.
     */
    public function usersRestore($userId)
    {
        $this->ensureAdmin();

        $user = User::withTrashed()->findOrFail($userId);

        if (!$user->trashed()) {
            return back()->withErrors(['restore' => 'User is not deleted']);
        }

        $user->restore();

        return redirect()->route('admin.users.index')->with('status', 'User restored.');
    }

    /**
     * Knowledgebase Management - page (blade)
     *
     * If called with ?include_deleted=1, the page will load its list from the
     * deleted-list endpoint so this same blade can be reused for the Trash view.
     */
    public function knowledgebaseIndex(Request $request)
    {
        $this->ensureAdmin();
        $isDeleted = (bool) $request->query('include_deleted', false);
        $listUrl = $isDeleted ? route('admin.knowledgebase.deleted.list') : route('admin.knowledgebase.list');
        return view('dashboards.admin.knowledgebase.index', [
            'listUrl' => $listUrl,
            'isDeletedView' => $isDeleted,
        ]);
    }
    /**
     * Store new Knowledgebase item (AJAX)
     */
    public function knowledgebaseStore(Request $request)
    {
        $this->ensureAdmin();
        $validated = $request->validate([
            'intent' => 'required|string|max:255',
            'description' => 'required|string',
            'response' => 'required|string',
        ]);

        // Log document change for training alert
        try {
            \App\Models\DocumentChange::create([
                'file_name' => 'faqs.json',
                'action' => 'updated',
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? null,
                'training_required' => true,
                'training_completed' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log FAQ create change: ' . $e->getMessage());
        }


        return response()->json(['ok' => true], 201);
    }

    /**
     * Update Knowledgebase item (AJAX)
     */
    public function knowledgebaseUpdate(Request $request)
    {
        $this->ensureAdmin();
        $validated = $request->validate([
            'intent' => 'required|string|max:255',
            'description' => 'required|string',
            'response' => 'required|string',
        ]);

        // Log document change for training alert
        try {
            DocumentChange::create([
                'file_name' => 'faqs.json',
                'action' => 'updated',
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? null,
                'training_required' => true,
                'training_completed' => false,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to log FAQ update change: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    /**
     * Delete Knowledgebase item (AJAX)
     *
     * Accepts soft-deleted items as well (permanent delete from Trash).
     * When called on a non-deleted item it will perform the usual soft-delete.
     * When called on an already trashed item it will permanently remove it.
     */
    public function knowledgebaseDestroy($faqId)
    {
        $this->ensureAdmin();
        try {
            \App\Models\DocumentChange::create([
                'file_name' => 'faqs.json',
                'action' => 'updated',
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name ?? null,
                'training_required' => true,
                'training_completed' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log FAQ delete change: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }
    /**
     * Disable a FAQ response so external systems (Rasa) will treat it as unavailable.
     * This sets the `response_disabled` flag and records a revision for audit.
     * Store new announcement (AJAX) - uploads to Rasa server
     */
    public function announcementsStore(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
        ]);

        // Check if server is online
        if (RasaServerService::isServerOnline()) {
            // Direct upload (existing logic)
            try {
                // Upload to Rasa server
                $rasaUrl = config('services.faq_list_docs.url');
                if (!$rasaUrl) {
                    throw new \Exception('Rasa server URL not configured');
                }

                // Replace /list-docs with /update-document
                $uploadUrl = str_replace('/list-docs', '/update-document', $rasaUrl);
                $secret = config('services.faq_list_docs.secret');

                // First, fetch current announcements to get next ID
                $listUrl = str_replace('/list-docs', '/download-announcements', $rasaUrl);
                $listResponse = Http::withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With' => 'XMLHttpRequest'
                ])->get($listUrl);

                $nextId = 1;
                if ($listResponse->successful()) {
                    $listData = $listResponse->json();
                    if ($listData['ok'] && !empty($listData['announcements'])) {
                        $maxId = max(array_column($listData['announcements'], 'id'));
                        $nextId = $maxId + 1;
                    }
                }

                // Format the announcement
                $announcementText = "id: {$nextId}\ntitle: {$validated['title']}\n{$validated['content']}\n---------\n";

                // If there are existing announcements, append to the content
                if ($listResponse->successful() && !empty($listData['announcements'])) {
                    // We need to get the full file content and append
                    // Since update-document replaces the file, we need to get current content first
                    $downloadUrl = str_replace('/list-docs', '/download/Announcements.txt', $rasaUrl);
                    $downloadResponse = Http::withHeaders([
                        'X-FAQ-UPDATER-TOKEN' => $secret,
                        'X-Requested-With' => 'XMLHttpRequest'
                    ])->get($downloadUrl);

                    if ($downloadResponse->successful()) {
                        $currentContent = $downloadResponse->body();
                        $announcementText = $currentContent . $announcementText;
                    }
                }

                // Upload the updated content
                $response = Http::withHeaders([
                    'X-FAQ-UPDATER-TOKEN' => $secret,
                    'X-Requested-With' => 'XMLHttpRequest'
                ])->post($uploadUrl, [
                    'file_name' => 'Announcements.txt',
                    'file_content' => $announcementText,
                    'file_type' => 'txt'
                ]);

                if (!$response->successful()) {
                    throw new \Exception('Failed to upload announcement to Rasa server: ' . $response->status());
                }

                $uploadData = $response->json();
                if (!$uploadData['ok']) {
                    throw new \Exception($uploadData['error'] ?? 'Failed to upload announcement');
                }

                // Log the document change for tracking
                try {
                    \App\Models\DocumentChange::create([
                        'file_name' => 'Announcements.txt',
                        'action' => 'created',
                        'user_id' => Auth::id(),
                        'user_name' => Auth::user()->name ?? null,
                        'training_required' => true,
                        'training_completed' => false,
                    ]);
                } catch (\Exception $e) {
                    // Log error but don't fail the announcement creation
                    \Illuminate\Support\Facades\Log::error('Failed to log announcement change: ' . $e->getMessage());
                }


                return response()->json([
                    'success' => true,
                    'message' => 'Announcement added successfully',
                    'announcement' => [
                        'id' => $nextId,
                        'title' => $validated['title'],
                        'content' => $validated['content']
                    ]
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to store announcement: ' . $e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add announcement: ' . $e->getMessage()
                ], 500);
            }
        } else {
            // Server offline: return an error (queued uploads removed)
            return response()->json([
                'success' => false,
                'message' => 'Rasa server is currently offline. Queued uploads have been removed from the codebase.',
                'server_offline' => true
            ], 503);
        }
    }

    /**
     * List announcements (AJAX) - fetches from Rasa server
     */
    public function announcementsList(Request $request)
    {
        $this->ensureAdmin();

        try {
            // Fetch announcements from Rasa server
            $rasaUrl = config('services.faq_list_docs.url');
            if (!$rasaUrl) {
                throw new \Exception('Rasa server URL not configured');
            }

            // Replace /list-docs with /download-announcements
            $announcementsUrl = str_replace('/list-docs', '/download-announcements', $rasaUrl);
            $secret = config('services.faq_list_docs.secret');

            $response = Http::withHeaders([
                'X-FAQ-UPDATER-TOKEN' => $secret,
                'X-Requested-With' => 'XMLHttpRequest'
            ])->get($announcementsUrl);

            if (!$response->successful()) {
                throw new \Exception('Failed to fetch announcements from Rasa server: ' . $response->status());
            }

            $data = $response->json();

            if (!$data['ok']) {
                throw new \Exception($data['error'] ?? 'Failed to fetch announcements');
            }

            $announcements = $data['announcements'] ?? [];

            // Get pinned announcement IDs
            $pinnedIds = DB::table('pinned_announcements')->pluck('announcement_id')->toArray();

            // Add pinned flag
            foreach ($announcements as &$ann) {
                $ann['pinned'] = in_array($ann['id'], $pinnedIds);
            }

            // Sort: pinned first, then by ID descending
            usort($announcements, function ($a, $b) {
                if ($a['pinned'] && !$b['pinned']) return -1;
                if (!$a['pinned'] && $b['pinned']) return 1;
                return $b['id'] <=> $a['id'];
            });

            return response()->json([
                'success' => true,
                'announcements' => $announcements
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch announcements from Rasa server: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Rasa server is offline.'
            ]);
        }
    }

    /**
     * Update announcement (AJAX)
     */
    public function announcementsUpdate(Request $request, $id)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
        ]);

        try {
            // Fetch current announcements
            $rasaUrl = config('services.faq_list_docs.url');
            if (!$rasaUrl) {
                throw new \Exception('Rasa server URL not configured');
            }

            $announcementsUrl = str_replace('/list-docs', '/download-announcements', $rasaUrl);
            $secret = config('services.faq_list_docs.secret');

            $listResponse = Http::withHeaders([
                'X-FAQ-UPDATER-TOKEN' => $secret,
                'X-Requested-With' => 'XMLHttpRequest'
            ])->get($announcementsUrl);

            if (!$listResponse->successful()) {
                throw new \Exception('Failed to fetch announcements from Rasa server');
            }

            $data = $listResponse->json();

            if (!$data['ok']) {
                throw new \Exception($data['error'] ?? 'Failed to fetch announcements');
            }

            $announcements = $data['announcements'];

            // Find and update
            $found = false;
            foreach ($announcements as &$ann) {
                if ($ann['id'] == $id) {
                    $ann['title'] = $validated['title'];
                    $ann['content'] = $validated['content'];
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                throw new \Exception('Announcement not found');
            }

            // Reconstruct content
            $content = '';
            foreach ($announcements as $ann) {
                $content .= "id: {$ann['id']}\ntitle: {$ann['title']}\n{$ann['content']}\n---------\n";
            }

            // Upload updated content
            $uploadUrl = str_replace('/list-docs', '/update-document', $rasaUrl);
            $response = Http::withHeaders([
                'X-FAQ-UPDATER-TOKEN' => $secret,
                'X-Requested-With' => 'XMLHttpRequest'
            ])->post($uploadUrl, [
                'file_name' => 'Announcements.txt',
                'file_content' => $content,
                'file_type' => 'txt'
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to upload updated announcements');
            }

            $uploadData = $response->json();
            if (!$uploadData['ok']) {
                throw new \Exception($uploadData['error'] ?? 'Failed to update announcement');
            }

            // Log the document change for training alert
            try {
                \App\Models\DocumentChange::create([
                    'file_name' => 'Announcements.txt',
                    'action' => 'updated',
                    'user_id' => Auth::id(),
                    'user_name' => Auth::user()->name ?? null,
                    'training_required' => true,
                    'training_completed' => false,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to log announcement update change: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Announcement updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update announcement: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update announcement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete announcement (AJAX)
     */
    public function announcementsDestroy($id)
    {
        $this->ensureAdmin();

        try {
            // Fetch current announcements
            $rasaUrl = config('services.faq_list_docs.url');
            if (!$rasaUrl) {
                throw new \Exception('Rasa server URL not configured');
            }

            $announcementsUrl = str_replace('/list-docs', '/download-announcements', $rasaUrl);
            $secret = config('services.faq_list_docs.secret');

            $listResponse = Http::withHeaders([
                'X-FAQ-UPDATER-TOKEN' => $secret,
                'X-Requested-With' => 'XMLHttpRequest'
            ])->get($announcementsUrl);

            if (!$listResponse->successful()) {
                throw new \Exception('Failed to fetch announcements from Rasa server');
            }

            $data = $listResponse->json();

            if (!$data['ok']) {
                throw new \Exception($data['error'] ?? 'Failed to fetch announcements');
            }

            $announcements = $data['announcements'];

            // Find and remove
            $announcements = array_filter($announcements, function ($ann) use ($id) {
                return $ann['id'] != $id;
            });

            // Reconstruct content
            $content = '';
            foreach ($announcements as $ann) {
                $content .= "id: {$ann['id']}\ntitle: {$ann['title']}\n{$ann['content']}\n---------\n";
            }

            // Upload updated content
            $uploadUrl = str_replace('/list-docs', '/update-document', $rasaUrl);
            $response = Http::withHeaders([
                'X-FAQ-UPDATER-TOKEN' => $secret,
                'X-Requested-With' => 'XMLHttpRequest'
            ])->post($uploadUrl, [
                'file_name' => 'Announcements.txt',
                'file_content' => $content,
                'file_type' => 'txt'
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to upload updated announcements');
            }

            $uploadData = $response->json();
            if (!$uploadData['ok']) {
                throw new \Exception($uploadData['error'] ?? 'Failed to delete announcement');
            }

            // Log the document change for training alert
            try {
                \App\Models\DocumentChange::create([
                    'file_name' => 'Announcements.txt',
                    'action' => 'deleted',
                    'user_id' => Auth::id(),
                    'user_name' => Auth::user()->name ?? null,
                    'training_required' => true,
                    'training_completed' => false,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to log announcement delete change: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Announcement deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete announcement: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete announcement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pin/unpin announcement (AJAX)
     */
    public function announcementsPin($id)
    {
        $this->ensureAdmin();

        try {
            // Check if already pinned
            $existing = DB::table('pinned_announcements')->where('announcement_id', $id)->first();

            if ($existing) {
                // Unpin
                DB::table('pinned_announcements')->where('announcement_id', $id)->delete();
                $message = 'Announcement unpinned successfully';
            } else {
                // Pin
                DB::table('pinned_announcements')->insert([
                    'announcement_id' => $id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $message = 'Announcement pinned successfully';
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to pin/unpin announcement: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to pin/unpin announcement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories for a specific role (AJAX endpoint for conditional dropdowns)
     */
    public function categoriesByRole(Request $request)
    {
        $this->ensureAdmin();

        $roleName = $request->query('role_name');
        if (!$roleName) {
            return response()->json([]);
        }

        $role = \App\Models\Role::where('name', $roleName)->first();
        if (!$role) {
            return response()->json([]);
        }

        $categories = $role->categories()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function ($c) {
                return ['id' => $c->id, 'name' => $c->name];
            })->values()->toArray();

        return response()->json($categories);
    }

    /**
     * Logs page - displays document change history
     */
    public function logsIndex(Request $request)
    {
        $this->ensureAdmin();

        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 25);
        if (!in_array($perPage, [25, 50, 100])) {
            $perPage = 25;
        }

        $logsQuery = \App\Models\DocumentChange::with('user')
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(function ($qq) use ($like) {
                    $qq->where('file_name', 'like', $like)
                       ->orWhere('action', 'like', $like)
                       ->orWhereHas('user', function ($qu) use ($like) {
                           $qu->where('name', 'like', $like);
                       });
                });
            })
            ->orderBy('change_timestamp', 'desc');

        $logs = $logsQuery->paginate($perPage)->appends(['q' => $q, 'per_page' => $perPage]);

        return view('dashboards.admin.logs.index', [
            'logs' => $logs,
            'q' => $q,
            'per_page' => $perPage,
        ]);
    }
}
