<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Role;
use App\Models\Faq;
use App\Models\FaqRevision;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Admin dashboard displaying system-wide metrics, charts and recent tickets.
     */
    public function index(Request $request)
    {
        // KPI metrics
        $openTickets = Ticket::where('status', 'Open')->count();
        $inProgressTickets = Ticket::where('status', 'Re-routed')->count();
        $faqCount = Faq::count();
        // number of untrained FAQs (displayed under Total FAQs on dashboard)
        $faqPendingCount = Faq::where('status', 'untrained')->count();
        $userCount = User::count();

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

        // Tickets by Category
        $categoryRows = Ticket::select('category', DB::raw('COUNT(*) as c'))
            ->groupBy('category')
            ->orderByDesc('c')
            ->get();

        $categoryLabels = $categoryRows->pluck('category')->map(function ($v) {
            return $v ?: 'Uncategorized';
        })->toArray();
        $categoryData = $categoryRows->pluck('c')->map(fn ($v) => (int)$v)->toArray();

        // Top senders (frequent ticket creators) - top 10
        $topSenders = Ticket::select('email', DB::raw('COUNT(*) as c'))
            ->groupBy('email')
            ->orderByDesc('c')
            ->limit(10)
            ->get();

        // Recent lists
        $openList = Ticket::with('staff')
            ->where('status', 'Open')
            ->orderByDesc('date_created')
            ->take(6)
            ->get();

        $inProgressList = Ticket::with('staff')
            ->where('status', 'Re-routed')
            ->orderByDesc('updated_at')
            ->take(6)
            ->get();

        return view('dashboards.admin.index', [
            'openTickets'       => $openTickets,
            'inProgressTickets' => $inProgressTickets,
            'faqCount'          => $faqCount,
            // pending FAQs count used by dashboard UI
            'faqPendingCount'   => $faqPendingCount,
            'userCount'         => $userCount,
            'activeStaffCount'  => $activeStaffCount,
            'weekLabels'        => $weekLabels,
            'weekData'          => $weekData,
            'categoryLabels'    => $categoryLabels,
            'categoryData'      => $categoryData,
            'topSenders'        => $topSenders,
            'openList'          => $openList,
            'inProgressList'    => $inProgressList,
            'activeStaff'       => $activeStaff,
            'staffContacts'     => $staffContacts,
        ]);
    }

    /**
     * Live data endpoint for admin dashboard auto-refresh.
     */
    public function data(Request $request)
    {
        // KPI metrics
        $openTickets = Ticket::where('status', 'Open')->count();
        $inProgressTickets = Ticket::where('status', 'Re-routed')->count();
        $faqCount = Faq::count();
        // include untrained FAQ count for live admin data
        $faqPendingCount = Faq::where('status', 'untrained')->count();
        $userCount = User::count();

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

        // Tickets by Category (all)
        $categoryRows = Ticket::select('category', DB::raw('COUNT(*) as c'))
            ->groupBy('category')
            ->orderByDesc('c')
            ->get();

        $categoryLabels = $categoryRows->pluck('category')->map(function ($v) {
            return $v ?: 'Uncategorized';
        })->values()->toArray();

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

        // Recent lists for live admin table refresh (limit 6 like initial view)
        $openListArr = Ticket::with('staff')
            ->where('status', 'Open')
            ->orderByDesc('date_created')
            ->take(6)
            ->get()
            ->map(function ($t) {
            return [
                'id'           => (int) $t->id,
                'status'       => (string) $t->status,
                'email'        => (string) ($t->email ?? ''),
                'category'     => (string) ($t->category ?? ''),
                'date_created' => optional($t->date_created ?? $t->created_at)->format('Y-m-d h:i a'),
                'created_at'   => optional($t->created_at)->format('Y-m-d h:i a'),
                'updated_at'   => optional($t->updated_at)->format('Y-m-d h:i a'),
                'staff'        => $t->staff ? ['name' => (string) $t->staff->name] : null,
            ];
        })
        ->values()
        ->toArray();

        $inProgressListArr = Ticket::with('staff')
            ->where('status', 'Re-routed')
            ->orderByDesc('updated_at')
            ->take(6)
            ->get()
            ->map(function ($t) {
            return [
                'id'           => (int) $t->id,
                'status'       => (string) $t->status,
                'email'        => (string) ($t->email ?? ''),
                'category'     => (string) ($t->category ?? ''),
                'date_created' => optional($t->date_created ?? $t->created_at)->format('Y-m-d h:i a'),
                'created_at'   => optional($t->created_at)->format('Y-m-d h:i a'),
                'updated_at'   => optional($t->updated_at)->format('Y-m-d h:i a'),
                'staff'        => $t->staff ? ['name' => (string) $t->staff->name] : null,
            ];
        })
        ->values()
        ->toArray();

        return response()->json([
            'openTickets'       => (int) $openTickets,
            'inProgressTickets' => (int) $inProgressTickets,
            'faqCount'          => (int) $faqCount,
            'faqPendingCount'   => (int) $faqPendingCount,
            'userCount'         => (int) $userCount,
            'activeStaffCount'  => (int) $activeStaffCount,
            'weekLabels'        => $weekLabels,
            'weekData'          => $weekData,
            'categoryLabels'    => $categoryLabels,
            'categoryData'      => $categoryData,
            'topSenders'        => $topSenders,
            'openList'          => $openListArr,
            'inProgressList'    => $inProgressListArr,
            'activeStaff'       => $activeStaffArr,
            'staffContacts'     => $staffContactsArr,
        ])
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
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
     */
    public function usersIndex(Request $request)
    {
        $this->ensureAdmin();

        $q = trim((string) $request->query('q', ''));

        $users = User::whereHas('role', function ($qRole) {
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
                       ->orWhere('category', 'like', $like);
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->appends(['q' => $q]);

        return view('dashboards.admin.users.index', [
            'users' => $users,
            'q' => $q,
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
            'category' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);
    
        // Resolve role id from provided role name
        $roleModel = Role::where('name', $validated['role'])->first();
    
        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role_id = $roleModel ? $roleModel->id : null;
        $user->category = $validated['category'] ?? null;
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
            'category' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
        ]);
    
        // Resolve role id
        $roleModel = Role::where('name', $validated['role'])->first();
    
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role_id = $roleModel ? $roleModel->id : null;
        $user->category = $validated['category'] ?? null;
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
     * FAQ Management - page (blade)
     *
     * If called with ?include_deleted=1, the page will load its list from the
     * deleted-list endpoint so this same blade can be reused for the Trash view.
     */
    public function faqsIndex(Request $request)
    {
        $this->ensureAdmin();
        $isDeleted = (bool) $request->query('include_deleted', false);
        $listUrl = $isDeleted ? route('admin.faqs.deleted.list') : route('admin.faqs.list');
        return view('dashboards.admin.faqs.index', [
            'listUrl' => $listUrl,
            'isDeletedView' => $isDeleted,
        ]);
    }

    /**
     * FAQ list (AJAX) - supports search and per_page options
     */
    public function faqsList(Request $request)
    {
        $this->ensureAdmin();

        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 25);
        if (!in_array($perPage, [25,50,100])) { $perPage = 25; }

        // Accept 'untrained', 'trained' or 'all' for preview statuses.
        // When 'all' is requested, do not apply a status filter.
        $status = trim((string) $request->query('status', 'untrained'));
        $allowedStatuses = ['untrained', 'trained', 'all'];
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'untrained';
        }
 
        // Build base query (search + optional status filter)
        $faqsQuery = Faq::when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(function ($qq) use ($like) {
                    $qq->where('intent', 'like', $like)
                       ->orWhere('response', 'like', $like);
                });
            })
            // apply status filter only when a specific status is requested (not 'all')
            ->when($status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            });

        // When showing "all" prioritize trained FAQs first, then sort by intent.
        // For specific-status views keep the normal alphabetical intent sort.
        if ($status === 'all') {
            $faqs = $faqsQuery
                ->orderByRaw("CASE WHEN status = 'trained' THEN 0 ELSE 1 END")
                ->orderBy('intent')
                ->paginate($perPage)
                ->appends(['q' => $q, 'per_page' => $perPage, 'status' => $status]);
        } else {
            $faqs = $faqsQuery
                ->orderBy('intent')
                ->paginate($perPage)
                ->appends(['q' => $q, 'per_page' => $perPage, 'status' => $status]);
        }

        // Format items so created_at / updated_at use "yyyy-mm-dd hh:mm am/pm"
        $items = array_map(function ($f) {
            return [
                'id' => $f->id,
                'intent' => $f->intent,
                'description' => $f->description ?? '',
                'response' => $f->response,
                'status' => $f->status,
                'response_disabled' => (bool) ($f->response_disabled ?? false),
                'created_at' => optional($f->created_at)->format('Y-m-d h:i a'),
                'updated_at' => optional($f->updated_at)->format('Y-m-d h:i a'),
            ];
        }, $faqs->items());

        return response()->json([
            'items' => $items,
            'meta' => [
                'total' => $faqs->total(),
                'per_page' => $faqs->perPage(),
                'current_page' => $faqs->currentPage(),
                'last_page' => $faqs->lastPage(),
            ],
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
 
    /**
     * Deleted FAQs page (redirect)
     *
     * Redirects to the main FAQ index with a flag so the index will load
     * the deleted-list AJAX endpoint instead of the normal list.
     */
    public function faqsDeletedIndex(Request $request)
    {
        $this->ensureAdmin();
        return redirect()->route('admin.faqs.index', ['include_deleted' => '1']);
    }
 
    /**
     * Deleted FAQs list (AJAX) - shows only soft-deleted FAQs
     */
    public function faqsDeletedList(Request $request)
    {
        $this->ensureAdmin();
 
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 25);
        if (!in_array($perPage, [25,50,100])) { $perPage = 25; }
 
        $faqs = Faq::onlyTrashed()
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(function ($qq) use ($like) {
                    $qq->where('intent', 'like', $like)
                       ->orWhere('response', 'like', $like);
                });
            })
            ->orderBy('intent')
            ->paginate($perPage)
            ->appends(['q' => $q, 'per_page' => $perPage]);
 
        $items = array_map(function ($f) {
            return [
                'id' => $f->id,
                'intent' => $f->intent,
                'description' => $f->description ?? '',
                'response' => $f->response,
                'status' => $f->status,
                'response_disabled' => (bool) ($f->response_disabled ?? false),
                'created_at' => optional($f->created_at)->format('Y-m-d h:i a'),
                'updated_at' => optional($f->updated_at)->format('Y-m-d h:i a'),
                'deleted_at' => optional($f->deleted_at)->format('Y-m-d h:i a'),
            ];
        }, $faqs->items());
 
        return response()->json([
            'items' => $items,
            'meta' => [
                'total' => $faqs->total(),
                'per_page' => $faqs->perPage(),
                'current_page' => $faqs->currentPage(),
                'last_page' => $faqs->lastPage(),
            ],
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
 
    /**
     * Return single FAQ (AJAX)
     *
     * Includes helper flags/urls used by the frontend to show contextual
     * "more actions" (restore / undo / view revisions) in the modal.
     */
    public function faqsShow(Faq $faq)
    {
        $this->ensureAdmin();

        $canRestore = method_exists($faq, 'trashed') ? $faq->trashed() : false;
        $canRevert = $faq->revisions()->exists();
        // We can offer an "undo" when there are revisions (the latest revision contains a snapshot)
        $canUndo = $faq->revisions()->exists();

        // Attach latest revision snapshot (if any) so the frontend can show a
        // collapsible "previous response" block and allow restoring to it.
        $latest = FaqRevision::where('faq_id', $faq->id)->orderByDesc('created_at')->first();
        $latestRevision = null;
        if ($latest) {
            $latestRevision = [
                'id' => $latest->id,
                'intent' => $latest->intent ?? $latest->topic,
                'response' => $latest->response,
                'action' => $latest->action,
                'created_at' => optional($latest->created_at)->format('Y-m-d h:i a'),
            ];
        }

        return response()->json([
            'id' => $faq->id,
            'intent' => $faq->intent,
            'description' => $faq->description ?? '',
            'response' => $faq->response,
            'created_at' => optional($faq->created_at)->format('Y-m-d h:i a'),
            'updated_at' => optional($faq->updated_at)->format('Y-m-d h:i a'),
            'status' => $faq->status,
            'response_disabled' => (bool) ($faq->response_disabled ?? false),
            'can_restore' => $canRestore,
            'can_revert' => $canRevert,
            'can_undo' => $canUndo,
            'can_untrain' => ($faq->status === 'trained'),
            'revisions_url' => route('admin.faqs.revisions', ['faq' => $faq->id]),
            'restore_url' => route('admin.faqs.restore', ['faq' => $faq->id]),
            'undo_url' => route('admin.faqs.undo', ['faq' => $faq->id]),
            'untrain_url' => route('admin.faqs.untrain', ['faq' => $faq->id]),
            'latest_revision' => $latestRevision,
        ]);
    }

    /**
     * Store new FAQ (AJAX)
     *
     * REQUIREMENT:
     * Ensure the external updater API responds SUCCESS before persisting the FAQ locally.
     * This prevents creating DB rows when the Rasa updater cannot register the new action/flow.
     */
    public function faqsStore(Request $request)
    {
        $this->ensureAdmin();
        $validated = $request->validate([
            'intent' => 'required|string|max:255',
            'description' => 'required|string',
            'response' => 'required|string',
        ]);
 
        // Ensure updater endpoint is configured
        $updaterUrl = env('FAQ_UPDATER_URL', null);
        if (empty($updaterUrl)) {
            return response()->json(['ok' => false, 'message' => 'FAQ updater not configured. Set FAQ_UPDATER_URL in .env'], 500);
        }
 
        // Prepare payload for updater (use provided values)
        $payload = [
            'intent' => $validated['intent'],
            'description' => $validated['description'],
            // let updater decide whether to restart actions
            'restart_actions' => true,
        ];
        $headers = [];
        $secret = env('FAQ_UPDATER_SECRET', null);
        if (!empty($secret)) {
            $headers['X-FAQ-UPDATER-TOKEN'] = $secret;
        }
 
        // Call updater and require success before saving
        try {
            $res = Http::withHeaders($headers)->timeout(8)->post($updaterUrl, $payload);
        } catch (\Throwable $e) {
            Log::error("FAQ updater request failed for intent={$validated['intent']}: " . $e->getMessage());
            return response()->json(['ok' => false, 'message' => 'Failed to contact updater service'], 502);
        }
 
        // Validate updater response
        if (!$res->ok()) {
            $body = (string) $res->body();
            Log::error("FAQ updater returned non-200 for intent={$validated['intent']}: HTTP {$res->status()} body={$body}");
            return response()->json(['ok' => false, 'message' => 'Updater service error', 'status' => $res->status(), 'body' => $body], 502);
        }
 
        $json = null;
        try {
            $json = $res->json();
        } catch (\Throwable $e) {
            Log::error("FAQ updater returned invalid JSON for intent={$validated['intent']}: " . $e->getMessage());
            return response()->json(['ok' => false, 'message' => 'Updater returned invalid JSON'], 502);
        }
 
        // Expecting {"ok": true, ...}
        if (empty($json['ok'])) {
            Log::error("FAQ updater reported failure for intent={$validated['intent']}: " . json_encode($json));
            return response()->json(['ok' => false, 'message' => 'Updater reported failure', 'details' => $json], 422);
        }
 
        // Updater succeeded — persist FAQ locally
        try {
            $faq = Faq::create([
                'intent' => $validated['intent'],
                'description' => $validated['description'],
                'response' => $validated['response'],
            ]);
        } catch (\Throwable $e) {
            Log::error("Failed to persist FAQ after updater success for intent={$validated['intent']}: " . $e->getMessage());
            // Optionally inform updater to rollback (not implemented) — at least return error
            return response()->json(['ok' => false, 'message' => 'Failed to save FAQ locally'], 500);
        }
 
        return response()->json(['ok' => true, 'faq' => $faq], 201);
    }

    /**
     * Update FAQ (AJAX)
     */
    public function faqsUpdate(Request $request, Faq $faq)
    {
        $this->ensureAdmin();
        $validated = $request->validate([
            'intent' => 'required|string|max:255',
            'description' => 'required|string',
            'response' => 'required|string',
        ]);
 
        $faq->update([
            'intent' => $validated['intent'],
            'description' => $validated['description'],
            'response' => $validated['response'],
        ]);
 
        return response()->json(['success' => true, 'faq' => $faq]);
    }

    /**
     * Delete FAQ (AJAX)
     *
     * Accepts soft-deleted items as well (permanent delete from Trash).
     * When called on a non-deleted FAQ it will perform the usual soft-delete.
     * When called on an already trashed FAQ it will permanently remove it.
     */
    public function faqsDestroy($faqId)
    {
        $this->ensureAdmin();

        // Resolve the FAQ whether it's trashed or not so we can handle permanent deletes.
        $faq = Faq::withTrashed()->findOrFail($faqId);

        if ($faq->trashed()) {
            // Permanently delete the FAQ (force delete)
            $faq->forceDelete();

            // Note: faq_revisions are configured to cascade on delete, so any revision rows
            // tied to this FAQ will be removed by the DB. If you want to retain a separate
            // audit trail for permanent deletions, consider storing a record outside of
            // the faq_revisions table.
            return response()->json(['success' => true]);
        }

        // Not trashed yet — perform soft-delete and record deletion revision
        // Mark status as 'deleted' for audit/filters, then soft-delete
        $faq->status = 'deleted';
        $faq->save();

        $faq->delete();

        // Create a revision snapshot for the deletion event (auditable)
        FaqRevision::create([
            'faq_id'   => $faq->id,
            'intent'   => $faq->intent ?? $faq->topic,
            'response' => $faq->response,
            'user_id'  => Auth::id(),
            'action'   => 'delete',
            'meta'     => null,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Restore a soft-deleted FAQ (AJAX).
     */
    public function faqsRestore($faqId)
    {
        $this->ensureAdmin();

        $faq = Faq::withTrashed()->findOrFail($faqId);

        if (!$faq->trashed()) {
            return response()->json(['message' => 'FAQ is not deleted'], 422);
        }

        // Restore the soft-deleted model
        $faq->restore();

        // After restore, set status back to 'untrained' (per new convention)
        $faq->status = 'untrained';
        $faq->save();

        // Record a revision for the restore action so it is auditable / undoable
        FaqRevision::create([
            'faq_id'   => $faq->id,
            'intent'   => $faq->intent ?? $faq->topic,
            'response' => $faq->response,
            'user_id'  => Auth::id(),
            'action'   => 'restore',
            'meta'     => null,
        ]);

        return response()->json(['success' => true, 'faq' => $faq]);
    }

    /**
     * Undo the most recent change for a FAQ (AJAX).
     *
     * This applies the latest revision snapshot (which the observer writes before changes)
     * so invoking undo will revert the FAQ to the state captured by the newest revision row.
     */
    public function faqsUndo($faqId)
    {
        $this->ensureAdmin();

        $faq = Faq::withTrashed()->findOrFail($faqId);

        // Get the latest revision (newest first)
        $latest = FaqRevision::where('faq_id', $faq->id)->orderByDesc('created_at')->first();

        if (!$latest) {
            return response()->json(['message' => 'No revisions available to undo'], 422);
        }

        // Save a snapshot of current state before undoing so the action is auditable
        FaqRevision::create([
            'faq_id'   => $faq->id,
            'intent'   => $faq->intent ?? $faq->topic,
            'response' => $faq->response,
            'user_id'  => Auth::id(),
            'action'   => 'undo',
            'meta'     => ['undone_revision' => $latest->id],
        ]);

        // Apply the snapshot from the latest revision
        $faq->intent = $latest->intent ?? $latest->topic;
        $faq->response = $latest->response;
        $faq->save();

        // Record final 'undone_to' state
        FaqRevision::create([
            'faq_id'   => $faq->id,
            'intent'   => $faq->intent ?? $faq->topic,
            'response' => $faq->response,
            'user_id'  => Auth::id(),
            'action'   => 'undone_to',
            'meta'     => ['source_revision' => $latest->id],
        ]);

        return response()->json(['success' => true, 'faq' => $faq]);
    }

    /**
     * List revisions for a FAQ (blade) - shows history and allows revert.
     */
    public function faqsRevisions(Faq $faq)
    {
        $this->ensureAdmin();
        // Paginate revisions for manageability
        $revisions = $faq->revisions()->with('user')->paginate(20);
        return view('dashboards.admin.faqs.revisions', compact('faq', 'revisions'));
    }

    /**
     * Revert a FAQ to a given revision (AJAX).
     */
    public function faqsRevert(Request $request, Faq $faq, FaqRevision $revision)
    {
        $this->ensureAdmin();

        // Ensure revision belongs to the FAQ
        if ($revision->faq_id !== $faq->id) {
            return response()->json(['message' => 'Invalid revision'], 422);
        }

        DB::transaction(function () use ($faq, $revision) {
            // Save current snapshot so the revert itself is auditable and undoable
            FaqRevision::create([
                'faq_id'   => $faq->id,
                'intent'   => $faq->intent ?? $faq->topic,
                'response' => $faq->response,
                'user_id'  => Auth::id(),
                'action'   => 'revert',
                'meta'     => ['reverted_to' => $revision->id],
            ]);

            // Apply the snapshot from the revision
            $faq->intent = $revision->intent ?? $revision->topic;
            $faq->response = $revision->response;
            $faq->save();

            // Record final 'reverted_to' state
            FaqRevision::create([
                'faq_id'   => $faq->id,
                'intent'   => $faq->intent ?? $faq->topic,
                'response' => $faq->response,
                'user_id'  => Auth::id(),
                'action'   => 'reverted_to',
                'meta'     => ['source_revision' => $revision->id],
            ]);
        });

        return response()->json(['success' => true]);
    }

    /**
     * Untrained FAQs page (blade)
     */
    public function faqsUntrainIndex(Request $request)
    {
        $this->ensureAdmin();
        return view('dashboards.admin.faqs.untrained');
    }

    /**
     * Pending FAQs list (AJAX) - supports search and per_page options
     */
    public function faqsUntrainList(Request $request)
    {
        $this->ensureAdmin();

        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 25);
        if (!in_array($perPage, [25,50,100])) { $perPage = 25; }

        $faqs = Faq::when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(function ($qq) use ($like) {
                    $qq->where('intent', 'like', $like)
                       ->orWhere('response', 'like', $like);
                });
            })
            ->where('status', 'untrained')
            ->orderBy('intent')
            ->paginate($perPage)
            ->appends(['q' => $q, 'per_page' => $perPage]);
 
        // Format pending items so timestamps follow desired format (Y-m-d h:i am/pm)
        $items = array_map(function ($f) {
            return [
                'id' => $f->id,
                'intent' => $f->intent,
                'description' => $f->description ?? '',
                'response' => $f->response,
                'status' => $f->status,
                'response_disabled' => (bool) ($f->response_disabled ?? false),
                'created_at' => optional($f->created_at)->format('Y-m-d h:i a'),
                'updated_at' => optional($f->updated_at)->format('Y-m-d h:i a'),
            ];
        }, $faqs->items());

        return response()->json([
            'items' => $items,
            'meta' => [
                'total' => $faqs->total(),
                'per_page' => $faqs->perPage(),
                'current_page' => $faqs->currentPage(),
                'last_page' => $faqs->lastPage(),
            ],
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    /**
     * Mark FAQ as trained (AJAX)
     */
    public function faqsTrain(Request $request, Faq $faq)
    {
        $this->ensureAdmin();

        if ($faq->status !== 'untrained') {
            return response()->json(['message' => 'FAQ is not untrained'], 422);
        }

        $faq->status = 'trained';
        $faq->save();

        return response()->json(['success' => true, 'faq' => $faq]);
    }

    /**
     * Mark FAQ as not trained (AJAX) — used when the chatbot reports an incorrect trained response.
     *
     * This sets the FAQ status back to 'pending' and records a revision for audit.
     */
    public function faqsUntrain(Request $request, Faq $faq)
    {
        $this->ensureAdmin();

        if ($faq->status !== 'trained') {
            return response()->json(['message' => 'FAQ is not marked as trained'], 422);
        }

        // Save a revision snapshot so this change is auditable
        FaqRevision::create([
            'faq_id'   => $faq->id,
            'intent'   => $faq->intent ?? $faq->topic,
            'response' => $faq->response,
            'user_id'  => Auth::id(),
            'action'   => 'untrain',
            'meta'     => null,
        ]);

        $faq->status = 'untrained';
        $faq->save();

        return response()->json(['success' => true, 'faq' => $faq]);
    }

    /**
     * Disable a FAQ response so external systems (Rasa) will treat it as unavailable.
     * This sets the `response_disabled` flag and records a revision for audit.
     */
    public function faqsDisable(Request $request, Faq $faq)
    {
        $this->ensureAdmin();

        if ($faq->response_disabled) {
            return response()->json(['message' => 'FAQ already disabled'], 422);
        }

        // record revision snapshot
        FaqRevision::create([
            'faq_id'   => $faq->id,
            'intent'   => $faq->intent ?? $faq->topic,
            'response' => $faq->response,
            'user_id'  => Auth::id(),
            'action'   => 'disable',
            'meta'     => null,
        ]);

        $faq->response_disabled = true;
        $faq->save();

        return response()->json(['success' => true, 'faq' => $faq]);
    }

    /**
     * Re-enable a previously disabled FAQ response.
     */
    public function faqsEnable(Request $request, Faq $faq)
    {
        $this->ensureAdmin();

        if (!$faq->response_disabled) {
            return response()->json(['message' => 'FAQ is not disabled'], 422);
        }

        // record revision snapshot
        FaqRevision::create([
            'faq_id'   => $faq->id,
            'intent'   => $faq->intent ?? $faq->topic,
            'response' => $faq->response,
            'user_id'  => Auth::id(),
            'action'   => 'enable',
            'meta'     => null,
        ]);

        $faq->response_disabled = false;
        $faq->save();

        return response()->json(['success' => true, 'faq' => $faq]);
    }
}