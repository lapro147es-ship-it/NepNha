<?php
if (hasRole($user, 'CUSTOMER')) {
    require __DIR__ . '/customer-dashboard.php';
} else {
    require __DIR__ . '/staff-dashboard.php';
}
