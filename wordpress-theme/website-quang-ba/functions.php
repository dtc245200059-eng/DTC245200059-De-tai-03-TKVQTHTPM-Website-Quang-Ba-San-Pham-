<?php
/**
 * Functions and definitions for Website Quảng Bá Sản Phẩm Theme
 *
 * @package Website_Quang_Ba
 * @author Nông Quốc Tuấn - DTC245200059
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Chống truy cập trực tiếp
}

define('WEBSITE_QUANG_BA_VERSION', '1.0.0');

/**
 * Thiết lập các tính năng hỗ trợ của Theme
 */
function website_quang_ba_setup() {
    // Tự động thêm thẻ <title> vào phần <head>
    add_theme_support('title-tag');

    // Hỗ trợ ảnh đại diện (Featured Image)
    add_theme_support('post-thumbnails');

    // Hỗ trợ logo tùy chỉnh trong Customizer
    add_theme_support('custom-logo', array(
        'height'      => 60,
        'width'       => 220,
        'flex-height' => true,
        'flex-width'  => true,
    ));

    // Hỗ trợ chuẩn HTML5 cho các thành phần cốt lõi
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));

    // Đăng ký các vị trí Menu điều hướng
    register_nav_menus(array(
        'primary-menu' => __('Menu Điều Hướng Chính', 'website-quang-ba'),
        'footer-menu'  => __('Menu Chân Trang', 'website-quang-ba'),
    ));
}
add_action('after_setup_theme', 'website_quang_ba_setup');

/**
 * Tải các tệp Stylesheet và JavaScript cho giao diện
 */
function website_quang_ba_scripts() {
    // Nhúng font chữ hiện đại Google Fonts: Plus Jakarta Sans
    wp_enqueue_style(
        'website-quang-ba-google-fonts',
        'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap',
        array(),
        null
    );

    // Nhúng stylesheet chính của theme
    wp_enqueue_style(
        'website-quang-ba-style',
        get_stylesheet_uri(),
        array(),
        WEBSITE_QUANG_BA_VERSION
    );

    // Nhúng script tương tác JavaScript
    wp_enqueue_script(
        'website-quang-ba-script',
        get_template_directory_uri() . '/assets/js/main.js',
        array(),
        WEBSITE_QUANG_BA_VERSION,
        true
    );

    // Truyền dữ liệu sang JavaScript nếu cần thiết
    wp_localize_script('website-quang-ba-script', 'themeData', array(
        'siteUrl'   => esc_url(home_url('/')),
        'themeUrl'  => esc_url(get_template_directory_uri()),
        'nonce'     => wp_create_nonce('website_quang_ba_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'website_quang_ba_scripts');

/**
 * Cung cấp danh mục sản phẩm tiêu biểu mẫu phục vụ đồ án
 * (Dữ liệu mẫu demo trực quan, sẵn sàng kết nối CSDL hoặc WooCommerce khi cần)
 *
 * @return array Danh sách sản phẩm chi tiết
 */
function website_quang_ba_get_sample_products() {
    $theme_uri = get_template_directory_uri();

    return array(
        array(
            'id'            => 'sp-01',
            'name'          => 'Smart Hub Pro X1',
            'category'      => 'Thiết Bị Trung Tâm',
            'category_slug' => 'thiet-bi-trung-tam',
            'badge'         => 'Sản Phẩm Tiêu Biểu',
            'badge_type'    => 'hot',
            'image'         => $theme_uri . '/assets/images/hub-pro.svg',
            'price'         => '2.450.000 đ',
            'summary'       => 'Bộ điều khiển trung tâm mô phỏng hỗ trợ định hướng giao thức Matter & Zigbee 3.0 với khả năng xử lý cục bộ.',
            'description'   => 'Smart Hub Pro X1 là thiết bị trung tâm mô phỏng cho hệ sinh thái nhà thông minh. Hỗ trợ mô hình kết nối đa giao thức Matter & Zigbee với khả năng quản trị thiết bị nội bộ (Dữ liệu mẫu demo).',
            'specs'         => array(
                'Giao thức'     => 'Matter, Zigbee 3.0, Wi-Fi, BLE',
                'Bộ vi xử lý'   => 'Quad-Core Cortex-A53 1.5GHz (Mô phỏng)',
                'Bộ nhớ'        => 'RAM 2GB DDR4, Flash 16GB eMMC',
                'Kết nối mạng'  => 'Gigabit Ethernet RJ45 + Dual-Band Wi-Fi',
                'Bảo mật'       => 'Mã hóa phần cứng tích hợp'
            )
        ),
        array(
            'id'            => 'sp-02',
            'name'          => 'UltraSensor IoT Node S2',
            'category'      => 'Cảm Biến Thông Minh',
            'category_slug' => 'cam-bien',
            'badge'         => 'Công Nghệ Mới',
            'badge_type'    => 'new',
            'image'         => $theme_uri . '/assets/images/sensor-iot.svg',
            'price'         => '690.000 đ',
            'summary'       => 'Cảm biến đa thông số nhiệt độ, độ ẩm, áp suất và cường độ ánh sáng phục vụ giám sát môi trường.',
            'description'   => 'Cảm biến đa năng thế hệ mới với thiết kế tối ưu hóa năng lượng, phù hợp cho việc giám sát môi trường phòng máy chủ và văn phòng thông minh (Dữ liệu mẫu demo).',
            'specs'         => array(
                'Độ chính xác'  => '±0.2°C (Nhiệt độ), ±1.5% (Độ ẩm)',
                'Pin sử dụng'   => 'Pin cúc áo CR2477 tiết kiệm năng lượng',
                'Kết nối'       => 'Zigbee 3.0 / BLE Mesh',
                'Tần suất gửi'  => 'Tự động gửi cảnh báo khi vượt ngưỡng',
                'Chuẩn bảo vệ'  => 'Tiêu chuẩn thiết kế kháng nước IP54'
            )
        ),
        array(
            'id'            => 'sp-03',
            'name'          => 'CyberEdge Gateway G3',
            'category'      => 'Bảo Mật & Mạng',
            'category_slug' => 'bao-mat-mang',
            'badge'         => 'Bảo Mật Thiết Bị',
            'badge_type'    => 'featured',
            'image'         => $theme_uri . '/assets/images/gateway-edge.svg',
            'price'         => '4.890.000 đ',
            'summary'       => 'Thiết bị định tuyến biên mô phỏng tích hợp tường lửa lọc gói và VPN bảo mật kết nối nội bộ.',
            'description'   => 'CyberEdge Gateway G3 cung cấp giải pháp hỗ trợ cách ly mạng IoT chuyên biệt với mạng nội bộ văn phòng, giảm thiểu rủi ro truy cập trái phép qua các thiết bị thông minh (Dữ liệu mẫu demo).',
            'specs'         => array(
                'Băng thông'    => 'Thông lượng định tuyến danh nghĩa 2.5 Gbps',
                'Cổng mạng'     => '2x 2.5GbE RJ45 + 4x Gigabit LAN',
                'Tường lửa'     => 'Kiểm tra gói tin và lọc lưu lượng nội bộ',
                'VPN hỗ trợ'    => 'WireGuard, OpenVPN, IPsec',
                'Quản trị'      => 'Giao diện Web GUI + Hỗ trợ SNMP/Prometheus'
            )
        ),
        array(
            'id'            => 'sp-04',
            'name'          => 'VisionAI Cam 4K Ultra',
            'category'      => 'Camera An Ninh',
            'category_slug' => 'camera-an-ninh',
            'badge'         => 'Trí Tuệ Nhân Tạo',
            'badge_type'    => 'ai',
            'image'         => $theme_uri . '/assets/images/ai-camera.svg',
            'price'         => '1.950.000 đ',
            'summary'       => 'Camera an ninh độ phân giải 4K với định hướng nhận diện đối tượng và chuyển động cục bộ.',
            'description'   => 'Mô hình camera an ninh hỗ trợ xử lý luồng video cục bộ, lưu trữ thẻ nhớ hoặc chuẩn RTSP/ONVIF nội bộ, tăng cường bảo vệ quyền riêng tư người dùng (Dữ liệu mẫu demo).',
            'specs'         => array(
                'Cảm biến ảnh'  => 'Độ phân giải 4K UHD (3840x2160)',
                'Góc nhìn'      => '135° góc siêu rộng, Chống ngược sáng WDR',
                'Xử lý AI'      => 'Chip xử lý NPU nhận diện đối tượng on-device',
                'Hồng ngoại'    => 'Tầm nhìn ban đêm có màu (Color Night Vision) 25m',
                'Lưu trữ'       => 'Thẻ nhớ MicroSD 256GB + Luồng RTSP/ONVIF'
            )
        ),
        array(
            'id'            => 'sp-05',
            'name'          => 'PowerGuard Mini UPS',
            'category'      => 'Nguồn & Phụ Kiện',
            'category_slug' => 'nguon-phu-kien',
            'badge'         => 'Thiết Yếu',
            'badge_type'    => 'essential',
            'image'         => $theme_uri . '/assets/images/ups-mini.svg',
            'price'         => '950.000 đ',
            'summary'       => 'Bộ lưu điện thông minh chuyển mạch nhanh, duy trì hoạt động cho Hub và Router khi mất điện lưới.',
            'description'   => 'PowerGuard Mini UPS hỗ trợ bảo vệ các thiết bị mạng và điều khiển trung tâm duy trì hoạt động liên tục khi xảy ra sự cố nguồn điện (Dữ liệu mẫu demo).',
            'specs'         => array(
                'Dung lượng pin' => 'Lithium LiFePO4 15.000mAh (Dự phòng)',
                'Thời gian lưu' => 'Khoảng 4 - 6 giờ tùy mức tải Router/Hub',
                'Điện áp ra'    => 'Đa ngõ ra: 12V 2.5A, 9V 1A, 5V USB 2A',
                'Chuyển mạch'   => 'Thiết kế chuyển mạch trực tuyến nhanh',
                'Chỉ báo'       => 'Màn hình OLED hiển thị điện áp và % pin'
            )
        ),
        array(
            'id'            => 'sp-06',
            'name'          => 'SmartTag Track R2',
            'category'      => 'Nguồn & Phụ Kiện',
            'category_slug' => 'nguon-phu-kien',
            'badge'         => 'Độ Chính Xác Cao',
            'badge_type'    => 'hot',
            'image'         => $theme_uri . '/assets/images/tracker-uwb.svg',
            'price'         => '420.000 đ',
            'summary'       => 'Thiết bị định vị theo dõi tài sản băng thông siêu rộng UWB với còi báo vị trí tiện dụng.',
            'description'   => 'SmartTag Track R2 hỗ trợ tìm kiếm đồ dùng, tài sản cầm tay với công nghệ định vị băng thông rộng UWB kết hợp Bluetooth (Dữ liệu mẫu demo).',
            'specs'         => array(
                'Công nghệ'     => 'UWB (Ultra-Wideband) + Bluetooth LE',
                'Định vị'       => 'Định vị không gian sai số thấp',
                'Âm thanh'      => 'Loa cảnh báo tìm kiếm tích hợp',
                'Thời lượng pin'=> 'Pin cúc áo CR2032 thay thế tiện lợi',
                'Kháng nước'    => 'Tiêu chuẩn thiết kế kháng nước IP67'
            )
        )
    );
}

/**
 * Lọc hoặc tùy biến độ dài đoạn trích tóm tắt (Excerpt)
 */
function website_quang_ba_excerpt_length($length) {
    return 24;
}
add_filter('excerpt_length', 'website_quang_ba_excerpt_length');

function website_quang_ba_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'website_quang_ba_excerpt_more');
