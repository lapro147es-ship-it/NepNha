<?php
$rid = (int) $r['id'];
$owner = isOwner($user, $r);
$consult = canConsult($user, $r);
$private = $owner || $consult || hasRole($user, 'ADMIN');
$requestTabs = [
    'request' => 'Tổng quan',
    'request-surveys' => 'Khảo sát',
    'request-quotations' => 'Báo giá',
    'request-work' => 'Công việc',
    'request-designs' => 'Thiết kế',
    'request-documents' => 'Tài liệu',
    'request-payments' => 'Thanh toán',
    'request-activity' => 'Lịch sử'
];
?>

<a class="back-link" href="<?= e(url('requests')) ?>">← Danh sách hồ sơ</a>

<div class="page-heading">
    <div>
        <p class="eyebrow"><?= e($r['code']) ?> · <?= e(TYPE_NAMES[$r['service_type']]) ?></p>
        <h1><?= e($title) ?></h1>
        <p><?= e($r['apartment_name']) ?> · <?= e($r['service_name']) ?></p>
    </div>
    <?= badge($r['status']) ?>
</div>

<nav class="section-tabs" aria-label="Điều hướng hồ sơ">

    <?php foreach ($requestTabs as $routeName => $tabLabel): ?>
    <?php if (!$private &&
            in_array($routeName, ['request-surveys', 'request-quotations', 'request-payments'], true)
        ) {
            continue;
        }
    ?>

    <a class="<?= $page === $routeName ? 'current' : '' ?>" href="<?= e(url($routeName, ['id' => $rid])) ?>">
        <?= e($tabLabel) ?>
    </a>
    <?php endforeach; ?>

</nav>
