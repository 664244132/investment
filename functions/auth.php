<?php
declare(strict_types=1);

/**
 * 🔐 functions/auth.php
 * โมดูลจัดการระบบยืนยันตัวตน (Authentication) และความปลอดภัยของเซสชัน
 * ปฏิบัติตามมาตรฐานความปลอดภัย: Password Hashing, Session Fixation Protection, Prepared Statements
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/helpers.php';

/**
 * เริ่มต้นการทำงานของเซสชันอย่างปลอดภัย (Session Hardening)
 *
 * @return void
 */
function startAppSession(): void
{
    // หากเซสชันทำงานอยู่แล้ว ให้ข้ามได้เลย (Early Return)
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // กำหนดการตั้งค่าความปลอดภัยของ Cookie เซสชัน
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');

    session_name(SESSION_NAME);
    session_start();
}

/**
 * ตรวจสอบและเข้าสู่ระบบ (User Login)
 *
 * @param PDO $pdo ออบเจกต์การเชื่อมต่อฐานข้อมูล
 * @param string $usernameOrEmail ชื่อผู้ใช้หรืออีเมล
 * @param string $password รหัสผ่านที่ผู้ใช้กรอก
 * @return array ['success' => bool, 'message' => string, 'user' => ?array]
 */
function loginUser(PDO $pdo, string $usernameOrEmail, string $password): array
{
    startAppSession();

    $usernameOrEmail = trim($usernameOrEmail);

    // Guard Clause 1: ตรวจสอบข้อมูลว่าง
    if ($usernameOrEmail === '' || $password === '') {
        return [
            'success' => false,
            'message' => 'กรุณากรอกชื่อผู้ใช้/อีเมล และรหัสผ่านให้ครบถ้วน',
            'user'    => null
        ];
    }

    // ค้นหาข้อมูลผู้ใช้ในฐานข้อมูลด้วย Prepared Statement ป้องกัน SQL Injection
    $sql = "SELECT id, username, email, password_hash, full_name, role 
            FROM users 
            WHERE username = :identifier_user OR email = :identifier_email 
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':identifier_user'  => $usernameOrEmail,
        ':identifier_email' => $usernameOrEmail
    ]);
    $user = $stmt->fetch();

    // Guard Clause 2: ตรวจสอบว่าพบผู้ใช้หรือไม่
    if (!$user) {
        return [
            'success' => false,
            'message' => 'ไม่พบบัญชีผู้ใช้ หรือรหัสผ่านไม่ถูกต้อง',
            'user'    => null
        ];
    }

    // Guard Clause 3: ตรวจสอบรหัสผ่านด้วย password_verify
    if (!password_verify($password, $user['password_hash'])) {
        return [
            'success' => false,
            'message' => 'ไม่พบบัญชีผู้ใช้ หรือรหัสผ่านไม่ถูกต้อง',
            'user'    => null
        ];
    }

    // ป้องกัน Session Fixation Attack ด้วยการสร้าง Session ID ใหม่ทันทีเมื่อยืนยันตัวตนสำเร็จ
    session_regenerate_id(true);

    // บันทึกข้อมูลที่ปลอดภัยลง Session (ไม่เก็บ password_hash)
    $_SESSION['user_id']   = (int)$user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['email']     = $user['email'];
    $_SESSION['full_name'] = $user['full_name'] ?? $user['username'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['logged_in'] = true;

    return [
        'success' => true,
        'message' => 'เข้าสู่ระบบสำเร็จ ยินดีต้อนรับกลับมา!',
        'user'    => [
            'id'        => (int)$user['id'],
            'username'  => $user['username'],
            'email'     => $user['email'],
            'full_name' => $user['full_name'],
            'role'      => $user['role']
        ]
    ];
}

/**
 * สมัครสมาชิกใหม่ (User Registration)
 *
 * @param PDO $pdo
 * @param string $username
 * @param string $email
 * @param string $password
 * @param string $fullName
 * @return array ['success' => bool, 'message' => string, 'user_id' => ?int]
 */
function registerUser(
    PDO $pdo,
    string $username,
    string $email,
    string $password,
    string $fullName = ''
): array {
    $username = trim($username);
    $email    = trim($email);
    $fullName = trim($fullName);

    // Guard Clause 1: ตรวจสอบความถูกต้องของข้อมูลพื้นฐาน
    if ($username === '' || $email === '' || $password === '') {
        return [
            'success' => false,
            'message' => 'กรุณากรอกข้อมูลให้ครบถ้วนทุกช่องที่มีเครื่องหมายดอกจัน (*)',
            'user_id' => null
        ];
    }

    // Guard Clause 2: ตรวจสอบรูปแบบอีเมล
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'success' => false,
            'message' => 'รูปแบบอีเมลไม่ถูกต้อง กรุณาตรวจสอบใหม่อีกครั้ง',
            'user_id' => null
        ];
    }

    // Guard Clause 3: ตรวจสอบความยาวรหัสผ่าน (อย่างน้อย 6 ตัวอักษร)
    if (strlen($password) < 6) {
        return [
            'success' => false,
            'message' => 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร',
            'user_id' => null
        ];
    }

    // ตรวจสอบว่าชื่อผู้ใช้หรืออีเมลซ้ำหรือไม่
    $checkSql = "SELECT id, username, email FROM users WHERE username = :username OR email = :email LIMIT 1";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([':username' => $username, ':email' => $email]);
    $existing = $checkStmt->fetch();

    if ($existing) {
        if ($existing['username'] === $username) {
            return [
                'success' => false,
                'message' => 'ชื่อผู้ใช้นี้มีผู้อื่นใช้งานแล้ว กรุณาเลือกชื่ออื่น',
                'user_id' => null
            ];
        }

        return [
            'success' => false,
            'message' => 'อีเมลนี้ได้ลงทะเบียนไว้ในระบบแล้ว กรุณาใช้อีเมลอื่นหรือเข้าสู่ระบบ',
            'user_id' => null
        ];
    }

    // เข้ารหัสผ่านด้วย Password Hashing API มาตรฐานของ PHP
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        // 1. สร้างบัญชีผู้ใช้ใหม่
        $insertSql = "INSERT INTO users (username, email, password_hash, full_name, role) 
                      VALUES (:username, :email, :password_hash, :full_name, 'user')";
        
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([
            ':username'      => $username,
            ':email'         => $email,
            ':password_hash' => $passwordHash,
            ':full_name'     => ($fullName !== '') ? $fullName : $username
        ]);

        $newUserId = (int)$pdo->lastInsertId();

        // 2. สร้างพอร์ตการลงทุนจำลองเริ่มต้นให้อัตโนมัติ (เงินทุนเริ่มต้น 1,000,000 บาท)
        $portfolioSql = "INSERT INTO portfolios (user_id, portfolio_name, initial_cash, cash_balance) 
                         VALUES (:user_id, :name, :initial_cash, :cash_balance)";
        
        $portfolioStmt = $pdo->prepare($portfolioSql);
        $portfolioStmt->execute([
            ':user_id'      => $newUserId,
            ':name'         => 'พอร์ตจำลองของ ' . $username,
            ':initial_cash' => DEFAULT_PORTFOLIO_CASH,
            ':cash_balance' => DEFAULT_PORTFOLIO_CASH
        ]);

        $pdo->commit();

        return [
            'success' => true,
            'message' => 'สมัครสมาชิกสำเร็จ! พอร์ตจำลองเงินทุน 1,000,000 บาท พร้อมใช้งานแล้ว',
            'user_id' => $newUserId
        ];
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log('Registration Error: ' . $e->getMessage());

        return [
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดของระบบในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง',
            'user_id' => null
        ];
    }
}

/**
 * ออกจากระบบ (Logout) และล้างเซสชันทั้งหมด
 *
 * @return void
 */
function logoutUser(): void
{
    startAppSession();

    $_SESSION = [];

    // ลบ Cookie ของ Session
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

/**
 * ตรวจสอบว่าผู้ใช้กำลังเข้าสู่ระบบอยู่หรือไม่
 *
 * @return bool
 */
function isLoggedIn(): bool
{
    startAppSession();

    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_id'])) {
        return true;
    }

    return false;
}

/**
 * ดึงข้อมูลผู้ใช้งานปัจจุบันจากเซสชัน
 *
 * @return array|null
 */
function getCurrentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id'        => (int)$_SESSION['user_id'],
        'username'  => $_SESSION['username'],
        'email'     => $_SESSION['email'],
        'full_name' => $_SESSION['full_name'],
        'role'      => $_SESSION['role']
    ];
}

/**
 * บังคับให้ต้องเข้าสู่ระบบก่อนเข้าใช้งานหน้านี้
 *
 * @param string $redirectPage
 * @return void
 */
function requireLogin(string $redirectPage = 'pages/login.php'): void
{
    if (isLoggedIn()) {
        return;
    }

    setFlashMessage('warning', 'กรุณาเข้าสู่ระบบก่อนเข้าใช้งานหน้านี้');
    redirect($redirectPage);
}
