# Changelog

All notable changes to the **Leads** project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased] - 2026-11-02

### Added
- **System Architecture & Design Documents:**
  - `docs/architecture/SYSTEM_ARCHITECTURE.md`: Complete modular monolith architecture, layers, tenant isolation, and Mermaid diagrams.
  - `docs/architecture/DATABASE_DESIGN.md`: Full relational database schema, Mermaid ERD, composite indexes, foreign key cascades, and business constraints.
  - `docs/architecture/API_DOCUMENTATION.md`: REST API design, endpoint catalogue, envelope structures, and error codes.
  - `docs/architecture/SECURITY.md`: Threat model (STRIDE), RBAC matrix, IDOR protection, and financial integrity rules.
  - `docs/architecture/PERFORMANCE.md`: Query optimization, indexing, Redis caching, and async job queues.
  - `docs/architecture/DEPLOYMENT.md`: Production architecture, Docker multi-stage configuration, backups, and disaster recovery.
  - `docs/architecture/DECISIONS.md`: Architectural Decision Records (ADR-001 through ADR-006).
  - `docs/DEVELOPMENT_GUIDE.md`: Developer onboarding, coding standards, and directory conventions.
  - `docs/TESTING.md`: Test pyramid, mandatory tenant isolation invariants, and test execution guide.
  - `docs/ROADMAP.md`: Release roadmap spanning P0 (MVP), P1 (Operations), and P2 (Integrations).
  - `docs/openapi.yaml`: Machine-readable OpenAPI 3.0 specification.
  - `README.md` and `.env.example`: Project overview, brand identity tokens, and environment blueprint.
- **Brand Identity & Color Tokens:**
  - Dark Indigo (`#280047`), Red Orange (`#F74D01`), Light Purple (`#C3B6D3`), Light Yellow (`#FDFEBA`), Main White (`#FFFFFF`), Dark Gray (`#242424`).

