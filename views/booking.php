<?php if ($page === 'booking-received'): ?>
<section class="public-section panel commerce-auth">
    <p class="eyebrow">YÊU CẦU ĐÃ ĐƯỢC TIẾP NHẬN</p>
    <h1>Cảm ơn bạn đã chia sẻ về căn hộ.</h1>
    <p>Mã yêu cầu: <strong><?= e($_SESSION['lead_receipt'] ?? 'Không có yêu cầu vừa gửi trong phiên này') ?></strong>
    </p>
    <p>Tư vấn viên sẽ liên hệ xác nhận hiện trạng và lịch hẹn. Ngày bạn chọn chưa phải lịch khảo sát
        đã được xác nhận; chưa phát sinh nghĩa vụ thanh toán.</p>
    <a class="button" href="<?= e(url('catalog')) ?>">Tiếp tục khám phá</a>
</section>
<?php else: ?>
<section class="public-section commerce-booking">
    <div>
        <p class="eyebrow">KHÔNG CẦN TÀI KHOẢN</p>
        <h1>Đặt lịch tư vấn đầu tiên cho tổ ấm.</h1>
        <p>Để lại nhu cầu, căn hộ và thời gian mong muốn. Chúng tôi tiếp nhận trước, xác nhận lịch
            và phạm vi sau khi trao đổi.</p>
        <img <?= photoAttributes('living-room') ?>>
    </div>
    <section class="panel form-panel">

        <?php
        formStart('lead_create');
        field('full_name', 'Họ tên', oldValue('full_name'));
        field('email', 'Email', oldValue('email'), 'email');
        field('phone', 'Số điện thoại', oldValue('phone'), 'tel');
        field('address', 'Địa chỉ căn hộ', oldValue('address'));
        choose(
            'service_id',
            'Dịch vụ quan tâm',
            array_column(
                Database::all('SELECT id,name FROM services WHERE is_active=1'),
                'name',
                'id'
            ),
            oldValue('service_id', $_GET['service_id'] ?? '')
        );
        field('preferred_date', 'Ngày mong muốn', oldValue('preferred_date'), 'date', false);
        area('content', 'Bạn muốn thay đổi hoặc bảo trì những gì?', oldValue('content'));
        ?>

        <label class="check">
            <input type="checkbox" name="consent" value="1" required> Tôi đồng ý để Nếp Nhà lưu
            thông tin này và liên hệ tư vấn cho yêu cầu của tôi.
        </label>
        <button class="button full" type="submit">Gửi đăng ký tư vấn →</button>
        </form>
    </section>
</section>
<?php endif; ?>
