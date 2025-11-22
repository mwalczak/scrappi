# Quick Reference Guide

## First-Time Setup

```bash
make setup
```

This will set up everything you need in ~2-3 minutes:
- Containers, databases, migrations, tests, and validation

## Common Commands

### Development
```bash
make up              # Start containers
make down            # Stop containers
make restart         # Restart containers
make clean           # Stop and remove volumes
make logs            # View container logs
make shell           # Open PHP container shell
make status          # Show container status
```

### Database
```bash
make db-create       # Create database
make db-migrate      # Run migrations
make db-reset        # Drop and recreate database
```

### Testing & Quality
```bash
make test            # Run PHPUnit tests
make phpstan         # Run static analysis
make deptrac         # Validate architecture
make qa              # Run all quality checks
```

### Cache
```bash
make cache-clear     # Clear Symfony cache
```

### Helpers
```bash
make help                           # Show all available commands
make composer CMD="require pkg"     # Run Composer command
make console CMD="debug:router"     # Run Symfony console command
```

## Helper Scripts (Alternative)

If you prefer scripts over Make:
```bash
./test                   # Run tests
./phpstan analyse        # Run static analysis
./deptrac analyze        # Validate architecture
./console [command]      # Symfony console
./composer [command]     # Composer commands
```

## Endpoints

- Health Check: http://localhost:8001/api/health
- API Docs: http://localhost:8001/api
- Netflix Videos: http://localhost:8001/api/netflix_videos

## Troubleshooting

### Clear all caches
```bash
make cache-clear
```

### Rebuild environment
```bash
make setup
```

### View logs
```bash
make logs
```

### Reset database
```bash
make db-reset
```

