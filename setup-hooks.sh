#!/bin/bash

# Setup script for git hooks
# Run this after cloning the repository: ./setup-hooks.sh

echo "🔧 Setting up git hooks..."

# Copy pre-commit hook
if [ -f .githooks/pre-commit ]; then
    cp .githooks/pre-commit .git/hooks/pre-commit
    chmod +x .git/hooks/pre-commit
    echo "✅ Pre-commit hook installed"
else
    echo "❌ .githooks/pre-commit not found"
    exit 1
fi

echo ""
echo "✨ Git hooks setup complete!"
echo ""
echo "The pre-commit hook will now run Deptrac before each commit to ensure"
echo "architectural boundaries are respected."
echo ""
