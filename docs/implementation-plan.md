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
* **Mốc Git dự kiến (Chờ phê duyệt để commit):**
  * `feat(proxy): configure Nginx reverse proxy with self-signed SSL and security headers (Commit 1)`

### Giai đoạn 4: Tích hợp Giám sát Prometheus & Grafana (Mốc COMMIT 2)
* **Tệp tạo mới:**
  * `prometheus/prometheus.yml` (cấu hình scrape interval và targets).
  * Bổ sung dịch vụ vào `docker-compose.yml`: `prometheus`, `grafana`, `cadvisor`, `node-exporter`, `mysql-exporter`.
  * `grafana/provisioning/datasources/prometheus.yml` (tự động nạp datasource).
  * `grafana/provisioning/dashboards/` (dashboard mẫu giám sát container, host và MySQL).
* **Kiểm thử:**
  * Kiểm tra trang `Status -> Targets` của Prometheus: Tất cả đều có trạng thái **UP**.
  * Đăng nhập Grafana (cổng 3000), xem các biểu đồ CPU, RAM, QPS hoạt động thực tế.
* **Mốc Git:** `git commit -m "feat(monitoring): integrate Prometheus and Grafana with cAdvisor, Node Exporter, and MySQL Exporter (Commit 2)"`.

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
* **Mốc Git:** `git commit -m "feat(logging): setup centralized logging with Loki, Promtail, and LogQL queries (Commit 3)"`.

### Giai đoạn 6: Triển khai Hardening và Bảo mật Hệ thống
* Áp dụng tối thiểu 4 biện pháp bảo mật:
  1. **Network Isolation:** 3 mạng độc lập; MySQL không expose cổng ra host.
  2. **Security Context & Capabilities:** Cấu hình `no-new-privileges: true` và `cap_drop: [ALL]`.
  3. **Mật khẩu mạnh & Biến môi trường:** Bảo vệ secrets, cấu hình `.env` chặt chẽ.
  4. **Nginx Security Hardening:** Tắt server tokens, thiết lập buffer limits và rate limit.
* **Kiểm thử:** Xác nhận quét cổng host không thấy cổng 3306; kiểm tra container inspect xác nhận security_opt.

### Giai đoạn 7: Kiểm thử Tổng thể, Soạn thảo Báo cáo & Chuẩn bị Demo
* Viết kịch bản kiểm thử tự động `tests/smoke-test.sh`.
* Soạn thảo tài liệu `README.md` chi tiết bằng tiếng Việt.
* Soạn thảo Báo cáo tổng hợp `>= 10 trang` định dạng chuẩn môn học tại `docs/bao-cao-tong-hop.md`.
* Chuẩn bị kịch bản Demo trình diễn trước giảng viên.

---

## 4. MA TRẬN ÁNH XẠ CỔNG DỊCH VỤ

| Tên dịch vụ | Image Container | Cổng nội bộ (Container) | Cổng bên ngoài (Host) | Mục đích sử dụng |
| :--- | :--- | :---: | :---: | :--- |
| **`nginx`** | `nginx:1.30.6-alpine` | `80`, `443` | **`80`**, **`443`** | Reverse Proxy, HTTPS, Security Headers (Điểm mở cổng duy nhất) |
| **`wordpress`** | `wordpress:6.7-php8.2-apache` | `80` | *Không mở* | Website quảng bá sản phẩm (Proxy qua `shop.local`) |
| **`mysql`** | `mysql:8.0` | `3306` | *Không mở (Cấm mở)* | Cơ sở dữ liệu WordPress (Chỉ trong `backend-net`) |
| **`phpmyadmin`** | `phpmyadmin:5.2-apache` | `80` | *Không mở* | Quản trị CSDL (Proxy qua `pma.shop.local` + Basic Auth) |
| **`prometheus`** | `prom/prometheus:v2.53.0` | `9090` | **`9090`** | Web UI máy chủ giám sát |
| **`grafana`** | `grafana/grafana:11.1.0` | `3000` | **`3000`** | Bảng điều khiển trực quan & Explore LogQL |
| **`loki`** | `grafana/loki:3.0.0` | `3100` | *Nội bộ* | Máy chủ lưu trữ log |
| **`promtail`** | `grafana/promtail:3.0.0` | `-` | *Nội bộ* | Agent thu gom và gửi log |
| **`cadvisor`** | `gcr.io/cadvisor/cadvisor:v0.49.1`| `8080` | *Nội bộ (8085)* | Exporter đo chỉ số container |
| **`node-exporter`**| `prom/node-exporter:v1.8.1` | `9100` | *Nội bộ* | Exporter đo chỉ số host |
| **`mysql-exporter`**| `prom/mysqld-exporter:v0.15.1`| `9104` | *Nội bộ* | Exporter đo chỉ số MySQL |
