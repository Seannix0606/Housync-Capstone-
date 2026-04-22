# Run OUTSIDE Cursor if the IDE terminal cannot write to .git (e.g. "unable to write new index file").
# Usage: powershell -ExecutionPolicy Bypass -File .\tools\git-commit-and-push-main.ps1
$ErrorActionPreference = 'Stop'
$Root = Split-Path $PSScriptRoot -Parent
if (-not (Test-Path (Join-Path $Root 'artisan'))) {
    throw "Run this from the Housync-Capstone- repo (artisan not found under $Root)"
}
Set-Location $Root
Write-Host "Repo: $Root" -ForegroundColor Cyan

$originUrl = git remote get-url origin 2>$null
if ($originUrl -notmatch 'Seannix0606|Housync-Capstone') {
    Write-Warning "Check that origin is your fork: git remote -v"
}

git fetch origin
$beh = [int](git rev-list --count 'main..origin/main' 2>$null)
$ahe = [int](git rev-list --count 'origin/main..main' 2>$null)
Write-Host "vs origin/main: behind=$beh ahead=$ahe"

if ($beh -gt 0) {
    Write-Host "Pulling origin/main (merge)..." -ForegroundColor Yellow
    git pull --no-rebase origin main
    if ($LASTEXITCODE -ne 0) { throw "git pull failed. Resolve conflicts, then re-run this script." }
}

git add -A
$status = git status -s
if ($status -match '^\?\?') { Write-Warning "There are new untracked files. Review: git status" }

$staged = @(git diff --cached --name-only) | Where-Object { $_ -ne '' }
if ($staged.Count -gt 0) {
    git commit -m "feat: Supabase storage fix, landlord upload fallback, pending cache, dark mode, gallery URLs" `
        -m "Supabase: explicit storage Bearer+apikey. Landlord registration: local fallback; cache bust. Super admin: pending scope and cache. Layouts: dark mode. Views: cover/gallery. package-lock name."
    if ($LASTEXITCODE -ne 0) { throw "git commit failed" }
} else {
    Write-Host "No staged changes to commit (working tree may be clean or add failed)." -ForegroundColor DarkYellow
}

git push origin main
if ($LASTEXITCODE -ne 0) { throw "git push failed. Check auth, or run: git pull origin main" }
Write-Host "Pushed to origin main." -ForegroundColor Green
