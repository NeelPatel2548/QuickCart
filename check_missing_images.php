<?php
/**
 * QuickCart - Missing Image Scanner (CLI Tool)
 * 
 * Scans all product image filenames stored in the database and checks
 * whether each corresponding file exists in the /img/ directory.
 * Reports missing files with product details for quick remediation.
 * 
 * Usage:
 *   php check_missing_images.php
 * 
 * @package QuickCart
 * @since   1.0
 */

// ─── Bootstrap ───────────────────────────────────────────
// Ensure this is run from CLI only
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

require_once __DIR__ . '/config.php';

// ─── Configuration ───────────────────────────────────────
$img_dir = __DIR__ . DIRECTORY_SEPARATOR . 'img';

// ─── Output Helpers ──────────────────────────────────────
function line(string $text = ''): void { echo $text . PHP_EOL; }

function colorize(string $text, string $color): string
{
    $colors = [
        'red'    => "\033[31m",
        'green'  => "\033[32m",
        'yellow' => "\033[33m",
        'cyan'   => "\033[36m",
        'bold'   => "\033[1m",
        'reset'  => "\033[0m",
    ];
    return ($colors[$color] ?? '') . $text . $colors['reset'];
}

// ─── Banner ──────────────────────────────────────────────
line();
line(colorize('╔══════════════════════════════════════════════════╗', 'cyan'));
line(colorize('║     QuickCart — Missing Image Scanner            ║', 'cyan'));
line(colorize('╚══════════════════════════════════════════════════╝', 'cyan'));
line();

// ─── Fetch All Product Images ────────────────────────────
try {
    $stmt = $pdo->query("SELECT id, name, image, cat_id FROM products ORDER BY id ASC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    line(colorize('  ✗ Database error: ' . $e->getMessage(), 'red'));
    exit(1);
}

if (empty($products)) {
    line(colorize('  ⚠ No products found in the database.', 'yellow'));
    exit(0);
}

// ─── Scan Each Product ───────────────────────────────────
$missing = [];
$found   = 0;

line(colorize('  Scanning ' . count($products) . ' products...', 'bold'));
line(str_repeat('─', 55));

foreach ($products as $p) {
    $filename  = $p['image'] ?? '';
    $full_path = $img_dir . DIRECTORY_SEPARATOR . $filename;

    if (empty($filename)) {
        $missing[] = $p;
        line(sprintf(
            "  %-4s %-35s %s",
            '#' . $p['id'],
            $p['name'],
            colorize('[NO FILENAME SET]', 'yellow')
        ));
    } elseif (!file_exists($full_path)) {
        $missing[] = $p;
        line(sprintf(
            "  %-4s %-35s %s",
            '#' . $p['id'],
            $p['name'],
            colorize('✗ MISSING: ' . $filename, 'red')
        ));
    } else {
        $found++;
        line(sprintf(
            "  %-4s %-35s %s",
            '#' . $p['id'],
            $p['name'],
            colorize('✓ ' . $filename, 'green')
        ));
    }
}

// ─── Also check category images ──────────────────────────
line();
line(colorize('  Scanning category images...', 'bold'));
line(str_repeat('─', 55));

try {
    $cat_stmt = $pdo->query("SELECT id, name, image FROM categories ORDER BY id ASC");
    $categories = $cat_stmt->fetchAll();
} catch (PDOException $e) {
    line(colorize('  ✗ Error fetching categories: ' . $e->getMessage(), 'red'));
    $categories = [];
}

$cat_missing = [];
foreach ($categories as $cat) {
    $cat_file = $cat['image'] ?? '';
    $cat_path = $img_dir . DIRECTORY_SEPARATOR . $cat_file;

    if (empty($cat_file)) {
        $cat_missing[] = $cat;
        line(sprintf("  %-4s %-35s %s", '#' . $cat['id'], $cat['name'], colorize('[NO FILENAME SET]', 'yellow')));
    } elseif (!file_exists($cat_path)) {
        $cat_missing[] = $cat;
        line(sprintf("  %-4s %-35s %s", '#' . $cat['id'], $cat['name'], colorize('✗ MISSING: ' . $cat_file, 'red')));
    } else {
        line(sprintf("  %-4s %-35s %s", '#' . $cat['id'], $cat['name'], colorize('✓ ' . $cat_file, 'green')));
    }
}

// ─── Summary ─────────────────────────────────────────────
line();
line(str_repeat('═', 55));
line(colorize('  SUMMARY', 'bold'));
line(str_repeat('─', 55));
line(sprintf("  Products:   %s found, %s missing",
    colorize((string)$found, 'green'),
    colorize((string)count($missing), count($missing) > 0 ? 'red' : 'green')
));
line(sprintf("  Categories: %s found, %s missing",
    colorize((string)(count($categories) - count($cat_missing)), 'green'),
    colorize((string)count($cat_missing), count($cat_missing) > 0 ? 'red' : 'green')
));
line(str_repeat('═', 55));

if (!empty($missing)) {
    line();
    line(colorize('  ACTION REQUIRED:', 'yellow'));
    foreach ($missing as $m) {
        line('    → Add "' . ($m['image'] ?? 'N/A') . '" to /img/ for product "' . $m['name'] . '" (ID: ' . $m['id'] . ')');
    }
    line('    → Or update the database to use an existing filename.');
    line('    → The get_product_image() helper in includes/image_helper.php');
    line('      will show a fallback placeholder in the meantime.');
}

line();
line(colorize('  Scan complete. ✓', 'green'));
line();
