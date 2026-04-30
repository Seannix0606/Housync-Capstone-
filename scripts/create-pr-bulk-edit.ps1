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
    param([string[]]$GitArgs)
    & git @GitArgs
    if ($LASTEXITCODE -ne 0) {
        throw "git $($GitArgs -join ' ') failed with exit code $LASTEXITCODE"
    }
}

Write-Host "Repo: $RepoRoot"

if (Test-Path ".git\index.lock") {
    $lockPath = Join-Path $RepoRoot ".git\index.lock"
    $lockItem = Get-Item -LiteralPath $lockPath
    $age = (Get-Date) - $lockItem.LastWriteTime
    $staleMinutes = 8
    $gitRelated = @('git', 'git-remote-https', 'ssh')
    $alive = $false
    foreach ($name in $gitRelated) {
        if (Get-Process -Name $name -ErrorAction SilentlyContinue) {
            $alive = $true
            break
        }
    }
    if ($alive) {
        Write-Warning "A git- or ssh-related process is running; skipping removal of .git\index.lock to avoid corrupting an active operation."
    }
    elseif ($age.TotalMinutes -lt $staleMinutes) {
        Write-Warning "index.lock exists and was modified within the last $staleMinutes minutes; not removing it. If no git command is running, wait or remove the lock manually."
    }
    else {
        Write-Host "Removing stale .git\index.lock (no git/ssh process, older than $staleMinutes minutes) ..."
        Remove-Item -LiteralPath $lockPath -Force
    }
}

$branch = "refactor/bulk-edit-units-modules"

Invoke-Git @('checkout', '-B', $branch)

Invoke-Git @(
    'add',
    'resources/views/landlord/bulk-edit-units.blade.php',
    'resources/views/layouts/landlord-app.blade.php',
    'vite.config.js',
    'resources/css/landlord/bulk-edit-units.css',
    'resources/js/landlord/bulk-edit-units.js',
    'resources/js/landlord/bulk-edit/app.js',
    'resources/js/landlord/bulk-edit/numeric.js',
    'resources/js/landlord/bulk-edit/templates.js',
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
