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
