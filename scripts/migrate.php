#!/usr/bin/env php
<?php

/**
 * Housync — standalone migration runner
 *
 * Bootstraps Laravel and runs all pending migrations without going through
 * the Artisan command bus (which can hang in certain Railway environments).
 *
 * Usage (from the project root):
 *   php scripts/migrate.php
 */
define('LARAVEL_START', microtime(true));

// ── 1. Resolve the project root (one level up from scripts/) ─────────────────
$root = dirname(__DIR__);

// ── 2. Composer autoloader ───────────────────────────────────────────────────
$autoload = $root.'/vendor/autoload.php';

if (! file_exists($autoload)) {
    fwrite(STDERR, "[ERROR] vendor/autoload.php not found. Run 'composer install' first.\n");
    exit(1);
}

require $autoload;

// ── 3. Bootstrap the Laravel application ────────────────────────────────────
$app = require $root.'/bootstrap/app.php';

// Boot the HTTP kernel so all service providers (including DatabaseServiceProvider
// and MigrationServiceProvider) are registered and booted.
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

// ── 4. Resolve the Migrator and run pending migrations ───────────────────────
/** @var \Illuminate\Database\Migrations\Migrator $migrator */
$migrator = $app->make('migrator');

$migrationPath = $root.'/database/migrations';

echo "[INFO] Connecting to database...\n";

// Ensure the migrations table exists before querying it.
try {
    $migrator->getRepository()->createRepository();
    echo "[INFO] Migrations table created (or already exists).\n";
} catch (\Exception $e) {
    // createRepository() throws if the table already exists on some drivers —
    // that is fine; we just continue.
    echo "[INFO] Migrations table already exists.\n";
}

echo "[INFO] Checking for pending migrations...\n";

$pending = $migrator->pendingMigrations(
    $migrator->getMigrationFiles([$migrationPath]),
    $migrator->getRepository()->getRan()
);

if (empty($pending)) {
    echo "[INFO] No pending migrations. Database is up to date.\n";
    exit(0);
}

echo '[INFO] Running '.count($pending)." pending migration(s):\n";
foreach ($pending as $migration) {
    echo "       - {$migration}\n";
}
echo "\n";

// Run migrations and capture the notes Migrator writes.
$migrator->run([$migrationPath]);

$notes = $migrator->getNotes();
foreach ($notes as $note) {
    // Strip any Symfony Console formatting tags (e.g. <info>, <comment>).
    echo preg_replace('/<[^>]+>/', '', $note)."\n";
}

// Verify by checking what ran vs. what was pending.
$ran = $migrator->getRepository()->getRan();
$allFiles = array_keys($migrator->getMigrationFiles([$migrationPath]));
$stillPending = array_diff($allFiles, $ran);

if (empty($stillPending)) {
    echo "\n[SUCCESS] All migrations ran successfully.\n";
    exit(0);
} else {
    fwrite(STDERR, "\n[ERROR] Some migrations did not run:\n");
    foreach ($stillPending as $m) {
        fwrite(STDERR, "        - {$m}\n");
    }
    exit(1);
}
