<?php
declare(strict_types=1);

/**
 * 🗄️ config/db.php
 * ไฟล์จัดการการเชื่อมต่อฐานข้อมูล MySQL ผ่าน PDO (PHP Data Objects)
 * ออกแบบตามหลัก Singleton Pattern เพื่อประสิทธิภาพและลดภาระเซิร์ฟเวอร์
 */

require_once __DIR__ . '/constants.php';

/**
 * ดึงออบเจกต์การเชื่อมต่อฐานข้อมูล PDO แบบ Singleton
 *
 * @return PDO
 * @throws RuntimeException เมื่อเกิดข้อผิดพลาดในการเชื่อมต่อ
 */
function getDbConnection(): PDO
{
    static $pdo = null;

    // หากมีการเชื่อมต่ออยู่แล้ว ให้ส่งคืนออบเจกต์เดิมทันที (Singleton & Early Return)
    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        // บังคับให้โยน Exception เสมอเมื่อเกิดข้อผิดพลาดในการคิวรี
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // ดึงผลลัพธ์เป็น Associative Array เป็นค่าเริ่มต้น
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // ปิดการจำลอง Prepared Statements เพื่อความปลอดภัยจาก SQL Injection ระดับเซิร์ฟเวอร์
        PDO::ATTR_EMULATE_PREPARES   => false,
        // บังคับใช้การเชื่อมต่อแบบ UTF-8mb4 สมบูรณ์แบบ
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // บันทึกข้อผิดพลาดจริงลง Log เซิร์ฟเวอร์
        error_log('Database Connection Error: ' . $e->getMessage());

        // ส่งข้อความปลอดภัยสำหรับแสดงผล ไม่เปิดเผยรหัสผ่านหรือโครงสร้างภายใน
        throw new RuntimeException(
            'ไม่สามารถเชื่อมต่อฐานข้อมูลได้ กรุณาตรวจสอบว่าเปิด MySQL ใน XAMPP และนำเข้าไฟล์ database_schema.sql เรียบร้อยแล้ว'
        );
    }
}
