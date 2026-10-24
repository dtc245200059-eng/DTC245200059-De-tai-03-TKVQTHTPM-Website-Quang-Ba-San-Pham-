#!/bin/bash
# ==============================================================================
# SCRIPT KHỞI TẠO TÀI KHOẢN GIÁM SÁT CHO MYSQL EXPORTER
# Đề tài số 03: Website Quảng bá Sản phẩm bằng WordPress
# Môn học: Triển khai và Quản trị Hệ thống Phần mềm
# Sinh viên: Nông Quốc Tuấn | MSV: DTC245200059 | Lớp: KTPM K23C
# ==============================================================================
# Tệp này được tự động thực thi bởi MySQL Docker Entrypoint trong lần khởi tạo
# cơ sở dữ liệu đầu tiên (thư mục /docker-entrypoint-initdb.d/).
#
# ĐẶC TẢ PHẠM VI QUYỀN HẠN & ĐÁNH ĐỔI BẢO MẬT:
# 1. PROCESS: Cho phép xem danh sách luồng/tiến trình đang thực thi trong MySQL.
# 2. REPLICATION CLIENT: Cho phép đọc trạng thái binlog và replication.
# 3. SELECT ON *.*: Cho phép đọc các bảng hệ thống (performance_schema,
#    information_schema) phục vụ các collectors của mysqld_exporter.
#    LƯU Ý BẢO MẬT: SELECT ON *.* là quyền đọc toàn cục, đồng nghĩa tài khoản này
#    CÓ THỂ ĐỌC DỮ LIỆU ỨNG DỤNG (bao gồm cả các bảng WordPress). Tài khoản không
#    có quyền sửa/xóa (INSERT/UPDATE/DELETE/DROP), nhưng có phạm vi đọc rộng.
# 4. WITH MAX_USER_CONNECTIONS 3: Giới hạn tối đa 3 kết nối đồng thời của riêng
#    tài khoản exporter, ngăn exporter làm cạn kiệt connection pool. Giới hạn này
#    không giới hạn quyền đọc và không thay thế cấu hình max_connections toàn server.
# ==============================================================================

set -euo pipefail

echo "[INIT-MYSQL] Bắt đầu thiết lập tài khoản giám sát MySQL Exporter..."

# Xác định tên người dùng và mật khẩu từ biến môi trường
EXPORTER_USER="${MYSQL_EXPORTER_USER:-exporter}"
EXPORTER_PASS="${MYSQL_EXPORTER_PASSWORD:-}"

if [[ -z "${EXPORTER_PASS}" ]]; then
    echo "[INIT-MYSQL-ERROR] Biến MYSQL_EXPORTER_PASSWORD chưa được thiết lập!" >&2
    exit 1
fi

# 1. Kiểm tra định dạng username (chỉ cho phép ký tự chữ, số và gạch dưới)
if [[ ! "${EXPORTER_USER}" =~ ^[a-zA-Z0-9_]+$ ]]; then
    echo "[INIT-MYSQL-ERROR] Tên người dùng EXPORTER_USER không hợp lệ: '${EXPORTER_USER}'! Chỉ chấp nhận [a-zA-Z0-9_]." >&2
    exit 1
fi

# 2. Xử lý escape ký tự nhạy cảm (dấu gạch chéo ngược và dấu nháy đơn) để tạo SQL an toàn
ESCAPED_PASS="${EXPORTER_PASS//\\/\\\\}"
ESCAPED_PASS="${ESCAPED_PASS//\'/\'\'}"

# 3. Thực thi lệnh SQL qua giao thức socket nội bộ với quyền root
# Lưu ý: Script này chỉ chạy một lần khi volume db_data rỗng. Nếu volume đã có từ trước,
# vui lòng chạy thủ công theo mẫu tại mysql/init/01-exporter.sql.example.
mysql --protocol=socket -u root -p"${MYSQL_ROOT_PASSWORD}" <<-EOSQL
    CREATE USER IF NOT EXISTS '${EXPORTER_USER}'@'%' IDENTIFIED BY '${ESCAPED_PASS}' WITH MAX_USER_CONNECTIONS 3;
    GRANT PROCESS, REPLICATION CLIENT, SELECT ON *.* TO '${EXPORTER_USER}'@'%';
    FLUSH PRIVILEGES;
EOSQL

echo "[INIT-MYSQL-SUCCESS] Đã cấp quyền giám sát thành công cho '${EXPORTER_USER}'@'%'. (Lưu ý: SELECT ON *.* cấp quyền đọc toàn cục phục vụ collectors)."
