<?php
/**
 * Plugin Name: My Simple Calendar Plus
 * Description: イベント無制限・カラーピッカー・イベント別形状選択機能付きカレンダー
 * Version: 5.3
 * Author: Onozaki Masashi[Design Studio Garage034.com]
 * Author URI:  https://garage034.com
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// --- メニューの追加 ---
function ccal_add_admin_menu() {
    add_menu_page('イベントカレンダー設定', '📅 カレンダー設定', 'manage_options', 'ccal-settings', 'ccal_render_admin_page', 'dashicons-calendar-alt', 25);
}
add_action( 'admin_menu', 'ccal_add_admin_menu' );

// --- 管理画面用アセット（CSS/JS）の読み込み ---
function ccal_admin_assets($hook) {
    if ( $hook !== 'toplevel_page_ccal-settings' ) return;
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_style( 'ccal-admin-style', plugin_dir_url( __FILE__ ) . 'css/admin-style.css', array(), '5.3' );
    wp_enqueue_script( 'ccal-admin-script', plugin_dir_url( __FILE__ ) . 'js/admin-script.js', array('jquery', 'wp-color-picker'), '5.3', true );
}
add_action( 'admin_enqueue_scripts', 'ccal_admin_assets' );

// --- フロント用アセット（CSS/JS）の読み込み ---
function ccal_front_assets() {
    wp_enqueue_style( 'ccal-front-style', plugin_dir_url( __FILE__ ) . 'css/front-style.css', array(), '5.3' );
    wp_enqueue_script( 'ccal-front-script', plugin_dir_url( __FILE__ ) . 'js/front-script.js', array(), '5.3', true );
}
add_action( 'wp_enqueue_scripts', 'ccal_front_assets' );

// --- データ取得（イベント） ---
function ccal_get_events() {
    $events = get_option( 'ccal_events', false );
    if ( $events === false ) {
        $events = array(
            'yasumi' => array('label' => '休業日', 'color' => '#ff6b6b', 'shape' => 'circle', 'time' => ''),
            'eventA' => array('label' => 'イベントA', 'color' => '#55af9c', 'shape' => 'rounded', 'time' => ''),
            'eventB' => array('label' => 'イベントB', 'color' => '#f3a663', 'shape' => 'triangle', 'time' => ''),
        );
        update_option( 'ccal_events', $events );
    }
    return $events;
}

// --- 管理画面の描画 ---
function ccal_render_admin_page() {
    $events = ccal_get_events();
    $existing_data = get_option( 'ccal_event_data', array() );
    $existing_custom_times = get_option( 'ccal_event_custom_times', array() );

    if ( isset($_POST['ccal_save_hidden']) && wp_verify_nonce($_POST['ccal_nonce'], 'ccal_save_action') ) {
        $new_events = array();
        if ( isset($_POST['evt_ids']) && is_array($_POST['evt_ids']) ) {
            foreach ( $_POST['evt_ids'] as $index => $id ) {
                $id = sanitize_text_field($id);
                if ( empty($id) ) { $id = 'evt_' . uniqid(); }
                $label  = sanitize_text_field($_POST['evt_labels'][$index]);
                $color  = sanitize_hex_color($_POST['evt_colors'][$index]);
                $shape  = sanitize_text_field($_POST['evt_shapes'][$index]);
                $time   = sanitize_text_field($_POST['evt_times'][$index]);
                $delete = isset($_POST['evt_delete'][$index]) ? 1 : 0;
                
                if ( !$delete && !empty($label) ) {
                    $new_events[$id] = array(
                        'label' => $label, 
                        'color' => $color ?: '#cccccc', 
                        'shape' => in_array($shape, array('circle', 'rounded', 'triangle')) ? $shape : 'circle',
                        'time'  => $time
                    );
                }
            }
        }
        if(empty($new_events)) { 
            $new_events['evt_1'] = array('label' => 'イベント', 'color' => '#ff6b6b', 'shape' => 'circle', 'time' => ''); 
        }
        update_option( 'ccal_events', $new_events );
        $events = $new_events;

        $submitted_data = isset($_POST['ccal_dates']) ? $_POST['ccal_dates'] : array();
        $submitted_custom_times = isset($_POST['ccal_custom_times']) ? $_POST['ccal_custom_times'] : array();
        
        $m_year  = isset($_GET['m_y']) ? intval($_GET['m_y']) : intval(date('Y'));
        $m_month = isset($_GET['m_m']) ? intval($_GET['m_m']) : intval(date('n'));
        $current_month_prefix = sprintf('%04d-%02d-', $m_year, $m_month);

        foreach ( $existing_data as $date => $val ) {
            if ( strpos($date, $current_month_prefix) === 0 ) { unset($existing_data[$date]); }
        }
        foreach ( $existing_custom_times as $date => $val ) {
            if ( strpos($date, $current_month_prefix) === 0 ) { unset($existing_custom_times[$date]); }
        }

        foreach ( $submitted_data as $date => $types_str ) {
            if ( preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date) ) {
                $types = explode(',', $types_str);
                $valid_types = array();
                foreach ( $types as $type ) {
                    $type = trim($type);
                    if ( isset($events[$type]) ) { $valid_types[] = $type; }
                }
                if ( !empty($valid_types) ) {
                    $existing_data[$date] = implode(',', array_slice($valid_types, 0, 2));
                    if (isset($submitted_custom_times[$date]) && !empty($submitted_custom_times[$date])) {
                        $existing_custom_times[$date] = sanitize_text_field($submitted_custom_times[$date]);
                    }
                }
            }
        }
        
        update_option( 'ccal_event_data', $existing_data );
        update_option( 'ccal_event_custom_times', $existing_custom_times );
        echo '<div class="updated"><p>設定を保存しました！</p></div>';
    }

    $saved_data = get_option( 'ccal_event_data', array() );
    $m_year  = isset($_GET['m_y']) ? intval($_GET['m_y']) : intval(date('Y'));
    $m_month = isset($_GET['m_m']) ? intval($_GET['m_m']) : intval(date('n'));

    $p_month = $m_month - 1; $p_year = $m_year; if ($p_month < 1) { $p_month = 12; $p_year--; }
    $n_month = $m_month + 1; $n_year = $m_year; if ($n_month > 12) { $n_month = 1; $n_year++; }
    $first_day = mktime( 0, 0, 0, $m_month, 1, $m_year );
    $days_in_month = date( 't', $first_day );
    $start_day = date( 'w', $first_day );
    ?>
    <div class="wrap">
        <h1>📅 カレンダー設定</h1>
        <p>固定ページや投稿などに <code>[my_calendar]</code> を挿入してください。</p>
        <form method="post" action="">
            <?php wp_nonce_field( 'ccal_save_action', 'ccal_nonce' ); ?>
            <input type="hidden" name="ccal_save_hidden" value="1">

            <div class="ccal-name-settings">
                <h3>🎨 イベントの種類・色・形の設定</h3>
                <p>※イベントごとに「色」と「形（丸・角丸・三角）」を個別に設定できます。</p>
                <table class="wp-list-table widefat striped" id="events-table">
                    <thead><tr><th>イベント名</th><th>色</th><th>形</th><th>デフォルト時間</th><th>削除</th></tr></thead>
                    <tbody id="events-tbody">
                        <?php foreach($events as $id => $ev): 
                            $current_ev_shape = isset($ev['shape']) ? $ev['shape'] : 'circle';
                        ?>
                        <tr>
                            <td><input type="text" name="evt_labels[]" value="<?php echo esc_attr($ev['label']); ?>"></td>
                            <td><input type="text" name="evt_colors[]" value="<?php echo esc_attr($ev['color']); ?>" class="ccal-color-picker"></td>
                            <td>
                                <select name="evt_shapes[]">
                                    <option value="circle" <?php selected($current_ev_shape, 'circle'); ?>>🟢 丸</option>
                                    <option value="rounded" <?php selected($current_ev_shape, 'rounded'); ?>>🔲 角丸</option>
                                    <option value="triangle" <?php selected($current_ev_shape, 'triangle'); ?>>🔺 三角</option>
                                </select>
                            </td>
                            <td><input type="text" name="evt_times[]" value="<?php echo esc_attr($ev['time']); ?>"></td>
                            <td><label><input type="checkbox" name="evt_delete[]" value="1"> 削除</label></td>
                            <input type="hidden" name="evt_ids[]" value="<?php echo esc_attr($id); ?>">
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="button" class="button" id="add-event-btn" style="margin-top:10px;">＋ 新しいイベントを追加</button>
            </div>
            
            <div class="ccal-palette-area">
                <strong>【1】パレットから選択（クリックで切替 / 同じものをもう一度押すと解除・最大2つまで重複可）：</strong><br><br>
                <?php foreach($events as $id => $ev): ?>
                    <label class="p-lbl">
                        <input type="radio" name="p_select" value="<?php echo esc_attr($id); ?>" class="palette-radio" data-color="<?php echo esc_attr($ev['color']); ?>" <?php checked($id, array_key_first($events)); ?>>
                        <span class="badge" style="background-color:<?php echo esc_attr($ev['color']); ?>; color:#fff; border-radius:3px;">
                            <?php echo esc_html($ev['label']); ?>
                        </span>
                    </label>
                <?php endforeach; ?>
                <label class="p-lbl"><input type="radio" name="p_select" value="clear" class="palette-radio" data-color=""> <span class="badge" style="background-color:#72777c; color:#fff; border-radius:3px;">消しゴム</span></label>
            </div>

            <div class="ccal-admin-header">
                <a class="button" href="?page=ccal-settings&m_y=<?php echo $p_year; ?>&m_m=<?php echo $p_month; ?>">&laquo; 前月</a>
                <h2><?php echo $m_year; ?>年 <?php echo $m_month; ?>月</h2>
                <a class="button" href="?page=ccal-settings&m_y=<?php echo $n_year; ?>&m_m=<?php echo $n_month; ?>">次月 &raquo;</a>
            </div>

            <table class="ccal-admin-table">
                <thead><tr><th>日</th><th>月</th><th>火</th><th>水</th><th>木</th><th>金</th><th>土</th></tr></thead>
                <tbody>
                    <tr>
                    <?php
                    $col = 0;
                    for ($i = 0; $i < $start_day; $i++) { echo '<td class="empty"></td>'; $col++; }
                    
                    for ($d = 1; $d <= $days_in_month; $d++) {
                        if ($col == 7) { echo '</tr><tr>'; $col = 0; }
                        $ymd = sprintf('%04d-%02d-%02d', $m_year, $m_month, $d);
                        $current_types_str = isset($saved_data[$ymd]) ? $saved_data[$ymd] : '';
                        $types_array = !empty($current_types_str) ? explode(',', $current_types_str) : array();
                        $cell_time_val = isset($existing_custom_times[$ymd]) ? $existing_custom_times[$ymd] : '';
                        
                        // 管理画面用プレビュー（簡易）
                        $bg_style = '';
                        if (count($types_array) == 1 && isset($events[$types_array[0]])) {
                            $bg_style = "background-color: " . $events[$types_array[0]]['color'] . "; color: #fff;";
                        } elseif (count($types_array) >= 2 && isset($events[$types_array[0]]) && isset($events[$types_array[1]])) {
                            $c1 = $events[$types_array[0]]['color']; $c2 = $events[$types_array[1]]['color'];
                            $bg_style = "background: linear-gradient(135deg, {$c1} 50%, {$c2} 50%); color: #fff;";
                        }
                        
                        echo "<td style='{$bg_style}' class='day-cell'>";
                        echo "<div class='ccal-cell-inner' data-ymd='{$ymd}' data-types='{$current_types_str}'>";
                        echo "<span class='d-num'>{$d}</span>";
                        echo "<input type='text' name='ccal_custom_times[{$ymd}]' value='{$cell_time_val}' class='cell-time-input' placeholder='個別時間'>";
                        if ($current_types_str) { echo "<input type='hidden' name='ccal_dates[{$ymd}]' value='{$current_types_str}' class='cell-hidden-input'>"; }
                        echo "</div></td>";
                        $col++;
                    }
                    while ($col < 7) { echo '<td class="empty"></td>'; $col++; }
                    ?>
                    </tr>
                </tbody>
            </table>
            <p><input type="submit" class="button button-primary button-large" value="設定を保存する"></p>
        </form>
    </div>
    <?php
}

// --- ショートコード ---
add_shortcode( 'my_calendar', 'ccal_render_front_view' );
function ccal_render_front_view() {
    $current_year  = isset( $_GET['sec_y'] ) ? intval( $_GET['sec_y'] ) : intval(date('Y'));
    $current_month = isset( $_GET['sec_m'] ) ? intval( $_GET['sec_m'] ) : intval(date('n'));

    $prev_month = $current_month - 1; $prev_year  = $current_year; if ( $prev_month < 1 ) { $prev_month = 12; $prev_year--; }
    $next_month = $current_month + 1; $next_year  = $current_year; if ( $next_month > 12 ) { $next_month = 1; $next_year++; }

    $events             = ccal_get_events();
    $saved_data         = get_option( 'ccal_event_data', array() );
    $saved_custom_times = get_option( 'ccal_event_custom_times', array() );

    $first_day = mktime( 0, 0, 0, $current_month, 1, $current_year );
    $days_in_month = date( 't', $first_day );
    $start_day = date( 'w', $first_day );

    $eng_months = array(1=>'JAN.', 2=>'FEB.', 3=>'MAR.', 4=>'APR.', 5=>'MAY.', 6=>'JUN.', 7=>'JUL.', 8=>'AUG.', 9=>'SEP.', 10=>'OCT.', 11=>'NOV.', 12=>'DEC.');
    
    ob_start();
    ?>
    <div class="sec-container">
        <div class="ccal-visual-header">
            <div class="header-left">
                <a href="?sec_y=<?php echo $prev_year; ?>&sec_m=<?php echo $prev_month; ?>" class="nav-arrow-btn">&laquo;</a>
                <span class="big-month-num"><?php echo sprintf('%02d', $current_month); ?></span>
                <span class="small-month-eng"><?php echo $eng_months[$current_month]; ?></span>
                <a href="?sec_y=<?php echo $next_year; ?>&sec_m=<?php echo $next_month; ?>" class="nav-arrow-btn">&raquo;</a>
            </div>
            <div class="header-right"><span class="outline-year-text"><?php echo $current_year; ?></span></div>
        </div>

        <table class="sec-table">
            <thead><tr><th class="sun">SUN</th><th>MON</th><th>TUE</th><th>WED</th><th>THU</th><th>FRI</th><th class="sat">SAT</th></tr></thead>
            <tbody>
                <tr>
                <?php
                $column = 0;
                for ( $i = 0; $i < $start_day; $i++ ) { echo '<td class="empty"></td>'; $column++; }

                for ( $d = 1; $d <= $days_in_month; $d++ ) {
                    if ( $column == 7 ) { echo '</tr><tr>'; $column = 0; }
                    $check_date = sprintf( '%04d-%02d-%02d', $current_year, $current_month, $d );
                    $classes = array(); $data_attr = ''; $num_style = ''; $shape_class = '';

                    if ( $column == 0 ) { $classes[] = 'sun'; }
                    if ( $column == 6 ) { $classes[] = 'sat'; }

                    if ( isset( $saved_data[$check_date] ) && !empty($saved_data[$check_date]) ) {
                        $types_str = $saved_data[$check_date];
                        $types_array = explode(',', $types_str);
                        $classes[] = 'has-event';
                        
                        $label_names = array(); $time_values = array(); $color_values = array(); $shape_values = array();
                        
                        foreach($types_array as $t) {
                            if(isset($events[$t])) {
                                $label_names[]  = $events[$t]['label'];
                                $color_values[] = $events[$t]['color'];
                                $shape_values[] = isset($events[$t]['shape']) ? $events[$t]['shape'] : 'circle';
                                $time_values[]  = !empty($saved_custom_times[$check_date]) ? $saved_custom_times[$check_date] : $events[$t]['time'];
                            }
                        }
                        
                        // 1つの場合のスタイル・形クラス
                        if (count($color_values) == 1) {
                            $num_style = " style='background-color: {$color_values[0]}; color: #fff;'";
                            $shape_class = " shape-" . $shape_values[0];
                        } elseif (count($color_values) >= 2) {
                            // 2つ重なっている場合はグラデーション ＋ デフォルトで丸（または最初の形状）
                            $c1 = $color_values[0]; $c2 = $color_values[1];
                            $num_style = " style='background: linear-gradient(135deg, {$c1} 50%, {$c2} 50%); color: #fff;'";
                            $shape_class = " shape-" . $shape_values[0];
                        }
                        
                        $data_attr = " data-labels='".implode(',', $label_names)."' data-times='".implode(',', $time_values)."' data-colors='".implode(',', $color_values)."' data-date='{$current_month}月{$d}日'";
                    }
                    $class_str = !empty( $classes ) ? ' class="' . implode( ' ', $classes ) . '"' : '';
                    
                    echo "<td{$class_str}{$data_attr}>";
                    echo "<span class='sec-num{$shape_class}'{$num_style}>{$d}</span>";
                    echo "</td>";
                    $column++;
                }
                while ( $column < 7 ) { echo '<td class="empty"></td>'; $column++; }
                ?>
                </tr>
            </tbody>
        </table>

        <div class="sec-legend-area">
            <?php foreach($events as $id => $ev): ?>
                <span class="legend-item"><span class="legend-dot" style="background-color:<?php echo esc_attr($ev['color']); ?>; border-radius:3px;"></span><?php echo esc_html($ev['label']); ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="sec-modal" class="sec-modal" style="display:none;">
        <div class="sec-modal-content">
            <span class="sec-modal-close">&times;</span>
            <h3 id="sec-modal-date"></h3>
            <div id="sec-modal-body"></div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
