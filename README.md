# AMnote Server Management v3 — Thông tin server đầy đủ

Phiên bản v3 mở rộng menu **Thông tin server** theo nhu cầu quản trị của AMnote:

- Định danh: hostname, IP/host truy cập (là cấu hình liên kết, không nhất thiết là IP network thật), OS, kernel, architecture, phiên bản PHP đang phục vụ API.
- Phần cứng: hãng/model/version mainboard (DMI), thông tin máy chủ, CPU model, sockets, cores/socket, logical threads.
- Bộ nhớ: RAM hệ thống khả dụng (Linux `/proc/meminfo`), đã dùng, khả dụng, Swap tổng/dùng/trống.
- Lưu trữ: từng thiết bị `/sys/block/*` gồm loại ổ do kernel báo, model, dung lượng; mdadm RAID `/proc/mdstat`; danh sách mounted filesystem `/proc/mounts` (tổng/dùng/trống).
- Hạ tầng: giao diện mạng, trạng thái, link speed, MTU.
- Metadata nhập tay: position, datacenter, rack, provider, backup, vai trò máy chủ và ghi chú lưu trữ (`server-meta.php`).

**QUAN TRỌNG:** Ví dụ phần cứng `118.69.170.74` chỉ là mẫu mà NhonTT đưa ra, không phải thông số của `.82`. Trang v3 không tự gán cấu hình server `.74` sang `.82`. Không mặc định phân loại RAID1, số ổ backup hoặc vị trí datacenter. Mainboard có thể bị Linux che bởi quyền DMI hoặc môi trường ảo.

## File

- `index.php`: giao diện 4 menu, mở rộng riêng menu Thông tin server, tự làm mới mỗi **3 giây**.
- `status.php`: API chỉ đọc thông số thật, PHP 7.3, không dùng shell, không truy cập database, không restart service.
- `server-meta.php`: thông tin phải xác minh và điền thủ công. **Không nhập password/token**.
- `access.php`: giữ nguyên danh sách IP cho phép từ bản v2.

## Cập nhật trên server `118.69.170.82`

Đường dẫn live: `/home/WEB/ammonitor`. Cấu hình Apache hiện tại chỉ cho HTTP truy cập `index.php` và `status.php`, các file `.php` khác được chặn ở VirtualHost. Nếu cấu hình thay đổi, phải xác minh `server-meta.php` không thể tải từ ngoài.

1. Sao lưu file `index.php` và `status.php` hiện tại.
2. Upload/copy `index.php`, `status.php`, `server-meta.php` từ gói v3 vào `/home/WEB/ammonitor` (không cần thay `access.php`).
3. Kiểm tra cú pháp với `/usr/local/php7.3/bin/php -l` cho các file PHP; kiểm tra JSON từ `status.php` bằng curl localhost sau cập nhật.
4. Điền chính xác vị trí/rack/backup trong `server-meta.php` sau khi xác nhận với nhà cung cấp server.
5. Nhấn Ctrl+F5 rồi vào menu **Thông tin server**.

Không cần restart Apache hoặc PHP-FPM để thay đổi mã PHP này (trừ khi PHP OPcache được cấu hình không kiểm tra timestamp).

## Bảo mật

- Giữ `access.php` và `Require ip` trong Apache; nguồn số liệu phần cứng có tính nhạy cảm.
- Không truy cập trang quản trị bằng HTTP không mã hóa qua Internet nếu có thể; cấu hình HTTPS hoặc SSH tunnel khi sử dụng thường xuyên.
- Giữ chặn truy cập trực tiếp `server-meta.php`, `README.md`, `.bak`, `.zip` và file dự phòng bằng Apache.
- Lệnh cập nhật không thay đổi firewall, vhost, Apache, Supervisor, MySQL, PHP-FPM, Cockpit hoặc Netdata.
- Các status TCP chỉ xác nhận cổng có phản hồi localhost, không xác nhận nghiệp vụ API.
