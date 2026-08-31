# FaciliCore Architecture & Technical Report: Figures 5.2 – 5.8

---

## 1. Figure 5.2: Complete Database Entity-Relationship Diagram (ERD) with Multi-Tenancy Keys

This diagram illustrates the complete relational schema design for FaciliCore's single-database multi-tenant architecture. Every tenant-specific table contains foreign key `tenant_id` linked to `tenants.id` and is automatically scoped by Eloquent's `TenantScope`.

```mermaid
erDiagram
    TENANTS ||--o{ USERS : "owns"
    TENANTS ||--o{ FACILITIES : "contains"
    TENANTS ||--o{ RESOURCE_CATEGORIES : "configures"
    TENANTS ||--o{ RESOURCES : "maintains"
    TENANTS ||--o{ COMPOSITE_BOOKINGS : "scopes"
    TENANTS ||--o{ COMPOSITE_BOOKING_TEMPLATES : "defines"
    TENANTS ||--o{ BOOKINGS : "manages"
    TENANTS ||--o{ MAINTENANCE_ORDERS : "schedules"
    TENANTS ||--o{ PREDICTIVE_MAINTENANCE_LOGS : "monitors"
    TENANTS ||--o{ AUDIT_LOGS : "tracks"

    USERS ||--o{ BOOKINGS : "requests"
    USERS ||--o{ COMPOSITE_BOOKINGS : "initiates"
    USERS ||--o{ MAINTENANCE_ORDERS : "assigned_to"
    USERS ||--o{ AUDIT_LOGS : "performs"
    USERS ||--o{ USERS : "approves (approved_by)"

    FACILITIES ||--o{ RESOURCES : "houses"
    RESOURCE_CATEGORIES ||--o{ RESOURCES : "classifies"
    RESOURCE_CATEGORIES ||--o{ COMPOSITE_BOOKING_TEMPLATE_ITEMS : "requires"

    RESOURCES ||--o{ BOOKINGS : "reserved_in"
    RESOURCES ||--o{ COMPOSITE_BOOKING_ITEMS : "allocated_to"
    RESOURCES ||--o{ MAINTENANCE_ORDERS : "undergoes"
    RESOURCES ||--o{ PREDICTIVE_MAINTENANCE_LOGS : "diagnosed_in"

    COMPOSITE_BOOKING_TEMPLATES ||--o{ COMPOSITE_BOOKING_TEMPLATE_ITEMS : "specifies"
    COMPOSITE_BOOKINGS ||--o{ COMPOSITE_BOOKING_ITEMS : "comprises"
    COMPOSITE_BOOKINGS ||--o{ BOOKINGS : "coordinates"

    TENANTS {
        bigint id PK
        string name
        string subdomain UK
        enum sector "university, healthcare, corporate, government"
        json settings
        timestamp created_at
        timestamp updated_at
    }

    USERS {
        bigint id PK
        bigint tenant_id FK
        string name
        string email UK
        string registration_number "Student ID / Employee No"
        string department
        string phone
        string password
        enum role "admin, supervisor, end_user"
        enum approval_status "pending, approved, rejected"
        timestamp approved_at
        bigint approved_by FK
        text rejection_reason
        timestamp email_verified_at
        timestamp created_at
        timestamp updated_at
    }

    FACILITIES {
        bigint id PK
        bigint tenant_id FK
        string name
        string location "Block / Campus Area"
        text description
        timestamp created_at
        timestamp updated_at
    }

    RESOURCE_CATEGORIES {
        bigint id PK
        bigint tenant_id FK
        string name "e.g. Operating Theatres, Computer Labs"
        text description
        timestamp created_at
        timestamp updated_at
    }

    RESOURCES {
        bigint id PK
        bigint tenant_id FK
        bigint facility_id FK
        bigint category_id FK
        string name
        text description
        json specifications
        int capacity
        enum status "active, maintenance, retired"
        string image_url
        timestamp created_at
        timestamp updated_at
    }

    BOOKINGS {
        bigint id PK
        bigint tenant_id FK
        bigint resource_id FK
        bigint user_id FK
        bigint composite_booking_id FK "nullable"
        datetime start_at
        datetime end_at
        enum status "pending, confirmed, cancelled, completed"
        text notes
        int priority "1-10"
        boolean urgency "urgency override boost"
        decimal priority_score
        timestamp created_at
        timestamp updated_at
    }

    COMPOSITE_BOOKING_TEMPLATES {
        bigint id PK
        bigint tenant_id FK
        string name "e.g., Surgery Package, Research Seminar"
        text description
        timestamp created_at
        timestamp updated_at
    }

    COMPOSITE_BOOKING_TEMPLATE_ITEMS {
        bigint id PK
        bigint template_id FK
        bigint category_id FK
        int quantity
        timestamp created_at
        timestamp updated_at
    }

    COMPOSITE_BOOKINGS {
        bigint id PK
        bigint tenant_id FK
        bigint user_id FK
        string title
        datetime start_at
        datetime end_at
        enum status "pending, confirmed, cancelled, failed"
        timestamp created_at
        timestamp updated_at
    }

    COMPOSITE_BOOKING_ITEMS {
        bigint id PK
        bigint composite_booking_id FK
        bigint resource_id FK
        bigint booking_id FK
        timestamp created_at
        timestamp updated_at
    }

    MAINTENANCE_ORDERS {
        bigint id PK
        bigint tenant_id FK
        bigint resource_id FK
        bigint assigned_to FK "nullable User"
        enum priority "low, medium, high, critical"
        enum status "scheduled, in_progress, completed, cancelled"
        text description
        datetime scheduled_start
        datetime scheduled_end
        timestamp completed_at
        text resolution_notes
        timestamp created_at
        timestamp updated_at
    }

    PREDICTIVE_MAINTENANCE_LOGS {
        bigint id PK
        bigint tenant_id FK
        bigint resource_id FK
        decimal stress_factor "0.00 - 1.00"
        int total_usage_hours
        int booking_frequency_30d
        boolean anomaly_detected
        text diagnostic_details
        timestamp created_at
        timestamp updated_at
    }

    AUDIT_LOGS {
        bigint id PK
        bigint tenant_id FK
        bigint user_id FK "nullable"
        string event "e.g. user_approved, booking_override"
        string auditable_type
        bigint auditable_id
        json old_values
        json new_values
        string ip_address
        timestamp created_at
    }
```

---

## 2. Figure 5.3: Comprehensive System Use Case Diagram across User Roles

This diagram captures user interactions with FaciliCore across all four primary actors: **Central Superadmin**, **Workspace Administrator**, **Operational Supervisor**, and **Regular Tenant User (End User)**.

```mermaid
graph TD
    %% Actors
    CentralAdmin(["👤 Central Superadmin"]):::actor
    WorkspaceAdmin(["👤 Workspace Admin"]):::actor
    Supervisor(["👤 Supervisor / Dept Head"]):::actor
    EndUser(["👤 Regular Tenant User"]):::actor

    %% Central Admin Use Cases
    subgraph Central_Management ["Central Platform Governance"]
        UC_CentralRegister["Register New Tenant Workspace"]:::usecase
        UC_TenantMonitor["Monitor Multi-Tenant Health"]:::usecase
        UC_SectorAssign["Assign Sector Strategy Configuration"]:::usecase
    end

    %% Workspace Admin Use Cases
    subgraph Admin_Console ["Workspace Administration"]
        UC_ApproveUsers["Review & Approve Tenant User Registrations"]:::usecase
        UC_ManageRoles["Promote User Roles & Permissions"]:::usecase
        UC_FacilityManage["Manage Campus Facilities & Building Blocks"]:::usecase
        UC_ResourceManage["Configure Physical Resources & Categories"]:::usecase
        UC_PriorityWeights["Tune Sector Priority Engine Weights"]:::usecase
        UC_PredictiveScans["Trigger ML Predictive Anomaly Scans"]:::usecase
        UC_AuditReview["Inspect Tenant Security Audit Logs"]:::usecase
    end

    %% Supervisor Use Cases
    subgraph Supervisor_Console ["Operational Supervision"]
        UC_ApproveBookings["Evaluate Pending Approval Bookings"]:::usecase
        UC_MaintenanceSchedule["Schedule Resource Work Orders"]:::usecase
        UC_AnalyticsView["Analyze Occupancy & Utilization Telemetry"]:::usecase
        UC_Forecasting["View ML Occupancy Forecast Models"]:::usecase
    end

    %% End User Use Cases
    subgraph Mobile_Companion ["End User Booking Portal"]
        UC_Register["Submit Tenant Registration (with ID & Dept)"]:::usecase
        UC_Browse["Browse Campus Directory & Resources"]:::usecase
        UC_SingleBook["Create Single Resource Reservation"]:::usecase
        UC_CompositeBook["Execute Multi-Resource Composite Bundle"]:::usecase
        UC_ViewPasses["Access Digital Passes & QR Check-in"]:::usecase
        UC_Notifications["Receive Real-Time Status Notifications"]:::usecase
    end

    %% Relationships
    CentralAdmin --> UC_CentralRegister
    CentralAdmin --> UC_TenantMonitor
    CentralAdmin --> UC_SectorAssign

    WorkspaceAdmin --> UC_ApproveUsers
    WorkspaceAdmin --> UC_ManageRoles
    WorkspaceAdmin --> UC_FacilityManage
    WorkspaceAdmin --> UC_ResourceManage
    WorkspaceAdmin --> UC_PriorityWeights
    WorkspaceAdmin --> UC_PredictiveScans
    WorkspaceAdmin --> UC_AuditReview
    WorkspaceAdmin --> UC_ApproveBookings
    WorkspaceAdmin --> UC_AnalyticsView

    Supervisor --> UC_ApproveBookings
    Supervisor --> UC_MaintenanceSchedule
    Supervisor --> UC_AnalyticsView
    Supervisor --> UC_Forecasting
    Supervisor --> UC_Browse

    EndUser --> UC_Register
    EndUser --> UC_Browse
    EndUser --> UC_SingleBook
    EndUser --> UC_CompositeBook
    EndUser --> UC_ViewPasses
    EndUser --> UC_Notifications

    %% System Includes
    UC_SingleBook -.->|<<includes>>| UC_Notifications
    UC_CompositeBook -.->|<<includes>>| UC_SingleBook
    UC_PredictiveScans -.->|<<triggers>>| UC_MaintenanceSchedule
    UC_ApproveUsers -.->|<<includes>>| UC_AuditReview

    classDef actor fill:#f8fafc,stroke:#334155,stroke-width:2px;
    classDef usecase fill:#eef2ff,stroke:#4f46e5,stroke-width:1.5px,rx:15,ry:15;
```

---

## 3. Figure 5.4: Domain Class Diagram Illustrating Strategy & Service Pattern Relationships

This diagram demonstrates FaciliCore's domain structure following **SOLID principles**:
- **Strategy Pattern (Open/Closed Principle & Liskov Substitution)** for sector adaptivity (`SectorStrategyInterface`).
- **Service Layer (Single Responsibility Principle)** for business rules (`BookingManager`, `PriorityEngine`, `CompositeBookingService`, `PredictiveMaintenanceService`).
- **Dependency Inversion Principle** through service interfaces.

```mermaid
classDiagram
    %% Core Interfaces
    class SectorStrategyInterface {
        <<interface>>
        +getSectorKey() string
        +getTerminology() array
        +getDefaultPriorityWeights() array
        +getValidationRules() array
        +getRequiredRegistrationFields() array
    }

    %% Concrete Strategies
    class UniversityStrategy {
        +getSectorKey() string
        +getTerminology() array
        +getDefaultPriorityWeights() array
        +getValidationRules() array
        +getRequiredRegistrationFields() array
    }

    class HealthcareStrategy {
        +getSectorKey() string
        +getTerminology() array
        +getDefaultPriorityWeights() array
        +getValidationRules() array
        +getRequiredRegistrationFields() array
    }

    class CorporateStrategy {
        +getSectorKey() string
        +getTerminology() array
        +getDefaultPriorityWeights() array
        +getValidationRules() array
        +getRequiredRegistrationFields() array
    }

    class GovernmentStrategy {
        +getSectorKey() string
        +getTerminology() array
        +getDefaultPriorityWeights() array
        +getValidationRules() array
        +getRequiredRegistrationFields() array
    }

    SectorStrategyInterface <|.. UniversityStrategy
    SectorStrategyInterface <|.. HealthcareStrategy
    SectorStrategyInterface <|.. CorporateStrategy
    SectorStrategyInterface <|.. GovernmentStrategy

    %% Core Services
    class PriorityEngine {
        -SectorStrategyInterface sectorStrategy
        +calculateScore(User user, Resource resource, Carbon start, Carbon end, int userPriority, bool urgency) float
        +evaluateConflictResolution(Booking existing, Booking incoming) bool
    }

    class BookingManager {
        -PriorityEngine priorityEngine
        +hasConflict(int resourceId, Carbon start, Carbon end, int excludeId) bool
        +getConflicts(int resourceId, Carbon start, Carbon end, int excludeId) Collection
        +suggestAlternatives(int resourceId, Carbon preferredStart, Carbon preferredEnd) array
        +createBooking(array data, User user) Booking
    }

    class CompositeBookingService {
        -BookingManager bookingManager
        +createCompositeBooking(User user, string title, Carbon start, Carbon end, array resourceAllocations) CompositeBooking
        +suggestBundleAlternatives(array resourceIds, Carbon preferredStart, Carbon preferredEnd) array
    }

    class PredictiveMaintenanceService {
        -MaintenanceService maintenanceService
        +calculateStressFactor(Resource resource) float
        +runAnomalyScan(Tenant tenant) array
        +autoScheduleWorkOrder(Resource resource, float stressScore) MaintenanceOrder
    }

    class MaintenanceService {
        +createOrder(int resourceId, array data) MaintenanceOrder
        +completeOrder(MaintenanceOrder order, string notes) bool
    }

    class ForecastService {
        +generateOccupancyForecast(int resourceId, int daysAhead) array
        +calculateDemandCoefficient(Resource resource, Carbon targetDate) float
    }

    %% Controllers
    class BookingController {
        -BookingManager bookingManager
        -PriorityEngine priorityEngine
        +index(Request request) JsonResponse
        +store(Request request) JsonResponse
        +cancel(Booking booking) JsonResponse
    }

    class CompositeBookingController {
        -CompositeBookingService compositeService
        +store(Request request) JsonResponse
    }

    class PredictiveMaintenanceController {
        -PredictiveMaintenanceService predictiveService
        +scan(Request request) JsonResponse
    }

    %% Service Associations
    BookingController --> BookingManager
    BookingController --> PriorityEngine
    CompositeBookingController --> CompositeBookingService
    PredictiveMaintenanceController --> PredictiveMaintenanceService
    PredictiveMaintenanceService --> MaintenanceService
    CompositeBookingService --> BookingManager
    BookingManager --> PriorityEngine
    PriorityEngine --> SectorStrategyInterface
```

---

## 4. Figure 5.5: Sequence Diagram for Atomic Composite Multi-Resource Reservation

This diagram details the transaction workflow for reserving multiple resources (e.g. Operating Theatre + MRI Scanner + Recovery Bay) atomically using database transactions (`DB::transaction`).

```mermaid
sequenceDiagram
    autonumber
    actor User as 👤 End User
    participant Frontend as 📱 Next.js Client
    participant Controller as ⚙️ CompositeBookingController
    participant Service as 🧩 CompositeBookingService
    participant Manager as 📅 BookingManager
    participant DB as 🗄️ Database (MySQL)
    participant Notification as 🔔 NotificationService

    User->>Frontend: Selects Bundle Template & Time Window
    Frontend->>Controller: POST /api/composite-bookings {title, start_at, end_at, resources: [R1, R2, R3]}
    Controller->>Service: createCompositeBooking(user, title, start_at, end_at, resourceAllocations)
    
    Service->>DB: DB::beginTransaction()
    
    loop For Each Resource Allocated (R1, R2, R3)
        Service->>Manager: hasConflict(resource_id, start_at, end_at)
        Manager->>DB: SELECT * FROM bookings WHERE resource_id = ? AND status IN ('confirmed','pending') AND start_at < end_at AND end_at > start_at
        DB-->>Manager: Conflict Query Result
        
        alt Conflict Detected on any Resource
            Manager-->>Service: Conflict Found (Resource R_i)
            Service->>Manager: suggestBundleAlternatives([R1, R2, R3], start_at, end_at)
            Manager-->>Service: Suggested Alternate Free Windows
            Service->>DB: DB::rollBack()
            Service-->>Controller: Throws BookingConflictException (409 + Alternatives)
            Controller-->>Frontend: 409 Conflict JSON {message, alternatives}
            Frontend-->>User: Display Conflict Warning & Smart Slot Suggestions
        end
    end

    Note over Service,DB: All resources free — proceed with atomic allocation

    Service->>DB: INSERT INTO composite_bookings (tenant_id, user_id, title, start_at, end_at, status='confirmed')
    DB-->>Service: Created CompositeBooking #CB_100

    loop For Each Resource
        Service->>DB: INSERT INTO bookings (tenant_id, resource_id, user_id, composite_booking_id, start_at, end_at, status='confirmed')
        Service->>DB: INSERT INTO composite_booking_items (composite_booking_id, resource_id, booking_id)
    end

    Service->>DB: DB::commit()
    Service->>Notification: dispatch(new CompositeBookingConfirmedNotification($compositeBooking))
    Notification-->>User: Real-Time In-App Alert & Email Confirmation
    Service-->>Controller: CompositeBooking Model
    Controller-->>Frontend: 201 Created JSON {composite_booking}
    Frontend-->>User: Render Confirmed Digital Reservation Pass
```

---

## 5. Figure 5.6: Sequence Diagram for Context-Aware Priority Conflict Evaluation & Preemption

This diagram demonstrates how FaciliCore evaluates resource contention through multi-variable priority scoring and executes preemption when critical requests arise.

```mermaid
sequenceDiagram
    autonumber
    actor IncomingUser as 👤 Incoming Applicant (e.g. Lead Surgeon / Emergency)
    participant API as ⚙️ BookingController
    participant Manager as 📅 BookingManager
    participant PriorityEngine as 🧠 PriorityEngine
    participant Strategy as 🎯 SectorStrategy
    participant DB as 🗄️ MySQL Database
    participant Audit as 📜 AuditLogger
    participant Notif as 🔔 Notification Engine

    IncomingUser->>API: POST /api/bookings {resource_id, start_at, end_at, priority=1, urgency=true}
    API->>Manager: createBooking(payload, incomingUser)
    Manager->>DB: getConflicts(resource_id, start_at, end_at)
    DB-->>Manager: Existing Conflicting Booking #B_45 (Booked by User B, Priority=6)

    Manager->>PriorityEngine: evaluateConflictResolution(existingBooking, incomingRequest)
    
    PriorityEngine->>Strategy: getDefaultPriorityWeights()
    Strategy-->>PriorityEngine: Weights {role_weight: 0.4, urgency_weight: 0.3, lead_time_weight: 0.2, demand_weight: 0.1}

    PriorityEngine->>PriorityEngine: Calculate Score(Incoming): Score = 92.5
    PriorityEngine->>PriorityEngine: Calculate Score(Existing): Score = 41.0

    alt Incoming Score > Existing Score + Threshold (Preemption Approved)
        PriorityEngine-->>Manager: Resolution: PREEMPT_ALLOWED
        Manager->>DB: DB::beginTransaction()
        Manager->>DB: UPDATE bookings SET status='cancelled', notes='Preempted by urgent priority request #B_NEW' WHERE id=45
        Manager->>DB: INSERT INTO bookings (resource_id, user_id, start_at, end_at, status='confirmed', priority_score=92.5)
        Manager->>Audit: log('booking_preemption', {preempted_id: 45, new_id: 99, reason: 'High Urgency Score'})
        Manager->>DB: DB::commit()
        
        Manager->>Notif: notifyPreemptedUser(User B, "Your booking #45 was rescheduled due to an emergency.")
        Notif-->>ExistingUser: Alert: Booking Rescheduled / Alternatives Offered
        
        Manager-->>API: 201 Created (Booking Confirmed with Preemption)
        API-->>IncomingUser: 201 Created {booking_id: 99, status: 'confirmed'}
    else Incoming Score <= Existing Score (Preemption Rejected)
        PriorityEngine-->>Manager: Resolution: PREEMPT_DENIED
        Manager->>Manager: suggestAlternatives(resource_id, start_at, end_at)
        Manager-->>API: 409 Conflict + Alternatives
        API-->>IncomingUser: 409 Conflict {message: "Resource occupied by equal or higher priority booking", alternatives: [...]}
    end
```

---

## 6. Figure 5.7: Activity Diagram for Predictive Maintenance Anomaly Detection Workflow

This diagram models the automated ML predictive maintenance workflow executed by the cron scheduler (`App\Console\Commands\RunPredictiveMaintenanceScan`).

```mermaid
stateDiagram-v2
    [*] --> ScheduledCronTrigger: Daily / Automated Trigger

    state ScheduledCronTrigger {
        [*] --> FetchActiveTenants
        FetchActiveTenants --> IterateTenants
    }

    state AnomalyDetectionProcess {
        IterateTenants --> QueryTenantResources: Load Active Resources
        QueryTenantResources --> CalculateUtilization: Aggregate Usage Hours & Booking Frequency (Past 30 Days)
        CalculateUtilization --> ComputeStressScore: Calculate Stress Factor = f(UsageHours, Frequency, PeakLoad)
        
        state StressDecision <<choice>>
        ComputeStressScore --> StressDecision

        StressDecision --> NormalState: Stress Factor < 0.75
        StressDecision --> AnomalyDetected: Stress Factor >= 0.75

        NormalState --> LogTelemetry: Record Metric Baseline
        AnomalyDetected --> CheckExistingOrder: Check for Active Maintenance Orders

        state OrderDecision <<choice>>
        CheckExistingOrder --> OrderDecision
        OrderDecision --> SkipOrderCreation: Active Order Already Scheduled
        OrderDecision --> GenerateWorkOrder: No Pending Work Order

        GenerateWorkOrder --> InsertMaintenanceRecord: Create Work Order (Status='scheduled', Priority='high')
        InsertMaintenanceRecord --> SetResourceMaintenanceStatus: Resource Status -> 'maintenance'
        SetResourceMaintenanceStatus --> DispatchAdminAlerts: Send Notification to Facility Supervisors
        DispatchAdminAlerts --> LogTelemetry: Record Diagnostic Metric
    }

    LogTelemetry --> NextResource
    NextResource --> IterateTenants: More Resources Exist
    NextResource --> CompleteScan: All Resources Evaluated

    CompleteScan --> [*]
```

---

## 7. Figure 5.8: UI Layout Architecture of the Admin Advanced Settings Console

This diagram depicts the frontend layout architecture of the **Administrator Workspace Settings Console** (`src/app/(app)/settings/page.js`), showcasing the separation between platform settings, sector customizer, user approval queues, and security token controls.

```mermaid
graph TD
    subgraph Browser_Viewport ["FaciliCore Admin Settings Console (/settings)"]
        
        subgraph Top_Header ["Console Banner & Identity Header"]
            Logo["🏢 FaciliCore Tenant Emblem"]
            WorkspaceTitle["Workspace: Apex University (acme.facilicore.me)"]
            SectorBadge["Sector Badge: 🎓 Higher Education / University"]
            QuickStats["KPI Counters: Total Members (42) | Pending Approvals (3) | Active Resources (18)"]
        end

        subgraph Navigation_Tabs ["Interactive Subsystem Navigation Tabs"]
            Tab1["⚙️ General Info & Branding"]
            Tab2["🎯 Sector Engine & Terminology Overrides"]
            Tab3["👥 User Directory & Approval Queue"]
            Tab4["🔒 Security & API Integration Tokens"]
        end

        subgraph Active_Tab_Panels ["Dynamic Tab Content Viewport"]
            
            subgraph Panel_General ["Panel 1: General Info & Branding"]
                G1["Workspace Display Name Input"]
                G2["Subdomain Identifier (Read-Only)"]
                G3["Notification Email & Contact Settings"]
                G4["Save General Settings Action Button"]
            end

            subgraph Panel_Sector ["Panel 2: Sector Engine & Terminology"]
                S1["Active Sector Strategy Selector (University / Healthcare / Corporate / Government)"]
                S2["Custom Terminology Override Key-Value Grid (Facility -> Hospital Wing, Resource -> Lab Bench)"]
                S3["Priority Engine Weight Tuner (Role Weight, Urgency Weight, Demand Factor)"]
                S4["Save Strategy Configuration Button"]
            end

            subgraph Panel_Users ["Panel 3: User Directory & Approval Queue"]
                U1["Pending User Approval Queue Table (Applicant Name, Email, Student/Employee ID, Department, Phone)"]
                U2["Action Triggers: [Approve Member ✅] | [Decline Application ❌ Modal]"]
                U3["Active Member Directory Table with Role Selector Dropdowns (Admin, Supervisor, End User)"]
                U4["Sole Administrator Protection Validator"]
            end

            subgraph Panel_Security ["Panel 4: Security & Integrations"]
                SEC1["API Access Token Generator"]
                SEC2["Session Timeout & Cookie Domain Scoping (.facilicore.me)"]
                SEC3["Audit Log Direct Link"]
            end

        end

        subgraph Persistent_Footer ["Platform Audit & Context Indicator"]
            FooterAudit["Authenticated as: Workspace Admin (Super) · Isolated Database Scope: Tenant ID #6"]
        end

    end

    Top_Header --> Navigation_Tabs
    Navigation_Tabs --> Active_Tab_Panels
    Tab1 -.-> Panel_General
    Tab2 -.-> Panel_Sector
    Tab3 -.-> Panel_Users
    Tab4 -.-> Panel_Security
    Active_Tab_Panels --> Persistent_Footer

    classDef headerBox fill:#1e1b4b,color:#fff,stroke:#4338ca,stroke-width:2px;
    classDef tabBox fill:#f1f5f9,color:#0f172a,stroke:#94a3b8,stroke-width:1px;
    classDef panelBox fill:#ffffff,color:#1e293b,stroke:#cbd5e1,stroke-width:1.5px;
    
    class Top_Header headerBox;
    class Navigation_Tabs tabBox;
    class Active_Tab_Panels panelBox;
```
