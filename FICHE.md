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

- Version déployée: **v1.0.1** (puis retour à v1.0.0, voir section 3)
- Image: `ghcr.io/med-slimen/todoapp_ci-cd:v1.0.1`, digest `sha256:096179de...`, publiée une seule fois et jamais reconstruite
- Vérification: `GET /health` renvoie `{"status":"ok","version":"v1.0.1","service":"todo-app"}`
- Mises en service: v1.0.0 https://github.com/Med-Slimen/toDoApp_CI-CD/actions/runs/45277625363 | v1.0.1 https://github.com/Med-Slimen/toDoApp_CI-CD/actions/runs/45280946677
- Pipelines CI: succès https://github.com/Med-Slimen/toDoApp_CI-CD/actions/runs/45266744974 | échec https://github.com/Med-Slimen/toDoApp_CI-CD/actions/runs/45265592901

## 3. Déroulé du retour arrière

1. Avant: v1.0.1 en service (`/health` affiche la version v1.0.1 et le champ `service`).
2. Déclenchement: Actions, workflow Rollback, version `v1.0.0` (https://github.com/Med-Slimen/toDoApp_CI-CD/actions/runs/45282140226).
3. Le workflow tire l'image déjà publiée (digest `2a1e0da4...`, identique à la première livraison) et lance `docker compose up -d --no-build`: aucune reconstruction.
4. Contrôle de santé OK: `/health` renvoie la version v1.0.0, sans le champ `service`.

## 4. Bonus réalisés

- **Protection de main (+1)**: ruleset exigeant une pull request et les trois contrôles (Tests PHP 8.2, Tests PHP 8.3, Build Docker image); une PR rouge ne peut pas être fusionnée.
- **Déploiement automatique sur tag (+2)**: `release.yml` se déclenche sur un tag `vX.Y.Z`, publie l'image, la déploie en test, puis vérifie `/health`.
- **Matrice de versions (+1)**: `ci.yml` exécute les tests sur PHP 8.2 et PHP 8.3 en parallèle (`fail-fast: false`). Résultat: les deux versions passent, ce qui garantit que le code marche sur la version minimale supportée (8.2, exigée par PHPUnit 11) et sur celle de l'image (8.3). Un échec limité à une seule version révélerait une incompatibilité entre versions.

