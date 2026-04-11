<?php
/**
 * El Celler Nou - Admin Logout
 */
require_once __DIR__ . '/includes/auth.php';

logout();

header('Location: ' . getAdminUrl('index.php?logout=1'));
exit;
