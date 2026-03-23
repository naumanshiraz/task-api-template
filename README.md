# Collaborative Task Management API

A ready-to-use API to manage projects, tasks, comments, and notifications, built in a clean way and fully tested.

## 🏗️ Architecture Overview

This project follows a **layered architecture** pattern:

- **Controllers**: Handle HTTP requests/responses
- **Services**: handle business logic
- **Repositories**: manage database queries
- **Models**: represent data
- **Events/Listeners**: trigger actions when something happens
- **Jobs**: Asynchronous processing

### Design Patterns Used

1. **Repository Pattern**: separates database logic from the app
2. **Service Layer Pattern**: keeps business logic in one place
3. **Observer Pattern**: runs actions when events happen
4. **Factory Pattern**: creates test data easily

### Why These Patterns?

- **Repository**: makes database work simple and flexible
- **Service Layer**: keeps all business logic in one place
- **Observer**: lets actions happen without tight connection
- **Events**: enable asynchronous processing of notifications

## 📋 Requirements Met

✅ **Authentication**: JWT via Laravel Sanctum  
✅ **Projects**: Full CRUD with ownership  
✅ **Tasks**: CRUD + filtering, search, pagination  
✅ **Comments**: Full CRUD  
✅ **Notifications**: Event-driven, asynchronous queue processing  
✅ **Caching**: Redis-based task listing cache  
✅ **Rate Limiting**: Built-in on auth endpoints  
✅ **Testing**: 70%+ coverage with unit + integration tests  
✅ **Docker**: Complete containerized setup  
✅ **CI/CD**: GitHub Actions pipeline  

## 🚀 Setup Instructions

### Prerequisites
- Docker & Docker Compose
- Node.js 18+ (optional, for frontend)

### Quick Start (Docker)

```bash
# Clone the repository
git clone https://github.com/naumanshiraz/task-api-template.git
cd task-api-template

# Copy environment file
cp .env.example .env

# Build and start containers
docker-compose up -d

# Install dependencies
docker-compose exec app composer install

# Generate app key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate

# Create test user
docker-compose exec app php artisan tinker
# Then run: User::factory()->create(['email' => 'test@example.com', 'password' => Hash::make('password')])

# Run tests
docker-compose exec app composer test

# Access API at http://localhost:8000