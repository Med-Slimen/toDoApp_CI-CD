# Fiche de projet: todo-app (DevOps, L3 DSI, ISET)

**Étudiant:** <nom prénom>  **Dépôt:** <URL GitHub>

## 1. Architecture

```
 Developer -> git push / Pull Request -> GitHub Actions (ci.yml)
                                           |-- test (PHP 8.2, 8.3) -> JUnit artifact
                                           '-- build (needs: test) -> Docker image
 Tag vX.Y.Z -> release.yml -> publish image to GHCR (once)
                           -> deploy-test: pull image, docker compose up, GET /health

 Runtime (Docker Compose):
   [ app: PHP 8.3, port 8000 ] --(service name "db")--> [ db: MySQL 8, volume dbdata, port NOT published ]
```

- Application: PHP, routes `/health`, `/tasks`.
- Image: non-root user, explicit tag, HEALTHCHECK, `.dockerignore`.
- Compose: DB reached by service name, DB port not published, persistent volume, app starts when DB is healthy.

## 2. Version mise en service

- Version déployée: **v<X.Y.Z>**
- Image: `ghcr.io/<user>/todo-app:v<X.Y.Z>` (published once, never rebuilt)
- Vérification: `GET /health` returns `{"status":"ok","version":"v<X.Y.Z>"}`
- Pipelines: succès <URL> | échec <URL>

## 3. Déroulé du retour arrière

1. State before: v<1.0.1> deployed (`/health` shows version v<1.0.1>).
2. Trigger: Actions > Rollback > version `v<1.0.0>` (or `IMAGE_REPO=... ./scripts/deploy.sh v<1.0.0>`).
3. The script pulls the existing image `:v<1.0.0>` (no rebuild) and runs `docker compose up -d --no-build`.
4. Health check passes; `/health` now shows version v<1.0.0>.

## 4. Bonus réalisés

- [ ] Protection de main (+1)
- [x] Déploiement automatique sur tag (+2)
- [x] Matrice de versions PHP 8.2 / 8.3 (+1)
