# AMnote Server Management v4 — Ubuntu/Linux read-only monitor

Giữ nguyên thiết kế bốn menu của AMnote Monitor. `index.php` tải `status.php` trên **cùng máy chủ đang phục vụ dashboard**, mỗi 3 giây, với kiểm tra timestamp và không chồng nhiều request. Không đọc database, không chạy shell, không sửa cấu hình, không restart dịch vụ.

## Giao diện

- Đã **bỏ nút Đổi IP / Host** và hộp thoại cấu hình IP; các liên kết quản trị dùng IP cấu hình tĩnh `118.69.170.82` trong `index.php`. Không sử dụng IP đã lưu trong `localStorage`.
- Đã **bỏ thẻ Thời gian hoạt động khỏi Tổng quan**. Uptime vẫn có trong chi tiết Thông tin server.
- Khi `status.php` lỗi, timestamp cũ hoặc lệch đồng hồ trên 90 giây: đặt các ô thống kê về `—`, xóa đồ thị CPU cũ và trạng thái dịch vụ cũ. Dòng trạng thái cho biết lần nhận dữ liệu hợp lệ cuối.
- CPU sử dụng tính theo hai mẫu liên tiếp từ `/proc/stat`, loại `guest` và `guest_nice` khỏi tổng do kernel đã tính chúng trong user/nice.
- RAM/Swap lấy `/proc/meminfo`; dung lượng ổ ở Tổng quan **chỉ là filesystem `/`**, không phải tổng dung lượng vật lý.
- Thiết bị block đọc từ `/sys/block` (có thể bao gồm ổ, ổ ảo, RAID md và LVM); **không cộng các dòng** để suy ra tổng dung lượng ổ.
- RAID phần mềm md đọc `/proc/mdstat`, phân biệt bình thường, thiếu ổ và đang đồng bộ/kiểm tra. **Không tự xác nhận RAID phần cứng hoặc tình trạng SMART ổ vật lý**.
- Kiểm tra TCP localhost và gửi HTTP `GET /` đến các dịch vụ HTTP được khai báo, chỉ để phân biệt cổng mở, có phản hồi HTTP, lỗi 5xx hoặc không phản hồi. **HTTP 2xx/3xx vẫn không xác nhận API nghiệp vụ khỏe mạnh.** Mỗi thử nghiệm HTTP tối đa ~0.4 giây đọc.
- Nhãn vị trí rack, nhà cung cấp và backup trong `server-meta.php` là **nhập thủ công, cần xác minh**.

## File

- `index.php`: giao diện HTML/JS, tự làm mới 3 giây, liên kết quản trị mặc định IP .82.
- `status.php`: PHP 7.3+ chỉ đọc /proc, /sys, /etc/os-release, DMI, local TCP/HTTP.
- `server-meta.php`: metadata nhập tay, **không lưu password/token**.
- `access.php`: allowlist IP truy cập. Giữ nguyên.

## Triển khai trên máy chủ 118.69.170.82

Đường dẫn dự kiến: `/home/WEB/ammonitor`.

1. Sao lưu `index.php` và `status.php` trước khi thay.
2. Upload `index.php`, `status.php` (metadata và access không cần thay).
3. Kiểm tra cú pháp: `/usr/local/php7.3/bin/php -l /home/WEB/ammonitor/status.php` và `php -l index.php`.
4. Kiểm tra JSON và các trường `ports`, `healthChecks`, `system.raid` bằng curl nội bộ.
5. Mở dashboard, Ctrl+F5 và đối chiếu `lscpu`, `free -h`, `lsblk`, `df -hT`, `cat /proc/mdstat`. Lưu ý đồng bộ đồng hồ NTP.
6. Chưa có cơ chế API health nghiệp vụ riêng; muốn xác định API thực sự khỏe nên thêm endpoint `/health` hoặc `/ready` có hợp đồng kết quả cụ thể cho từng ứng dụng.

## An toàn và giới hạn

- Giữ allowlist trong `access.php`, `Require ip` Apache; chặn truy cập trực tiếp `server-meta.php`/`README.md`/backup.
- Tránh HTTP plaintext từ Internet; dùng HTTPS hoặc SSH tunnel.
- PHP chạy container có thể phản ánh filesystem/cgroup/thiết bị container, không hoàn toàn là host.
- Không tự nhận biết cấu hình ổ vật lý thực, SMART, phần cứng RAID hay backup thực tế; cần xác minh với nhà cung cấp và công cụ riêng.
- `healthChecks` chỉ là bằng chứng phản hồi HTTP/TCP của localhost, không phải deep business health. Các cổng HTTPS/TCP-only giữ trạng thái chỉ kiểm tra TCP.
