# GitHub Flow for **Sistem Inventori & Penjualan**

This project follows the **GitHub Flow** development model. Below are the key steps and conventions that contributors should follow when working on new features, bug fixes, or documentation updates.

## 1. Keep `main` Up‑to‑Date
- Pull the latest changes before starting any work:
  ```bash
  git checkout main && git pull origin main
  ```
- `main` is a protected branch: direct pushes are disallowed, and merges are only allowed via **Pull Requests** after CI checks pass.

## 2. Create a Feature Branch
- Branches should be created from the latest `main`.
- Use **snake‑case** naming with a clear prefix:
  - `feat/` – new feature
  - `fix/` – bug fix
  - `docs/` – documentation changes
  - `test/` – test additions or adjustments
- Example:
  ```bash
  git checkout -b feat/add-supplier-filter
  ```

## 3. Develop Locally
- Follow the project architecture: **Controller → Service → Repository**.
- Write **unit/feature tests** for any new logic (see `CONTRIBUTING.md`).
- Run the test suite locally:
  ```bash
  php artisan test
  vendor/bin/pint --test   # code style check
  ```
- Keep commits **atomic** and follow **Conventional Commits** (e.g., `feat: add supplier filter`).

## 4. Push Branch & Open a Pull Request
- Push your branch to the remote:
  ```bash
  git push -u origin feat/add-supplier-filter
  ```
- Open a PR targeting `main`. The repository provides a PR template (`.github/PULL_REQUEST_TEMPLATE.md`) that automatically appears in the PR description.
- Fill in the **Description**, **Change Type**, and **Checklist** sections.
- Link any related issue or task identifier (`TASK-XXX`).

## 5. Continuous Integration
- The CI workflow (`.github/workflows/tests.yml`) runs on **every PR** and on pushes to `main`.
- It performs:
  - Dependency installation via Composer and npm
  - Front‑end asset build (`npm run build`)
  - Code‑style check with **Laravel Pint**
  - Full test suite on PHP 8.4 and 8.5
- The PR cannot be merged until all checks are green.

## 6. Review & Merge
- At least one reviewer must approve the PR.
- Resolve any review comments and push additional commits to the same branch; the CI will re‑run automatically.
- Merge using **Squash and merge** (default) to keep the history clean – one logical change results in a single commit on `main`.
- After merging, delete the feature branch.

## 7. Release
- Releases are created from **tags** that follow semantic versioning (e.g., `v0.2.0`).
- Tag creation is performed after the PR is merged and the code is verified in production.

---

For more detailed contribution steps, see **[CONTRIBUTING.md](CONTRIBUTING.md)**.
