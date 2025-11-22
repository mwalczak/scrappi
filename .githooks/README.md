# Git Hooks

This directory contains git hooks that are automatically installed for all developers.

## Automatic Installation

Git hooks are automatically installed when you run:
- `composer install`
- `composer update`
- `make install`
- `make hooks`
- `make setup`

## Available Hooks

### pre-commit

Runs before each commit to ensure code quality:
- **PHPStan** (Level 9): Static analysis for type safety
- **Deptrac**: Architecture boundary validation

**Bypass hook (not recommended)**:
```bash
git commit --no-verify
```

## Manual Installation

If hooks are not automatically installed:
```bash
make hooks
# or
bash .githooks/install-hooks.sh
```

## Adding New Hooks

1. Create the hook file in `.githooks/`
2. Make it executable: `chmod +x .githooks/your-hook`
3. Run `make hooks` to install
4. Commit the new hook to the repository

All developers will get the new hook on their next `composer install/update`.
