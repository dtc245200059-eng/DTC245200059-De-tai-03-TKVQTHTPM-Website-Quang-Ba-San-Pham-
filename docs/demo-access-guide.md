# HƯỚNG DẪN TRUY CẬP VÀ DEMO HỆ THỐNG

## ĐỀ TÀI SỐ 03: WEBSITE QUẢNG BÁ SẢN PHẨM BẰNG WORDPRESS

**Môn học:** Triển khai và Quản trị Hệ thống Phần mềm
**Sinh viên:** Nông Quốc Tuấn | **MSV:** DTC245200059 | **Lớp:** KTPM K23C

---

## 1. MÔ HÌNH TRUY CẬP VÀ PHÂN BỔ TÊN MIỀN

Hệ thống sử dụng Nginx làm điểm tiếp nhận duy nhất (Single Entry Point), định tuyến theo tên miền ảo (Virtual Host):

| Dịch vụ / Hostname          | Tên miền Demo               | Giao thức & Cổng | Hành vi định tuyến & Cơ chế bảo vệ                                                                                        |
| :-------------------------- | :-------------------------- | :--------------: | :------------------------------------------------------------------------------------------------------------------------ |
| **Website WordPress**       | `shop.local`                |  `HTTPS` (443)   | SSL/TLS tự ký, HTTP 80 tự chuyển hướng 301 sang `https://shop.local/`.                                                    |
| **phpMyAdmin**              | `pma.shop.local`            |  `HTTPS` (443)   | SSL/TLS tự ký, HTTP 80 tự chuyển hướng 301 sang `https://pma.shop.local/` + **HTTP Basic Authentication** cấp Nginx.      |
| **Truy cập Cục bộ (Alias)** | `localhost`, `127.0.0.1`    |  `HTTPS` (443)   | Hỗ trợ demo trực tiếp không cần sửa hosts file: HTTP 80 chuyển hướng 301 sang `https://localhost/`, proxy tới WordPress.  |
| **Hostname Bất kỳ khác**    | `_` (Mọi domain lạ, IP thô) | `HTTP` / `HTTPS` | **BỊ TỪ CHỐI LẬP TỨC (HTTP 444):** Ngắt kết nối ngay lập tức, triệt tiêu tấn công Host Header Injection và Open Redirect. |

---

## 2. CẤU HÌNH ÁNH XẠ TÊN MIỀN (HOSTS FILE) CHO TỪNG MÔI TRƯỜNG

Trước khi truy cập trên trình duyệt máy khách (Client / Host), cần xác định đúng địa chỉ IP máy chủ Docker và thêm vào tệp `hosts`:

### 2.1. Phân biệt theo môi trường thực thi:

- **Trường hợp 1: Chạy Docker Desktop trực tiếp trên máy Windows:**
  - Địa chỉ IP phục vụ chính là máy cục bộ: `127.0.0.1`.
  - Dòng cấu hình thêm vào tệp hosts:
    ```text
    127.0.0.1 shop.local pma.shop.local
    ```

- **Trường hợp 2: Chạy Docker trên máy ảo Ubuntu Server (VMware / VirtualBox):**
  - Địa chỉ IP phục vụ là **địa chỉ IP thực tế của máy ảo Ubuntu** (thường thuộc mạng NAT hoặc Host-Only của VMware, ví dụ `192.168.206.128` hoặc dải mạng do DHCP cấp phát).
  - **Cách xác minh địa chỉ IP hiện tại của máy ảo Ubuntu:**
    Mở terminal trên Ubuntu Server và chạy một trong hai lệnh sau:
    ```bash
    hostname -I
    # hoặc
    ip -br a
    ```
    _(Ghi lại địa chỉ IP IPv4 đang hoạt động, ví dụ `192.168.206.128`)_
  - Dòng cấu hình thêm vào tệp hosts trên máy dùng trình duyệt (máy host Windows):
    ```text
    <IP_THỰC_TẾ_UBUNTU> shop.local pma.shop.local
    ```
    _(Ví dụ minh họa: `192.168.206.128 shop.local pma.shop.local`)_

---

### 2.2. Hướng dẫn chỉnh sửa tệp `hosts` trên máy trình duyệt:

#### Trên máy tính Windows:

1. Nhấn phím `Windows`, gõ `Notepad`, click chuột phải chọn **Run as Administrator**.
2. Trong Notepad, chọn `File` -> `Open` và mở đường dẫn: `C:\Windows\System32\drivers\etc\hosts` _(lưu ý chuyển bộ lọc file sang All Files `_._`)_.
3. Thêm dòng ánh xạ phù hợp vào cuối tệp.
4. Lưu tệp (Ctrl + S).

#### Trên máy tính Linux / macOS:

1. Mở terminal và chỉnh sửa file hosts:
   ```bash
   sudo nano /etc/hosts
   ```
2. Thêm dòng ánh xạ phù hợp vào cuối tệp.
3. Lưu (Ctrl + O, Enter) và thoát (Ctrl + X).

---

## 3. KHỞI TẠO CHỨNG CHỈ SSL VÀ MẬT KHẨU QUẢN TRỊ AN TOÀN (RUN-TIME)

Khi triển khai trên môi trường Docker thực tế:

### Bước 1: Sinh chứng chỉ SSL/TLS tự ký

Chạy script tự động đã được chuẩn bị sẵn:

```bash
chmod +x scripts/generate-ssl.sh
./scripts/generate-ssl.sh
```

_Script sẽ tự động tạo cặp khóa `nginx/ssl/selfsigned.key` (RSA 2048-bit, quyền 600) và chứng chỉ `nginx/ssl/selfsigned.crt` hỗ trợ các tên miền `shop.local`, `pma.shop.local`, `localhost`, `127.0.0.1`._

### Bước 2: Thiết lập tài khoản HTTP Basic Auth tương tác cho phpMyAdmin

Để bảo vệ an toàn thông tin xác thực, **tuyệt đối không nhập mật khẩu trực tiếp trên dòng lệnh**. Hãy sử dụng chế độ nhập tương tác:

- **Tạo mới tệp lần đầu (khi tệp chưa tồn tại):**

  ```bash
  htpasswd -c nginx/.htpasswd admin_pma
  ```

  _Hệ thống sẽ hiển thị lời nhắc `New password:` và `Re-type new password:` để nhập mật khẩu an toàn mà không lưu vào lịch sử lệnh shell._

- **Thêm hoặc đổi mật khẩu tài khoản khi tệp đã tồn tại:**
  _(Lưu ý: Không dùng cờ `-c` để tránh ghi đè làm mất tài khoản cũ)_

  ```bash
  htpasswd nginx/.htpasswd admin_pma
  ```

- **Trường hợp môi trường chưa cài sẵn tiện ích `htpasswd`:**
  Chạy tương tác qua container Apache tạm thời:
  - Khi tạo mới lần đầu (chưa có tệp):
    ```bash
    docker run --rm -it -v "${PWD}/nginx:/etc/nginx" httpd:alpine htpasswd -c /etc/nginx/.htpasswd admin_pma
    ```
  - Khi cập nhật hoặc thêm người dùng (đã có tệp, không dùng `-c`):
    ```bash
    docker run --rm -it -v "${PWD}/nginx:/etc/nginx" httpd:alpine htpasswd /etc/nginx/.htpasswd admin_pma
    ```

---

## 4. XỬ LÝ CẢNH BÁO BẢO MẬT TRÌNH DUYỆT (SELF-SIGNED CERTIFICATE)

Vì đây là chứng chỉ SSL/TLS tự ký phục vụ học tập và demo (không mua từ CA công cộng như DigiCert, Let's Encrypt):

1. Khi mở `https://shop.local` hoặc `https://pma.shop.local`, trình duyệt (Chrome, Edge, Firefox) sẽ hiển thị cảnh báo:
   - **"Your connection is not private"** hoặc **"Kết nối của bạn không phải là kết nối riêng tư"**.
2. Nhấn vào nút **"Advanced"** (Nâng cao).
3. Chọn **"Proceed to shop.local (unsafe)"** hoặc **"Tiếp tục truy cập shop.local (không an toàn)"**.
4. Trình duyệt sẽ tải trang web bình thường qua giao thức mã hóa HTTPS an toàn.

---

## 5. TRUY CẬP HỆ THỐNG GIÁM SÁT PROMETHEUS & GRAFANA (MỐC COMMIT 2)

Hệ thống giám sát phục vụ demo môn học được cấu hình sẵn các bảng điều khiển trực quan:

| Dịch vụ Giám sát | URL Truy cập Demo | Cổng Host | Cơ chế Xác thực & Hướng dẫn |
| :--- | :--- | :---: | :--- |
| **Prometheus Web UI** | `http://localhost:9090` hoặc `http://<IP_UBUNTU>:9090` | `9090` | Truy cập trực tiếp; kiểm tra trạng thái 4 Scrape Targets tại mục `Status` -> `Targets`. |
| **Grafana Dashboard** | `http://localhost:3000` hoặc `http://<IP_UBUNTU>:3000` | `3000` | Đăng nhập bằng tài khoản quản trị thiết lập trong `.env`: <br>- **Username:** Giá trị `GRAFANA_ADMIN_USER` (mặc định `admin`) <br>- **Password:** Giá trị bí mật trong `GRAFANA_ADMIN_PASSWORD`. |

### 5.1. Danh mục Bảng điều khiển (Dashboards) đã nạp sẵn tự động:
Sau khi đăng nhập Grafana, điều hướng tới mục **Dashboards** -> thư mục **`WordPress System Monitoring`**:
1. **`01. Giám sát Máy chủ (Host Infrastructure)`** (UID: `host-monitoring`):
   - Đo chỉ số CPU Usage, RAM Usage, Root Disk %, System Uptime, CPU per Core, Memory Breakdown, Network I/O, Load Average (1m/5m/15m) từ `node-exporter`.
2. **`02. Giám sát Container (cAdvisor)`** (UID: `container-monitoring`):
   - Giám sát trạng thái và số lượng container Docker đang chạy, CPU Usage % từng container, Memory Working Set (`container_memory_working_set_bytes`), Network Traffic I/O (RX/TX) từ `cadvisor`.
3. **`03. Giám sát Cơ sở dữ liệu (MySQL Overview)`** (UID: `mysql-monitoring`):
   - Giám sát trạng thái hoạt động MySQL (`mysql_up`), Queries Per Second (QPS), Client Connections, Active Running Threads, Phân rã câu lệnh (Select, Insert, Update, Delete), InnoDB Buffer Pool Utilization, MySQL Network Throughput và Slow Queries Rate từ `mysql-exporter`.

### 5.2. Khuyến cáo An toàn Bảo mật:
- Các cổng `9090` (Prometheus) và `3000` (Grafana) được publish ra host nhằm phục vụ giảng viên kiểm tra và trình diễn demo trong khuôn khổ môn học.
- **Trên môi trường vận hành thực tế (Production):** Các cổng này bắt buộc phải được đóng lại khỏi mạng công cộng (loại bỏ `ports:` trong Compose), chỉ cho phép truy cập qua VPN nội bộ hoặc định tuyến qua Nginx Reverse Proxy có phân quyền và mã hóa SSL/TLS.
- **Lưu ý về tài khoản MySQL Exporter:** Tài khoản `exporter` được cấp `PROCESS, REPLICATION CLIENT, SELECT ON *.*` để tương thích với các collectors của `mysqld_exporter`. Tài khoản này không có quyền sửa/xóa dữ liệu, nhưng có quyền đọc dữ liệu toàn cục (bao gồm cả các bảng ứng dụng WordPress). Giới hạn `MAX_USER_CONNECTIONS 3` chỉ khống chế kết nối của riêng exporter nhằm bảo vệ connection pool, không hạn chế quyền đọc dữ liệu.

---

## 6. HỆ THỐNG NHẬT KÝ TẬP TRUNG LOKI & GRAFANA ALLOY QUA SOCKET PROXY (MỐC COMMIT 3)

Hệ thống nhật ký tập trung tự động thu gom toàn bộ log từ các container vào Grafana Loki (`grafana/loki:3.7.9`) thông qua **Grafana Alloy** (`grafana/alloy:v1.21.2`) — agent telemetry thế hệ mới thay thế Promtail (vốn đã kết thúc vòng đời hỗ trợ từ ngày 02/03/2026). Dữ liệu nhật ký được tích hợp sẵn thành nguồn dữ liệu (Datasource) trong Grafana.

Để bảo vệ Docker Engine API, hệ thống sử dụng **Docker Socket Proxy** dựa trên **HAProxy** (`haproxy:2.8.31-alpine`) với tệp cấu hình chuyên dụng `docker-socket-proxy/haproxy.cfg` làm tường lửa trung gian áp dụng chính sách **Strict Allowlist (Deny-by-default)**, thay thế cho giải pháp Tecnativa proxy vốn tồn tại lỗ hổng bảo mật cấp quyền quá mức (CVE-2026-78122 / Issue #182).

### 6.1. Hướng dẫn truy cập và khám phá nhật ký trên Grafana Explore:
1. Đăng nhập vào Grafana tại `http://localhost:3000` (hoặc `http://<IP_UBUNTU>:3000`).
2. Trên thanh menu bên trái, chọn biểu tượng **Explore** (la bàn) hoặc truy cập trực tiếp `http://localhost:3000/explore`.
3. Tại ô chọn nguồn dữ liệu (Datasource) ở góc trên bên trái, chọn **`Loki`**.
4. Các nhãn (Labels) đã được chuẩn hóa bởi pipeline của Alloy để lọc dữ liệu:
   * `container`: Tên container phát sinh log (`nginx`, `wordpress`, `mysql`, `phpmyadmin`, `prometheus`, `grafana`, `node-exporter`, `cadvisor`, `mysql-exporter`, `loki`, `alloy`, `docker-socket-proxy`).
   * `service`: Tên dịch vụ Docker Compose tương ứng.
   * `stream`: Luồng đầu ra (`stdout` hoặc `stderr`).
   * `project`: Định danh dự án cố định (`de-03-wordpress`).
   * `environment`: Nhãn môi trường phù hợp với đồ án môn học (`demo`).

### 6.2. Danh mục 3 Truy vấn LogQL chuẩn phục vụ Demo và Đánh giá:

Nhập các câu lệnh sau vào khung soạn thảo LogQL và nhấn **"Run query"**:

| Mục đích kiểm thử | Câu lệnh LogQL | Ý nghĩa & Dữ liệu hiển thị kỳ vọng |
| :--- | :--- | :--- |
| **1. Giám sát truy cập Web Nginx** | `{container="nginx"} \|= "GET"` | Lọc toàn bộ các yêu cầu HTTP GET từ client đi qua Nginx Reverse Proxy, bao gồm IP client, URL request, mã phản hồi HTTP (200, 301, 304, ...) và User-Agent. *(Chưa qua thử nghiệm runtime)* |
| **2. Phát hiện lỗi Web & Ứng dụng** | `{container=~"wordpress\|nginx"} \|~ "(?i)(error\|failed\|404\|500)"` | Tìm kiếm không phân biệt hoa thường các dòng log báo lỗi (PHP fatal error/warning trên WordPress, Nginx upstream connection failure, mã lỗi HTTP 404/500). *(Chưa qua thử nghiệm runtime)* |
| **3. Cảnh báo an ninh CSDL MySQL** | `{container="mysql"} \|= "Access denied"` | Phát hiện các cảnh báo bảo mật từ engine MySQL Server khi có client kết nối sai mật khẩu hoặc tấn công brute-force (`Access denied for user...`). Quản trị viên cũng có thể truy vấn `{container="mysql"}` để xem log khởi động, cấu hình InnoDB và transaction của database. *(Chưa qua thử nghiệm runtime)* |

### 6.3. Truy vấn nâng cao chuyển đổi Log sang Metric (LogQL Metrics):
* **Tần suất yêu cầu HTTP Nginx theo thời gian:**
  ```logql
  sum by (status) (rate({container="nginx"} | pattern `<ip> - - [<time>] "<method> <uri> <proto>" <status> <size> <_>` [1m]))
  ```
* **Đếm số lượng lỗi phát sinh theo container:**
  ```logql
  sum by (container) (count_over_time({container=~"wordpress|nginx|mysql"} |~ "(?i)(error|failed|denied)" [5m]))
  ```

### 6.4. An toàn bảo mật hệ thống Logging & Phân tích rủi ro Socket Proxy:
* **Rà soát lỗ hổng Tecnativa Proxy (CVE-2026-78122 / Issue #182):**
  * Trong thiết kế ban đầu với `tecnativa/docker-socket-proxy`, biến môi trường `CONTAINERS=1` mở toàn bộ namespace `/containers/*`. Mặc dù đặt `POST=0` để chặn các method ghi, **các request GET độc hại vẫn lọt qua hoàn toàn**:
    * `GET /containers/{id}/archive`: Cho phép tải/đọc trộm bất kỳ tệp tin nào trong container (kể cả `/etc/shadow`, `wp-config.php`, SSH keys).
    * `GET /containers/{id}/export`: Cho phép tải dump toàn bộ filesystem container dạng tarball.
    * `GET /containers/{id}/attach/ws`: Cho phép cướp terminal tương tác container qua WebSocket.
    * `GET /containers/{id}/top`: Cho phép theo dõi danh sách tiến trình nội bộ của container trên host.
  * Vì vậy, khẳng định *`POST=0 loại bỏ hoàn toàn rủi ro`* là **hoàn toàn sai lầm về mặt an ninh**.
* **Phân tách mạng chuyên dụng (Network Isolation - `docker-api-net`):**
  * Socket thật `/var/run/docker.sock:ro` **chỉ được mount vào `docker-socket-proxy`**. Alloy tuyệt đối không mount socket vật lý.
  * Thiết lập mạng nội bộ độc lập `docker-api-net` (`internal: true`). `docker-socket-proxy` **chỉ tham gia `docker-api-net`**, không tham gia `monitoring-net` hay bất kỳ mạng nào khác.
  * `alloy` tham gia đồng thời 2 mạng: `docker-api-net` (kết nối proxy tại `tcp://docker-socket-proxy:2375`) và `monitoring-net` (đẩy log tới `http://loki:3100`).
  * Tuyệt đối không publish cổng 2375 ra host. Không có bất kỳ container nào khác trong hệ thống có thể tiếp cận được Socket Proxy.
* **Kiến trúc HAProxy Strict Allowlist (Chính sách Deny-by-default):**
  * Dự án sử dụng image chính thức `haproxy:2.8.31-alpine` với tệp cấu hình độc lập `docker-socket-proxy/haproxy.cfg`.
  * **Quy tắc chặn Method:** Chặn 403 Forbidden ngay lập tức mọi HTTP method ngoài `GET` và `HEAD` (ngăn chặn triệt để `POST`, `PUT`, `DELETE`, `PATCH`).
  * **Quy tắc Allowlist 5 Endpoint tối thiểu cần thiết:**
    1. `GET /_ping`: Healthcheck daemon probe (`^(/v[0-9\.]+)?/_ping$`).
    2. `GET /version`: Tra cứu phiên bản Docker API trong handshake ban đầu (`^(/v[0-9\.]+)?/version$`).
    3. `GET /containers/json`: Liệt kê danh sách container cho `discovery.docker` (`^(/v[0-9\.]+)?/containers/json$`).
    4. `GET /containers/{id}/logs`: Thu thập luồng log stdout/stderr cho `loki.source.docker` (`^(/v[0-9\.]+)?/containers/[a-zA-Z0-9_.-]+/logs$`).
    5. `GET /containers/{id}/json`: Cho phép tailer của `loki.source.docker` xác minh container state và timestamp khởi tạo (`^(/v[0-9\.]+)?/containers/[a-zA-Z0-9_.-]+/json$`).
  * **Loại bỏ `GET /events`:** Thành phần `discovery.docker` của Alloy hoạt động bằng cơ chế định kỳ polling `ContainerList` qua `refresh_interval = "5s"` chứ không đăng ký lắng nghe event stream. Do đó, endpoint `/events` bị loại bỏ khỏi allowlist để tối giản bề mặt tấn công.
  * **Chính sách Timeout:** Cấu hình `timeout client 1h`, `timeout server 1h`, `timeout tunnel 1h` để hỗ trợ các luồng log kéo dài mà không bị ngắt kết nối khi container tạm thời không có output mới.
  * **Quy tắc Deny-by-default (403 Forbidden):** Mặc định từ chối tất cả các request còn lại, bao gồm `/archive`, `/export`, `/top`, `/attach`, `/exec`, `/build`, `/images`, `/volumes`, `/networks`.
* **Đánh giá rủi ro còn lại của `GET /containers/{id}/json` & Phạm vi Relabel:**
  * Endpoint inspect trả về mảng `Config.Env` chứa các biến môi trường cấu hình của container. Nếu ứng dụng cấu hình mật khẩu dạng plain text env, client trong mạng `docker-api-net` (ở đây chỉ có Alloy) có thể đọc được metadata này.
  * *Lưu ý về Relabel Whitelist:* Bộ lọc `discovery.relabel` trong Alloy chỉ giới hạn các log được chuyển tiếp lên Loki, **không giới hạn quyền đọc API của Alloy** đối với các container qua Socket Proxy (Alloy vẫn có thể gọi inspect/list mọi container qua proxy trước khi lọc nhãn).
  * *Đánh đổi bảo mật:* Endpoint inspect bắt buộc phải mở để Docker SDK tailer trong Alloy hoạt động. Rủi ro này được khoanh vùng tối đa nhờ cô lập mạng riêng `docker-api-net` (không expose cổng host, chỉ duy nhất Alloy được kết nối).
* **Quyền hạn và Lưu trữ trạng thái của Alloy:**
  * Alloy lưu offset đọc log bền vững trên named volume `alloy_data` gắn vào `/var/lib/alloy/data` (cấu hình qua cờ `--storage.path`).
  * Do Alloy chỉ mở kết nối mạng TCP tới proxy (`tcp://docker-socket-proxy:2375`) mà không cần đọc socket file Unix, tiến trình chạy dưới user unprivileged `alloy` (UID `473`, GID `473`), không cần đặc quyền root (`0:0`).
* **Lưu ý kiểm thử:** Các quy tắc regex allowlist đã được kiểm tra bằng test script độc lập. Do môi trường Windows hiện tại không cài đặt sẵn công cụ `haproxy` CLI cục bộ (`haproxy -c`), parser nội bộ của HAProxy chưa thể được kiểm chứng tĩnh và không coi thử nghiệm regex Python là bằng chứng tương đương. Hiệu năng thực tế và kết nối API sẽ được kiểm chứng chính thức trong bước chạy runtime trên máy chủ Ubuntu.
