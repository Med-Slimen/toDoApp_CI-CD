# todo-app

Tiny PHP task list used to demonstrate a full DevOps chain: Git, Bash check script, Docker, Compose, GitHub Actions CI, versioned releases, rollback.

## Start the project in one command

```bash
docker compose up --build
```

Then open:

- `http://localhost:8000/health` -> `{"status":"ok","version":"dev"}`
- `http://localhost:8000/tasks` -> list tasks
- Add a task: `curl -X POST -d "title=Buy milk" http://localhost:8000/tasks`

Optional: `cp .env.example .env` and change the values (`.env` is git-ignored).

Stop: `docker compose down` (data is kept in the `dbdata` volume; `docker compose down -v` deletes it).

## Run the checks locally

```bash
./check.sh        # exit code 0 = OK, non-zero = failure
```

## CI/CD

| Workflow | Trigger | What it does |
|---|---|---|
| `ci.yml` | pull request to `main` | tests on PHP 8.2 and 8.3 (JUnit report as artifact), then Docker build (`needs: test`) |
| `release.yml` | tag `vX.Y.Z` | builds and publishes the image to GHCR once, then deploys that image to a test environment and checks `/health` |
| `rollback.yml` | manual | redeploys a previous published version and checks `/health` |

## Release a version

```bash
git tag v1.0.0
git push origin v1.0.0
```

## Roll back

Actions tab > **Rollback** > Run workflow > enter the previous version (e.g. `v1.0.0`).
Or locally: `IMAGE_REPO=ghcr.io/<user>/todo-app ./scripts/deploy.sh v1.0.0`
