<?php
declare(strict_types=1);

/**
 * pages/logout.php
 * SmartPort AI - Secure Session Termination
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/auth.php';

logoutUser();
setFlashMessage('info', 'คุณได้ออกจากระบบเรียบร้อยแล้ว');
redirect('pages/login.php');
