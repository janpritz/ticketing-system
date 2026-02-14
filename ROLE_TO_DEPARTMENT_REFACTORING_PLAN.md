# Role to Department and Category to Role Refactoring Plan

## Executive Summary

This document outlines a comprehensive refactoring plan to change the current Role → Category → Ticket hierarchy to Department → Role → Ticket. This restructuring will improve organizational clarity, enable multiple role assignments per department, and better align with real-world organizational structures.

## 1. Current Structure Analysis

### Current Database Schema

```sql
-- Current structure
roles (id, name, description)
categories (id, role_id, name, description)
tickets (id, category_id, question, response, staff_id, status, ...)
users (id, name, email, role_id, category_id, ...)
```

### Current Relationships
- **Role**: Has many Categories, Has many Users
- **Category**: Belongs to Role, Has many Tickets
- **User**: Belongs to Role, Belongs to Category
- **Ticket**: Belongs to Category, Belongs to Staff (User)

### Current Ticket Assignment Workflow
1. Ticket created with category_id
2. ProcessTicketCreation job resolves category → role
3. Staff selected from users with matching role_id (load balancing)
4. Ticket assigned to staff

## 2. Proposed Changes

### New Database Schema

```sql
-- New structure
departments (id, name, description)
roles (id, department_id, name, description)
tickets (id, role_id, question, response, staff_id, status, ...)
users (id, name, email, department_id, ...)
user_roles (id, user_id, role_id, primary_role, ...)
```

### New Relationships
- **Department**: Has many Roles, Has many Users
- **Role**: Belongs to Department, Has many Tickets, Has many Users (through user_roles)
- **User**: Belongs to Department, Has many Roles (through user_roles)
- **Ticket**: Belongs to Role, Belongs to Staff (User)

### New Ticket Assignment Workflow
1. Ticket created with role_id
2. ProcessTicketCreation job resolves role → department
3. Staff selected from users with matching role_id (load balancing)
4. Ticket assigned to staff

## 3. Database Migration Strategy

### Phase 1: Schema Changes

```sql
-- 1. Create new departments table
CREATE TABLE departments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(191) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Add department_id to roles table
ALTER TABLE roles ADD COLUMN department_id BIGINT NULL AFTER id;
ALTER TABLE roles ADD CONSTRAINT fk_roles_departments FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL;

-- 3. Add department_id to users table
ALTER TABLE users ADD COLUMN department_id BIGINT NULL AFTER id;
ALTER TABLE users ADD CONSTRAINT fk_users_departments FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL;

-- 4. Create user_roles junction table
CREATE TABLE user_roles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    role_id BIGINT NOT NULL,
    primary_role BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_role (user_id, role_id),
    CONSTRAINT fk_user_roles_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_roles_roles FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- 5. Add role_id to tickets table
ALTER TABLE tickets ADD COLUMN role_id BIGINT NULL AFTER id;
ALTER TABLE tickets ADD CONSTRAINT fk_tickets_roles FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL;
```

### Phase 2: Data Migration

```sql
-- 1. Create departments from existing roles
INSERT INTO departments (name, description)
SELECT DISTINCT name, description FROM roles WHERE name != 'Primary Administrator';

-- 2. Update roles with department_id
UPDATE roles r
JOIN departments d ON r.name = d.name
SET r.department_id = d.id
WHERE r.name != 'Primary Administrator';

-- 3. Update users with department_id
UPDATE users u
JOIN roles r ON u.role_id = r.id
JOIN departments d ON r.department_id = d.id
SET u.department_id = d.id;

-- 4. Create user_roles entries
INSERT INTO user_roles (user_id, role_id, primary_role)
SELECT user_id, role_id, TRUE FROM users WHERE role_id IS NOT NULL;

-- 5. Update tickets with role_id via category_id
UPDATE tickets t
JOIN categories c ON t.category_id = c.id
JOIN roles r ON c.role_id = r.id
SET t.role_id = r.id
WHERE t.category_id IS NOT NULL;

-- 6. Handle Primary Administrator
UPDATE users SET department_id = (SELECT id FROM departments WHERE name = 'Primary Administrator') WHERE role_id = (SELECT id FROM roles WHERE name = 'Primary Administrator');
```

### Phase 3: Cleanup

```sql
-- 1. Drop category_id from tickets
ALTER TABLE tickets DROP FOREIGN KEY fk_tickets_categories;
ALTER TABLE tickets DROP COLUMN category_id;

-- 2. Drop role_id from users
ALTER TABLE users DROP FOREIGN KEY fk_users_roles;
ALTER TABLE users DROP COLUMN role_id;

-- 3. Drop category_id from users
ALTER TABLE users DROP FOREIGN KEY fk_users_categories;
ALTER TABLE users DROP COLUMN category_id;

-- 4. Drop categories table
DROP TABLE categories;
```

## 4. Code Refactoring Steps

### 4.1 Models

#### Department Model (New)
```php
// app/Models/Department.php
class Department extends Model
{
    protected $fillable = ['name', 'description'];
    
    public function roles()
    {
        return $this->hasMany(Role::class);
    }
    
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
```

#### Role Model Updates
```php
// app/Models/Role.php
class Role extends Model
{
    protected $fillable = ['department_id', 'name', 'description'];
    
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles')->withPivot('primary_role');
    }
    
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
```

#### User Model Updates
```php
// app/Models/User.php
class User extends Authenticatable
{
    protected $fillable = ['department_id', 'name', 'email', 'password', 'profile_photo'];
    
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')->withPivot('primary_role');
    }
    
    public function primaryRole()
    {
        return $this->roles()->wherePivot('primary_role', true)->first();
    }
    
    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'staff_id');
    }
}
```

#### Ticket Model Updates
```php
// app/Models/Ticket.php
class Ticket extends Model
{
    protected $fillable = ['role_id', 'question', 'response', 'staff_id', 'status', 'date_created', 'date_closed', 'attachments'];
    
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
```

### 4.2 Controllers

#### AdminController Updates
- Update user management to handle department_id and user_roles
- Update role management to include department_id
- Update category management to role management
- Update ticket assignment logic

#### TicketController Updates
- Update ticket creation to use role_id instead of category_id
- Update ticket filtering and display
- Update ticket assignment workflow

#### CategoriesController → RolesController Updates
- Rename controller to RolesController
- Update methods to handle department_id
- Update validation rules
- Update views and forms

#### RolesController Updates
- Add department management
- Update role creation with department assignment
- Update role editing with department assignment

### 4.3 Views

#### Admin Dashboard
- Update user management forms to include department selection
- Update role management forms to include department selection
- Update ticket assignment displays
- Update statistics and charts

#### Ticket Creation
- Update ticket creation form to use role dropdown instead of category dropdown
- Update ticket assignment logic
- Update ticket display and filtering

#### User Management
- Update user creation/editing forms to handle multiple roles
- Update user display to show department and roles
- Update role assignment interface

### 4.4 Jobs

#### ProcessTicketCreation Updates
```php
// app/Jobs/ProcessTicketCreation.php
class ProcessTicketCreation implements ShouldQueue
{
    protected $ticketId;
    protected $roleId;
    
    public function handle(): void
    {
        $ticket = Ticket::find($this->ticketId);
        if (!$ticket) {
            Log::error('ProcessTicketCreation: Ticket not found', ['ticket_id' => $this->ticketId]);
            return;
        }

        Log::info('ProcessTicketCreation: Starting ticket assignment', [
            'ticket_id' => $this->ticketId,
            'role_id' => $this->roleId
        ]);

        // Find staff with the lowest open-ticket load within the selected role
        $staff = null;
        if ($this->roleId) {
            Log::info('ProcessTicketCreation: Searching for staff in role', [
                'role_id' => $this->roleId
            ]);
            
            $candidates = User::whereHas('roles', function($q) {
                    $q->where('roles.id', $this->roleId);
                })
                ->withCount(['assignedTickets as open_tickets_count' => function ($q) {
                    $q->where('status', 'Open');
                }])
                ->get();

            if ($candidates->isNotEmpty()) {
                $min = $candidates->min('open_tickets_count');
                $ties = $candidates->where('open_tickets_count', $min);
                $staff = $ties->count() > 1 ? $ties->random() : $ties->first();
            }
        }

        // Update ticket with staff_id
        $staffId = $staff ? $staff->id : null;
        $ticket->update(['staff_id' => $staffId]);

        // Record initial routing history
        TicketRoutingHistory::create([
            'ticket_id' => $ticket->id,
            'staff_id' => $staffId,
            'status' => 'Open',
            'routed_at' => now(),
            'notes' => 'Ticket created' . ($staffId ? ' and assigned to staff ' . $staffId : ''),
        ]);
    }
}
```

## 5. Multiple Role Assignment Options

### Option 1: Database Changes (Recommended)
- Implement user_roles junction table
- Allow users to have multiple roles
- Add primary_role flag for default assignment
- Update ticket assignment to consider all user roles

### Option 2: No Database Changes
- Use existing role_id field for primary role
- Add additional role_ids as comma-separated string
- Implement role hierarchy for assignment priority
- Less flexible but requires no schema changes

### Option 3: Hybrid Approach
- Keep user_roles table for multiple roles
- Add role_priority field for assignment order
- Implement role inheritance for permissions
- Most flexible but most complex

## 6. Backward Compatibility

### Migration Path
1. **Phase 1**: Add new columns and tables (non-breaking)
2. **Phase 2**: Migrate data (non-breaking)
3. **Phase 3**: Update code to use new structure (breaking)
4. **Phase 4**: Clean up old columns (breaking)

### Compatibility Layers
- Keep category_id in tickets temporarily for data access
- Maintain role_id in users temporarily for user management
- Implement accessors for legacy code
- Provide migration scripts for data conversion

### Feature Flags
- Use feature flags to enable new functionality gradually
- Allow switching between old and new workflows
- Provide rollback capability

## 7. Testing Strategy

### Unit Tests
- Test Department model relationships
- Test Role model relationships
- Test User model relationships
- Test Ticket model relationships
- Test ProcessTicketCreation job

### Integration Tests
- Test ticket creation with role assignment
- Test user role management
- Test department management
- Test ticket assignment workflow
- Test multiple role assignment

### End-to-End Tests
- Test complete ticket lifecycle
- Test user management with multiple roles
- Test department and role management
- Test admin dashboard functionality

### Performance Tests
- Test ticket assignment performance
- Test role-based filtering performance
- Test user management performance
- Test database query optimization

## 8. Rollback Plan

### Immediate Rollback
1. **Code Rollback**: Revert code changes
2. **Database Rollback**: Restore from backup
3. **Feature Flag**: Disable new functionality

### Data Recovery
1. **Backup Restoration**: Restore database from pre-migration backup
2. **Data Migration Reversal**: Reverse data migration scripts
3. **Schema Restoration**: Restore original schema

### Communication Plan
1. **User Notification**: Inform users of rollback
2. **Issue Documentation**: Document rollback reasons
3. **Post-Mortem**: Analyze failure causes

### Prevention Measures
1. **Staged Deployment**: Deploy in phases
2. **Feature Flags**: Use feature flags for gradual rollout
3. **Monitoring**: Implement comprehensive monitoring
4. **Testing**: Extensive testing before production deployment

## 9. Implementation Timeline

### Week 1-2: Preparation
- Database schema changes
- Migration scripts creation
- Code refactoring preparation
- Testing environment setup

### Week 3-4: Implementation
- Database migrations
- Code refactoring
- Testing and validation
- Documentation updates

### Week 5-6: Testing and Deployment
- Comprehensive testing
- Performance optimization
- User acceptance testing
- Production deployment

### Week 7-8: Monitoring and Optimization
- Performance monitoring
- User feedback collection
- Issue resolution
- Documentation finalization

## 10. Risk Assessment

### Technical Risks
- **Data Migration Issues**: Complex data migration may cause data loss
- **Performance Impact**: New queries may impact performance
- **Compatibility Issues**: Legacy code may break

### Mitigation Strategies
- **Backup Strategy**: Comprehensive database backups
- **Testing**: Extensive testing in staging environment
- **Monitoring**: Real-time monitoring during deployment
- **Rollback Plan**: Immediate rollback capability

### Business Risks
- **User Disruption**: Changes may disrupt user workflows
- **Training Requirements**: Users may need training on new system
- **Timeline Delays**: Implementation may take longer than expected

### Mitigation Strategies
- **Communication Plan**: Clear communication with stakeholders
- **Training Materials**: Comprehensive training documentation
- **Phased Rollout**: Gradual implementation to minimize disruption
- **Buffer Time**: Include buffer time in timeline

## 11. Success Metrics

### Technical Metrics
- **Migration Success Rate**: Percentage of successful data migration
- **Performance Impact**: Performance change percentage
- **Error Rate**: Number of errors during and after migration

### Business Metrics
- **User Adoption**: Percentage of users using new features
- **Ticket Resolution Time**: Impact on ticket resolution time
- **User Satisfaction**: User satisfaction scores

### Operational Metrics
- **System Uptime**: System availability during and after migration
- **Support Tickets**: Number of support tickets related to changes
- **Training Effectiveness**: Training completion and effectiveness rates

---

**Document Version**: 1.0  
**Last Updated**: 2026-02-14  
**Prepared by**: Architect  
**Reviewers**: Development Team  
**Approval**: Required before implementation