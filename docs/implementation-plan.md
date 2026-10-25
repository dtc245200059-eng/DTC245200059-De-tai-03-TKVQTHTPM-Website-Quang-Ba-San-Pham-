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

### Giai đoạn 4: Tích hợp Giám sát Prometheus & Grafana (Mốc COMMIT 2) — [ĐÃ HOÀN THÀNH VÀ COMMIT]
* **Mốc Git đã tạo:** `cb8fa3b` — *Tích hợp Prometheus, Grafana và các Exporter giám sát (Mốc Commit 2)*
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

### Giai đoạn 5: Hệ thống Log tập trung Loki + Grafana Alloy qua HAProxy Allowlist & Truy vấn LogQL (Mốc COMMIT 3) — [ĐÃ HOÀN THÀNH CẤU HÌNH TĨNH HIỆN ĐẠI HÓA]
* **Hiện đại hóa kiến trúc Logging (Grafana Alloy & HAProxy Allowlist Proxy):**
  * Do Promtail đã chính thức hết vòng đời hỗ trợ (End-of-Life - EOL) từ ngày 02/03/2026, hệ thống chuyển đổi toàn diện sang **Grafana Alloy** (`grafana/alloy:v1.21.2`) — bản phát hành ổn định chính thức của Grafana.
  * Tách biệt ranh giới bảo mật Docker Socket: Loại bỏ hoàn toàn việc mount trực tiếp socket `/var/run/docker.sock` vào Alloy. Thiết lập dịch vụ trung gian **Docker Socket Proxy** sử dụng **HAProxy** (`haproxy:2.8-alpine`) với tệp cấu hình allowlist chuyên dụng (`docker-socket-proxy/haproxy.cfg`), áp dụng chính sách nghiêm ngặt **Deny-by-default**.
* **Rà soát & Khắc phục lỗ hổng bảo mật của Docker Socket Proxy (CVE-2026-78122 / Issue #182):**
  * **Lỗ hổng của Tecnativa Proxy:** Khi chỉ bật `CONTAINERS=1` và `POST=0`, Tecnativa proxy chỉ chặn các HTTP verbs ghi (`POST/PUT/DELETE`), nhưng **vẫn cho phép toàn bộ các GET request** trong namespace `/containers/*`. Kẻ tấn công có thể khai thác các endpoint GET nguy hiểm:
    * `GET /containers/{id}/archive`: Tải tệp tùy ý từ filesystem của container (rò rỉ mã nguồn, wp-config.php, private keys).
    * `GET /containers/{id}/export`: Tải toàn bộ bản sao filesystem của container.
    * `GET /containers/{id}/top`: Do thám các tiến trình đang chạy bên trong container.
    * `GET /containers/{id}/attach/ws`: Chiếm quyền điều khiển terminal qua WebSocket.
  * **Giải pháp khắc phục triệt để:** Không dùng biến môi trường coarse-grained của Tecnativa, mà chuyển sang cấu hình **HAProxy Native Allowlist** với regex đường dẫn chính xác, từ chối mặc định mọi endpoint không nằm trong danh mục cho phép.
* **Tệp đã tạo và tích hợp:**
  * `loki/loki-config.yml`: Cấu hình lưu trữ chunks trên filesystem, schema v13 TSDB, retention period 7 ngày (168h), compactor tự động dọn dẹp log cũ theo chu kỳ 10 phút.
  * `docker-socket-proxy/haproxy.cfg`: Cấu hình HAProxy strict allowlist (deny-by-default), chỉ mở 5 endpoint GET thiết yếu và chặn toàn bộ các endpoint nguy hiểm khác; mở rộng timeouts (1h) hỗ trợ log streaming.
  * `alloy/config.alloy`: Cấu hình Alloy với cú pháp native (River syntax), gồm 4 khối pipeline liên kết: `discovery.docker` (khám phá container qua `tcp://docker-socket-proxy:2375`), `discovery.relabel` (chuẩn hóa nhãn, lọc whitelist 12 containers, gán nhãn môi trường demo), `loki.source.docker` (thu thập log từ proxy API) và `loki.write` (chuyển tiếp log về Loki).
  * `grafana/provisioning/datasources/loki.yml`: Tự động nạp Loki làm Datasource trong Grafana tại `http://loki:3100`.
  * `docker-compose.yml`: Bổ sung 3 dịch vụ `loki` (`grafana/loki:3.7.9`), `alloy` (`grafana/alloy:v1.21.2`) và `docker-socket-proxy` (`haproxy:2.8.31-alpine`); tách mạng `docker-api-net` (internal); bổ sung 2 volumes `loki_data` và `alloy_data`.
* **Đặc tả kiến trúc thu thập log & Phân tích an toàn bảo mật Socket Proxy Allowlist:**
  * **Ranh giới cô lập Mạng & Socket (Network & Socket Segregation):**
    * Socket thật `/var/run/docker.sock:ro` CHỈ được mount vào `docker-socket-proxy`.
    * Tách riêng mạng nội bộ `docker-api-net` (`internal: true`). `docker-socket-proxy` CHỈ tham gia `docker-api-net`, tuyệt đối không tham gia `monitoring-net`.
    * `alloy` tham gia cả 2 mạng: `docker-api-net` (giao tiếp với proxy qua `tcp://docker-socket-proxy:2375`) và `monitoring-net` (đẩy log về `http://loki:3100`).
    * Tuyệt đối không publish cổng 2375 ra host. Không dịch vụ nào khác trong hệ thống có thể kết nối tới Socket Proxy.
  * **Chính sách kiểm soát Endpoint (Method & Path Allowlist tối giản):**
    * `Method Restriction`: Chặn tuyệt đối mọi method khác ngoài `GET` và `HEAD`. Không thể gửi `POST`, `PUT`, `DELETE`, `PATCH`.
    * `GET /_ping`: Cho phép kiểm tra trạng thái Docker daemon và phục vụ healthcheck.
    * `GET /version`: Cho phép kiểm tra phiên bản Docker API cho handshake ban đầu.
    * `GET /containers/json`: Cho phép `discovery.docker` liệt kê danh sách container, IDs và labels.
    * `GET /containers/{id}/logs`: Cho phép `loki.source.docker` đọc stream log stdout/stderr (hỗ trợ timeout 1h tránh đứt luồng khi container idle).
    * `GET /containers/{id}/json`: Cho phép tailer của `loki.source.docker` xác minh container còn sống và kiểm tra timestamp khởi tạo. *(Đánh giá rủi ro: Endpoint này trả về mảng `Config.Env` chứa các biến môi trường của container; cần lưu ý quản lý secret chặt chẽ).*
    * `Loại bỏ GET /events`: Thành phần `discovery.docker` hoạt động bằng cơ chế định kỳ polling danh sách containers qua `refresh_interval`, không cần event stream, loại bỏ để tối giản bề mặt tấn công.
    * `Deny-by-default (403 Forbidden)`: Chặn tuyệt đối `/archive` (chặn CVE-2026-78122), `/export`, `/top`, `/attach`, `/exec`, `/build`, `/images`, `/volumes`, `/networks`...
    * `Lưu ý phạm vi Relabel Whitelist:` Bộ lọc `discovery.relabel` trong Alloy chỉ giới hạn các log được chuyển tiếp lên Loki, **không giới hạn quyền đọc API của Alloy** đối với các container qua Socket Proxy (Alloy vẫn có thể gọi inspect/list mọi container qua proxy trước khi lọc nhãn).
  * **Quyền thực thi & Lưu trữ trạng thái của Alloy:**
    * Alloy lưu offset và positions bền vững trên named volume `alloy_data` gắn vào `/var/lib/alloy/data` (cấu hình qua cờ `--storage.path`).
    * Trong image `grafana/alloy`, user `alloy` có UID `473`, GID `473` với thư mục `/var/lib/alloy` được cấp quyền `0770`. Do Alloy chỉ mở kết nối mạng TCP tới proxy mà không cần truy cập socket file Unix, tiến trình hoàn toàn không cần đặc quyền root để hoạt động.
  * **Kiểm soát Cardinality:** Nhãn được chuẩn hóa gồm `container`, `service`, `stream`, `project="de-03-wordpress"`, `environment="demo"` (tổng cộng 24 streams cho 12 containers), tuyệt đối không gán nhãn giá trị động (IP client, URL động, User-Agent, Session ID, timestamp).
  * **Cô lập mạng:** `loki` (3100), `alloy` (12345) và `docker-socket-proxy` (2375) KHÔNG công bố cổng ra máy host; toàn bộ luồng truyền log diễn ra khép kín trong `monitoring-net` và `docker-api-net`.
* **Kiểm thử & 3 Truy vấn LogQL chuẩn:**
  1. Giám sát truy cập web Nginx: `{container="nginx"} |= "GET"`
  2. Phát hiện lỗi ứng dụng WordPress và Nginx: `{container=~"wordpress|nginx"} |~ "(?i)(error|failed|404|500)"`
  3. Cảnh báo an ninh CSDL MySQL: `{container="mysql"} |= "Access denied"` hoặc `{container="mysql"}`
* **Mốc Git dự kiến (Chờ phê duyệt để commit):**
  * `Tích hợp hệ thống log tập trung Loki, Grafana Alloy qua HAProxy Allowlist và LogQL (Mốc Commit 3)`

### Giai đoạn 6: Triển khai Hardening và Bảo mật Hệ thống
* Áp dụng tối thiểu 4 biện pháp bảo mật:
  1. **Network Isolation:** 4 mạng độc lập (`frontend-net`, `backend-net` internal, `monitoring-net`, `docker-api-net` internal); MySQL không expose cổng ra host.
  2. **Security Context & Capabilities:** Cấu hình `no-new-privileges: true` trên tất cả 12 dịch vụ.
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
| **`loki`** | `grafana/loki:3.7.9` | `monitoring-net` | `3100` | *Nội bộ* | Máy chủ lưu trữ và truy vấn log tập trung (TSDB schema v13, retention 168h) |
| **`alloy`** | `grafana/alloy:v1.21.2` | `monitoring-net`, `docker-api-net` | `12345` | *Nội bộ* | Agent thu gom và gán nhãn log container qua Socket Proxy (thay thế Promtail EOL) |
| **`docker-socket-proxy`** | `haproxy:2.8.31-alpine` | `docker-api-net` (internal) | `2375` | *Nội bộ* | Tường lửa API Docker Engine (Chính sách Deny-by-default, chặn archive/export/exec) |
