<?php
declare(strict_types=1);

function customerDashboard(array $user): array
{
    $customer = ['id' => $user['id'], 'roles' => ['CUSTOMER']];
    $requests = requestRows($customer, 1000);
    $selection = (int) ($_GET['project_id'] ?? 0);
    $project = null;

    if ($selection) {
        $project = requestAccess($user, $selection);
        if (!isOwner($user, $project)) {
            throw new DomainException('Hồ sơ được chọn không thuộc tài khoản của bạn.');
        }
    } else {
        foreach ($requests['rows'] as $candidate) {
            if (in_array($candidate['status'], ['IN_PROGRESS', 'APPROVED'], true)) {
                $project = requestAccess($user, (int) $candidate['id']);
                break;
            }
        }
        if (!$project && $requests['rows']) {
            $project = requestAccess($user, (int) $requests['rows'][0]['id']);
        }
    }

    $actions = [];
    $definitions = [
        [
            'quotations',
            'SENT',
            'quotation-detail',
            'Báo giá cần phản hồi',
            'Xem báo giá',
            'quotations'
        ],
        [
            'designs',
            'SUBMITTED',
            'design-detail',
            'Thiết kế chờ bạn duyệt',
            'Xem thiết kế',
            'designs'
        ],
        ['tasks', 'REPORTED', 'task-detail', 'Kết quả chờ nghiệm thu', 'Kiểm tra kết quả', 'tasks'],
        ['surveys', 'PENDING', 'survey-detail', 'Xác nhận lịch khảo sát', 'Xem lịch hẹn', 'surveys']
    ];
    foreach ($definitions as [$kind, $status, $route, $label, $cta, $symbol]) {
        $rows = moduleRows($kind, $customer, 1, '', $status)['rows'];
        foreach ($rows as $row) {
            if ($kind === 'tasks' && $row['work_type'] === 'DESIGN') {
                continue;
            }
            $actions[] = [
                'label' => $label,
                'cta' => $cta,
                'icon' => $symbol,
                'apartment' => $row['apartment_name'],
                'code' => $row['request_code'],
                'url' => url($route, ['id' => $row['id']])
            ];
            break;
        }
    }

    $appointments = Database::all(
        "SELECT sv.*,sr.code,a.name apartment_name,u.full_name surveyor_name
         FROM surveys sv JOIN service_requests sr ON sr.id=sv.request_id
         JOIN apartments a ON a.id=sr.apartment_id JOIN users u ON u.id=sv.surveyor_id
         WHERE sr.customer_id=? AND sv.status IN ('PENDING','CONFIRMED') AND sv.ends_at>=?
         ORDER BY sv.starts_at LIMIT 2",
        [$user['id'], now()]
    );
    $finance = $project
        ? finances((int) $project['id'])
        : ['total' => 0, 'paid' => 0, 'remaining' => 0, 'quote' => null];
    $jobs = $project
        ? Database::all(
            "SELECT w.*,u.full_name assignee_name,u.phone,
         (SELECT wu.progress_percent FROM work_updates wu WHERE wu.work_order_id=w.id
          AND wu.progress_percent IS NOT NULL ORDER BY wu.id DESC LIMIT 1) latest_progress
         FROM work_orders w JOIN users u ON u.id=w.assignee_id
         WHERE w.request_id=? AND w.status<>'CANCELLED' ORDER BY w.id",
            [$project['id']]
        )
        : [];
    $done = 0;
    $progressTotal = 0;
    foreach ($jobs as $job) {
        $done += $job['status'] === 'DONE' ? 1 : 0;
        $progressTotal += in_array($job['status'], ['DONE', 'REPORTED'], true)
            ? 100
            : (int) ($job['latest_progress'] ?? 0);
    }
    $progress = $jobs ? (int) round($progressTotal / count($jobs)) : 0;
    $team = [];
    if ($project && $project['consultant_id']) {
        $person = Database::one('SELECT id,full_name,phone FROM users WHERE id=?', [
            $project['consultant_id']
        ]);
        if ($person) {
            $team[$person['id']] = $person + ['job_label' => 'Tư vấn phụ trách'];
        }
    }
    foreach ($jobs as $job) {
        $team[$job['assignee_id']] = [
            'id' => $job['assignee_id'],
            'full_name' => $job['assignee_name'],
            'phone' => $job['phone'],
            'job_label' => TYPE_NAMES[$job['work_type']]
        ];
    }
    $files = [];
    if ($project) {
        foreach (
            Database::all(
                'SELECT a.id FROM attachments a WHERE a.request_id=? AND NOT EXISTS (SELECT 1 FROM quotation_attachments qa WHERE qa.attachment_id=a.id) ORDER BY a.uploaded_at DESC,a.id DESC LIMIT 4',
                [$project['id']]
            )
            as $file
        ) {
            $files[] = attachmentAccess($customer, (int) $file['id']);
        }
    }
    $completed = $project && in_array($project['status'], ['COMPLETED', 'CLOSED'], true);
    $received = $project && !in_array($project['status'], ['NEW', 'CANCELLED', 'REJECTED'], true);
    $design = false;
    $execution = false;
    foreach ($jobs as $job) {
        if ($job['work_type'] === 'DESIGN' && $job['status'] === 'DONE') {
            $design = true;
        }
        if ($job['work_type'] !== 'DESIGN' && $job['status'] === 'DONE') {
            $execution = true;
        }
    }
    $serviceType = $project['service_type'] ?? 'PACKAGE';
    $steps = [
        ['Tiếp nhận', $received],
        ['Báo giá', (bool) $finance['quote']],
        [
            $serviceType === 'DESIGN' || $serviceType === 'PACKAGE' ? 'Thiết kế' : 'Phân công',
            in_array($serviceType, ['DESIGN', 'PACKAGE'], true) ? $design : (bool) $jobs
        ],
        [
            $serviceType === 'MAINTENANCE'
                ? 'Bảo trì'
                : ($serviceType === 'DESIGN'
                    ? 'Duyệt bản vẽ'
                    : 'Lắp đặt'),
            $serviceType === 'DESIGN' ? $design : $execution
        ],
        ['Nghiệm thu', $completed]
    ];
    return compact(
        'requests',
        'project',
        'actions',
        'appointments',
        'finance',
        'jobs',
        'done',
        'progress',
        'team',
        'files',
        'steps'
    );
}
