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
use App\Services\FaqDeleterService;
use App\Events\FaqEnabled;
use App\Events\FaqDisabled;

class AdminController extends Controller
{
    /**
     * Admin dashboard displaying system-wide metrics, charts and recent tickets.
     */
    public function index(Request $request)
    {
        // KPI metrics
        $openTickets = Ticket::where('status', 'Open')->count();
        $forwardedTickets = Ticket::where('status', 'Forwarded')->count();
        $faqCount = Faq::count();
        // number of new FAQs (created today, displayed under Total FAQs on dashboard)
        $faqPendingCount = Faq::whereDate('created_at', today())->count();
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

        // Show unassigned tickets (no staff assigned)
        $myTicketsList = Ticket::with('staff')
            ->whereNull('staff_id')
            ->where('status', 'Open')
            ->orderByDesc('updated_at')
            ->take(6)
            ->get();

        return view('dashboards.admin.index', [
            'openTickets'       => $openTickets,
            'forwardedTickets' => $forwardedTickets,
            'faqCount'          => $faqCount,
            // pending FAQs count used by dashboard UI
            'faqPendingCount'   => $faqPendingCount,
            'userCount'         => $userCount,
            'activeStaffCount'  => $activeStaffCount,
            'lastTraining'      => $lastTrainingFormatted,
            'weekLabels'        => $weekLabels,
            'weekData'          => $weekData,
            'categoryLabels'    => $categoryLabels,
            'categoryData'      => $categoryData,
            'topSenders'        => $topSenders,
            'myTicketsList'    => $myTicketsList,
            'activeStaff'       => $activeStaff,
            'staffContacts'     => $staffContacts,
            'faqUpdaterSecret'  => env('FAQ_UPDATER_SECRET'),
            'faqUpdaterUrl'     => env('RASA_SERVER_CHECKER'),
        ]);
    }

    /**
     * Live data endpoint for admin dashboard auto-refresh.
     */
    public function data(Request $request)
    {
        // KPI metrics
        $openTickets = Ticket::where('status', 'Open')->count();
        $forwardedTickets = Ticket::where('status', 'Forwarded')->count();
        $faqCount = Faq::count();
        // include new FAQ count for live admin data
        $faqPendingCount = Faq::whereDate('created_at', today())->count();
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

        $myTicketsListArr = Ticket::with('staff')
            ->whereNull('staff_id')
            ->where('status', 'Open')
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
            'forwardedTickets' => (int) $forwardedTickets,
            'faqCount'          => (int) $faqCount,
            'faqPendingCount'   => (int) $faqPendingCount,
            'userCount'         => (int) $userCount,
            'activeStaffCount'  => (int) $activeStaffCount,
            'lastTraining'      => $lastTrainingLiveFormatted,
            'weekLabels'        => $weekLabels,
            'weekData'          => $weekData,
            'categoryLabels'    => $categoryLabels,
            'categoryData'      => $categoryData,
            'topSenders'        => $topSenders,
            'myTicketsList'    => $myTicketsListArr,
            'activeStaff'       => $activeStaffArr,
            'staffContacts'     => $staffContactsArr,
        ])
        ->header('Cache-Control', 'public, max-age=10')
        ->header('Pragma', 'cache');
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
                       ->orWhere('category', 'like', $like);
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

        // Build base query (search)
        $faqsQuery = Faq::when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(function ($qq) use ($like) {
                    $qq->where('intent', 'like', $like)
                       ->orWhere('response', 'like', $like);
                });
            });

        $faqs = $faqsQuery
            ->orderBy('intent')
            ->paginate($perPage)
            ->appends(['q' => $q, 'per_page' => $perPage]);

        // Format items so created_at / updated_at use "yyyy-mm-dd hh:mm am/pm"
        $items = array_map(function ($f) {
            return [
                'id' => $f->id,
                'intent' => $f->intent,
                'description' => $f->description ?? '',
                'response' => $f->response,
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
            'response_disabled' => (bool) ($faq->response_disabled ?? false),
            'can_restore' => $canRestore,
            'can_revert' => $canRevert,
            'can_undo' => $canUndo,
            'revisions_url' => route('admin.faqs.revisions', ['faq' => $faq->id]),
            'restore_url' => route('admin.faqs.restore', ['faq' => $faq->id]),
            'undo_url' => route('admin.faqs.undo', ['faq' => $faq->id]),
            'latest_revision' => $latestRevision,
        ]);
    }

    /**
     * Store new FAQ (AJAX)
     */
    public function faqsStore(Request $request)
    {
        $this->ensureAdmin();
        $validated = $request->validate([
            'intent' => 'required|string|max:255',
            'description' => 'required|string',
            'response' => 'required|string',
        ]);

        // Create FAQ locally - event will trigger sync
        $faq = Faq::create([
            'intent' => $validated['intent'],
            'description' => $validated['description'],
            'response' => $validated['response'],
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
            \Illuminate\Support\Facades\Log::error('Failed to log FAQ create change: ' . $e->getMessage());
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
            \Illuminate\Support\Facades\Log::error('Failed to log FAQ update change: ' . $e->getMessage());
        }

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
            // Permanently delete the FAQ - MUST call external deleter service first and require success
            try {
                $deleterService = new FaqDeleterService();
                $deleterService->deleteFaq($faq->intent, true);
            } catch (\Exception $e) {
                // If external deleter fails, DO NOT delete locally
                Log::error("FAQ deleter service failed for intent={$faq->intent}: " . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete FAQ from external system: ' . $e->getMessage()
                ], 502);
            }

            // Only delete locally if external service succeeded
            $faq->forceDelete();

            // Note: faq_revisions are configured to cascade on delete, so any revision rows
            // tied to this FAQ will be removed by the DB. If you want to retain a separate
            // audit trail for permanent deletions, consider storing a record outside of
            // the faq_revisions table.
            return response()->json(['success' => true]);
        }

        // Not trashed yet — perform soft-delete and record deletion revision
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
            \Illuminate\Support\Facades\Log::error('Failed to log FAQ delete change: ' . $e->getMessage());
        }

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

        // Dispatch event for sync system
        event(new FaqDisabled($faq));

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

        // Dispatch event for sync system
        event(new FaqEnabled($faq));

        return response()->json(['success' => true, 'faq' => $faq]);
    }

    /**
     * Get all FAQs as JSON for sync purposes.
     */
    public function faqsAllJson(Request $request)
    {
        $this->ensureAdmin();

        $faqs = Faq::where('response_disabled', false)
            ->select('id', 'intent', 'description', 'response')
            ->get()
            ->map(function ($faq) {
                return [
                    'id' => $faq->id,
                    'intent' => $faq->intent,
                    'description' => $faq->description ?? '',
                    'response' => $faq->response ?? '',
                    'sync_type' => 'update'
                ];
            });

        return response()->json([
            'faqs' => $faqs->toArray()
        ]);
    }

    /**
     * Store new announcement (AJAX) - uploads to Rasa server
     */
    public function announcementsStore(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:10000',
        ]);

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