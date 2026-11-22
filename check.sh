#!/bin/bash
# Control script: exit 0 if everything is fine, non-zero otherwise.
set -euo pipefail

echo "==> Installing dependencies"
composer install --no-interaction --quiet

echo "==> Linting PHP files"
find src public tests -name '*.php' -print0 | xargs -0 -n1 php -l > /dev/null

echo "==> Running tests"
./vendor/bin/phpunit

echo "==> All checks passed"
