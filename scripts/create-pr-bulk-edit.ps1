# Creates branch, commits bulk-edit refactor, pushes, opens PR (requires gh).
#
# IMPORTANT: Run this from PowerShell OUTSIDE Cursor (Windows Terminal / VS Code integrated
# terminal with Cursor closed), or after closing Cursor — otherwise Git may fail with
# "unable to write new index file" / index.lock rename errors.
#
# Usage (repo root):
#   powershell -ExecutionPolicy Bypass -File .\scripts\create-pr-bulk-edit.ps1
$ErrorActionPreference = 'Stop'
$RepoRoot = Split-Path -Parent $PSScriptRoot
Set-Location $RepoRoot

function Invoke-Git {
    param([string[]]$Args)
    & git @Args
    if ($LASTEXITCODE -ne 0) {
        throw "git $($Args -join ' ') failed with exit code $LASTEXITCODE"
    }
}

Write-Host "Repo: $RepoRoot"

if (Test-Path ".git\index.lock") {
    Write-Host "Removing stale .git\index.lock ..."
    Remove-Item -Force ".git\index.lock"
}

$branch = "refactor/bulk-edit-units-modules"

Invoke-Git @('checkout', '-B', $branch)

Invoke-Git @(
    'add',
    'resources/views/landlord/bulk-edit-units.blade.php',
    'resources/views/layouts/landlord-app.blade.php',
    'vite.config.js',
    'resources/css/landlord/',
    'resources/js/landlord/',
    'scripts/create-pr-bulk-edit.ps1'
)

$subject = "Refactor bulk edit units into Vite CSS/JS modules"
$body = @"
Extract bulk-edit styles to resources/css and client logic to resources/js/landlord/bulk-edit with slim Blade + JSON config.
Register assets in vite.config.js. Extend landlord layout dark-mode rules for bulk-edit UI; fix orphaned CSS fragment in dark-mode block.
Add scripts/create-pr-bulk-edit.ps1 helper for teams hitting Windows index locks.
"@

Invoke-Git @('commit', '-m', $subject, '-m', $body)

Invoke-Git @('push', '-u', 'origin', $branch)

if (Get-Command gh -ErrorAction SilentlyContinue) {
    gh pr create --base main --head $branch `
        --title "Refactor bulk edit units (Vite CSS/JS modules)" `
        --body @"
## Summary
Moves bulk-edit-units assets into Vite: Blade supplies markup + ``#bulk-edit-config`` JSON; styles in ``resources/css/landlord/bulk-edit-units.css``; behavior under ``resources/js/landlord/bulk-edit/``. Updates ``vite.config.js`` and landlord layout dark-mode rules for bulk-edit.

## Review checklist
- Landlord bulk-edit flow: floors, units, finalize submit.
- Dark mode toggle with bulk-edit page.
- ``npm run build`` passes locally.

## Notes
Uses ``window.*`` handlers for existing Blade ``onclick`` parity with the previous inline script.
"@
    Write-Host "Done: PR created via gh."
} else {
    Write-Host @"

Push succeeded. GitHub CLI (gh) not installed — open:

https://github.com/Seannix0606/Housync-Capstone-/compare/main...refactor%2Fbulk-edit-units-modules

Click **Create pull request**. Or install gh from https://cli.github.com/

"@
}
