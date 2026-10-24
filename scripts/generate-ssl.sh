#!/usr/bin/env bash
# ==============================================================================
# SCRIPT TỰ ĐỘNG TẠO CHỨNG CHỈ SSL/TLS TỰ KÝ (SELF-SIGNED CERTIFICATE)
# Đề tài số 03: Website Quảng bá Sản phẩm bằng WordPress
# Môn học: Triển khai và Quản trị Hệ thống Phần mềm
# Sinh viên: Nông Quốc Tuấn | MSV: DTC245200059 | Lớp: KTPM K23C
# ==============================================================================
# Script này tạo cặp khóa và chứng chỉ SSL/TLS tự ký hỗ trợ SAN (Subject Alternative Name)
# cho các tên miền demo: shop.local, pma.shop.local, localhost, IP 127.0.0.1
# ==============================================================================

set -euo pipefail

# Xác định đường dẫn thư mục lưu trữ chứng chỉ dựa trên vị trí của script
SSL_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../nginx/ssl" && pwd)"
KEY_FILE="${SSL_DIR}/selfsigned.key"
CRT_FILE="${SSL_DIR}/selfsigned.crt"
DAYS_VALID=365
FORCE=false

# Xử lý và kiểm toán tham số dòng lệnh
if [[ $# -gt 1 ]]; then
    echo "[ERROR] Quá nhiều tham số. Chỉ chấp nhận tối đa 1 tham số." >&2
    echo "Sử dụng: $0 [--force]" >&2
    echo "  --force : Tạo lại chứng chỉ ngay cả khi cặp khóa đã tồn tại." >&2
    echo "  --help  : Hiển thị hướng dẫn này." >&2
    exit 1
fi

if [[ $# -eq 1 ]]; then
    case "${1}" in
        --help|-h)
            echo "Sử dụng: $0 [--force]"
            echo "  --force : Tạo lại chứng chỉ ngay cả khi cặp khóa đã tồn tại."
            echo "  --help  : Hiển thị hướng dẫn này."
            exit 0
            ;;
        --force)
            FORCE=true
            ;;
        *)
            echo "[ERROR] Tham số không hợp lệ: ${1}" >&2
            echo "Sử dụng: $0 [--force]" >&2
            echo "  --force : Tạo lại chứng chỉ ngay cả khi cặp khóa đã tồn tại." >&2
            echo "  --help  : Hiển thị hướng dẫn này." >&2
            exit 1
            ;;
    esac
fi

# Kiểm tra sự sẵn sàng của công cụ OpenSSL
if ! command -v openssl >/dev/null 2>&1; then
    echo "[ERROR] Không tìm thấy lệnh 'openssl' trên hệ thống. Vui lòng cài đặt OpenSSL trước khi tiếp tục." >&2
    exit 1
fi

echo "======================================================================"
echo " KHỞI TẠO CHỨNG CHỈ SSL/TLS TỰ KÝ CHO NGINX REVERSE PROXY"
echo " Thư mục lưu trữ: ${SSL_DIR}"
echo "======================================================================"

# Kiểm tra sự tồn tại của tệp chứng chỉ và tính toàn vẹn của cặp khóa
if [[ "${FORCE}" == false ]]; then
    if [[ -f "${KEY_FILE}" && -f "${CRT_FILE}" ]]; then
        echo "[INFO] Cặp chứng chỉ SSL đã tồn tại đầy đủ tại:"
        echo "       - Key: ${KEY_FILE}"
        echo "       - Crt: ${CRT_FILE}"
        echo "[INFO] Bỏ qua quá trình tạo mới. Để tạo lại chứng chỉ, chạy lệnh: $0 --force"
        exit 0
    elif [[ -f "${KEY_FILE}" || -f "${CRT_FILE}" ]]; then
        echo "[WARN] Phát hiện trạng thái không đồng bộ (chỉ tồn tại một trong hai tệp):"
        echo "       - Key (${KEY_FILE}): $( [[ -f "${KEY_FILE}" ]] && echo 'ĐÃ CÓ' || echo 'THIẾU' )"
        echo "       - Crt (${CRT_FILE}): $( [[ -f "${CRT_FILE}" ]] && echo 'ĐÃ CÓ' || echo 'THIẾU' )"
        echo "[INFO] Tự động tạo mới cả cặp tệp để đảm bảo tính khớp khóa và tính toàn vẹn hệ thống."
    fi
else
    echo "[INFO] Kích hoạt cờ --force: Sẵn sàng tạo lại toàn bộ cặp chứng chỉ SSL..."
fi

# Tạo thư mục lưu trữ nếu chưa có và kiểm tra quyền ghi
mkdir -p "${SSL_DIR}"
if [[ ! -w "${SSL_DIR}" ]]; then
    echo "[ERROR] Không có quyền ghi vào thư mục: ${SSL_DIR}" >&2
    exit 1
fi

if [[ -f "${KEY_FILE}" && ! -w "${KEY_FILE}" ]]; then
    echo "[ERROR] Không có quyền ghi đè tệp khóa riêng tư: ${KEY_FILE}" >&2
    exit 1
fi

if [[ -f "${CRT_FILE}" && ! -w "${CRT_FILE}" ]]; then
    echo "[ERROR] Không có quyền ghi đè tệp chứng chỉ: ${CRT_FILE}" >&2
    exit 1
fi

# Thiết lập thư mục tạm trên CÙNG filesystem với thư mục đích để bảo đảm thao tác rename nguyên tử
TMP_DIR="$(mktemp -d "${SSL_DIR}/.tmp_ssl.XXXXXX")"
TMP_CNF="${TMP_DIR}/openssl.cnf"
TMP_KEY="${TMP_DIR}/selfsigned.key"
TMP_CRT="${TMP_DIR}/selfsigned.crt"

# Cơ chế dọn dẹp tài nguyên tạm thời an toàn khi kết thúc hoặc gặp lỗi
cleanup() {
    if [[ -n "${TMP_DIR:-}" && -d "${TMP_DIR}" ]]; then
        rm -rf "${TMP_DIR}"
    fi
}
trap cleanup EXIT INT TERM

# Cấu hình OpenSSL với SAN (Subject Alternative Name)
cat <<EOF > "${TMP_CNF}"
[req]
default_bits        = 2048
prompt              = no
default_md          = sha256
distinguished_name  = req_distinguished_name
x509_extensions     = v3_req

[req_distinguished_name]
C  = VN
ST = ThaiNguyen
L  = ThaiNguyen
O  = ICTU_DevOps_Project
OU = KTPM_K23C
CN = shop.local

[v3_req]
subjectAltName      = @alt_names
basicConstraints    = CA:FALSE
keyUsage            = nonRepudiation, digitalSignature, keyEncipherment
extendedKeyUsage    = serverAuth

[alt_names]
DNS.1 = shop.local
DNS.2 = pma.shop.local
DNS.3 = localhost
IP.1  = 127.0.0.1
EOF

echo "[ACTION] Đang sinh khóa riêng tư RSA 2048-bit và chứng chỉ x509 vào không gian tạm..."

# Thực thi lệnh OpenSSL vào tệp tạm thời (Không ẩn stderr để dễ dàng chẩn đoán nếu lỗi)
openssl req -x509 -nodes -days "${DAYS_VALID}" \
    -newkey rsa:2048 \
    -keyout "${TMP_KEY}" \
    -out "${TMP_CRT}" \
    -config "${TMP_CNF}" \
    -extensions v3_req

# 1. Xác minh tính toàn vẹn của từng tệp (File Integrity Check)
if [[ ! -s "${TMP_KEY}" || ! -s "${TMP_CRT}" ]]; then
    echo "[ERROR] OpenSSL không thể tạo tệp khóa hoặc chứng chỉ hợp lệ (tệp rỗng hoặc không tồn tại)!" >&2
    exit 1
fi

# 2. Xác minh tính tương ứng toán học giữa Private Key và Certificate (Modulus Verification)
KEY_MOD="$(openssl rsa -in "${TMP_KEY}" -modulus -noout 2>/dev/null)"
CRT_MOD="$(openssl x509 -in "${TMP_CRT}" -modulus -noout 2>/dev/null)"
if [[ -z "${KEY_MOD}" || -z "${CRT_MOD}" || "${KEY_MOD}" != "${CRT_MOD}" ]]; then
    echo "[ERROR] Xác thực thất bại: Khóa riêng tư và chứng chỉ không khớp nhau!" >&2
    exit 1
fi

# Phân quyền an toàn trước khi di chuyển vào thư mục đích
chmod 600 "${TMP_KEY}"
chmod 644 "${TMP_CRT}"

# Lưu bản sao lưu tạm thời trong TMP_DIR để phục vụ rollback nếu thao tác thay thế gặp sự cố
BACKUP_KEY=""
BACKUP_CRT=""
if [[ -f "${KEY_FILE}" ]]; then
    cp -p "${KEY_FILE}" "${TMP_DIR}/backup.key" 2>/dev/null || true
    BACKUP_KEY="${TMP_DIR}/backup.key"
fi
if [[ -f "${CRT_FILE}" ]]; then
    cp -p "${CRT_FILE}" "${TMP_DIR}/backup.crt" 2>/dev/null || true
    BACKUP_CRT="${TMP_DIR}/backup.crt"
fi

# 3. Thực hiện thay thế từng tệp qua rename trên cùng filesystem (Atomic Rename per file)
# Kèm cơ chế khôi phục (Rollback) nếu thao tác thay thế tệp thứ hai thất bại
if ! mv -f "${TMP_KEY}" "${KEY_FILE}"; then
    echo "[ERROR] Thay thế tệp khóa riêng tư thất bại!" >&2
    exit 1
fi

if ! mv -f "${TMP_CRT}" "${CRT_FILE}"; then
    echo "[ERROR] Thay thế tệp chứng chỉ thất bại! Đang khôi phục lại trạng thái trước đó..." >&2
    if [[ -n "${BACKUP_KEY}" && -f "${BACKUP_KEY}" ]]; then
        mv -f "${BACKUP_KEY}" "${KEY_FILE}" || true
    else
        rm -f "${KEY_FILE}" || true
    fi
    exit 1
fi

# Xác minh cặp tệp đích cuối cùng
if [[ ! -s "${KEY_FILE}" || ! -s "${CRT_FILE}" ]]; then
    echo "[ERROR] Kiểm tra cặp tệp chứng chỉ đích cuối cùng thất bại!" >&2
    exit 1
fi

echo "[SUCCESS] Tạo chứng chỉ SSL/TLS tự ký thành công!"
echo "          - Khóa riêng tư: ${KEY_FILE} (Quyền: 600)"
echo "          - Chứng chỉ x509: ${CRT_FILE} (Quyền: 644)"
echo "          - Thời hạn hiệu lực: ${DAYS_VALID} ngày"
echo "          - Danh sách SAN: shop.local, pma.shop.local, localhost, 127.0.0.1"
echo "          - Xác thực Modulus: Khóa và chứng chỉ khớp 100%"
echo ""
echo "[LƯU Ý BẢO MẬT]:"
echo "  1. Khóa riêng tư (*.key) được .gitignore bảo vệ, tuyệt đối KHÔNG đưa vào Git."
echo "  2. Do đây là chứng chỉ tự ký (Self-Signed), trình duyệt sẽ hiển thị cảnh báo"
echo "     'Kết nối không an toàn / Cảnh báo bảo mật'. Trong môi trường demo/thực hành,"
echo "     chọn 'Advanced' -> 'Proceed to shop.local (unsafe)' để tiếp tục truy cập."
echo "======================================================================"
