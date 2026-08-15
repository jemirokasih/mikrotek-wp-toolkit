# Workspace Rules for MZI White Label Pro

## Branching & Release Workflow Rules
1. **Always Work on `dev` Branch**: Never commit directly to `master` / `main`. All feature additions, bug fixes, and development work must be performed on the `dev` branch.
2. **Never Delete `dev` Branch**: When merging changes from `dev` into `master` / `main` for releases, always preserve the `dev` branch (do not delete or delete-branch on merge).
3. **Commit & Push to Remote**: Always push changes to `origin dev` before creating PRs or merging to `master` for production releases.
