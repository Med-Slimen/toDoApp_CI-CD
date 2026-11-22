#!/bin/bash
# Deploy (or roll back) a PUBLISHED image version. The image is pulled, never rebuilt.
# Usage: IMAGE_REPO=ghcr.io/<user>/todo-app ./scripts/deploy.sh v1.0.0
set -euo pipefail
VERSION="${1:?Usage: IMAGE_REPO=ghcr.io/<user>/todo-app ./scripts/deploy.sh vX.Y.Z}"
REPO="${IMAGE_REPO:?Set IMAGE_REPO, e.g. ghcr.io/<user>/todo-app}"

export APP_IMAGE="${REPO}:${VERSION}"
export APP_VERSION="${VERSION}"

echo "==> Deploying ${APP_IMAGE}"
docker pull "${APP_IMAGE}"
docker compose up -d --no-build
./scripts/healthcheck.sh http://localhost:8000/health
echo "==> Version ${VERSION} is live"
