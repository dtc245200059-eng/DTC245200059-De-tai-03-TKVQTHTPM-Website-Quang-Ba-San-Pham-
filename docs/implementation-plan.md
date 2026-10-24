# KẾ HOẠCH TRIỂN KHAI ĐỒ ÁN DEVOPS
## ĐỀ TÀI SỐ 03: WEBSITE QUẢNG BÁ SẢN PHẨM BẰNG WORDPRESS
**Môn học:** Triển khai và Quản trị Hệ thống Phần mềm
**Sinh viên thực hiện:** Nông Quốc Tuấn | **MSV:** DTC245200059 | **Lớp:** KTPM K23C
**Kho mã nguồn dự kiến:** `https://github.com/dtc245200059-eng/DTC245200059-De-tai-03-TKVQTHTPM-Website-Quang-Ba-San-Pham-.git`

---

## 1. MỤC TIÊU VÀ PHẠM VI DỰ ÁN

* **Mục tiêu:** Xây dựng, đóng gói và tự động hóa điều phối hệ thống Website Quảng bá Sản phẩm sử dụng WordPress, cơ sở dữ liệu MySQL, công cụ quản trị phpMyAdmin, reverse proxy Nginx (HTTPS tự ký + Security Headers), hệ thống giám sát Prometheus + Grafana và hệ thống thu thập log tập trung Loki + Promtail qua LogQL.
* **Quy chuẩn kỹ thuật:**
  * Toàn bộ dịch vụ chạy trên nền tảng Docker và điều phối bằng Docker Compose.
  * Phân tách mạng nội bộ (Network Isolation: `frontend-net`, `backend-net`, `monitoring-net`).
  * Tuyệt đối không mở cổng 3306 của MySQL container ra host (chống xung đột và đảm bảo an toàn).
  * Quản lý Git với 03 mốc commit bắt buộc theo đúng tiêu chí chấm điểm của đề bài.

---

## 2. KIẾN TRÚC HỆ THỐNG MỤC TIÊU

### 2.1. Phân vùng mạng và luồng giao tiếp

1. **`frontend-net` (Bridge):**
   * Nginx (cổng 80, 443 public).
   * WordPress (cổng 80 nội bộ).
   * phpMyAdmin (cổng 80 nội bộ hoặc qua cổng phụ 8080).
2. **`backend-net` (Bridge, cách ly hoàn toàn với bên ngoài):**
   * WordPress kết nối `mysql:3306`.
   * phpMyAdmin kết nối `mysql:3306`.
   * MySQL Exporter kết nối `mysql:3306` qua user giám sát chuyên biệt.
3. **`monitoring-net` (Bridge):**
   * Prometheus scrape metrics từ `cAdvisor:8080`, `node-exporter:9100`, `mysql-exporter:9104`, `nginx`.
   * Grafana truy vấn Prometheus (metrics) và Loki (logs).
   * Promtail đọc log container qua `/var/run/docker.sock` và đẩy về `loki:3100`.

---

## 3. KẾ HOẠCH TRIỂN KHAI THEO TỪNG GIAI ĐOẠN

### Giai đoạn 1: Khảo sát Workspace và Tài liệu (Đã hoàn thành)
* Đã đọc và phân tích [De_Tai_Thuc_Hanh - TKQT HTPM K23-1.pdf](file:///d:/%C4%90%E1%BB%81_3-DTC245200059/Tailieu/De_Tai_Thuc_Hanh%20-%20TKQT%20HTPM%20K23-1.pdf) và 38 tài liệu học liệu.
* Xác định 7 tiêu chí chấm điểm, 3 mốc commit và các ràng buộc kỹ thuật.

### Giai đoạn 2: Khởi tạo Nền tảng Core (WordPress, MySQL, phpMyAdmin) — [ĐÃ HOÀN THÀNH CẤU HÌNH TĨNH]
* **Tệp đã tạo:**
  * `.gitignore`: Bảo vệ nghiêm ngặt secrets `.env`, khóa SSL và persistent volumes.
  * `.env.example`: Cung cấp template biến môi trường mẫu, không chứa mật khẩu thật.
  * `.env`: Khởi tạo cục bộ với mật khẩu có độ phức tạp cao, được Git loại trừ hoàn toàn.
  * `docker-compose.yml`: Khai báo 3 dịch vụ `mysql`, `wordpress`, `phpmyadmin` với healthcheck, `no-new-privileges`, cô lập cổng 3306.
* **Kết quả kiểm thử tĩnh:**
  * Cú pháp YAML đã được xác thực hợp lệ qua `yaml.safe_load()`.
  * Xác nhận toàn bộ 3 dịch vụ đều không mở cổng public tùy tiện, MySQL chỉ nằm trong `backend-net`.
  * Xác nhận kiểm thử runtime (khởi chạy container) đang ở trạng thái **CHỜ MÔI TRƯỜNG DOCKER PHÙ HỢP (Chưa xác minh runtime)**.

### Giai đoạn 3: Nginx Reverse Proxy, SSL Tự ký & Security Headers (Mốc COMMIT 1) — [ĐÃ HOÀN THÀNH CẤU HÌNH TĨNH]
* **Tệp đã tạo và cập nhật:**
  * `nginx/nginx.conf`: Điểm truy cập duy nhất, định tuyến `shop.local` (WordPress) và `pma.shop.local` (phpMyAdmin).
  * `nginx/ssl/.gitkeep`: Thư mục lưu trữ chứng chỉ SSL, các tệp private key và cert được `.gitignore` bảo vệ.
  * `nginx/.htpasswd.example`: Mẫu và tài liệu hướng dẫn tạo Basic Auth cho phpMyAdmin.
  * `nginx/.htpasswd`: Tệp xác thực cục bộ phục vụ mount volume, được `.gitignore` loại trừ hoàn toàn.
  * `scripts/generate-ssl.sh`: Script sinh chứng chỉ SSL/TLS tự ký hỗ trợ SAN đa tên miền.
  * `docs/demo-access-guide.md`: Hướng dẫn cấu hình file hosts và truy cập demo.
  * `docker-compose.yml`: Bổ sung dịch vụ `nginx` (publish cổng 80, 443; tham gia `frontend-net`).
* **Đặc tả an toàn & Security Headers:**
  * Chỉ duy nhất Nginx mở cổng `80:80` và `443:443`.
  * HTTP 80 tự động chuyển hướng 301 sang HTTPS 443.
  * Áp dụng TLS 1.2 và TLS 1.3, không bật HSTS trên môi trường demo self-signed.
  * Thiết lập 5 Security Headers: `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy: camera=(), microphone=(), geolocation=()`, `Content-Security-Policy-Report-Only` (bảo vệ không làm hỏng script/theme WordPress).
  * phpMyAdmin được bảo vệ bằng lớp xác thực kép (Nginx Basic Auth + form đăng nhập CSDL).
### Giai đoạn 3: Nginx Reverse Proxy, SSL Tự ký & Security Headers (Mốc COMMIT 1) — [ĐÃ HOÀN THÀNH VÀ COMMIT]
* **Mốc Git đã tạo:** `73b5ba0` — *Cấu hình Nginx reverse proxy, HTTPS và bảo mật (Mốc Commit 1)*
* **Tệp đã tạo và tích hợp:**
  * `nginx/nginx.conf`: Điểm truy cập duy nhất, định tuyến `shop.local` (WordPress) và `pma.shop.local` (phpMyAdmin).
  * `nginx/ssl/.gitkeep`: Thư mục lưu trữ chứng chỉ SSL, các tệp private key và cert được `.gitignore` bảo vệ.
  * `nginx/.htpasswd.example`: Mẫu và tài liệu hướng dẫn tạo Basic Auth cho phpMyAdmin.
  * `scripts/generate-ssl.sh`: Script sinh chứng chỉ SSL/TLS tự ký hỗ trợ SAN đa tên miền với cơ chế nguyên tử, kiểm tra modulus và rollback.
  * `docs/demo-access-guide.md`: Hướng dẫn cấu hình file hosts và truy cập demo.
  * `docker-compose.yml`: Dịch vụ `nginx` (image `nginx:1.30.6-alpine`, publish cổng 80, 443; tham gia `frontend-net`).

### Giai đoạn 4: Tích hợp Giám sát Prometheus & Grafana (Mốc COMMIT 2) — [ĐÃ HOÀN THÀNH CẤU HÌNH TĨNH]
* **Tệp đã tạo và tích hợp:**
  * `prometheus/prometheus.yml`: Cấu hình scrape interval (15s) cho 4 jobs: `prometheus`, `node-exporter`, `cadvisor`, `mysql-exporter`.
  * `mysql/init/01-exporter.sh`: Script tự động khởi tạo user `exporter` với bộ quyền tương thích collectors (`PROCESS, REPLICATION CLIENT, SELECT ON *.*` kèm `MAX_USER_CONNECTIONS 3`) trong MySQL 8.0.
  * `mysql/init/01-exporter.sql.example`: Tệp hướng dẫn câu lệnh SQL tạo user thủ công cho trường hợp database đã có dữ liệu.
  * `grafana/provisioning/datasources/prometheus.yml`: Tự động nạp Prometheus làm Default Datasource cho Grafana.
  * `grafana/provisioning/dashboards/dashboards.yml`: Cấu hình Dashboard Provider tự động nạp các tệp JSON từ `/etc/grafana/dashboards`.
  * `grafana/dashboards/host-monitoring.json`: Bảng điều khiển giám sát máy chủ host (CPU, Memory, Disk, Network I/O, Load Average, Uptime).
  * `grafana/dashboards/container-monitoring.json`: Bảng điều khiển giám sát Docker container (CPU %, Memory working set, Network RX/TX, Container count).
  * `grafana/dashboards/mysql-monitoring.json`: Bảng điều khiển giám sát CSDL MySQL (MySQL Up/Down, QPS, Connections, Running Threads, Buffer Pool, Slow Queries).
  * `docker-compose.yml`: Bổ sung 5 dịch vụ giám sát (`prometheus`, `grafana`, `node-exporter`, `cadvisor`, `mysql-exporter`), mạng `monitoring-net` và 2 volumes lưu trữ (`prometheus_data`, `grafana_data`).
  * `.env.example`: Bổ sung biến môi trường cấu hình mật khẩu quản trị Grafana và tài khoản MySQL Exporter.
* **Đặc tả kiến trúc mạng & An toàn bảo mật:**
  * `backend-net` được giữ nguyên cấu hình `internal: true`, cô lập hoàn toàn CSDL MySQL khỏi Internet.
  * `mysql-exporter` tham gia đồng thời 2 mạng: `backend-net` (kết nối `mysql:3306`) và `monitoring-net` (cung cấp cổng nội bộ 9104 cho Prometheus scrape).
  * MySQL container tuyệt đối KHÔNG mở cổng 3306 ra máy host.
  * `node-exporter` (9100), `cadvisor` (8080) và `mysql-exporter` (9104) KHÔNG publish cổng ra máy host; chỉ Prometheus truy cập nội bộ qua `monitoring-net`.
  * Chỉ duy nhất máy chủ Prometheus (`9090:9090`) và Grafana (`3000:3000`) mở cổng phục vụ giảng viên kiểm tra và trình diễn demo.
* **Giải trình Lựa chọn Phiên bản & Quyền hạn Giám sát:**
  * **Prometheus Image (`prom/prometheus:v2.53.3`):** Đây là bản phát hành bảo mật cuối cùng của nhánh Prometheus v2.53 LTS (vốn có chu kỳ EOL vào 31/07/2025). Quyết định giữ phiên bản này nhằm đảm bảo tính tương thích tuyệt đối với học liệu và bài giảng môn học (Bài 7 - Giám sát hệ thống máy chủ), đồng thời tránh các breaking changes của Prometheus 3.x (như unbundled console templates, thay đổi cờ quản lý TSDB). Không tự động nâng lên Prometheus 3.x (như `v3.13.5`) khi chưa có yêu cầu từ giảng viên.
  * **Đặc tả Quyền hạn MySQL Exporter & Rủi ro Bảo mật:** Cấu hình tài khoản exporter theo tài liệu chuẩn của Prometheus: `PROCESS, REPLICATION CLIENT, SELECT ON *.*` kèm `MAX_USER_CONNECTIONS 3`. Cần làm rõ phạm vi quyền thực tế:
    1. `PROCESS`: Cho phép xem thông tin luồng và tiến trình đang thực thi trong MySQL.
    2. `REPLICATION CLIENT`: Cho phép đọc trạng thái binlog và replication.
    3. `SELECT ON *.*`: Cho phép đọc các bảng hệ thống (`performance_schema`, `information_schema`) phục vụ collectors. Mặc dù không có quyền sửa/xóa (`INSERT`, `UPDATE`, `DELETE`, `DROP`), nhưng `SELECT ON *.*` là quyền đọc toàn cục, đồng nghĩa tài khoản này **có thể đọc toàn bộ dữ liệu ứng dụng (bao gồm cả các bảng WordPress)**. Đây là một đánh đổi/rủi ro bảo mật cần ghi nhận.
    4. `WITH MAX_USER_CONNECTIONS 3`: Chỉ giới hạn số kết nối đồng thời của riêng tài khoản exporter để chống cạn kiệt connection pool từ phía exporter; không giới hạn phạm vi đọc và không thay thế việc cấu hình `max_connections` toàn máy chủ.
* **Mốc Git dự kiến (Chờ phê duyệt để commit):**
  * `feat(monitoring): integrate Prometheus, Grafana, cAdvisor, Node Exporter, and MySQL Exporter with dashboards (Commit 2)`

### Giai đoạn 5: Hệ thống Log tập trung Loki + Promtail & Truy vấn LogQL (Mốc COMMIT 3)
* **Tệp tạo mới:**
  * `loki/loki-config.yml` (cấu hình lưu trữ chunks, schema retention).
  * `promtail/promtail-config.yml` (cấu hình đọc log docker socket, parse json, gán nhãn).
  * Bổ sung dịch vụ vào `docker-compose.yml`: `loki`, `promtail`.
  * `grafana/provisioning/datasources/loki.yml` (tự động nạp Loki datasource).
* **Kiểm thử & 3 Truy vấn LogQL chuẩn:**
  1. `{container="nginx"} |= "GET"`
  2. `{container=~"wordpress|nginx"} |~ "(?i)(error|failed|404|500)"`
  3. `{container="mysql"} |= "Access denied"` hoặc `{container="mysql"}`
* **Mốc Git:** `feat(logging): setup centralized logging with Loki, Promtail, and LogQL queries (Commit 3)`.

### Giai đoạn 6: Triển khai Hardening và Bảo mật Hệ thống
* Áp dụng tối thiểu 4 biện pháp bảo mật:
  1. **Network Isolation:** 3 mạng độc lập; MySQL không expose cổng ra host; `backend-net` có `internal: true`.
  2. **Security Context & Capabilities:** Cấu hình `no-new-privileges: true` trên tất cả 9 dịch vụ.
  3. **Mật khẩu mạnh & Biến môi trường:** Bảo vệ secrets, cấu hình `.env` chặt chẽ, exporter phân định rõ ranh giới quyền đọc toàn cục.
  4. **Nginx Security Hardening:** Tắt server tokens, thiết lập buffer limits và rate limit.
* **Kiểm thử:** Xác nhận quét cổng host không thấy cổng 3306; kiểm tra container inspect xác nhận security_opt.

### Giai đoạn 7: Kiểm thử Tổng thể, Soạn thảo Báo cáo & Chuẩn bị Demo
* Viết kịch bản kiểm thử tự động `tests/smoke-test.sh`.
* Soạn thảo tài liệu `README.md` chi tiết bằng tiếng Việt.
* Soạn thảo Báo cáo tổng hợp `>= 10 trang` định dạng chuẩn môn học tại `docs/bao-cao-tong-hop.md`.
* Chuẩn bị kịch bản Demo trình diễn trước giảng viên.

---

## 4. MA TRẬN ÁNH XẠ CỔNG DỊCH VỤ

| Tên dịch vụ | Image Container | Mạng tham gia | Cổng nội bộ (Container) | Cổng bên ngoài (Host) | Mục đích sử dụng |
| :--- | :--- | :--- | :---: | :---: | :--- |
| **`nginx`** | `nginx:1.30.6-alpine` | `frontend-net` | `80`, `443` | **`80`**, **`443`** | Reverse Proxy, HTTPS, Security Headers (Điểm mở cổng duy nhất cho web) |
| **`wordpress`** | `wordpress:6.7-php8.2-apache` | `frontend-net`, `backend-net` | `80` | *Không mở* | Website quảng bá sản phẩm (Proxy qua `shop.local`) |
| **`mysql`** | `mysql:8.0` | `backend-net` (internal) | `3306` | *Không mở (Cấm mở)* | Cơ sở dữ liệu WordPress (Chỉ trong `backend-net`) |
| **`phpmyadmin`** | `phpmyadmin:5.2-apache` | `frontend-net`, `backend-net` | `80` | *Không mở* | Quản trị CSDL (Proxy qua `pma.shop.local` + Basic Auth) |
| **`prometheus`** | `prom/prometheus:v2.53.3` | `monitoring-net` | `9090` | **`9090`** | Web UI máy chủ giám sát & lưu trữ TSDB |
| **`grafana`** | `grafana/grafana:11.1.0` | `monitoring-net` | `3000` | **`3000`** | Bảng điều khiển trực quan hóa & Explore LogQL |
| **`node-exporter`**| `prom/node-exporter:v1.8.2` | `monitoring-net` | `9100` | *Nội bộ* | Exporter đo chỉ số máy chủ host |
| **`cadvisor`** | `gcr.io/cadvisor/cadvisor:v0.49.1`| `monitoring-net` | `8080` | *Nội bộ* | Exporter đo chỉ số tài nguyên container Docker |
| **`mysql-exporter`**| `prom/mysqld-exporter:v0.15.1`| `backend-net`, `monitoring-net` | `9104` | *Nội bộ* | Exporter đo chỉ số CSDL MySQL (Cầu nối an toàn) |
