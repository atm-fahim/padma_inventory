# Module 01 — Architecture

## Pattern

**MVC + Repository**

- Controllers: HTTP, validation, session, view data  
- `Interfaces/Repo/Backend/*Repo.php`: contracts  
- `Repository/Backend/*RepoImpl.php`: queries & persistence  
- Bindings: `app/Providers/RepositoryServiceProvider.php`

## Cross-cutting

| Component | Role |
|-----------|------|
| `UserAccessTraits` | Menu slug → `access_menu` (view/add/edit/delete) |
| `CommonTraits` | HRM API token + employee JSON |
| `app/Support/*` | SET_UNIQ_NO, CSV mapper, stock number prefixes |

## Security

- Session login (`auth` middleware on most backend controllers)  
- CSRF on POST  
- Org scoping: `auth()->user()->org_id`

## Diagram

```
Controller → Repo Interface → RepoImpl → DB
         ↘ Support helpers
         ↘ Traits (HRM, access)
```

Full structure tree: [STRUCTURE.md](../STRUCTURE.md).
