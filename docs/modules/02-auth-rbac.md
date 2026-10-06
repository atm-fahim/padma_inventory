# Module 02 — Authentication & RBAC

## Login

| Item | Detail |
|------|--------|
| Routes | `/login`, `Auth::routes(['register' => false])`, `/logout` |
| Controller | `App\Http\Controllers\Auth\LoginController` |
| Views | `resources/views/auth/*` |

## Users

| Route | Action |
|-------|--------|
| `/users` | List / manage users |
| `/user-save-update` | Create/update |
| `/user-edit/{id}`, `/user-delete/{id}` | Edit/delete |
| `/user-active/{id}`, `/user-in-active/{id}` | Status |

**Controller:** `UsersController`  
**Repo:** `UserRepo` / `UserRepoImpl`

## User types

| Route | Controller |
|-------|------------|
| `/user-type` | `UsersTypeController` |

Field **`user_types`** on user links to permission rows.

## Menu permission

| Route | Controller |
|-------|------------|
| `/permission` | `UserPermissionController` |
| `/show-permission-menu/{permission_type}` | Menu tree for type |
| `/give-permission` | Save `access_menu` |

**Tables:** `backend_menu`, `access_menu`

## Backend menu CRUD

| Route | Controller |
|-------|------------|
| `/backend-menu` | `MenuController` |

Menu **`slug`** must match first URL segment for `UserAccessTraits::userAccessFunction()` (see trait for IP-based segment index).

## Task login

| Route | `/task-login` — `TaskLoginController` |
