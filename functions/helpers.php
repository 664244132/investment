<?php
declare(strict_types=1);

/**
 * functions/helpers.php
 * รวมฟังก์ชันช่วยเหลือทั่วไป (Utility & Helper Functions)
 * ออกแบบตามหลัก De Morgan's Laws, Early Return Guard Clauses และ Security Best Practices
 */

require_once __DIR__ . '/../config/constants.php';

/**
 * ป้องกันการโจมตี Cross-Site Scripting (XSS) ด้วยการ Escape ข้อมูลก่อนแสดงผลใน HTML
 *
 * @param string|null $value ข้อความที่ต้องการ Escape
 * @return string ข้อความที่ปลอดภัยสำหรับแสดงผล
 */
function e(?string $value): string
{
    // หากค่าเป็น null หรือสตริงว่าง ให้รีบส่งคืนสตริงว่างทันที (Early Return)
    if ($value === null || $value === '') {
        return '';
    }

    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * ตรวจสอบว่าหุ้นเป็นหุ้นไทย (SET / mai) หรือหุ้นต่างประเทศ
 *
 * @param array|string|null $stockOrMarket ข้อมูลหุ้น (array) หรือชื่อตลาด (string เช่น 'SET', 'NASDAQ')
 * @param string $symbol ชื่อย่อหุ้น
 * @return bool true หากเป็นหุ้นไทย
 */
function isThaiStock(array|string|null $stockOrMarket = 'SET', string $symbol = ''): bool
{
    $market = 'SET';
    $sym = $symbol;

    if (is_array($stockOrMarket)) {
        $market = (string)($stockOrMarket['market'] ?? 'SET');
        $sym = (string)($stockOrMarket['symbol'] ?? $sym);
    } elseif ($stockOrMarket !== null) {
        $market = (string)$stockOrMarket;
    }

    $marketUpper = strtoupper(trim($market));
    
    // หากตลาดเป็น SET, mai หรือลงท้ายด้วย .BK ถือเป็นหุ้นไทย
    if ($marketUpper === 'SET' || $marketUpper === 'MAI' || str_ends_with(strtoupper($sym), '.BK')) {
        return true;
    }

    return false;
}

/**
 * ดึงรหัสสกุลเงินของหุ้น ('THB' หรือ 'USD')
 *
 * @param array|string|null $stockOrMarket
 * @param string $symbol
 * @return string
 */
function getStockCurrency(array|string|null $stockOrMarket = 'SET', string $symbol = ''): string
{
    return isThaiStock($stockOrMarket, $symbol) ? 'THB' : 'USD';
}

/**
 * ดึงสัญลักษณ์สกุลเงินของหุ้น ('฿' หรือ '$')
 *
 * @param array|string|null $stockOrMarket
 * @param string $symbol
 * @return string
 */
function getStockCurrencySymbol(array|string|null $stockOrMarket = 'SET', string $symbol = ''): string
{
    return isThaiStock($stockOrMarket, $symbol) ? '฿' : '$';
}

/**
 * แปลงมูลค่าจากดอลลาร์สหรัฐ (USD) เป็นบาทไทย (THB)
 *
 * @param float $amountInUsd จำนวนเงินดอลลาร์
 * @param float $rate อัตราแลกเปลี่ยน (ค่าเริ่มต้น 35.50)
 * @return float จำนวนเงินบาท
 */
function convertUsdToThb(float $amountInUsd, float $rate = DEFAULT_USD_THB_RATE): float
{
    return $amountInUsd * $rate;
}

/**
 * แปลงมูลค่าจากบาทไทย (THB) เป็นดอลลาร์สหรัฐ (USD)
 *
 * @param float $amountInThb จำนวนเงินบาท
 * @param float $rate อัตราแลกเปลี่ยน (ค่าเริ่มต้น 35.50)
 * @return float จำนวนเงินดอลลาร์
 */
function convertThbToUsd(float $amountInThb, float $rate = DEFAULT_USD_THB_RATE): float
{
    return ($rate > 0) ? ($amountInThb / $rate) : 0.0;
}

/**
 * จัดรูปแบบราคาหุ้นพร้อมสัญลักษณ์สกุลเงินจริง (เช่น ฿148.50 หรือ $208.48)
 *
 * @param float $price ราคาหุ้น
 * @param array|string|null $stockOrMarket ข้อมูลหุ้นหรือตลาด
 * @param string $symbol ชื่อย่อหุ้น
 * @param int $decimals ทศนิยม
 * @return string
 */
function formatStockPrice(float $price, array|string|null $stockOrMarket = 'SET', string $symbol = '', int $decimals = 2): string
{
    $sym = getStockCurrencySymbol($stockOrMarket, $symbol);
    return $sym . number_format($price, $decimals, '.', ',');
}

/**
 * จัดรูปแบบตัวเลขจำนวนเงิน (เช่น 1,000,000.00 บาท)
 *
 * @param float $amount จำนวนเงิน
 * @param int $decimals ทศนิยม
 * @param string $currency สกุลเงิน
 * @return string
 */
function formatMoney(float $amount, int $decimals = 2, string $currency = 'THB'): string
{
    $formatted = number_format($amount, $decimals, '.', ',');
    
    // หากไม่ระบุสกุลเงิน ให้ส่งคืนเฉพาะตัวเลขทันที
    if ($currency === '') {
        return $formatted;
    }

    return $formatted . ' ' . $currency;
}

/**
 * จัดรูปแบบตัวเลขทั่วไปพร้อมใส่คอมม่าและทศนิยม
 *
 * @param float $number
 * @param int $decimals
 * @return string
 */
function formatNumber(float $number, int $decimals = 2): string
{
    return number_format($number, $decimals, '.', ',');
}

/**
 * จัดรูปแบบเปอร์เซ็นต์ (เช่น +4.58% หรือ -1.57%)
 *
 * @param float $percent ค่าเปอร์เซ็นต์
 * @param bool $showPlus แสดงเครื่องหมายบวกข้างหน้าหรือไม่
 * @param int $decimals ทศนิยม
 * @return string
 */
function formatPercent(float $percent, bool $showPlus = true, int $decimals = 2): string
{
    $sign = '';
    
    // ตรวจสอบเงื่อนไขการใส่เครื่องหมาย +
    if ($showPlus && $percent > 0) {
        $sign = '+';
    }

    return $sign . number_format($percent, $decimals, '.', ',') . '%';
}

/**
 * จัดรูปแบบตัวเลขขนาดใหญ่ให้กระชับ (เช่น 1.85T, 42.15M, 150K)
 *
 * @param float $number ตัวเลข
 * @param int $decimals ทศนิยม
 * @return string
 */
function formatCompactNumber(float $number, int $decimals = 2): string
{
    $abs = abs($number);
    $sign = ($number < 0) ? '-' : '';

    if ($abs >= 1000000000000) {
        return $sign . number_format($abs / 1000000000000, $decimals) . 'T';
    }

    if ($abs >= 1000000000) {
        return $sign . number_format($abs / 1000000000, $decimals) . 'B';
    }

    if ($abs >= 1000000) {
        return $sign . number_format($abs / 1000000, $decimals) . 'M';
    }

    if ($abs >= 1000) {
        return $sign . number_format($abs / 1000, $decimals) . 'K';
    }

    return $sign . number_format($abs, $decimals);
}

/**
 * ดึงชื่อ Class CSS สำหรับสีกำไร/ขาดทุนตามค่าที่เปลี่ยนแปลง
 *
 * @param float $change ค่าราคาหรือ % ที่เปลี่ยน
 * @return string 'text-gain', 'text-loss', หรือ 'text-neutral'
 */
function getPnlColorClass(float $change): string
{
    if ($change > 0) {
        return 'text-gain';
    }

    if ($change < 0) {
        return 'text-loss';
    }

    return 'text-neutral';
}

/**
 * ดึงชื่อ Class Badge สำหรับสีกำไร/ขาดทุน
 *
 * @param float $change
 * @return string
 */
function getPnlBadgeClass(float $change): string
{
    if ($change > 0) {
        return 'badge-gain';
    }

    if ($change < 0) {
        return 'badge-loss';
    }

    return 'badge-neutral';
}

/**
 * ดึงชื่อ Class CSS สีตัวหนังสือสำหรับคะแนน AI Composite Rating (0-100)
 *
 * @param int|float $score
 * @return string 'text-gain', 'text-gold', หรือ 'text-loss'
 */
function getScoreColorClass(int|float $score): string
{
    if ($score >= 80) {
        return 'text-gain';
    }

    if ($score >= 60) {
        return 'text-gold';
    }

    return 'text-loss';
}

/**
 * ดึงชื่อ Class Badge สำหรับคะแนน AI Composite Rating (0-100)
 *
 * @param int|float $score
 * @return string 'badge-gain', 'badge-gold', หรือ 'badge-loss'
 */
function getScoreBadgeClass(int|float $score): string
{
    if ($score >= 80) {
        return 'badge-gain';
    }

    if ($score >= 60) {
        return 'badge-gold';
    }

    return 'badge-loss';
}

/**
 * แปลงวันที่และเวลาเป็นรูปแบบภาษาไทย (พ.ศ.) เช่น "24 สิงหาคม 2569 เวลา 09:46:05 น."
 *
 * @param string|int|DateTimeInterface|null $dateTime
 * @param bool $showTime
 * @return string
 */
function formatThaiDate(mixed $dateTime = null, bool $showTime = true): string
{
    if ($dateTime === null || $dateTime === '') {
        $timestamp = time();
    } elseif (is_numeric($dateTime)) {
        $timestamp = (int)$dateTime;
    } elseif ($dateTime instanceof DateTimeInterface) {
        $timestamp = $dateTime->getTimestamp();
    } else {
        $timestamp = strtotime((string)$dateTime);
        if ($timestamp === false) {
            return (string)$dateTime;
        }
    }

    $thaiMonths = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
        5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
        9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
    ];

    $day   = (int)date('j', $timestamp);
    $month = (int)date('n', $timestamp);
    $year  = (int)date('Y', $timestamp) + 543;

    $monthName = $thaiMonths[$month] ?? '';
    $formattedDate = "{$day} {$monthName} {$year}";

    if ($showTime) {
        $time = date('H:i:s', $timestamp);
        $formattedDate .= " เวลา {$time} น.";
    }

    return $formattedDate;
}

/**
 * ตอบกลับการร้องขอในรูปแบบ JSON พร้อมกำหนด Header ที่ถูกต้อง
 *
 * @param array $data ข้อมูลที่ต้องการส่งกลับ
 * @param int $statusCode รหัสสถานะ HTTP (200, 400, 404, 500)
 * @return never
 */
function jsonResponse(array $data, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * เปลี่ยนเส้นทาง URL หน้าเว็บ (Redirect)
 *
 * @param string $path เส้นทางที่ต้องการย้ายไป
 * @return never
 */
function redirect(string $path): never
{
    // ถ้า path ไม่ได้ขึ้นต้นด้วย http หรือ slash ให้เติม BASE_URL
    if (strpos($path, 'http') !== 0 && strpos($path, '/') !== 0) {
        $path = BASE_URL . '/' . $path;
    }

    header('Location: ' . $path);
    exit;
}

/**
 * ตรวจสอบว่าคำขอส่งมาเป็น AJAX / JSON Request หรือไม่
 *
 * @return bool
 */
function isAjaxRequest(): bool
{
    $hasHeader = isset($_SERVER['HTTP_X_REQUESTED_WITH']);
    $isAjax = $hasHeader && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    $isJson = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

    // ใช้ Early Return ชัดเจน
    if ($isAjax || $isJson) {
        return true;
    }

    return false;
}

/**
 * กำหนดข้อความ Flash Message ชั่วคราวในเซสชัน
 *
 * @param string $type ประเภทข้อความ เช่น 'success', 'danger', 'warning', 'info'
 * @param string $message ข้อความที่ต้องการแจ้งเตือน
 * @return void
 */
function setFlashMessage(string $type, string $message): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $_SESSION['flash_message'] = [
        'type'    => $type,
        'message' => $message
    ];
}

/**
 * ดึงและล้างข้อความ Flash Message จากเซสชัน
 *
 * @return array|null ['type' => string, 'message' => string] หรือ null
 */
function getFlashMessage(): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // หากไม่มีข้อความ Flash Message ให้รีบส่งคืน null ทันที
    if (!isset($_SESSION['flash_message'])) {
        return null;
    }

    $flash = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);

    return $flash;
}

/**
 * สร้าง CSRF Token ประจำเซสชัน (Cryptographically Secure Pseudo-Random Bytes)
 *
 * @return string
 */
function generateCsrfToken(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        startAppSession();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION['csrf_token'];
}

/**
 * ตรวจสอบความถูกต้องของ CSRF Token ป้องกันการโจมตีข้ามไซต์ (Timing Attack Safe)
 *
 * @param string|null $token
 * @return bool
 */
function verifyCsrfToken(?string $token): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        startAppSession();
    }

    if ($token === null || $token === '' || empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals((string)$_SESSION['csrf_token'], $token);
}

/**
 * สร้าง HTML Input Hidden สำหรับส่ง CSRF Token ในแบบฟอร์ม
 *
 * @return string HTML Input Element
 */
function getCsrfInputField(): string
{
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * เรนเดอร์ Empty State Card สำหรับหน้าที่ไม่มีข้อมูลหรือผลลัพธ์ว่างเปล่า (Reusable DRY UI Component)
 *
 * @param string $iconName ชื่อไอคอนจาก icon()
 * @param string $title หัวข้อ
 * @param string $description รายละเอียด
 * @param string $actionHtml ปุ่ม Action เสริม (ถ้ามี)
 * @return string HTML Markup
 */
function renderEmptyState(string $iconName, string $title, string $description, string $actionHtml = ''): string
{
    $iconSvg = icon($iconName, 'text-gold', 28);
    $escapedTitle = e($title);
    $escapedDesc = e($description);

    $html = <<<HTML
<div class="empty-watchlist-state">
  <div class="empty-watchlist-icon">
    {$iconSvg}
  </div>
  <h2 class="empty-state-title">{$escapedTitle}</h2>
  <p class="empty-state-desc">{$escapedDesc}</p>
  {$actionHtml}
</div>
HTML;

    return $html;
}

