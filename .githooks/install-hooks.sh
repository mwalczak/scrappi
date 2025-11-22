#!/bin/bash

# Script to install git hooks
# This is automatically run by Composer after install/update

HOOKS_DIR=".githooks"
GIT_HOOKS_DIR=".git/hooks"

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

echo ""
echo -e "${BLUE}Installing git hooks...${NC}"

# Check if .git directory exists
if [ ! -d ".git" ]; then
    echo "Not a git repository. Skipping git hooks installation."
    exit 0
fi

# Create .git/hooks directory if it doesn't exist
mkdir -p "$GIT_HOOKS_DIR"

# Copy hooks from .githooks to .git/hooks
for hook in "$HOOKS_DIR"/*; do
    hook_name=$(basename "$hook")
    # Skip install script and documentation files
    if [ -f "$hook" ] && [ "$hook_name" != "install-hooks.sh" ] && [ "$hook_name" != "README.md" ]; then
        cp "$hook" "$GIT_HOOKS_DIR/$hook_name"
        chmod +x "$GIT_HOOKS_DIR/$hook_name"
        echo -e "${GREEN}✓${NC} Installed: $hook_name"
    fi
done

echo -e "${GREEN}✓ Git hooks installed successfully${NC}"
echo ""
