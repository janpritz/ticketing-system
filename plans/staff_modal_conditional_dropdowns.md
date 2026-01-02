# Plan for Implementing Conditional Dropdowns in Staff Modals

## Current System Analysis

### Data Structure
- **Roles**: Table with `id`, `name`, `description`
- **Categories**: Table with `id`, `role_id` (FK to roles), `name`, `description`
- **Users**: `role_id` (FK to roles), `category` (string matching category.name)

### Relationships
- Roles have many Categories (via `role_id` in categories)
- Users belong to a Role and optionally have a Category (string field)

### Current Implementation Issues
- Category dropdown in both add/edit staff modals shows ALL categories from all roles
- No filtering based on selected role
- Categories are role-specific, so showing irrelevant categories is confusing

## Proposed Solution

### Backend Changes

1. **Add API Endpoint for Role-Based Categories**
   - Add `categoriesByRole(Request $request)` method to `AdminController`
   - Accepts `role_id` parameter
   - Returns JSON array of category names for the given role
   - Route: `GET /admin/categories-by-role?role_id={id}`

2. **Update Category Fetching Logic**
   - Modify views to initially load categories based on selected/default role
   - For add modal: start with empty categories (or all if no role selected)
   - For edit modal: load categories for the user's current role

### Frontend Changes

#### Add Staff Modal (`create.blade.php`)
1. **Role Dropdown Event Listener**
   - Add JavaScript to listen for changes on role select
   - On change: AJAX call to `/admin/categories-by-role?role_id={selected_role_id}`
   - Update category select options with returned categories
   - Always include "— None —" as first option

2. **Initial State**
   - Category dropdown starts empty (only "— None —")
   - User must select role first to populate categories

#### Edit Staff Modal (`edit.blade.php`)
1. **Initial Load**
   - On page load: fetch categories for the user's current role
   - Pre-select the user's current category if it exists

2. **Role Change Handling**
   - Same as add modal: update categories when role changes
   - If role changes, clear current category selection (or keep if still valid)

3. **Validation**
   - Ensure selected category belongs to selected role (backend validation in `usersUpdate`)

### Implementation Steps

1. **Backend API Endpoint**
   ```php
   // AdminController.php
   public function categoriesByRole(Request $request)
   {
       $roleId = $request->query('role_id');
       if (!$roleId) {
           return response()->json([]);
       }

       $categories = Category::where('role_id', $roleId)
           ->orderBy('name')
           ->pluck('name')
           ->toArray();

       return response()->json($categories);
   }
   ```

2. **Route Addition**
   ```php
   // web.php or admin routes
   Route::get('/admin/categories-by-role', [AdminController::class, 'categoriesByRole'])->name('admin.categories.by-role');
   ```

3. **JavaScript Implementation**
   - Add event listener to role select
   - AJAX call to fetch categories
   - Dynamically update category select options
   - Handle loading states and errors

4. **Backend Validation Updates**
   - In `usersStore` and `usersUpdate`: validate that if category is provided, it exists for the selected role
   - Add custom validation rule or check in controller

### Edge Cases
- Role with no categories: show only "— None —"
- Switching roles: clear invalid category selections
- Edit mode: preserve current category if still valid after role change
- AJAX failures: fallback to showing all categories or error message

### Benefits
- Improved UX: users only see relevant categories
- Data integrity: prevents assigning categories from wrong roles
- Maintains optional nature: "— None —" always available
- Consistent behavior between add and edit modals