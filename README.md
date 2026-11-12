# AMnote Server Management – Portal v2

## Thành phần

- `index.php`: Dashboard 4 màn hình thực sự: Tổng quan, Giám sát hiệu năng, Dịch vụ hệ thống, Thông tin server. Chỉ IP công ty được mở.
- `status.php`: API chỉ đọc (Linux `/proc`, ổ đĩa `/`, kết nối TCP localhost).
- `access.php`: Allowlist IP cho **cả dashboard và API**, chỉ `115.78.232.22`, `127.0.0.1`, `::1`.
- Toàn bộ giao diện nằm trong `index.php` và chỉ được trả về sau khi kiểm tra IP. Không có file HTML riêng để vượt qua allowlist.

## Lưu ý quan trọng trước khi triển khai

1. Đây là bản build để **đánh giá trước**. Chưa được triển khai tới server 118.69.170.82.
2. Triển khai vào đường dẫn riêng do Apache/PHP-FPM hiện tại phục vụ, chỉ sau khi xác nhận vhost/DocumentRoot phù hợp. **Không ghi đè file hiện có**.
3. Từ chối HTTP truy cập các file `README.md` và `access.php` bằng cấu hình Apache; `index.php` và `status.php` đã có kiểm tra IP riêng.
4. Nếu sử dụng Hanhbiro Reverse Proxy, kiểm tra `REMOTE_ADDR` mà PHP thực tế nhận. Code **cố ý không tin `X-Forwarded-For`** để tránh giả mạo IP. Nếu PHP thấy IP proxy thay vì IP công ty, chính sách sẽ từ chối để an toàn.
5. Hanhbiro có firewall là tốt nhưng vẫn cần đảm bảo port `9090` (Cockpit), `19999` (Netdata), `9002` (Supervisor) chỉ mở cho IP công ty. Truy cập HTTP Netdata/Supervisor trực tiếp không mã hóa; nếu đi Internet, nên dùng HTTPS reverse proxy hoặc SSH tunnel.
6. Portal **không đăng nhập thay Cockpit/Netdata/Supervisor** và không yêu cầu credentials. Cần đăng nhập tại công cụ đích.
7. Trạng thái dịch vụ chỉ dựa trên kết nối TCP `127.0.0.1:PORT`. Port mở không xác nhận chức năng HTTP hay xử lý nghiệp vụ thành công. Không gọi API gửi thông điệp thuế hoặc tác động DB.
8. CPU cần 2 mẫu liên tiếp nên xuất hiện sau khoảng 8 giây.
9. Phiên bản hiện tại ưu tiên **chỉ đọc và không thay đổi cấu hình ứng dụng hiện hữu**; không bao gồm stop/start/restart API từ dashboard.

## Yêu cầu tương thích

- PHP 7.3+ (server hiện dùng PHP 7.3 FPM cho Apache).
- Linux procfs `/proc/stat`, `/proc/meminfo`, `/proc/uptime`, `/proc/net/dev`.
- Các hàm PHP: `stream_socket_client`, `disk_total_space`, `disk_free_space`.
- Không yêu cầu Composer, Node, Docker, MySQL hay quyền root.

## URL & chuyển IP

- Mặc định liên kết công cụ dùng `118.69.170.82`.
- Nút **Đổi IP / Host** lưu tuỳ chọn trên browser (localStorage), không thay đổi server. Có thể đổi sang `118.69.170.101` sau chuyển IP.
- Các dịch vụ Cockpit, Netdata, Supervisor cần được cài/chạy riêng. Portal không tự cài chúng.

## Kiểm tra cú pháp mà không gọi API nghiệp vụ

```bash
php -l index.php
php -l access.php
php -l status.php
```

## Điều hướng phiên bản 2

- Menu dùng `location.hash` (`#overview`, `#monitor`, `#services`, `#system`) để chuyển màn hình; chỉ một vùng nội dung được hiển thị.
- Bộ dữ liệu read-only tiếp tục cập nhật mỗi 8 giây dù đang ở màn hình nào.
- Giao diện di động có thanh menu ngang cuộn được, không bị mất menu.
- File Preview HTML dùng để xem offline. **Không triển khai Preview HTML lên Apache** vì không có lớp chặn IP như `index.php`.
- Portal không tự cài Cockpit/Netdata; các nút chỉ mở website tương ứng.
