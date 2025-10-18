<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CategoriesController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureAdmin();
        $perPage = (int)$request->query('per_page', 25);
        $categories = Category::select('id','name','role_id','description','updated_at')
            ->with('role')
            ->orderBy('name')
            ->paginate($perPage);

        return view('dashboards.admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $this->ensureAdmin();
        $roles = Role::orderBy('name')->get();
        return view('dashboards.admin.categories.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();
        $validated = $request->validate([
            'role_id' => ['required','exists:roles,id'],
            'name' => ['required','string','max:191', Rule::unique('categories','name')->where(function($q) use($request){ return $q->where('role_id',$request->role_id); })],
            'description' => ['nullable','string','max:1000'],
        ]);

        Category::create([
            'role_id' => $validated['role_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        try {
            Cache::put('categories_last_changed', time(), 3600);
        } catch (\Throwable $e) {
            Log::warning('Failed to update categories_last_changed cache: '.$e->getMessage());
        }

        return redirect()->route('admin.categories.index')->with('status','Category created.');
    }

    public function edit(Category $category)
    {
        $this->ensureAdmin();
        $roles = Role::orderBy('name')->get();
        return view('dashboards.admin.categories.edit', compact('category','roles'));
    }

    public function update(Request $request, Category $category)
    {
        $this->ensureAdmin();
        $validated = $request->validate([
            'role_id' => ['required','exists:roles,id'],
            'name' => ['required','string','max:191', Rule::unique('categories','name')->where(function($q) use($request,$category){ return $q->where('role_id',$request->role_id)->where('id','<>',$category->id); })],
            'description' => ['nullable','string','max:1000'],
        ]);

        $category->update([
            'role_id' => $validated['role_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        try {
            Cache::put('categories_last_changed', time(), 3600);
        } catch (\Throwable $e) {
            Log::warning('Failed to update categories_last_changed cache: '.$e->getMessage());
        }

        return redirect()->route('admin.categories.index')->with('status','Category updated.');
    }

    public function destroy(Category $category)
    {
        $this->ensureAdmin();
        $category->delete();

        try {
            Cache::put('categories_last_changed', time(), 3600);
        } catch (\Throwable $e) {
            Log::warning('Failed to update categories_last_changed cache: '.$e->getMessage());
        }

        return redirect()->route('admin.categories.index')->with('status','Category deleted.');
    }

    private function ensureAdmin(): void
    {
        $u = Auth::user();
        $isAdmin = $u && strtolower((string)($u->role ?? '')) === 'primary administrator';
        abort_unless($isAdmin, 403, 'Unauthorized');
    }
}