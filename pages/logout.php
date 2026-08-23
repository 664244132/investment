<?php
declare(strict_types=1);

/**
 * 🚪 pages/logout.php
 * ตัวจัดการการออกจากระบบ (Logout Handler)
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/auth.php';

logoutUser();
setFlashMessage('info', 'คุณได้ออกจากระบบเรียบร้อยแล้ว');
redirect('pages/login.php');
