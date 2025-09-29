<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Faq;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
        $userCount = User::count();

        // Active staff in the last 10 minutes (based on sessions table)
        // sessions.last_activity is an epoch seconds integer
        $cutoff = now()->subMinutes(10)->getTimestamp();
        $activeStaffCount = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->whereNotNull('sessions.user_id')
            ->where('sessions.last_activity', '>=', $cutoff)
            ->where('users.role', '!=', 'Primary Administrator')
            ->distinct('sessions.user_id')
            ->count('sessions.user_id');

        // Build initial active staff list (name, email, last_activity)
        $activeStaff = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->whereNotNull('sessions.user_id')
            ->where('sessions.last_activity', '>=', $cutoff)
            ->where('users.role', '!=', 'Primary Administrator')
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
        $staffContacts = User::where('role', '!=', 'Primary Administrator')
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
        $userCount = User::count();

        // Active staff in the last 10 minutes
        $cutoff = now()->subMinutes(10)->getTimestamp();
        $activeStaffCount = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->whereNotNull('sessions.user_id')
            ->where('sessions.last_activity', '>=', $cutoff)
            ->where('users.role', '!=', 'Primary Administrator')
            ->distinct('sessions.user_id')
            ->count('sessions.user_id');

        $activeStaffArr = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->whereNotNull('sessions.user_id')
            ->where('sessions.last_activity', '>=', $cutoff)
            ->where('users.role', '!=', 'Primary Administrator')
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
        $staffContactsArr = User::where('role', '!=', 'Primary Administrator')
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
                    'date_created' => optional($t->date_created ?? $t->created_at)->toDateTimeString(),
                    'created_at'   => optional($t->created_at)->toDateTimeString(),
                    'updated_at'   => optional($t->updated_at)->toDateTimeString(),
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
                    'date_created' => optional($t->date_created ?? $t->created_at)->toDateTimeString(),
                    'created_at'   => optional($t->created_at)->toDateTimeString(),
                    'updated_at'   => optional($t->updated_at)->toDateTimeString(),
                    'staff'        => $t->staff ? ['name' => (string) $t->staff->name] : null,
                ];
            })
            ->values()
            ->toArray();

        return response()->json([
            'openTickets'       => (int) $openTickets,
            'inProgressTickets' => (int) $inProgressTickets,
            'faqCount'          => (int) $faqCount,
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
     */
    private function ensureAdmin(): void
    {
        $u = Auth::user();
        abort_unless($u && ($u->role === 'Primary Administrator'), 403, 'Unauthorized');
    }

    /**
     * List staff users (excluding Primary Administrator) with simple search + pagination.
     */
    public function usersIndex(Request $request)
    {
        $this->ensureAdmin();

        $q = trim((string) $request->query('q', ''));

        $users = User::where('role', '!=', 'Primary Administrator')
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(function ($qq) use ($like) {
                    $qq->where('name', 'like', $like)
                       ->orWhere('email', 'like', $like)
                       ->orWhere('role', 'like', $like)
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
            'role' => 'required|string|max:255|not_in:Primary Administrator',
            'category' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
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
            'role' => 'required|string|max:255|not_in:Primary Administrator',
            'category' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
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
        if ($user->role === 'Primary Administrator') {
            return back()->withErrors(['delete' => 'Cannot delete Primary Administrator.']);
        }

        $user->delete();
    
        return redirect()->route('admin.users.index')->with('status', 'Staff deleted.');
    }

    /**
     * FAQ Management - page (blade)
     */
    public function faqsIndex(Request $request)
    {
        $this->ensureAdmin();
        return view('dashboards.admin.faqs.index');
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

        $faqs = Faq::when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(function ($qq) use ($like) {
                    $qq->where('topic', 'like', $like)
                       ->orWhere('response', 'like', $like);
                });
            })
            ->orderBy('topic')
            ->paginate($perPage)
            ->appends(['q' => $q, 'per_page' => $perPage]);

        return response()->json([
            'items' => $faqs->items(),
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
     */
    public function faqsShow(Faq $faq)
    {
        $this->ensureAdmin();
        return response()->json([
            'id' => $faq->id,
            'topic' => $faq->topic,
            'response' => $faq->response,
            'created_at' => optional($faq->created_at)->toDateTimeString(),
            'updated_at' => optional($faq->updated_at)->toDateTimeString(),
        ]);
    }

    /**
     * Store new FAQ (AJAX)
     */
    public function faqsStore(Request $request)
    {
        $this->ensureAdmin();
        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'response' => 'required|string',
        ]);

        $faq = Faq::create([
            'topic' => $validated['topic'],
            'response' => $validated['response'],
        ]);

        return response()->json(['success' => true, 'faq' => $faq], 201);
    }

    /**
     * Update FAQ (AJAX)
     */
    public function faqsUpdate(Request $request, Faq $faq)
    {
        $this->ensureAdmin();
        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'response' => 'required|string',
        ]);

        $faq->update([
            'topic' => $validated['topic'],
            'response' => $validated['response'],
        ]);

        return response()->json(['success' => true, 'faq' => $faq]);
    }

    /**
     * Delete FAQ (AJAX)
     */
    public function faqsDestroy(Faq $faq)
    {
        $this->ensureAdmin();
        $faq->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Pending FAQs page (blade)
     */
    public function faqsPendingIndex(Request $request)
    {
        $this->ensureAdmin();
        return view('dashboards.admin.faqs.pending');
    }

    /**
     * Pending FAQs list (AJAX) - supports search and per_page options
     */
    public function faqsPendingList(Request $request)
    {
        $this->ensureAdmin();

        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 25);
        if (!in_array($perPage, [25,50,100])) { $perPage = 25; }

        $faqs = Faq::when($q !== '', function ($query) use ($q) {
                $like = '%' . $q . '%';
                $query->where(function ($qq) use ($like) {
                    $qq->where('topic', 'like', $like)
                       ->orWhere('response', 'like', $like);
                });
            })
            ->where('status', 'pending')
            ->orderBy('topic')
            ->paginate($perPage)
            ->appends(['q' => $q, 'per_page' => $perPage]);

        return response()->json([
            'items' => $faqs->items(),
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

        if ($faq->status !== 'pending') {
            return response()->json(['message' => 'FAQ is not pending'], 422);
        }

        $faq->status = 'trained';
        $faq->save();

        return response()->json(['success' => true, 'faq' => $faq]);
    }
}