<?php
declare(strict_types=1);

/**
 * config/constants.php
 * SmartPort AI - System Constants & Configurationไฟล์กำหนดค่าคงที่และการตั้งค่าพื้นฐานของระบบวิเคราะห์หุ้นและพอร์ตจำลอง
 */

// ตรวจสอบว่ามีการโหลดค่าคงที่ซ้ำหรือไม่
if (!defined('APP_INIT')) {
    define('APP_INIT', true);

    // ข้อมูลแอปพลิเคชัน
    define('APP_NAME', 'SmartPort AI');
    define('APP_TITLE', 'SmartPort AI - ระบบวิเคราะห์หุ้นและพอร์ตจำลองอัจฉริยะ');
    define('APP_VERSION', '1.4.5');

    // URL และ Path พื้นฐาน (Auto-detect BASE_URL สำหรับ Localhost XAMPP และ Production Hosting เช่น InfinityFree)
    if (!defined('BASE_URL')) {
        $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']) ?: $_SERVER['DOCUMENT_ROOT']), '/') : '';
        $projectRoot = rtrim(str_replace('\\', '/', realpath(dirname(__DIR__)) ?: dirname(__DIR__)), '/');

        if (!empty($docRoot) && strpos($projectRoot, $docRoot) === 0) {
            $subDir = substr($projectRoot, strlen($docRoot));
            $detectedBase = rtrim(str_replace('\\', '/', $subDir), '/');
        } elseif (isset($_SERVER['SCRIPT_NAME'])) {
            $scriptDir = dirname(str_replace('\\', '/', $_SERVER['SCRIPT_NAME']));
            $clean = preg_replace('#/(pages|api|errors|functions|templates|config)$#', '', $scriptDir);
            $detectedBase = ($clean === '/' || $clean === '.') ? '' : rtrim($clean, '/');
        } else {
            $detectedBase = '';
        }

        define('BASE_URL', $detectedBase);
    }
    define('ASSETS_URL', BASE_URL . '/assets');
    define('CSS_URL', BASE_URL . '/css');
    define('JS_URL', BASE_URL . '/js');

    // การตั้งค่าฐานข้อมูล (รองรับ Environment Variables หรือค่า Default สำหรับ XAMPP / InfinityFree)
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
    define('DB_NAME', getenv('DB_NAME') ?: 'investment_db');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: '');
    define('DB_CHARSET', 'utf8mb4');

    // การตั้งค่าพอร์ตการลงทุนจำลองและการแลกเปลี่ยนสกุลเงิน (Multi-Currency & FX)
    define('DEFAULT_PORTFOLIO_CASH', 1000000.00); // เงินทุนตั้งต้น 1,000,000 บาท
    define('SIMULATED_BROKER_FEE_RATE', 0.00157); // ค่าคอมมิชชันจำลอง 0.157%
    define('DEFAULT_USD_THB_RATE', 35.50);        // อัตราแลกเปลี่ยนอ้างอิงมาตรฐาน 1 USD = 35.50 THB

    // การตั้งค่าเซสชัน (Session Security)
    define('SESSION_NAME', 'AI_INVEST_SESSION');
    define('SESSION_LIFETIME', 86400); // 1 วัน (24 ชั่วโมง)
}
