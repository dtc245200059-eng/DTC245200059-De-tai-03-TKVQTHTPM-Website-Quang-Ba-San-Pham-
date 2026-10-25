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

### 6.5. Cấu hình Giới hạn và Quay vòng Nhật ký Cục bộ Docker (Log Rotation - Đợt 1A):
- Toàn bộ 12 dịch vụ trong hệ thống đều được cấu hình giới hạn xoay vòng log cục bộ qua driver `json-file`:
  ```yaml
  logging:
    driver: "json-file"
    options:
      max-size: "10m"
      max-file: "3"
  ```
- **Mục đích:** Thiết lập mức giới hạn danh nghĩa khoảng 30 MB mỗi container theo cấu hình `max-size: 10m`, `max-file: 3` (tổng tương ứng khoảng 360 MB cho 12 container theo cấu hình dự kiến, không phải bảo đảm tuyệt đối về toàn bộ dung lượng log trên host), giúp giảm thiểu rủi ro cạn kiệt đĩa máy chủ do log dồn ứ lâu ngày.
- **Lưu ý về hành vi và tác động runtime:**
  - `max-size: 10m` và `max-file: 3` chỉ giới hạn dung lượng log cục bộ của Docker daemon, **không thay thế chính sách retention 7 ngày (168h) của Loki**. Grafana Alloy đọc và stream log tức thời về Loki, nên dữ liệu phục vụ phân tích/điều tra sự cố được quản lý độc lập tại Loki.
  - Khi log cục bộ quay vòng, các log cũ nhất trong Docker engine có thể bị loại bỏ; không tuyên bố toàn bộ log đã được sao lưu vĩnh viễn trên host.
  - Cấu hình logging mới chỉ áp dụng cho container được tạo mới hoặc tạo lại (`recreate`). Đợt này chỉ thực hiện cấu hình tĩnh, chưa chạy container và chưa kiểm chứng runtime.

### 6.6. Cấu hình Giới hạn Tài nguyên CPU, RAM và PIDs (Đợt 1B):
Hệ thống thiết lập giới hạn tài nguyên trực tiếp tại cấp `services.<service>` trong `docker-compose.yml` (`mem_reservation`, `mem_limit`, `cpus`, `pids_limit`), không khai báo trùng lặp `deploy.resources`:

| Dịch vụ | Memory Reservation | Memory Hard Limit | CPU Limit (Quota) | PIDs Limit | Rủi ro chính khi OOM-Kill |
| :--- | :---: | :---: | :---: | :---: | :--- |
| `mysql` | 384M | 768M | 1.00 | 150 | Rất cao: hỏng bảng CSDL, ngắt kết nối WordPress |
| `wordpress` | 256M | 512M | 1.00 | 100 | Cao: gián đoạn website, Nginx trả về lỗi 502/504 |
| `phpmyadmin` | 128M | 256M | 0.50 | 60 | Trung bình: mất phiên quản trị database |
| `nginx` | 32M | 128M | 0.50 | 50 | Nghiêm trọng: sập Single Entry Point toàn hệ thống |
| `prometheus` | 256M | 512M | 0.50 | 100 | Trung bình-Cao: mất metrics in-memory chưa flush |
| `grafana` | 128M | 256M | 0.50 | 100 | Trung bình: mất kết nối dashboard web UI |
| `loki` | 256M | 512M | 0.75 | 100 | Trung bình-Cao: mất buffer log ingester |
| `alloy` | 128M | 256M | 0.50 | 100 | Trung bình: nghẽn stream chuyển tiếp log |
| `docker-socket-proxy` | 24M | 64M | 0.25 | 30 | Cao: Alloy mất quyền đọc Docker Engine API |
| `cadvisor` | 64M | 192M | 0.30 | 50 | Thấp-Trung bình: gián đoạn metrics container |
| `node-exporter` | 24M | 64M | 0.20 | 30 | Thấp: gián đoạn metrics host |
| `mysql-exporter` | 24M | 64M | 0.20 | 30 | Thấp: gián đoạn metrics MySQL |
| **TỔNG CỘNG** | **1.704 MB (~1.66 GB)** | **3.584 MB (~3.50 GB)** | **6.20 cores** | **900 PIDs** | *(Tổng ngân sách chỉ tính 12 dịch vụ đã liệt kê)* |

**Ý nghĩa kỹ thuật và nguyên tắc an toàn:**
- **`mem_limit` (Hard Limit):** Ngưỡng chặn cứng; nếu container chạm trần này, Linux OOM Killer sẽ can thiệp tắt tiến trình (`OOMKilled: true`, exit code 137).
- **`mem_reservation` (Soft Limit):** Ngưỡng gợi ý bộ nhớ tối thiểu khi host thiếu hụt RAM; **không khẳng định RAM được bảo đảm dành riêng vật lý** cho container.
- **`cpus` (CFS Bandwidth Quota):** Giới hạn tỷ lệ thời gian CPU được cấp phát trong chu kỳ 100ms; khi chạm trần, container bị throttle chứ không bị kill.
- **`pids_limit`:** Ngăn chặn fork bomb và kiểm soát số luồng/tiến trình tối đa trong container namespace.
- **Ranh giới tài nguyên máy chủ host:**
  - Cấu hình máy chủ Ubuntu đã đo thực tế: 8 CPU logic, RAM 6.2 GiB, RAM khả dụng 4.9 GiB, swap 4 GiB, phân vùng `/` còn trống 33 GB.
  - Tổng Hard Limit của 12 dịch vụ là 3.584 MB (~3.50 GB), chiếm khoảng 73% lượng RAM khả dụng (4.9 GiB), để lại khoảng đệm an toàn hơn 1.3 GiB cho Kernel, Systemd, Docker Daemon và Page Cache.
  - Hệ thống **không dựa vào swap** để coi như có thêm RAM vật lý (swap chỉ là vùng đệm khẩn cấp, việc swap container ra đĩa sẽ làm giảm sút hiệu năng nghiêm trọng).
  - Cấu hình mới chỉ có hiệu lực khi container được tạo mới hoặc tạo lại (`recreate`). Các thông số trên là mức thiết lập ban đầu, chưa qua thử nghiệm hiệu năng hay kiểm chứng mức sử dụng thực tế và cần được theo dõi, đo đạc lại bằng lệnh `docker stats` khi chạy runtime trên máy chủ Ubuntu.

### 6.7. Cơ chế Bảo mật Nginx Reverse Proxy: Rate Limiting, XML-RPC & Timeouts (Đợt 2):

Hệ thống đã triển khai các quy tắc hardening tại tầng biên Nginx Reverse Proxy (`nginx/nginx.conf`):

#### 1. Chính sách Rate Limiting cho Đăng nhập WordPress:
- **Khai báo bộ nhớ:** `limit_req_zone $binary_remote_addr zone=wp_login:10m rate=5r/s;`
  - Theo tài liệu chuẩn Nginx (`ngx_http_limit_req_module`), trên nền tảng 64-bit mỗi trạng thái lưu trữ chiếm 128 bytes (1 MB lưu được ~8.000 trạng thái). Do đó, vùng nhớ 10MB lưu trữ và theo dõi được khoảng **80.000 địa chỉ IP** đồng thời (thay vì 160.000 vốn là định mức của nền tảng 32-bit với trạng thái 64-byte).
- **Phạm vi áp dụng:** Chỉ áp dụng cục bộ cho endpoint `location = /wp-login.php`, không áp dụng cho toàn bộ website nhằm tránh làm ảnh hưởng tới người dùng duyệt trang sản phẩm.
- **Bản chất kỹ thuật:** Đây là cơ chế giới hạn tốc độ tần suất yêu cầu HTTP (HTTP request rate limiting) theo địa chỉ IP nguồn tại tầng Nginx Reverse Proxy, **hoàn toàn không phải là bộ đếm số lần đăng nhập thất bại** (failed login attempts counter / account lockout). Nginx không giải mã nội dung form hay kiểm tra tính đúng/sai của tài khoản/mật khẩu WordPress.
- **Tham số `burst=10 nodelay`:**
  - Cho phép người dùng gửi một đợt tối đa 10 requests đột biến mà không bị trễ thời gian phản hồi (no artificial delay).
  - Khi tần suất vượt quá ngưỡng giới hạn (vượt mức 10 requests burst trên tốc độ 5 req/s), Nginx lập tức từ chối yêu cầu và phản hồi mã **HTTP 429 Too Many Requests** (`limit_req_status 429;`).
- **Lưu ý ranh giới bảo mật:** Giới hạn này là biện pháp giảm tốc độ request (rate throttling) làm chậm các cuộc tấn công quét từ điển từ một địa chỉ IP đơn lẻ; nó **không ngăn chặn triệt để brute-force**, đặc biệt là tấn công brute-force phân tán (Distributed Brute-Force từ botnet nhiều IP) hoặc tấn công tốc độ chậm (low-and-slow). Hệ thống vẫn cần kết hợp với mật khẩu phức tạp, CAPTCHA, xác thực hai yếu tố (MFA) hoặc plugin bảo mật chuyên dụng ở tầng ứng dụng WordPress.
- **Trạng thái kiểm thử:** Mã HTTP 429 và số lượng request trong kịch bản kiểm thử (ví dụ: gửi 15 request liên tục dự kiến nhận 10 request 200 OK và các request sau nhận 429) là **kết quả kỳ vọng trên lý thuyết cấu hình**, chưa phải bằng chứng thực nghiệm runtime do chưa khởi chạy container và chưa chạy lệnh test trên máy Ubuntu.

#### 2. Kiểm soát và Vô hiệu hóa WordPress XML-RPC:
- **Cấu hình:** `location = /xmlrpc.php { deny all; return 403; }` trên các server block WordPress (`shop.local`, `localhost`).
- **Cơ sở lựa chọn:** Đồ án không có nhu cầu tích hợp ứng dụng di động bên ngoài hay pingback/trackback blog. Việc vô hiệu hóa XML-RPC giúp loại bỏ nguy cơ tấn công brute-force amplification (qua phương thức `system.multicall` thử hàng trăm mật khẩu trong 1 request) và tấn công từ chối dịch vụ phản xạ (Pingback Reflection DDoS).
- **Phạm vi hiệu lực:** Sử dụng so khớp chính xác (`= /xmlrpc.php`), hoàn toàn không làm gián đoạn `/wp-login.php`, giao diện quản trị `/wp-admin/` hay WordPress REST API (`/wp-json/`).
- **Ranh giới:** Không tuyên bố việc chặn XML-RPC giải quyết được mọi kỹ thuật tấn công đoán mật khẩu; các vector qua giao diện web hoặc REST API vẫn cần được bảo vệ độc lập.

#### 3. Rà soát Timeouts và Buffers:
- **Khả năng tải lên:** Giữ nguyên `client_max_body_size 64M;` để đảm bảo tương thích hoàn toàn với nhu cầu upload ảnh sản phẩm, themes/plugins WordPress và import file dump database qua phpMyAdmin.
- **Cấu hình Timeouts:**
  - `client_header_timeout 15s;`, `client_body_timeout 15s;`, `send_timeout 15s;`
  - *Ý nghĩa:* Rút ngắn thời gian nhàn rỗi cho phép giữa các lần đọc/ghi dữ liệu, giúp Nginx nhanh chóng giải phóng worker connections bị chiếm giữ bởi các client chậm (giảm thiểu rủi ro Slowloris).
  - *Lưu ý kỹ thuật:* Timeouts là thời gian chờ giữa 2 thao tác I/O liên tiếp, không phải là tổng thời gian truyền tải tệp tin; việc upload file 64MB hợp lệ qua kết nối bình thường hoàn toàn không bị ảnh hưởng.
- **Cấu hình Buffers:**
  - `client_body_buffer_size 128k;`: Giúp xử lý các form POST WordPress và API JSON in-memory.
  - `client_header_buffer_size 2k;` và `large_client_header_buffers 4 16k;`: Đảm bảo tiếp nhận các chuỗi cookie xác thực dài của WordPress và phpMyAdmin mà không gặp lỗi 400 Bad Request hoặc 414 Request-URI Too Large.
  - *Lưu ý:* Việc tinh chỉnh buffer nhằm tối ưu I/O và hạn chế ghi đĩa tạm, không được coi là biện pháp đủ để ngăn chặn tấn công buffer overflow hay mọi biến thể của tấn công Slowloris.

### 6.8. Thu hẹp Đặc quyền Linux Capabilities (Principle of Least Privilege - Đợt 3):

Nhằm tăng cường phòng thủ chiều sâu (defense-in-depth) trong trường hợp container bị khai thác mã độc, hệ thống đã cấu hình loại bỏ các Linux capabilities (`cap_drop: [ALL]`) cho 7 dịch vụ giám sát và logging trong `docker-compose.yml`:

- **Bản chất kỹ thuật của `cap_drop: [ALL]`:**
  - Cấu hình này loại bỏ các Linux capabilities được cấp mặc định cho container.
  - Tiến trình bên trong container vẫn có thể sử dụng các system calls thông thường, giao tiếp mạng và truy cập tài nguyên được cho phép bởi UID/GID, filesystem permissions (DAC), cgroups, namespaces và cấu hình container.
- **Ranh giới an toàn:**
  - Đây là một lớp phòng thủ chiều sâu, **không phải bảo đảm loại bỏ mọi nguy cơ**.
  - Đối với HAProxy Docker Socket Proxy, `cap_drop` **không thay thế** allowlist Docker API: nếu HAProxy bị xâm nhập, những endpoint vẫn được cho phép có thể tiếp tục bị gọi.

| Dịch vụ | Trạng thái Capabilities | Căn cứ kỹ thuật & Ranh giới an toàn |
| :--- | :---: | :--- |
| **`prometheus`** | `cap_drop: [ALL]` | Chạy unprivileged (`nobody`), cổng 9090, ghi TSDB qua quyền DAC volume. Không có dấu hiệu cần capability đặc biệt. |
| **`grafana`** | `cap_drop: [ALL]` | Chạy unprivileged (`grafana`), cổng 3000, ghi SQLite/dashboards qua quyền DAC volume. |
| **`loki`** | `cap_drop: [ALL]` | Chạy unprivileged (`loki`), cổng 3100, ghi chunks log qua quyền DAC volume. |
| **`alloy`** | `cap_drop: [ALL]` | Chạy unprivileged (`alloy`), cổng 12345, kết nối TCP tới Socket Proxy. Không cần raw/mount sockets. |
| **`mysql-exporter`**| `cap_drop: [ALL]` | Chạy unprivileged (`nobody`), cổng 9104, chỉ gửi truy vấn SQL qua kết nối TCP `mysql:3306`. |
| **`node-exporter`** | `cap_drop: [ALL]` | Đọc sysfs/procfs (`ro`), cổng 9100. Khả năng thu thập metrics phụ thuộc vào quyền đọc của từng mount và danh mục collectors được kích hoạt (không khẳng định mọi tệp đều có mode `0444`). |
| **`docker-socket-proxy`**| `cap_drop: [ALL]` | Chạy `user: "0:0"` và lắng nghe cổng 2375 (high port, không cần `NET_BIND_SERVICE`). Khả năng truy cập thực tế vào `/var/run/docker.sock` phụ thuộc quyền sở hữu, mode của socket trên host Ubuntu và cấu hình user namespace/remapping của Docker daemon (cần xác minh runtime trên Ubuntu). `cap_drop` không thay thế allowlist Docker API. |
| **`nginx`** | *Giữ nguyên* | Giữ nguyên bộ capability mặc định để tránh phá vỡ tương thích. Master process có khả năng cần `NET_BIND_SERVICE` (cổng 80/443), `SETUID`/`SETGID` để chuyển đổi worker; cần kiểm tra hành vi runtime riêng trước khi thu hẹp. |
| **`wordpress`** | *Giữ nguyên* | Giữ nguyên bộ capability mặc định để tránh phá vỡ tương thích. Apache master có khả năng cần `NET_BIND_SERVICE`, `SETUID`/`SETGID` để quản lý worker pool `www-data`; cần kiểm tra riêng. |
| **`phpmyadmin`** | *Giữ nguyên* | Giữ nguyên bộ capability mặc định để tránh phá vỡ tương thích; Apache master có khả năng cần các quyền tương tự WordPress. |
| **`mysql`** | *Giữ nguyên* | Giữ nguyên bộ capability mặc định để tránh phá vỡ tương thích. Daemon có khả năng cần `SETUID`, `SETGID`, `CHOWN`, `SYS_NICE` cho entrypoint init và điều phối luồng; cần xác minh runtime. |
| **`cadvisor`** | `privileged: true` (cấu hình hiện tại trên Ubuntu) | Container đang chạy ở chế độ privileged, có phạm vi quyền rộng hơn bộ Linux capabilities mặc định. Đây là ngoại lệ rủi ro cao; không thể coi là đã harden chỉ nhờ `cap_drop`. Chỉ thu hẹp hoặc loại bỏ sau khi kiểm tra collectors, mounts và xác minh runtime; không thay đổi cấu hình Compose trong lượt tài liệu này. |
*Lưu ý:* Việc áp dụng `cap_drop: [ALL]` là cấu hình tĩnh, chưa qua kiểm chứng runtime bằng lệnh `capsh` hoặc `docker inspect` trên container đang chạy thực tế trên máy Ubuntu. Không thay đổi mount socket hoặc user của proxy trong lượt này; cần xác minh runtime trên Ubuntu.

---

## 7. HƯỚNG DẪN KÍCH HOẠT VÀ KIỂM TRA GIAO DIỆN WEBSITE QUẢNG BÁ SẢN PHẨM

### 7.1. Cấu trúc Theme tùy chỉnh (`website-quang-ba`)

Theme được xây dựng chuyên biệt theo chuẩn Classic Theme của WordPress, đặt tại thư mục:
`wordpress-theme/website-quang-ba/`

- **`style.css`**: Khai báo metadata nhận diện theme trong WordPress Admin và nhúng stylesheet chính.
- **`functions.php`**: Khai báo `title-tag`, `post-thumbnails`, `custom-logo`, đăng ký menu điều hướng, enqueue CSS/JS và danh mục 6 sản phẩm mẫu tiêu biểu.
- **`header.php`**: Header chuẩn HTML5, thương hiệu TechSmart, menu điều hướng responsive và nút CTA "Tư Vấn Ngay".
- **`footer.php`**: Chân trang hoàn chỉnh 4 cột (thương hiệu đồ án, liên kết nhanh, hỗ trợ demo, huy hiệu hạ tầng DevOps), modal xem chi tiết và container thông báo toast.
- **`front-page.php`**: Trang chủ quảng bá sản phẩm chuyên nghiệp, bao gồm:
  1. *Hero Section:* Tiêu đề nổi bật, mô tả giá trị, nút CTA kép và đồ họa minh họa hệ sinh thái thiết bị.
  2. *Danh mục sản phẩm tiêu biểu (`#san-pham`):* 6 thẻ sản phẩm công nghệ (Smart Hub, UltraSensor, CyberEdge Gateway, VisionAI Cam, PowerGuard UPS, SmartTag) với nhãn nổi bật, giá niêm yết, thông số tóm tắt và nút xem chi tiết.
  3. *Bộ lọc danh mục (Tabs):* Chuyển đổi mượt mà theo từng dòng thiết bị (Thiết bị trung tâm, Cảm biến, Bảo mật & mạng, Camera AI, Nguồn & phụ kiện).
  4. *Tính năng vượt trội (`#tinh-nang`):* 4 khối giá trị cốt lõi (Bảo mật đa tầng, Định hướng chuẩn mở IoT, Giám sát Prometheus/Grafana/Loki, Mô hình xử lý cục bộ on-device).
  5. *Về chúng tôi (`#ve-chung-toi`):* Năng lực nghiên cứu, trải nghiệm người dùng và hạ tầng DevOps.
  6. *Liên hệ & Tư vấn (`#lien-he`):* Form đăng ký nhận tư vấn và hộp thông tin hỗ trợ kỹ thuật.
- **`index.php` & `page.php`**: Template dự phòng chuẩn cho bài viết và trang tĩnh, loại bỏ hoàn toàn giao diện thô sơ mặc định.
- **`assets/images/`**: Toàn bộ hình ảnh minh họa sản phẩm và logo được vẽ dưới dạng vector SVG độc lập, đảm bảo không phụ thuộc internet và không lỗi đường dẫn ảnh.
- **`screenshot.png`**: Ảnh đại diện preview giao diện trong trang quản trị WordPress Themes.

### 7.2. Phương thức Tích hợp vào Docker Compose

Trong `docker-compose.yml`, thư mục theme được gắn kết dạng bind mount vào đường dẫn themes của WordPress:
```yaml
services:
  wordpress:
    volumes:
      - wordpress_data:/var/www/html
      - ./wordpress-theme/website-quang-ba:/var/www/html/wp-content/themes/website-quang-ba:ro
```
- **Ý nghĩa kỹ thuật:**
  - `wordpress_data` tiếp tục lưu trữ toàn bộ mã nguồn core, database config và upload.
  - Mount `./wordpress-theme/website-quang-ba` thêm thư mục theme vào vị trí `/var/www/html/wp-content/themes/website-quang-ba` với cờ `:ro` (chỉ đọc), bảo vệ mã nguồn theme khỏi bị sửa đổi trái phép từ bên trong container.

### 7.3. Quy trình Kích hoạt Theme trên WordPress

> [!IMPORTANT]
> Việc mount thư mục theme vào container **không đồng nghĩa với việc theme sẽ tự động kích hoạt**. Quản trị viên cần thực hiện một trong hai cách sau để kích hoạt theme:

#### Cách 1: Kích hoạt qua Giao diện WordPress Admin (Khuyến nghị)
1. Truy cập trang quản trị qua Nginx Reverse Proxy: `https://shop.local/wp-admin/` (hoặc `https://localhost/wp-admin/`).
2. Đăng nhập bằng tài khoản quản trị WordPress đã khởi tạo ở Giai đoạn 2.
3. Trên thanh menu bên trái, chọn **Appearance (Giao diện)** -> **Themes (Giao diện)**.
4. Tìm theme mang tên **Website Quảng Bá Sản Phẩm - Đề Tài 03** (có ảnh đại diện TechSmart).
5. Bấm nút **Activate (Kích hoạt)**.

#### Cách 2: Kích hoạt nhanh qua dòng lệnh (WP-CLI)
Nếu môi trường container có sẵn công cụ WP-CLI, chạy lệnh:
```bash
docker compose exec wordpress wp theme activate website-quang-ba --allow-root
```

### 7.4. Các Bước Kiểm tra Sau Triển khai (Checklist)

Sau khi kích hoạt theme, mở trình duyệt truy cập `https://shop.local/` và kiểm tra các tiêu chí:
1. **Kiểm tra Header & Navigation:**
   - Logo hiển thị rõ ràng, thanh điều hướng ghim (sticky) trên cùng màn hình khi cuộn trang.
   - Nhấp vào các liên kết "Trang Chủ", "Sản Phẩm", "Tính Năng", "Về Chúng Tôi", "Liên Hệ" cuộn êm (smooth scroll) đến đúng từng section.
2. **Kiểm tra Responsive trên Thiết bị Di động:**
   - Thu nhỏ cửa sổ trình duyệt hoặc bật F12 Device Emulation (iPhone / iPad / Android).
   - Xác nhận giao diện không tràn ngang (`overflow-x: hidden`).
   - Nút menu hamburger trên di động mở/đóng menu điều hướng chính xác.
3. **Kiểm tra Bộ lọc Sản phẩm (Tabs):**
   - Nhấp các nút danh mục: "Tất Cả", "Thiết Bị Trung Tâm", "Cảm Biến", "Bảo Mật & Mạng", "Camera An Ninh", "Nguồn & Phụ Kiện".
   - Các thẻ sản phẩm tương ứng xuất hiện/ẩn mượt mà.
4. **Kiểm tra Modal Xem Chi Tiết:**
   - Bấm nút "Xem Chi Tiết" trên bất kỳ sản phẩm nào.
   - Hộp thoại Modal bật lên hiển thị đầy đủ tên, giá niêm yết, mô tả chi tiết và bảng thông số kỹ thuật.
   - Đóng modal bằng nút `X`, click ra ngoài vùng backdrop hoặc nhấn phím `ESC`.
5. **Kiểm tra Form Đăng Ký Tư Vấn (Demo UI):**
   - Nhập thông tin và bấm "Gửi Thử Nghiệm (Demo Giao Diện)".
   - Thông báo Toast hiển thị phản hồi tức thì giải thích rõ đây là mô phỏng giao diện (dữ liệu chưa gửi/lưu trữ lên máy chủ) và form tự động reset.
6. **Xác nhận Loại bỏ Giao diện Mặc định:**
   - Trang chủ hiển thị trọn vẹn website quảng bá sản phẩm thương mại hiện đại, không còn xuất hiện các bài viết mặc định "Hello world!" hay trang mẫu "Sample Page".
