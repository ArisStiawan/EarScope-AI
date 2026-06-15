# Admin Dashboard Route & Folder Structure Restructuring Plan

## 📋 Tujuan
1. Menyeragamkan struktur route admin dengan doctor untuk konsistensi
2. Membuat folder structure admin sama dengan doctor
3. Mengatasi masalah 404 errors akibat inconsistency
4. Meningkatkan maintainability

---

## 🔍 Analisis Current State

### Current Admin Routes
```
/dashboard                    → admin.dashboard
/doctors                       → admin.doctors
/doctors/create               → admin.doctors.create
/doctors/{id}/edit            → admin.doctors.edit
/doctors (POST)               → admin.doctors.store
/doctors/{id} (PATCH)         → admin.doctors.update
/doctors/{id} (DELETE)        → admin.doctors.delete
```

### Current Doctor Routes (Reference)
```
/doctor/dashboard             → doctor.dashboard
/doctor/consultations         → doctor.consultations
/doctor/patients-profile      → doctor.patients-profile
/doctor/consultation/{id}/details → consultation.details
/doctor/consultation/{id}/approve → consultation.approve
/doctor/consultation/{id}/reject  → consultation.reject
/doctor/consultation/{id}/schedule → consultation.schedule
```

### Current Folder Structure
```
admin/
├── dashboard.blade.php
├── layouts/
├── components/
│   └── stat-card.blade.php
└── doctors/
    ├── index.blade.php
    ├── create.blade.php
    ├── edit.blade.php
    └── partials/

doctor/
├── dashboard.blade.php
├── consultations.blade.php
├── patients-profile.blade.php
├── diagnoses.blade.php
├── modals/
│   ├── consultation-detail-modal.blade.php
│   └── schedule-modal.blade.php
└── (no explicit components folder, but reusable components in views)
```

### Problems Identified
1. ❌ Admin route prefix is `/` instead of `/admin/` - inconsistent with doctor
2. ❌ Folder structure differs: admin uses subfolder `doctors/` for CRUD, doctor uses flat structure
3. ❌ No dedicated `modals/` folder in admin views
4. ❌ No standardized components structure
5. ❌ Route naming convention differs

---

## ✅ Target Structure

### Target Admin Routes (Normalized)
```
/admin/dashboard              → admin.dashboard
/admin/doctors                → admin.doctors (list)
/admin/doctors/create         → admin.doctors.create
/admin/doctors/{id}           → admin.doctors.show
/admin/doctors/{id}/edit      → admin.doctors.edit
/admin/doctors (POST)         → admin.doctors.store
/admin/doctors/{id} (PATCH)   → admin.doctors.update
/admin/doctors/{id} (DELETE)  → admin.doctors.delete
```

### Target Folder Structure (Standardized)
```
admin/
├── dashboard.blade.php
├── layouts/
├── components/
│   └── stat-card.blade.php
├── doctors/
│   ├── index.blade.php
│   ├── show.blade.php (NEW)
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── partials/
├── modals/ (NEW)
│   └── (modals will be added here as needed)
└── (other admin features as they develop)

doctor/
├── dashboard.blade.php
├── consultations.blade.php
├── patients-profile.blade.php
├── diagnoses.blade.php
├── modals/
│   ├── consultation-detail-modal.blade.php
│   └── schedule-modal.blade.php
└── (matches admin structure pattern)
```

---

## 🔧 Implementation Steps

### Phase 1: Route Structure Refactoring
**Files to modify:** `routes/web.php`

#### Step 1.1: Add Admin Route Prefix
- Change admin middleware group to use `/admin/` prefix
- Update route names to maintain consistency (e.g., `admin.doctors.index` instead of `admin.doctors`)
- Update all PATCH routes to use `{doctor}` instead of `{id}` for resource convention

#### Step 1.2: Route Changes Summary
```php
// OLD
Route::middleware(['role:admin'])->group(function () { 
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard'); 
    Route::prefix('doctors')->group(function () { 
        // ... doctors routes
    });
});

// NEW
Route::middleware(['role:admin'])->prefix('admin')->group(function () { 
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard'); 
    
    Route::prefix('doctors')->name('admin.doctors.')->group(function () { 
        Route::get('/', [AdminController::class, 'indexDoctor'])->name('index');
        Route::get('create', [AdminController::class, 'createDoctor'])->name('create');
        Route::post('/', [AdminController::class, 'storeDoctor'])->name('store');
        Route::get('{doctor}/edit', [AdminController::class, 'editDoctor'])->name('edit');
        Route::patch('{doctor}', [AdminController::class, 'updateDoctor'])->name('update');
        Route::delete('{doctor}', [AdminController::class, 'deleteDoctor'])->name('delete');
    });
});
```

---

### Phase 2: Controller Updates
**Files to modify:** `app/Http/Controllers/AdminController.php`

#### Step 2.1: Update Method Parameters
- Change `$id` to `$doctor` in indexDoctor, editDoctor, updateDoctor, deleteDoctor methods
- Ensure methods return correct view paths with new structure

#### Step 2.2: Update Return View Paths
- Views should reference updated folder structure
- Example: `view('admin.doctors.index')` → `view('admin.doctors.index')`

---

### Phase 3: Folder Structure Reorganization
**Files to modify/create:** Blade templates in `resources/views/admin/`

#### Step 3.1: Create New Folders
```
resources/views/admin/modals/
```

#### Step 3.2: Ensure Components Folder Structure
```
admin/components/ (verify all reusable components)
├── stat-card.blade.php
└── (add more shared components as needed)
```

#### Step 3.3: Doctor CRUD Views Organization
Current:
```
admin/doctors/
├── index.blade.php
├── create.blade.php
├── edit.blade.php
└── partials/
```

Verify structure matches and update file paths if needed.

---

### Phase 4: View Updates
**Files to modify:** All Blade templates in `resources/views/admin/`

#### Step 4.1: Update Route References in Views
- Update all `route()` calls to use new route names
- Example: `route('admin.doctors')` → `route('admin.doctors.index')`

#### Step 4.2: Files to Update
- [x] `admin/dashboard.blade.php` - any doctor links
- [x] `admin/doctors/index.blade.php` - all CRUD links
- [x] `admin/doctors/create.blade.php` - form action routes
- [x] `admin/doctors/edit.blade.php` - form action routes
- [x] `admin/layouts/app.blade.php` or navigation - sidebar links

#### Step 4.3: Common Route Updates Pattern
```php
// OLD
route('admin.doctors')           → route('admin.doctors.index')
route('admin.doctors.create')    → route('admin.doctors.create')
route('admin.doctors.edit', $id) → route('admin.doctors.edit', $doctor)
route('admin.doctors.delete', $id) → route('admin.doctors.delete', $doctor)
route('admin.doctors.store')     → route('admin.doctors.store')
route('admin.doctors.update', $id) → route('admin.doctors.update', $doctor)
```

---

### Phase 5: Testing & Verification
**Files to test:**

#### Step 5.1: Route Testing
- [ ] GET `/admin/dashboard` - should display admin dashboard
- [ ] GET `/admin/doctors` - should display doctor list
- [ ] GET `/admin/doctors/create` - should display create form
- [ ] POST `/admin/doctors` - should store doctor
- [ ] GET `/admin/doctors/{id}/edit` - should display edit form
- [ ] PATCH `/admin/doctors/{id}` - should update doctor
- [ ] DELETE `/admin/doctors/{id}` - should delete doctor

#### Step 5.2: 404 Error Verification
- Ensure no broken links in navigation
- Test all CRUD operations
- Verify activity logs (if applicable)

#### Step 5.3: Middleware Testing
- Verify admin role middleware still works
- Test unauthorized access returns proper error

---

## 📊 Implementation Order

1. **First:** Update `routes/web.php` - add admin prefix and fix route names
2. **Second:** Update controller method parameters in `AdminController.php`
3. **Third:** Create/organize folder structure if needed
4. **Fourth:** Update all view files with new route references
5. **Fifth:** Run tests and fix any 404 errors

---

## 🎯 Expected Outcomes

✅ **Before:**
```
Routes: /dashboard, /doctors/* (inconsistent prefix)
Errors: Frequent 404s, inconsistent naming conventions
```

✅ **After:**
```
Routes: /admin/dashboard, /admin/doctors/* (consistent)
Structure: admin and doctor folders follow same pattern
Errors: No 404s, all routes properly namespaced
```

---

## 📝 Notes

- All route name changes must be tracked to update view references
- Use Laravel's route model binding for cleaner parameter handling
- Consider adding admin patients management in future (same structure)
- Keep naming conventions consistent across all roles (admin, doctor, patient)

---

## Changes Summary Table

| Component | Old | New | Status |
|-----------|-----|-----|--------|
| Route Prefix | `/` | `/admin/` | 📋 Pending |
| Route Names | `admin.doctors` | `admin.doctors.index` | 📋 Pending |
| Parameter Name | `{id}` | `{doctor}` | 📋 Pending |
| Folder Structure | Exists | Verify | 📋 Pending |
| View References | Old routes | New routes | 📋 Pending |
| Testing | - | All routes | 📋 Pending |
