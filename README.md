# MarsC Insurance Platform

A motor insurance management system built with **Laravel 13**, **Blade**, and **Tailwind CSS v4**.

MarsC models the full lifecycle of a motor insurance policy in Kenya — from client onboarding through claims processing. It's a demonstration project, not a production system, but it encodes real Kenyan insurance rules and shows how they map to code.

---

## What It Does

**Client onboarding** — captures identity, contact, and consent data. Includes IPRS and NTSA verification buttons that call a mock external service and auto-fill the form.

**Vehicle onboarding** — registers vehicles with NTSA TIMS verification. Vehicle identity fields (registration, chassis, logbook, declared value) are immutable after creation.

**Vehicle drivers** — supports multiple drivers per vehicle with NTSA licence class validation and a single-primary-driver invariant.

**Valuations** — underwriters record the assessor's findings (Actual Cash Value and Forced Sale Value). A vehicle is eligible for a policy once valued.

**Premium calculation** — three pricing models:
- **Comprehensive** — 4% of the vehicle's ACV
- **TPO (Third Party Only)** — fixed floor of KES 5,000
- **TPTF (Third Party Fire & Theft)** — floor + 1% of ACV

All types apply a risk multiplier (1.0 private, 1.4 commercial, 1.8 PSV), plus statutory levies (Training Levy 0.2%, PHCF 0.25%, Stamp Duty KES 40), plus optional add-ons.

**Policy issuance** — creates the policy, snapshots add-on prices, flips the vehicle to Insured, and sends an email notification.

**Endorsements** — mid-term changes (cover type, add-ons, drivers) with pro-rata premium adjustments. The policy number stays the same; the change is recorded as a formal endorsement.

**Cancellations** — two-step IRA-compliant workflow: cover stops, then the certificate must be surrendered within 7 days. Refunds are calculated on a short-period basis.

**Quotations** — priced offers for prospects who aren't yet clients. Reuses the same premium calculator. Sends an email when marked as sent.

**Claims** — full state machine: `Submitted → Acknowledged → UnderReview → Assessed → Approved → Paid`. Requires specific documents before review. Fraud rejections require documented evidence.

**Role-based access control** — five roles (Admin, Underwriter, Claims Officer, Agent) with route-level enforcement.

---

## Tech Stack

- **Laravel 13** (PHP 8.3+)
- **Blade** — server-rendered views
- **Tailwind CSS v4**
- **Pest** — testing framework
- **Laravel Breeze** — authentication
- **SQLite** — local database
- **Express (Node.js)** — mock server for IPRS and NTSA
- **GitHub Actions** — CI pipeline

---



Architecture
The app follows a layered architecture:
Controllers → FormRequests → Services → Models → Database

Controllers are thin — they validate, delegate, and redirect.

FormRequests handle validation and authorization.

Services own the business logic (premium calculation, KYC, issuance, endorsements, claims).

Models own the domain rules — casts, relationships, immutability guards, status transitions.

Enums define every fixed set of values (statuses, types, roles).

What's Done
✅ Client onboarding with IPRS + NTSA verification
✅ Vehicle onboarding with NTSA TIMS verification
✅ Vehicle driver management with DL class validation
✅ Valuation recording with vehicle status transition
✅ Premium calculator for all three policy types
✅ Policy issuance with add-on price snapshots
✅ Endorsements with pro-rata adjustments
✅ Cancellations with certificate surrender workflow
✅ Quotations with email notification
✅ Claims state machine with document guard
✅ Role-based access control
✅ Event-driven email notifications
✅ GitLab Issues tracking

What's Not Done
These are deliberate MVP gaps, not oversights. Each is a documented next step.

Quote → policy conversion — the pieces exist (a policies.quote_id column, an markAsAccepted() method), but the multi-step flow that carries the quote ID across onboarding isn't wired. This was left unwired rather than shipped half-working.

Valuation PDF upload — valuations are captured via form input. The assessor's signed report isn't uploaded.

Authorization letter for valuation — the templated letter that clients take to the assessor isn't generated. In production, it would be a PDF emailed to the client.

Certificate PDF generation — policies record when a certificate was issued, but the document isn't generated.
DL number on the client record — personal vehicles auto-create the owner as a driver with a placeholder DL. In production, the DL would be captured during NTSA verification and stored on the client.

Claims officer reassignment — the assign-to flow works but there's no way to reassign a claim if the officer is unavailable.

Claim notification emails — claims transitions don't trigger emails yet.

Payment processing — premium balances are tracked but not collected. No M-Pesa integration.

User provisioning — users are seeded, not created through an admin panel.

Full audit logging — timestamps exist on transitions, but there's no actor + IP + payload audit trail.
