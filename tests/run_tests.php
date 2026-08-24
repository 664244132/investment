<?php
declare(strict_types=1);

/**
 * tests/run_tests.php
 * SmartPort AI - Comprehensive Automated CLI Test Suite
 * ทดสอบความถูกต้องของระบบ, กฎ Markdown Rules, Security, และฟังก์ชันคำนวณทางการเงินทั้งหมด
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/icons.php';
require_once __DIR__ . '/../functions/auth.php';
require_once __DIR__ . '/../functions/stocks.php';
require_once __DIR__ . '/../functions/portfolio.php';
require_once __DIR__ . '/../functions/watchlist.php';
require_once __DIR__ . '/../functions/journal.php';
require_once __DIR__ . '/../functions/dca.php';
require_once __DIR__ . '/../functions/financial_models.php';
require_once __DIR__ . '/../functions/export.php';

class TestRunner
{
    private int $passed = 0;
    private int $failed = 0;
    private array $failures = [];

    public function assert(bool $condition, string $testName, string $failureDetails = ''): void
    {
        if ($condition) {
            $this->passed++;
            echo "  [PASS] {$testName}\n";
        } else {
            $this->failed++;
            $this->failures[] = "{$testName}: {$failureDetails}";
            echo "  [FAIL] {$testName} - {$failureDetails}\n";
        }
    }

    public function printSummary(): int
    {
        $total = $this->passed + $this->failed;
        echo "\n=======================================================\n";
        echo "TEST SUMMARY: {$this->passed}/{$total} PASSED (" . number_format(($this->passed / max($total, 1)) * 100, 1) . "%)\n";
        if ($this->failed > 0) {
            echo "FAILURES:\n";
            foreach ($this->failures as $f) {
                echo "  - {$f}\n";
            }
            echo "=======================================================\n";
            return 1;
        }
        echo "ALL TESTS PASSED SUCCESSFULLY! 100% GREEN.\n";
        echo "=======================================================\n";
        return 0;
    }
}

$runner = new TestRunner();
$projectDir = dirname(__DIR__);

echo "=======================================================\n";
echo " SmartPort AI - Automated CLI Test Suite Runner\n";
echo "=======================================================\n\n";

// ==============================================================================
// 1. PHP Strict Typing & Syntax Integrity
// ==============================================================================
echo "1. Checking PHP Strict Typing across all PHP files...\n";
$phpFiles = [];
$dirIter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($projectDir));
foreach ($dirIter as $f) {
    if ($f->isDir()) continue;
    if ($f->getExtension() !== 'php') continue;
    $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($projectDir) + 1));
    if (str_starts_with($rel, '.agents/') || str_starts_with($rel, 'vendor/')) continue;
    $phpFiles[] = $rel;
}

$strictOk = true;
$missingStrict = [];
foreach ($phpFiles as $file) {
    if ($file === 'tests/run_tests.php') continue;
    $content = file_get_contents($projectDir . '/' . $file);
    if (!str_contains($content, 'declare(strict_types=1);')) {
        $strictOk = false;
        $missingStrict[] = $file;
    }
}
$runner->assert($strictOk, "All " . (count($phpFiles) - 1) . " production PHP files have declare(strict_types=1);", implode(', ', $missingStrict));

// ==============================================================================
// 2. Markdown Rules: De Morgan's Laws Validator
// ==============================================================================
echo "\n2. Checking De Morgan's Laws Compliance...\n";
$deMorganOk = true;
$deMorganViolations = [];
foreach ($phpFiles as $file) {
    if ($file === 'tests/run_tests.php') continue;
    $content = file_get_contents($projectDir . '/' . $file);
    $lines = explode("\n", $content);
    foreach ($lines as $ln => $line) {
        if (preg_match('/!\s*\([^()]*(&&|\|\|)[^()]*\)/', $line)) {
            $deMorganOk = false;
            $deMorganViolations[] = "$file:" . ($ln + 1);
        }
    }
}
$runner->assert($deMorganOk, "No De Morgan's Laws violations !(A && B) / !(A || B)", implode(', ', $deMorganViolations));

// ==============================================================================
// 3. Markdown Rules: Zero External CDNs
// ==============================================================================
echo "\n3. Checking Zero External CDN Policy...\n";
$cdnOk = true;
$cdnViolations = [];
foreach ($dirIter as $f) {
    if ($f->isDir()) continue;
    $ext = $f->getExtension();
    if (!in_array($ext, ['php', 'css', 'js', 'html'])) continue;
    $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($projectDir) + 1));
    if (str_starts_with($rel, 'markdowns/') || str_starts_with($rel, '.agents/') || $rel === 'js/chart.umd.min.js') continue;
    $content = file_get_contents($f->getPathname());
    if (preg_match('/(https?:\/\/(?:cdn|unpkg|cdnjs|jsdelivr|fonts\.googleapis)[^"\'\s>]+)/i', $content, $m)) {
        if (!str_contains($m[1], 'schema.org') && !str_contains($m[1], 'w3.org')) {
            $cdnOk = false;
            $cdnViolations[] = "$rel: {$m[1]}";
        }
    }
}
$runner->assert($cdnOk, "Zero External CDN dependencies in all source code", implode(', ', $cdnViolations));

// ==============================================================================
// 4. Security Helpers & Output Escaping
// ==============================================================================
echo "\n4. Testing Security Helpers & Output Escaping...\n";
$xssTest = '<script>alert("xss")</script>&"\'';
$escaped = e($xssTest);
$runner->assert(!str_contains($escaped, '<script>') && str_contains($escaped, '&lt;script&gt;'), "e() correctly escapes HTML characters", $escaped);
$runner->assert(e(null) === '', "e(null) returns empty string");
$runner->assert(e('') === '', "e('') returns empty string");

// CSRF Token Generation & Verification
startAppSession();
$token = generateCsrfToken();
$runner->assert(strlen($token) === 64, "generateCsrfToken() produces 64-char hex string", "Got length " . strlen($token));
$runner->assert(verifyCsrfToken($token) === true, "verifyCsrfToken() passes valid token");
$runner->assert(verifyCsrfToken('invalid_token') === false, "verifyCsrfToken() rejects invalid token");
$runner->assert(verifyCsrfToken(null) === false, "verifyCsrfToken() rejects null token");

$inputField = getCsrfInputField();
$runner->assert(str_contains($inputField, '<input type="hidden" name="csrf_token" value="') && str_contains($inputField, $token), "getCsrfInputField() produces valid hidden input", $inputField);

// ==============================================================================
// 5. Financial Formatting Helpers
// ==============================================================================
echo "\n5. Testing Financial Formatting Helpers...\n";
$runner->assert(formatMoney(1000000.50) === '1,000,000.50 THB', "formatMoney with default THB", formatMoney(1000000.50));
$runner->assert(formatMoney(500.0, 2, '') === '500.00', "formatMoney without currency", formatMoney(500.0, 2, ''));
$runner->assert(formatNumber(1234567.89, 2) === '1,234,567.89', "formatNumber with 2 decimals", formatNumber(1234567.89, 2));
$runner->assert(formatPercent(4.58) === '+4.58%', "formatPercent positive with plus", formatPercent(4.58));
$runner->assert(formatPercent(-1.57) === '-1.57%', "formatPercent negative", formatPercent(-1.57));
$runner->assert(formatCompactNumber(1850000000000.0) === '1.85T', "formatCompactNumber Trillion (T)", formatCompactNumber(1850000000000.0));
$runner->assert(formatCompactNumber(42150000.0) === '42.15M', "formatCompactNumber Million (M)", formatCompactNumber(42150000.0));
$runner->assert(formatCompactNumber(150000.0) === '150.00K', "formatCompactNumber Thousand (K)", formatCompactNumber(150000.0));
$runner->assert(getScoreColorClass(94) === 'text-gain' && getScoreColorClass(70) === 'text-gold' && getScoreColorClass(40) === 'text-loss', "getScoreColorClass maps scores to semantic colors");
$runner->assert(getScoreBadgeClass(94) === 'badge-gain' && getScoreBadgeClass(70) === 'badge-gold' && getScoreBadgeClass(40) === 'badge-loss', "getScoreBadgeClass maps scores to semantic badges");

// ==============================================================================
// 6. Advanced Financial Health Models (Altman Z-Score & Piotroski F-Score)
// ==============================================================================
echo "\n6. Testing Statistical Financial Models (Altman Z & Piotroski F)...\n";
$strongFinances = [
    'roa' => 16.5,
    'roe' => 24.8,
    'net_profit_margin' => 12.58,
    'de_ratio' => 0.42,
    'current_ratio' => 2.15,
    'revenue_growth_yoy' => 23.5,
    'free_cash_flow' => 15200000000.0,
    'net_profit' => 18412000000.0
];

$zScore = calculateAltmanZScore($strongFinances);
$runner->assert(isset($zScore['score'], $zScore['zone']), "calculateAltmanZScore returns score and zone");
$runner->assert($zScore['score'] >= 2.99 && $zScore['zone'] === 'Safe', "Strong company classified in Safe Zone (Score: {$zScore['score']}, Zone: {$zScore['zone']})");

$distressedFinances = [
    'roa' => -5.0,
    'roe' => -10.0,
    'net_profit_margin' => -8.0,
    'de_ratio' => 4.5,
    'current_ratio' => 0.65,
    'revenue_growth_yoy' => -15.0,
    'free_cash_flow' => -2000000.0,
    'net_profit' => -5000000.0
];
$zDistressed = calculateAltmanZScore($distressedFinances);
$runner->assert($zDistressed['score'] < 1.81 && $zDistressed['zone'] === 'Distress', "Distressed company classified in Distress Zone (Score: {$zDistressed['score']}, Zone: {$zDistressed['zone']})");

$fScore = calculatePiotroskiFScore($strongFinances);
$runner->assert(isset($fScore['score'], $fScore['checklist']), "calculatePiotroskiFScore returns score and checklist");
$runner->assert($fScore['score'] >= 7, "Strong company receives high F-Score ({$fScore['score']}/9)");

// ==============================================================================
// 7. DCA Simulator & DRIP Growth Engine
// ==============================================================================
echo "\n7. Testing DCA Simulator Engine (functions/dca.php)...\n";
$dcaResult = simulateDcaGrowth(10000.0, 10, 8.0, 3.0, true);
$runner->assert(isset($dcaResult['final_stock_value'], $dcaResult['total_principal'], $dcaResult['yearly_breakdown']), "simulateDcaGrowth returns comprehensive breakdown");
$runner->assert($dcaResult['total_principal'] === 1200000.0, "10 years at 10,000/mo has total principal 1,200,000 THB", "Got " . $dcaResult['total_principal']);
$runner->assert($dcaResult['final_stock_value'] > $dcaResult['total_principal'], "Final DCA value exceeds principal due to compounding", "Got " . $dcaResult['final_stock_value']);
$runner->assert(count($dcaResult['yearly_breakdown']) === 10, "10 years produces 10 yearly amortization records", "Got " . count($dcaResult['yearly_breakdown']));

// ==============================================================================
// 8. Vector Icon Dictionary & Rendering
// ==============================================================================
echo "\n8. Testing SVG Vector Icon System (functions/icons.php)...\n";
$iconNames = ['wallet', 'chart-bar', 'search', 'lock', 'trending-up', 'check', 'alert-triangle', 'bookmark'];
$allIconsOk = true;
foreach ($iconNames as $name) {
    $svg = icon($name);
    if (!str_contains($svg, '<svg') || !str_contains($svg, '</svg>')) {
        $allIconsOk = false;
        break;
    }
}
$runner->assert($allIconsOk, "SVG Vector Icon system successfully renders key SVG icons");

// ==============================================================================
// 9. Database Connection & Schema Verification (if DB is active)
// ==============================================================================
echo "\n9. Testing Database Connectivity & Schema...\n";
$pdo = getDbConnection();
if ($pdo !== null) {
    $expectedTables = [
        'users', 'stocks', 'financial_metrics', 'financial_statements',
        'ai_analyses', 'portfolios', 'portfolio_items', 'transactions'
    ];
    $tablesFound = true;
    foreach ($expectedTables as $tbl) {
        try {
            $stmt = $pdo->query("SELECT 1 FROM `{$tbl}` LIMIT 1");
            if ($stmt === false) {
                $tablesFound = false;
            }
        } catch (Throwable $e) {
            $tablesFound = false;
        }
    }
    $runner->assert($tablesFound, "Database connection active and core tables present");
} else {
    echo "  [INFO] MySQL not running locally in this process; skipping live DB queries.\n";
}

// Exit with status code based on test results
exit($runner->printSummary());
