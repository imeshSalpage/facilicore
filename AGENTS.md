# FaciliCore Developer & Agent Guidelines (AGENTS.md)

This document establishes the architecture, directory patterns, coding standards, and development workflows for **FaciliCore** to ensure strict quality control, compliance with SOLID principles, and maintainability.

---

## 1. Project Overview & Tenant Architecture

FaciliCore is a multi-sector, multi-tenant Facility & Resource Management SaaS. 

### Multi-Tenancy Architecture
- **Isolation Model**: Single-database multi-tenancy.
- **Scoping Mechanic**: Every query targeting tenant-specific tables must be scoped via the `TenantScope` query scope using the active `tenant_id`.
- **Tenant Context Binding**: The `TenantMiddleware` extracts the subdomain from the hostname (e.g., `acme.lvh.me` -> `acme`) and registers the corresponding `Tenant` model in the container.
- **Dynamic Session Cookies**: Wildcard session cookies are stored under `.lvh.me` (for development) and `.facilicore.com` (for production).
- **Same-Origin Reverse Proxy**: Next.js (frontend) and Laravel (backend) run behind an Nginx proxy mapping port 80. All authentication endpoints are prefixed with `/api` (e.g., `POST /api/register`, `POST /api/login`) to route cleanly through the Nginx proxy to Laravel.

---

## 2. SOLID Design Principles & Best Practices

To avoid monolithic code bloat, follow these object-oriented patterns:

### Single Responsibility Principle (SRP)
- **Controllers**: Keep them thin. Use them only to validate request parameters, call service components, and return JSON responses.
- **Services**: Place complex business logic (e.g., priority evaluation, booking conflict detection, ML forecasts) in dedicated classes inside `app/Services/`.

### Open/Closed Principle (OCP)
- **Sector Configuration (University, Healthcare, etc.)**: Use the **Strategy Pattern**. Define a `SectorStrategyInterface` and write separate implementations (e.g., `UniversityStrategy`, `HealthcareStrategy`) to resolve terminology and rules without editing core scheduling files.

### Liskov Substitution Principle (LSP)
- Ensure all concrete sector strategies, notification drivers, and forecasting providers can be interchanged safely by adhering strictly to their interface contracts.

### Interface Segregation Principle (ISP)
- Create targeted, highly-cohesive interfaces. For example, separate `SchedulableInterface` (for resources) from `MaintainableInterface` (for equipment requiring service).

### Dependency Inversion Principle (DIP)
- Depend on abstractions, not concretions. Inject interfaces into controllers and services, and bind them to concrete classes in `AppServiceProvider`.

---

## 3. Directory Layout Conventions

### Laravel Backend
- `app/Models/` — Database models (e.g., `Tenant`, `User`, `Resource`, `Booking`, `MaintenanceOrder`).
- `app/Http/Controllers/` — Thin endpoints grouped by domain.
- `app/Http/Middleware/` — Filters (e.g., `TenantMiddleware`).
- `app/Services/` — Business logic engines (e.g., `BookingManager`, `PriorityEngine`, `MaintenanceService`).
- `app/Strategies/` — Sector-specific strategies.
- `database/migrations/` — Structured schema definitions.

### Next.js Frontend
- `src/app/` — Next.js App Router folders.
  - `(auth)/` — Pages for guest registration, login, and email verification.
  - `(app)/` — Sidebar-protected pages (e.g., dashboard, resource grids, calendars).
- `src/hooks/` — Custom hooks (e.g., `auth.js` communicating with `/api/*`).
- `src/lib/` — Client-side helpers (e.g., `axios.js`, `subdomain.js`).
- `src/components/` — Reusable visual UI components (buttons, cards, forms).

---

## 4. Git Commit & Message Conventions

All commits must follow the **Conventional Commits** standard to facilitate automated changelog generation and clean version histories:

### Format:
```text
<type>(<scope>): <short description>

[Optional longer body detail]
```

### Types:
- `feat`: A new feature implementation (e.g., `feat(booking): implement conflict detection algorithm`).
- `fix`: A bug fix (e.g., `fix(auth): solve subdomain cookie validation fail`).
- `refactor`: Code changes that neither fix bugs nor add features (e.g., `refactor(priority): decouple weight calculations`).
- `docs`: Documentation updates (e.g., `docs(readme): update environment setup instructions`).
- `style`: Cosmetic, formatting, or layout edits.
- `test`: Adding or correcting tests.

---

## 5. Verification Checklist for Future Agents

Before marking any task as complete, future agents must:
1. Verify that all database queries on tenant tables are automatically filtered by `tenant_id`.
2. Run tests (e.g. `php artisan test`) and verify no compilation errors exist on either frontend or backend.
3. Test layout responsiveness and confirm cookie authorization states persist across subdomains.
4. Follow the existing git structure and maintain clean file links in all status walkthroughs.
