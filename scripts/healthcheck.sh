#!/bin/bash
# Usage: ./scripts/healthcheck.sh http://localhost:8000/health
set -uo pipefail
URL="${1:-http://localhost:8000/health}"
for i in $(seq 1 30); do
  if curl -fsS "$URL"; then
    echo; echo "Health check OK"
    exit 0
  fi
  echo "Waiting for $URL ($i/30)..."
  sleep 2
done
echo "Health check FAILED"
exit 1
