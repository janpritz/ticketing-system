# FAQs Landing Page Implementation Plan

## Overview
Create a public FAQs landing page with email verification session management for ticket operations.

## Architecture

### 1. Routes
- **GET `/faqs`** - Public FAQs landing page (no authentication required)
- **GET `/faqs/api`** - JSON endpoint for approved FAQs (for AJAX loading)

### 2. Controller: `FAQsController` (new public controller)
**Location:** `app/Http/Controllers/FAQsController.php`

**Methods:**
- `index()` - Display FAQs landing page with:
  - Approved FAQs from `StagedFaq` table (status='approved')
  - Create Ticket button (top right)
  - Profile icon (top right, visible only if email verified in session)
  - Responsive card layout
  
- `getFaqsJson()` - Return approved FAQs as JSON for AJAX loading

### 3. Views

#### A. FAQs Landing Page
**Location:** `resources/views/faqs/index.blade.php`

**Components:**
- Header with:
  - Title: "Frequently Asked Questions"
  - Create Ticket button (top right) - redirects to `/tickets/verify-otp`
  - Profile icon (top right, conditional) - if email verified in session proceed with viewing ticket history, if not, show send otp for verification page.
  
- FAQ Cards Container:
  - Grid layout (responsive: 1 col mobile, 2 cols tablet, 3 cols desktop)
  - Each card displays:
    - Question (always visible)
    - Answer (hidden by default, toggle on click)
    - Smooth expand/collapse animation
    
- Mobile-friendly design:
  - Full-width cards on mobile
  - Proper spacing and touch targets
  - Readable font sizes

#### B. Profile Dropdown Menu (component)
**Location:** `resources/views/faqs/components/profile-menu.blade.php`

**Options:**
- View Ticket History (links to `/tickets/{email}`)
- Create New Ticket (links to `/tickets/verify-otp`)
- Logout/Clear Session (clears `otp_verified_{email}` session)

### 4. Session Management

**Session Key:** `otp_verified_{email}`
**Session Data:**
```php
[
    'verified_at' => now(),
    'identifier' => $email,
    'email' => $email
]
```

**Session Duration:** 30 minutes (checked on each request)

**Permissions with Active Session:**
- View ticket history
- Create new tickets
- Edit existing tickets
- Access profile menu

### 5. JavaScript Functionality

#### FAQ Card Toggle
- Click on card to expand/collapse answer
- Smooth CSS transitions
- Active state styling
- Keyboard accessible (Enter/Space to toggle)

#### Profile Menu
- Dropdown toggle on profile icon click
- Close on outside click
- Mobile-friendly touch handling

#### Session Expiry Check
- Check session expiry on page load
- Redirect to OTP verification if expired
- Show warning before expiry (optional)

### 6. Responsive Design

**Breakpoints:**
- Mobile: < 640px (1 column, full-width cards)
- Tablet: 640px - 1024px (2 columns)
- Desktop: > 1024px (3 columns)

**Mobile Optimizations:**
- Touch-friendly card heights
- Larger tap targets (min 44px)
- Readable font sizes (16px minimum)
- Proper spacing between elements
- No horizontal scroll

### 7. Integration Points

**Existing Systems:**
- OTP verification flow (already implemented in `TicketController`)
- Session management (Laravel sessions)
- Ticket creation flow (existing routes)
- Ticket history view (existing `/tickets/{email}` route)

**Data Flow:**
1. User visits `/faqs` (public, no auth required)
2. FAQs loaded from `StagedFaq` table (approved only)
3. User clicks "Create Ticket" → redirects to `/tickets/verify-otp`
4. After OTP verification → session created with `otp_verified_{email}`
5. User can now see profile icon and access ticket operations
6. Session expires after 30 minutes → redirect to OTP verification

## Implementation Steps

### Phase 1: Backend Setup
1. Create `FAQsController` with `index()` and `getFaqsJson()` methods
2. Add routes for `/faqs` and `/faqs/api`
3. Query `StagedFaq` table for approved FAQs

### Phase 2: Frontend - Landing Page
1. Create `resources/views/faqs/index.blade.php`
2. Implement FAQ card layout with Tailwind CSS
3. Add Create Ticket button (top right)
4. Add conditional profile icon (top right)
5. Implement responsive grid layout

### Phase 3: Frontend - Interactivity
1. Add JavaScript for FAQ card toggle functionality
2. Implement profile dropdown menu
3. Add session expiry checking
4. Handle keyboard accessibility

### Phase 4: Session Management
1. Verify session checking on FAQs page load
2. Redirect to OTP if session expired
3. Display profile menu only if session active
4. Handle profile menu actions (logout, view history, create ticket)

### Phase 5: Testing & Polish
1. Test responsive design on mobile/tablet/desktop
2. Test OTP flow integration
3. Test session expiry handling
4. Test profile menu functionality
5. Verify accessibility (keyboard navigation, screen readers)

## Database Query

**Get Approved FAQs:**
```php
StagedFaq::where('status', 'approved')
    ->orderBy('general_topic')
    ->orderBy('suggested_q')
    ->get();
```

## Security Considerations

1. **Session Validation:** Check session expiry on each request
2. **CSRF Protection:** Use `@csrf` in forms
3. **Email Verification:** Require OTP before allowing ticket operations
4. **Data Privacy:** Only show tickets for verified email
5. **Rate Limiting:** Apply to OTP endpoints (already done)

## Accessibility Features

1. Keyboard navigation (Tab, Enter, Space)
2. ARIA labels for interactive elements
3. Semantic HTML structure
4. Color contrast compliance
5. Focus indicators
6. Screen reader friendly

## Performance Considerations

1. Lazy load FAQ content (optional)
2. Cache approved FAQs (optional)
3. Minimize JavaScript bundle
4. Optimize images/icons
5. Use CSS transitions (GPU accelerated)

## Future Enhancements

1. Search/filter FAQs by topic
2. FAQ categories/sections
3. FAQ rating/feedback system
4. Analytics tracking
5. FAQ sync with Rasa knowledge base
