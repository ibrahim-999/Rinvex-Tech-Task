# Acme - Team Skills Tracker

A Filament v4 module for managing team skills, built as a tech assignment for Rinvex.

## Quick Setup

```bash
# Clone and enter the project
git clone <repo-url> acme
cd acme

# Copy environment file
cp .env.example .env

# Run full setup (Docker, dependencies, migrations, seeding)
make setup
```

**Access the application:**
- URL: http://localhost:8000/admin
- Email: `admin@acme.test`
- Password: `password`

## Manual Setup

If `make` is unavailable:

```bash
# Start containers
docker-compose up -d

# Install dependencies
docker-compose exec app composer install
docker-compose exec app npm install

# Setup Laravel
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed
docker-compose exec app php artisan storage:link
docker-compose exec app npm run build
```

## Running Tests

```bash
  make test
# or
docker-compose exec app ./vendor/bin/pest
```

## Useful Commands

| Command | Description |
|---------|-------------|
| `make up` | Start containers |
| `make down` | Stop containers |
| `make shell` | Access app container |
| `make fresh` | Fresh migration with seed |
| `make test` | Run tests |
| `make logs` | View container logs |

## Architectural Decisions

### 1. Action Classes for Business Logic
Extracted `ArchiveSkillAction` and `SyncCategoriesFromApiAction` into dedicated action classes. This keeps controllers/resources thin and makes the logic testable and reusable.

### 2. Cluster-Based Navigation
Used Filament's cluster feature (`SkillsManagement`) to group skill-related pages. This provides a clean navigation structure with badge counts.

### 3. Form Wizard
Implemented a 3-step wizard (Basic Info → Details → Media & Tags) to organize the form logically without overwhelming users. Made it skippable for quick edits.

### 4. Conditional Field Visibility
`proficiency_level` only appears when `category = "Technical"`, demonstrating Filament's reactive forms with `->visible(fn (Get $get) => ...)`.

### 5. Database Notifications + Flash Notifications
Used both notification types as required:
- Flash notifications for immediate feedback (create/update)
- Database notifications for the archive action (persisted in notification center)

### 6. External API Integration
`PublicApisClient` fetches categories from a public API with:
- Caching (1 hour TTL)
- Graceful fallback to default categories on failure
- Error logging

### 7. Policy-Based Authorization
`SkillPolicy` controls access. Notable: archived skills cannot be deleted (`delete` returns `false` if archived).

### 8. Factory States
`SkillFactory` includes states (`active()`, `inactive()`, `archived()`, `technical()`) for expressive test setup.

## What I Would Improve With More Time

1. **More comprehensive tests** - Add tests for filters, widgets, and edge cases
2. **Bulk actions** - Bulk archive/activate skills from the table
3. **Activity log viewer** - A relation manager to view skill history in the admin panel
4. **Export functionality** - Export skills to CSV/Excel
5. **Soft deletes** - Instead of hard deletes, implement soft deletes with restore capability
6. **Category management** - Separate CRUD for categories instead of free-text input
7. **File preview** - Better attachment previews in the infolist (thumbnails, PDF viewer)
8. **Performance** - Add database indexes review, query optimization for large datasets
9. **API documentation** - If API was required, add OpenAPI/Swagger docs

## Project Structure

```
app/
├── Actions/
│   ├── ArchiveSkillAction.php
│   └── SyncCategoriesFromApiAction.php
├── Console/
│   └── Commands/
│       └── SyncCategoriesCommand.php
├── Filament/
│   ├── Clusters/
│   │   └── SkillsManagement.php
│   ├── Resources/
│   │   └── SkillResource.php
│   │       └── Pages/
│   │           ├── CreateSkill.php
│   │           ├── EditSkill.php
│   │           ├── ListSkills.php
│   │           └── ViewSkill.php
│   └── Widgets/
│       ├── ProficiencyDistribution.php
│       └── SkillStatsOverview.php
├── Integrations/
│   └── PublicApisClient.php
├── Models/
│   ├── Skill.php
│   └── SkillActivity.php
└── Policies/
    └── SkillPolicy.php
```

## Tech Stack

- PHP 8.3
- Laravel 12
- Filament v4
- Livewire 3
- Alpine.js
- MySQL 8.0
- Docker + Nginx
