<?php
/**
 * Thông tin nhập tay cho AMnote Server Management.
 * Chỉ sửa các chuỗi bên dưới theo thông tin thật của server .82.
 * Không lưu mật khẩu, API key hay dữ liệu nhạy cảm trong file này.
 * File bị chặn HTTP bởi VirtualHost, chỉ được PHP include nội bộ.
 */
return array(
    'position'     => '', // Vi du: Vietnam FPT-DC7-K5-11 (chi dien neu dung)
    'datacenter'   => '', // Ten datacenter
    'rack'         => '', // Ma rack/vi tri
    'serverRole'   => 'AMnote Web / API',
    'provider'     => '', // Don vi hosting
    'storageNotes' => '', // Vi du: 2 x SSD 500GB, RAID1 - can xac minh thuc te
    'backupNotes'  => ''  // Vi du: 1 x HDD 4TB backup - can xac minh thuc te
);
