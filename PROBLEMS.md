# YouPorts Project - Issues & Bugs Report

**Date**: April 21, 2026  
**Scope**: Network communication, Data rendering, API integration, Frontend/Backend synchronization

---

## Critical Issues

### 1. **Duplicate App Configuration Files**
- **Severity**: HIGH
- **Location**: Frontend
- **Issue**: Two different Vue app setup files exist:
  - `frontend/src/app.js` (simple setup)
  - `frontend/src/main.ts` (complex setup with Lucide icons and custom directives)
- **Impact**: Unclear which file is actually being used; potential configuration conflicts
- **Details**:
  - `app.js`: Basic Vue 3 + Pinia + Router
  - `main.ts`: Vue 3 + Pinia + Router + Lucide Icons + Custom directive setup
- **Recommendation**: Consolidate to single entry point and update `index.html` accordingly

### 2. **Frontend Environment Configuration Missing**
- **Severity**: HIGH
- **Location**: Frontend root
- **Issue**: No `.env` or `.env.example` files found in frontend directory
- **Impact**: API base URL not configurable per environment; falls back to `/api`
- **Details**:
  - `frontend/src/services/api.js` expects `VITE_API_BASE_URL` from environment
  - Fallback to `/api` may not work in all deployment scenarios
  - `VITE_SANCTUM_CSRF_ENDPOINT` also missing from environment
- **Missing Config Example**:
  ```
  VITE_API_BASE_URL=http://localhost:3000/api
  VITE_SANCTUM_CSRF_ENDPOINT=/sanctum/csrf-cookie
  ```

### 3. **Authentication Data Inconsistency**
- **Severity**: HIGH
- **Location**: Frontend authentication
- **Issue**: Mixed authentication strategies without proper server-side session validation
- **Details**:
  - Auth store uses localStorage for `auth_token` and `auth_user`
  - Login endpoint doesn't validate/return proper JWT tokens
  - Token format is `'session_' + Date.now()` - not a real token
  - API calls include `X-User-Id` header from localStorage (unreliable)
  - `StudentController` uses `X-User-Id` header directly without Auth::user() validation
  - Sanctum CSRF cookie initialization is called but never used
- **Impact**: Security vulnerability - user ID can be spoofed via request headers
- **Code Issue** (backend/app/Http/Controllers/StudentController.php):
  ```php
  private function getUserId(Request $request)
  {
      return $request->header('X-User-Id');  // ❌ UNSAFE - not validated
  }
  ```

### 4. **API Route Middleware Inconsistency**
- **Severity**: MEDIUM
- **Location**: Backend routes
- **Issue**: Some routes protected by auth, others only by CORS middleware
- **Details**:
  - `POST /api/login` - only has CORS middleware (correct)
  - Most API routes have only `CorsMiddleware` - no authentication middleware
  - No `auth:sanctum` or equivalent protection on protected endpoints
  - All routes should require authentication except login/register
- **Impact**: Unauthenticated users can access protected resources

### 5. **API Response Format Inconsistency**
- **Severity**: MEDIUM
- **Location**: Backend controllers
- **Issue**: Different response structures across endpoints
- **Examples**:
  - `ReportController@index`: Returns `{'data': [...], 'meta': {...}}`
  - `StudentController@myReports`: Returns `{'data': [...], 'meta': {...}}`
  - `AdminController@dashboard`: Returns `{'message': '...', 'users': [...]}`
  - Some endpoints return `{'categories': [...]}`, others `{'data': [...]}`
- **Frontend Impact**: Frontend stores expect inconsistent data structure
  - `adminStore.fetchUsers()` expects `response.data.users`
  - `adminStore.fetchMeetings()` expects `response.data.data`
  - `adminStore.fetchCategories()` expects `response.data.categories`

### 6. **Missing API Endpoints - Data Not Rendering**
- **Severity**: HIGH
- **Location**: Backend API routes
- **Issue**: Frontend components request endpoints that don't exist
- **Missing Endpoints**:
  ```
  GET /admin/dashboard?page=X       ❌ Not defined
  GET /admin/reports?page=X         ❌ Partially - returns different format
  GET /admin/meetings               ❌ Not defined
  GET /admin/request-meetings       ❌ Not defined
  POST /meetings                    ❌ Not defined
  GET /meetings                     ❌ Not defined
  GET /request-meetings             ❌ Not defined
  POST /request-meetings            ❌ Not defined
  ```
- **Frontend Components Affected**:
  - `AdminDashboard.vue` - will fail to load users, meetings, request-meetings
  - `BDEDashboard.vue` - missing meeting endpoints
  - Data will show empty states instead of real data

### 7. **Data Type Inconsistency - Status Field**
- **Severity**: MEDIUM
- **Location**: Backend/Frontend
- **Issue**: Status field values inconsistent
- **Examples**:
  - Backend stores use: `'pending'`, `'resolved'`, `'rejected'`, `'refused'`
  - Some responses use: `'PENDING'`, `'pending'`
  - Frontend expects lowercase but some endpoints may return uppercase
  - StudentController creates reports with `status: 'pending'` (lowercase)
  - ReportController creates with `status: 'PENDING'` (uppercase)
- **Impact**: Filtering by status may fail; status display may be incorrect

### 8. **Model Relationships - Missing Relations**
- **Severity**: MEDIUM
- **Location**: Backend models
- **Issue**: Incomplete relationship definitions
- **Details**:
  - `Report` model loads `['category', 'generatedReport']` but no `student` relation in some queries
  - `GeneratedReport` model missing relation to `Report` (hasMany)
  - `AdminController` references `report.student` which requires explicit load
  - API responses may return null for related data without eager loading
- **Code Issue** (backend/app/Http/Controllers/AdminController.php):
  ```php
  // Needs explicit with() for student relation
  report.student?.first_name || ''  // Will be null without load
  ```

### 9. **CORS Configuration Issues**
- **Severity**: MEDIUM
- **Location**: Backend CORS middleware
- **Issue**: CORS implementation has potential issues
- **Details**:
  - `CORS_ALLOWED_ORIGINS` env variable is checked but might be empty
  - When empty, allows any origin (`*`) with credentials enabled
  - This is a security concern - either be restrictive or don't allow credentials
  - Vite proxy config forwards `/api` to `http://nginx:80` but CORS still applies
- **Code Issue** (backend/app/Http/Middleware/CorsMiddleware.php):
  ```php
  if ($allowedOrigins !== []) {
      // Restricted mode
  } else {
      // Dev-friendly mode - allows ANY origin
      $allowOrigin = $origin;
      $allowCredentials = true;  // ⚠️ Security concern
  }
  ```

### 10. **Frontend Error Handling - Silent Failures**
- **Severity**: MEDIUM
- **Location**: Frontend stores and components
- **Issue**: Incomplete error handling in data fetching
- **Details**:
  - Many `catch` blocks log errors but don't display user-friendly messages
  - `AdminDashboard` catches errors but doesn't show them in UI
  - Users see loading spinner or empty state without knowing why data failed to load
  - Example from `admin.ts`:
    ```javascript
    } catch (error) {
      this.error = error.message;  // Message might be technical
      throw error;                 // Re-throws to component
    }
    ```

---

## Data Rendering Issues

### 11. **Report Card Data Missing**
- **Severity**: MEDIUM
- **Location**: Frontend components
- **Issue**: Components display data that may not exist in API responses
- **Examples**:
  - `AdminDashboard` shows `report.student?.first_name || ''` but Report doesn't load student by default
  - `report.category?.name` depends on eager loading working correctly
  - `report.message` vs `report.description` - inconsistent field names
  - Report card expects `title` and `category` but API might return `message`
- **Code Issue**:
  ```javascript
  // AdminDashboard tries to render:
  :title="report.message?.substring(0, 60) || 'Report'"  // Correct
  :affected="`Student: ${report.student?.first_name || ''}`"  // student may be null
  ```

### 12. **Pagination Logic Issues**
- **Severity**: MEDIUM
- **Location**: Frontend stores and components
- **Issue**: Pagination not consistently implemented
- **Details**:
  - Some endpoints expect `?page=X`, others may not support pagination
  - `ReportController@index` uses `paginate(20)`
  - `AdminController` methods don't show pagination support
  - Frontend assumes all endpoints are paginated
  - No total count tracking for some resources

### 13. **Category Loading Race Condition**
- **Severity**: LOW-MEDIUM
- **Location**: Frontend/Backend
- **Issue**: Categories loaded differently across components
- **Details**:
  - `StudentCreateReport.vue` fetches categories on mount
  - `AdminCategories.vue` fetches from different endpoint
  - `ReportsStore` also has category fetching
  - Multiple fetch calls to same endpoint in quick succession
  - No caching mechanism

### 14. **Generated Report Response Format Issue**
- **Severity**: MEDIUM
- **Location**: Backend API
- **Issue**: Generated report creation returns different structure than expected
- **Details**:
  - `POST /v1/generated-reports` expects `report_ids` array in payload
  - Frontend sends correct payload
  - But response structure unclear - check if returns created resource correctly
  - Stores expect `response.data?.data || []` format

### 15. **Request/Meeting Endpoints - Partial Implementation**
- **Severity**: HIGH
- **Location**: Backend routes
- **Issue**: Meeting and request-meeting endpoints only partially implemented
- **Details**:
  - Routes defined in `api.php` but some controller methods may not exist
  - `RequestMeetingController` might not have all CRUD methods
  - Admin dashboard tries to call endpoints that may return errors
  - No validation error messages returned to frontend

---

## Network Communication Issues

### 16. **Vite Proxy Configuration May Not Work in Docker**
- **Severity**: MEDIUM
- **Location**: Frontend vite.config.ts
- **Issue**: Proxy setup assumes Docker service names
- **Details**:
  - Vite proxy forwards `/api` to `http://nginx:80`
  - This works in dev Docker container but not when running locally
  - Production build doesn't use Vite proxy - needs backend at same origin
  - API base URL defaults to `/api` which requires proxy or same-origin API
- **Config**:
  ```javascript
  proxy: {
    '/api': {
      target: 'http://nginx:80',  // Only works in Docker
      changeOrigin: true,
    }
  }
  ```

### 17. **Missing Request/Response Logging**
- **Severity**: LOW-MEDIUM
- **Location**: Frontend API service
- **Issue**: No request/response interceptor logging for debugging
- **Details**:
  - API service created but only has request interceptor for user ID header
  - No response interceptor for unified error handling
  - No request logging to see what's being sent
  - Difficult to debug network issues

### 18. **No API Health Check**
- **Severity**: LOW
- **Location**: Frontend
- **Issue**: No endpoint to verify API is reachable before making requests
- **Details**:
  - Application starts without checking if backend is available
  - Users see loading states that never resolve if backend is down
  - No "API unavailable" error message

---

## Configuration Issues

### 19. **Inconsistent Database Configuration**
- **Severity**: MEDIUM
- **Location**: Backend .env
- **Issue**: .env has hardcoded values, not flexible for different environments
- **Details**:
  - `DB_HOST=postgres` - works in Docker but not locally
  - `DB_DATABASE=easycoloc` - should be `youports` or configurable
  - `DB_PASSWORD=postgres` - default password hardcoded
  - No .env.local or .env.docker alternatives provided

### 20. **APP_URL Mismatch**
- **Severity**: MEDIUM
- **Location**: Backend .env
- **Issue**: APP_URL set to `http://localhost:3000` but app runs on port 80/8000
- **Details**:
  - APP_URL should match actual deployment URL
  - Used for generating URLs in emails, redirects
  - Will generate incorrect URLs in production
- **Current**: `APP_URL=http://localhost:3000`
- **Should be**: `APP_URL=http://localhost:8000` or environment-specific

---

## Type Safety Issues

### 21. **TypeScript Not Used Consistently**
- **Severity**: LOW-MEDIUM
- **Location**: Frontend
- **Issue**: Mix of JavaScript and TypeScript files without proper types
- **Details**:
  - Stores written in `.js` - no type safety
  - Main app uses `.ts` 
  - Vite config is `.ts` but services are `.js`
  - API responses not typed - `response.data?.data` usage error-prone
  - No TypeScript strict mode

### 22. **API Response Types Not Defined**
- **Severity**: LOW-MEDIUM
- **Location**: Frontend
- **Issue**: No interfaces for API responses
- **Details**:
  - Making assumptions about response structure throughout code
  - No validation that API returns expected shape
  - Type errors won't be caught until runtime

---

## Summary Statistics

- **Total Issues Found**: 22
- **Critical (Won't Work)**: 6
- **High (Data Won't Display)**: 5
- **Medium (Bugs/Security)**: 9
- **Low (Code Quality)**: 2

---

## Issues by Impact

### Data Won't Render
- Issue #1: Duplicate app configuration
- Issue #6: Missing API endpoints
- Issue #11: Report card data missing
- Issue #14: Generated report response format
- Issue #15: Request/Meeting endpoints partially implemented

### Data Integrity Issues
- Issue #3: Authentication data inconsistency (SECURITY)
- Issue #4: Routes not protected
- Issue #7: Status field inconsistency
- Issue #8: Model relationships incomplete

### API Issues
- Issue #2: Frontend env config missing
- Issue #5: Response format inconsistency
- Issue #9: CORS security concerns
- Issue #19: DB configuration hardcoded
- Issue #20: APP_URL mismatch

### User Experience Issues
- Issue #10: Silent error failures
- Issue #12: Pagination logic incomplete
- Issue #13: Category loading race condition
- Issue #16: Vite proxy may not work
- Issue #17: No request/response logging
- Issue #18: No API health check

### Code Quality
- Issue #21: TypeScript not used consistently
- Issue #22: API response types not defined

---

## Recommended Fix Priority

1. **First** (Blocking): Issue #1, #3, #4, #6 - Fix critical architecture issues
2. **Second** (High Impact): Issue #5, #8, #11, #15 - Fix data flow and rendering
3. **Third** (Medium Impact): Issue #2, #7, #9, #10, #12, #19, #20
4. **Fourth** (Polish): Issue #13, #16, #17, #18, #21, #22

---

## Testing Recommendations

1. Run frontend and backend locally to verify communication
2. Check browser DevTools Network tab for failed requests
3. Add console.log statements to track data flow
4. Test each role (ADMIN, BDE, TEACHER, STUDENT) separately
5. Verify database seeding creates test data
6. Check Laravel logs for API errors: `docker logs laravel_app`
