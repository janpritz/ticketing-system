# User Testing Process for Ticketing System

This document outlines a comprehensive user testing process for the Laravel-based ticketing system. The process starts with a fresh database (no existing data) and covers all major functionalities including user management, ticket handling, knowledgebase, announcements, Rasa server management, and reports. This process is designed for thorough user testing and system defense validation.

## Prerequisites

- Fresh Laravel installation with empty database
- Primary Administrator account created (default credentials or via seeder)
- Rasa server configured and accessible (if applicable)
- Web browser for testing

## 1. Initial Setup and User Management

### 1.1 Login as Primary Administrator
- Navigate to `/login`
- Enter Primary Administrator credentials
- Verify dashboard loads at `/admin/dashboard`
- Expected: Dashboard shows 0 users, 0 tickets, no active staff

### 1.2 Create Roles
- Navigate to `/admin/roles`
- Click "Create Role"
- Create roles: "Support Staff", "IT Staff", "HR Staff"
- Verify roles appear in the list

### 1.3 Create Categories
- Navigate to `/admin/categories`
- Click "Create Category"
- Create categories linked to roles:
  - For IT Staff: "Hardware", "Software", "Network"
  - For Support Staff: "General", "Billing"
  - For HR Staff: "Payroll", "Benefits"
- Verify categories are associated with correct roles

### 1.4 Create Staff Users
- Navigate to `/admin/users`
- Click "Create Staff"
- Create multiple staff users with different roles and categories:
  - User 1: John Doe, IT Staff, Hardware
  - User 2: Jane Smith, Support Staff, General
  - User 3: Bob Johnson, IT Staff, Software
- Verify users are created and can login
- Check dashboard updates user count

## 2. Ticket Creation and Assignment

### 2.1 Create Ticket as Guest
- Navigate to `/tickets/create`
- Fill form:
  - Email: test@example.com
  - Category: Hardware
  - Subject: "Computer not starting"
  - Message: "My computer won't turn on"
- Submit ticket
- Expected: Ticket created with ID, status "Open", no staff assigned

### 2.2 Create Another Ticket
- Create additional tickets with different categories
- Verify tickets appear in admin dashboard unassigned list

### 2.3 Assign Ticket to Staff
- As admin, go to `/admin/tickets`
- Select unassigned ticket
- Assign to staff user (e.g., John Doe for Hardware ticket)
- Verify assignment in ticket details

## 3. Ticket Management (Staff Perspective)

### 3.1 Staff Login and Dashboard
- Login as staff user (e.g., John Doe)
- Navigate to `/staff/dashboard`
- Verify assigned tickets appear
- Check active staff status

### 3.2 View Ticket Details
- Click on assigned ticket
- Verify ticket information displays correctly
- Check ticket history (initial creation)

### 3.3 Forward Ticket
- From ticket view, use forward option
- Forward to another staff (e.g., Bob Johnson)
- Add forwarding note: "Escalating to software specialist"
- Verify:
  - Ticket status changes to "Forwarded"
  - History records forwarding action
  - New staff receives notification (if configured)

### 3.4 Respond to Ticket
- As receiving staff (Bob Johnson), view forwarded ticket
- Add response: "I've diagnosed the issue. It's a software problem."
- Send response
- Verify:
  - Email sent to ticket creator
  - Ticket status updates appropriately
  - Response appears in ticket history

### 3.5 Close Ticket
- Mark ticket as resolved
- Verify status changes to "Closed" or equivalent

## 4. Knowledgebase (FAQ) Management

### 4.1 Access Knowledgebase
- As admin, navigate to `/admin/knowledgebase`
- Verify empty knowledgebase initially

### 4.2 Create FAQ
- Click "Add FAQ"
- Fill form:
  - Intent: "password_reset"
  - Description: "How to reset password"
  - Response: "Go to login page, click 'Forgot Password', enter email, follow OTP instructions"
- Save FAQ
- Verify FAQ appears in list

### 4.3 Create Multiple FAQs
- Create additional FAQs for different scenarios:
  - Hardware troubleshooting
  - Account access issues
  - Billing inquiries
- Test search and filtering

### 4.4 Edit FAQ
- Edit existing FAQ
- Update response with additional details
- Verify changes save and log in document changes

### 4.5 Disable/Enable FAQ
- Disable a FAQ
- Verify it's marked as disabled
- Re-enable it

### 4.6 Sync with Rasa
- Trigger Rasa training (if server online)
- Check training status in logs
- Verify FAQs are available for chatbot

## 5. Announcements Management

### 5.1 Access Announcements
- As admin, navigate to `/admin/announcements`
- Verify empty announcements list

### 5.2 Create Announcement
- Click "Create Announcement"
- Fill form:
  - Title: "System Maintenance Notice"
  - Content: "The system will be down for maintenance on Saturday 2AM-4AM"
- Save announcement
- Verify announcement appears in list

### 5.3 Create Multiple Announcements
- Create additional announcements:
  - Policy updates
  - New feature releases
  - Holiday schedules

### 5.4 Pin Announcement
- Pin an important announcement
- Verify it appears at top of list
- Unpin and verify order changes

### 5.5 Edit Announcement
- Edit existing announcement
- Update content
- Verify changes and document logging

### 5.6 Delete Announcement
- Delete an announcement
- Verify removal from list
- Check logs for deletion record

## 6. Rasa Server Manager

### 6.1 Access Rasa Server Manager
- As admin, navigate to `/admin/rasa-server`
- Check server status
- Expected: Server status (online/offline), last training timestamp

### 6.2 View Training History
- Click "Training History" tab
- Verify list of training sessions
- Check timestamps and status

### 6.3 View Backup History
- Click "Backup History" tab
- Verify existing backups (if any)
- Check backup dates and sizes

### 6.4 Create Backup
- Click "Create Backup"
- Confirm backup creation
- Verify new backup appears in history
- Download backup files and verify contents

### 6.5 View Models List
- Click "Models List" tab
- Verify available Rasa models
- Check model versions and dates

### 6.6 Cleanup Models
- Run cleanup models function
- Verify old/unused models are removed
- Check logs for cleanup actions

### 6.7 Start Action Server
- If action server is stopped, start it
- Verify status changes to running
- Test action server functionality

### 6.8 Fetch FAQs
- Use "Fetch FAQs" function
- Verify FAQs are retrieved from Rasa server
- Check synchronization status

## 7. Reports and Analytics

### 7.1 Access Reports
- As admin, navigate to `/admin/reports`
- Verify reports dashboard loads

### 7.2 View Backlog Trend
- Check backlog trend chart
- Verify data reflects ticket creation/closure over time
- Test date range filtering

### 7.3 View Dynamic Data
- Check dynamic reports:
  - Tickets by category
  - Tickets by status
  - Staff performance metrics
  - Response time analytics

### 7.4 Export Reports (if available)
- Test report export functionality
- Verify CSV/PDF generation
- Check data accuracy in exports

## 8. System Defense and Security Testing

### 8.1 Authentication Testing
- Test invalid login attempts
- Verify rate limiting (throttle middleware)
- Test session management
- Check logout functionality

### 8.2 Authorization Testing
- Attempt to access admin routes as staff user
- Verify role-based access control
- Test category-based permissions

### 8.3 Input Validation
- Test SQL injection attempts in forms
- Verify XSS protection
- Check file upload restrictions
- Test email validation

### 8.4 Data Integrity
- Test concurrent ticket updates
- Verify transaction handling
- Check foreign key constraints

### 8.5 Performance Testing
- Create multiple tickets simultaneously
- Test dashboard auto-refresh
- Check response times under load

## 9. Logs and Audit Trail

### 9.1 Access Logs
- Navigate to `/admin/logs`
- Verify document change history
- Check FAQ/announcement modifications
- Test log filtering and search

### 9.2 Verify Audit Trail
- Check all CRUD operations are logged
- Verify user attribution in logs
- Test log retention and cleanup

## 10. Final Validation

### 10.1 End-to-End Ticket Flow
- Create ticket as user
- Assign to staff
- Forward between staff
- Respond and resolve
- Verify all parties receive notifications

### 10.2 Knowledge Base Integration
- Test chatbot integration with FAQs
- Verify announcement display to users
- Check Rasa server synchronization

### 10.3 System Health Check
- Verify all services running
- Check database integrity
- Test backup/restore procedures
- Validate all user roles and permissions

This comprehensive testing process ensures all system components function correctly, security measures are effective, and the system is ready for production use.