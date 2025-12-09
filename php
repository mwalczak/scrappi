#!/bin/bash

# Helper script to run commands in the PHP container
# Usage: ./php [command]
# Examples:
#   ./php composer install
#   ./php bin/console cache:clear
#   ./php vendor/bin/phpunit

cd "$(dirname "$0")"

if [ $# -eq 0 ]; then
    # No arguments - open interactive bash shell
    docker-compose exec php bash
else
    # Execute the provided command
    docker-compose exec php "$@"
fi

