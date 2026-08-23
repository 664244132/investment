<?php
declare(strict_types=1);

/**
 * templates/flash.php
 * Modular Flash Message Alert Renderer
 * SmartPort AI
 */

$flash = $flash ?? getFlashMessage();
if ($flash):
    $alertClass = 'alert-warning';
    $alertIcon = 'info';

    if ($flash['type'] === 'success') {
        $alertClass = 'alert-success';
        $alertIcon = 'check';
    } elseif ($flash['type'] === 'danger' || $flash['type'] === 'error') {
        $alertClass = 'alert-danger';
        $alertIcon = 'alert-triangle';
    }
?>
  <div class="alert <?= e($alertClass) ?>" role="alert">
    <?= icon($alertIcon, '', 16) ?>
    <span><?= e($flash['message']) ?></span>
  </div>
<?php endif; ?>
