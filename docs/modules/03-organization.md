# Module 03 — Organization Structure

## Organization

| Route | Controller |
|-------|------------|
| `/organization` | `OrganizationController` |

**Repo:** `OrganizationRepo` / `OrganizationRepoImpl`

## Branch

| Route | Controller |
|-------|------------|
| `/branch` | `BranchController` |

**Important fields:** `hr_branch_id` (maps to HRM branch), `branch_name`, `status`

Used by: inventory scoping, CSV upload branch select, stock reference prefixes.

## Division

| Route | Controller |
|-------|------------|
| `/division` | `DivisionController` |

## Department

| Route | Controller |
|-------|------------|
| `/department` | `DepartmentController` |
| `/department-by-division-id/{id}` | AJAX departments |

**Repo:** `DivisionRepo`, `DepartmentRepo`

## Data relationships (logical)

```
organization (org_id)
    └── branch (branch_id / hr_branch_id)
            └── division
                    └── department
```

Inventory rows carry `org_id`, `branch_id`; movements may store `from_*` / `to_*` branch/division/employee.
