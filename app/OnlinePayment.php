<?php
declare(strict_types=1);

final class OnlinePayment
{
    public static function create(array $user, array $data): string
    {
        global $config;
        requireRole($user, 'CUSTOMER');
        return Database::transaction(static function () use ($user, $data, $config): string {
            $user =
                userById((int) $user['id']) ??
                throw new DomainException('Tài khoản không còn hoạt động.');
            requireRole($user, 'CUSTOMER');
            $request = requestAccess($user, positiveId($data, 'request_id'));
            if ((int) $request['customer_id'] !== (int) $user['id']) {
                throw new DomainException('Bạn không phải chủ hồ sơ.');
            }
            if (!in_array($request['status'], ['APPROVED', 'IN_PROGRESS', 'COMPLETED'], true)) {
                throw new DomainException('Hồ sơ hiện không nhận thêm thanh toán.');
            }
            $finance = finances((int) $request['id']);
            $amount = amount($data, 'amount');
            if (!$finance['quote'] || $amount > $finance['remaining']) {
                throw new DomainException(
                    'Cần báo giá đã duyệt và số tiền không vượt khoản còn lại.'
                );
            }
            $mode = $config['payment_mode'] ?? 'demo';
            if (!in_array($mode, ['demo', 'vnpay_sandbox', 'vnpay'], true)) {
                throw new DomainException('Cổng thanh toán chưa được cấu hình.');
            }
            if (
                $mode !== 'demo' &&
                (empty($config['vnpay_tmn_code']) ||
                    empty($config['vnpay_hash_secret']) ||
                    empty($config['app_url']))
            ) {
                throw new DomainException(
                    'Cần cấu hình mã merchant, khóa ký và URL ứng dụng trước khi dùng VNPAY.'
                );
            }
            if ($mode === 'vnpay' && parse_url($config['app_url'], PHP_URL_SCHEME) !== 'https') {
                throw new DomainException('Cổng thực tế cần app_url HTTPS.');
            }
            $id = Database::insert('payment_intents', [
                'request_id' => $request['id'],
                'customer_id' => $user['id'],
                'reference_code' => 'NNP' . gmdate('ymdHis') . strtoupper(bin2hex(random_bytes(5))),
                'amount' => $amount,
                'provider' => strtoupper($mode),
                'status' => 'PENDING',
                'expires_at' => gmdate('Y-m-d H:i:s', time() + 900),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            return url('online-payment', ['id' => $id]);
        });
    }

    public static function access(array $user, int $id): array
    {
        $intent = Database::one('SELECT * FROM payment_intents WHERE id=?', [$id]);
        if (!$intent) {
            throw new DomainException('Không tìm thấy giao dịch.');
        }
        $r = privateRequest($user, (int) $intent['request_id']);
        if (
            (int) $intent['customer_id'] !== (int) $user['id'] &&
            !hasRole($user, 'ADMIN', 'CONSULTANT')
        ) {
            throw new DomainException('Giao dịch không thuộc tài khoản của bạn.');
        }
        return $intent;
    }

    public static function demo(array $user, array $data): string
    {
        requireRole($user, 'CUSTOMER');
        return Database::transaction(static function () use ($user, $data): string {
            $user =
                userById((int) $user['id']) ??
                throw new DomainException('Tài khoản không còn hoạt động.');
            requireRole($user, 'CUSTOMER');
            $intent = self::access($user, positiveId($data, 'id'));
            if (
                (int) $intent['customer_id'] !== (int) $user['id'] ||
                $intent['provider'] !== 'DEMO'
            ) {
                throw new DomainException('Không thể mô phỏng giao dịch này.');
            }
            if ($intent['status'] !== 'PENDING') {
                return url('online-payment', ['id' => $intent['id']]);
            }
            if ($intent['expires_at'] < now()) {
                throw new DomainException('Phiên thanh toán đã hết hạn.');
            }
            $status = match ($data['result'] ?? '') {
                'success' => 'DEMO_SUCCESS',
                'cancel' => 'CANCELLED',
                'fail' => 'FAILED',
                default => throw new DomainException('Kết quả không hợp lệ.')
            };
            Database::run('UPDATE payment_intents SET status=?,updated_at=? WHERE id=?', [
                $status,
                now(),
                $intent['id']
            ]);
            notifyUser(
                (int) $user['id'],
                'demo-payment:' . $intent['id'],
                'Đã hoàn tất mô phỏng thanh toán',
                'Kết quả: ' . $status . '. Không trừ tiền và không ghi nhận khoản thu thật.',
                'online-payment',
                (int) $intent['id']
            );
            return url('online-payment', ['id' => $intent['id']]);
        });
    }

    public static function signedUrl(array $intent): string
    {
        global $config;
        if (
            !in_array($intent['provider'], ['VNPAY', 'VNPAY_SANDBOX'], true) ||
            $intent['status'] !== 'PENDING' ||
            $intent['expires_at'] < now()
        ) {
            throw new DomainException('Giao dịch không còn chờ thanh toán.');
        }
        $base = rtrim($config['app_url'], '/');
        $parameters = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $config['vnpay_tmn_code'],
            'vnp_Amount' => (string) ((int) $intent['amount'] * 100),
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $intent['reference_code'],
            'vnp_OrderInfo' => 'Thanh toan ho so noi that ' . $intent['request_id'],
            'vnp_OrderType' => 'other',
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => $base . '/index.php?page=online-return',
            'vnp_IpAddr' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_ExpireDate' => date('YmdHis', strtotime($intent['expires_at'] . ' UTC'))
        ];
        $query = self::query($parameters);
        $endpoint =
            $intent['provider'] === 'VNPAY_SANDBOX'
                ? 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'
                : 'https://pay.vnpay.vn/vpcpay.html';
        return $endpoint .
            '?' .
            $query .
            '&vnp_SecureHash=' .
            hash_hmac('sha512', $query, $config['vnpay_hash_secret']);
    }

    private static function query(array $parameters): string
    {
        ksort($parameters);
        return http_build_query($parameters, '', '&', PHP_QUERY_RFC1738);
    }

    public static function callback(array $input): array
    {
        global $config;
        $parameters = [];
        foreach ($input as $key => $value) {
            if (
                str_starts_with((string) $key, 'vnp_') &&
                !in_array($key, ['vnp_SecureHash', 'vnp_SecureHashType'], true)
            ) {
                if (!is_scalar($value)) {
                    throw new DomainException('Dữ liệu cổng thanh toán không hợp lệ.');
                }
                $parameters[$key] = (string) $value;
            }
        }
        $signature = $input['vnp_SecureHash'] ?? '';
        if (
            empty($config['vnpay_hash_secret']) ||
            !is_string($signature) ||
            !hash_equals(
                hash_hmac('sha512', self::query($parameters), $config['vnpay_hash_secret']),
                $signature
            ) ||
            ($parameters['vnp_TmnCode'] ?? '') !== ($config['vnpay_tmn_code'] ?? '')
        ) {
            throw new DomainException('Chữ ký cổng thanh toán không hợp lệ.');
        }
        return Database::transaction(static function () use ($parameters): array {
            $intent = Database::one('SELECT * FROM payment_intents WHERE reference_code=?', [
                $parameters['vnp_TxnRef'] ?? ''
            ]);
            if (
                !$intent ||
                !in_array($intent['provider'], ['VNPAY', 'VNPAY_SANDBOX'], true) ||
                ($parameters['vnp_Amount'] ?? '') !== (string) ((int) $intent['amount'] * 100)
            ) {
                throw new DomainException('Giao dịch hoặc số tiền không khớp.');
            }
            if ($intent['status'] !== 'PENDING') {
                return $intent + ['duplicate' => true];
            }
            $success =
                ($parameters['vnp_ResponseCode'] ?? '') === '00' &&
                ($parameters['vnp_TransactionStatus'] ?? '') === '00';
            $status = $success
                ? ($intent['provider'] === 'VNPAY_SANDBOX'
                    ? 'SANDBOX_SUCCESS'
                    : 'SUCCESS')
                : 'FAILED';
            $payment = null;
            if ($success && $intent['provider'] === 'VNPAY') {
                $finance = finances((int) $intent['request_id']);
                if (!$finance['quote'] || $finance['remaining'] < (int) $intent['amount']) {
                    throw new DomainException('Giao dịch cần đối soát vì công nợ đã thay đổi.');
                }
                $txn = txt($parameters, 'vnp_TransactionNo', 100);
                if (
                    Database::one('SELECT id FROM payment_intents WHERE provider_txn=? AND id<>?', [
                        $txn,
                        $intent['id']
                    ])
                ) {
                    throw new DomainException('Mã giao dịch đã được dùng.');
                }
                $request = Database::one('SELECT consultant_id FROM service_requests WHERE id=?', [
                    $intent['request_id']
                ]);
                $payment = Database::insert('payments', [
                    'request_id' => $intent['request_id'],
                    'quotation_id' => $finance['quote'],
                    'receipt_code' => 'VNP-' . $intent['reference_code'],
                    'amount' => $intent['amount'],
                    'method' => 'BANK_TRANSFER',
                    'status' => 'POSTED',
                    'paid_at' => now(),
                    'recorded_by' => $request['consultant_id'],
                    'created_at' => now()
                ]);
                Database::insert('audit_logs', [
                    'actor_id' => $request['consultant_id'],
                    'request_id' => $intent['request_id'],
                    'entity_type' => 'payment',
                    'entity_id' => $payment,
                    'action' => 'VNPAY_VERIFIED',
                    'reason' => 'Đối soát phản hồi cổng có chữ ký, số tiền và merchant khớp.',
                    'created_at' => now()
                ]);
            }
            Database::run(
                'UPDATE payment_intents SET status=?,provider_txn=?,payment_id=?,updated_at=? WHERE id=?',
                [
                    $status,
                    $success ? $parameters['vnp_TransactionNo'] ?? null : null,
                    $payment,
                    now(),
                    $intent['id']
                ]
            );
            notifyUser(
                (int) $intent['customer_id'],
                'online-payment:' . $intent['id'],
                'Giao dịch thanh toán đã cập nhật',
                'Kết quả: ' .
                    $status .
                    ($intent['provider'] === 'VNPAY_SANDBOX'
                        ? '. Giao dịch thử, không ghi nhận tiền thật.'
                        : ''),
                'online-payment',
                (int) $intent['id']
            );
            return Database::one('SELECT * FROM payment_intents WHERE id=?', [$intent['id']]);
        });
    }
}
