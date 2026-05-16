# Research: Architecture Refactoring to Isolated Tenancy

## Decisions
- **Decision**: Adopt "Database-per-client" (Isolated Tenant) model instead of "Column-based" (Branch filtering) for the primary ERP isolation.
- **Rationale**: Physical isolation provides maximum security, easier backups per client, and avoids data leakage risks inherent in shared tables. It also allows for client-specific database scaling.
- **Alternatives considered**: 
  - **Shared Database with RLS**: Rejected because it requires complex policy management and doesn't offer the same backup/restore isolation as physical databases.
  - **Column-based filtering (branch_id)**: Rejected for the core multi-tenancy, but may still be used INSIDE a single tenant's database for internal branches.

## Technical Context Resolution
- **Multi-connection**: Use Laravel's standard multi-connection system. `central` for metadata, `tenant` for client data.
- **Dynamic Configuration**: Tenant connection is configured at runtime via middleware based on `Cliente` credentials stored in the `central` database.
- **Testing**: Use SQLite in-memory/file for testing isolation without requiring a full Supabase stack locally.

## Constitution Alignment
- **Violation**: Principle 1 mandates `branch_id` filtering.
- **Justification**: Physical isolation IS a stricter form of multi-tenancy. The constitution needs to be updated to formalize this architectural shift as the "Core Isolation Model", while keeping `branch_id` for internal filial separation *within* a tenant.
