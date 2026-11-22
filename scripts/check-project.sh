#!/usr/bin/env bash
set -u

errors=0
required=(
  README.md
  .gitignore
  .dockerignore
  .env.example
  Dockerfile
  docker-compose.yml
  composer.json
  public/index.php
  src/Tasks.php
  tests/TasksTest.php
  .github/workflows/ci.yml
)

echo "[CHECK] todo-app"
for file in "${required[@]}"; do
  if [[ -f "$file" ]]; then
    echo "[OK] $file"
  else
    echo "[ERREUR] fichier absent: $file" >&2
    errors=$((errors + 1))
  fi
done

if git ls-files | grep -Eq '(^|/)\.env$'; then
  echo "[ERREUR] un fichier .env est suivi par Git" >&2
  errors=$((errors + 1))
else
  echo "[OK] aucun .env suivi"
fi

if git ls-files | grep -q '^vendor/'; then
  echo "[ERREUR] le dossier vendor/ est suivi par Git" >&2
  errors=$((errors + 1))
else
  echo "[OK] vendor/ non suivi"
fi

if [[ $errors -gt 0 ]]; then
  echo "[ECHEC] $errors erreur(s)" >&2
  exit 1
fi

echo "[SUCCES] contrôles validés"
exit 0