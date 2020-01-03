<?php

if ( !defined( 'ABSPATH' ) ) exit;

// 文章格式类型
function wp_miniprogram_post_formats() {
    $settings = wp_miniprogram_option('formats');
    $formats = array();
    if( is_array( $settings ) ) {
        $formats = array_keys( $settings );
    }
    return $formats;
}
if( wp_miniprogram_post_formats() ) {
    add_theme_support( 'post-formats', wp_miniprogram_post_formats() );
}
// 描述清理HTML标签
if( !function_exists('wp_delete_html_code') ) {
    function wp_delete_html_code($description) {
        $description = trim($description);
        $description = strip_tags($description,"");
        return ($description);
    }
}
// 之前时间格式
if( !function_exists('datetime_before') ) {
    function datetime_before($the_time) {
        $now_time = date("Y-m-d H:i:s",time()+8*60*60); 
        $now_time = strtotime($now_time);
        $show_time = strtotime($the_time);
        $dur = $now_time - $show_time;
        if ($dur < 0) {
            return $the_time; 
        } else {
            if ($dur < 60) {
                return $dur.'秒前'; 
            } else {
                if ($dur < 3600) {
                    return floor($dur/60).'分钟前'; 
                } else {
                    if ($dur < 86400) {
                        return floor($dur/3600).'小时前';
                    } else {
                        if ($dur < 259200) {//3天内
                            return floor($dur/86400).'天前';
                        } else {
                            return date("Y-m-d",$show_time); 
                        }
                    }
                }
            }
        }
    }
}
// 推送订阅消息错误码信息
function mp_subscribe_errcode_msg($key) {
    $msg = array(
        '0' => __('消息推送成功','imahui'),
        '40003' => __('用户 OpenID 错误','imahui'),
        '40037' => __('订阅模板 ID 错误','imahui'),
        '43101' => __('用户拒绝接受消息','imahui'),
        '47003' => __('模板参数不准确','imahui'),
        '41030' => __('页面路径不正确','imahui')
    );
    
    return isset($msg[$key]) ? $msg[$key] : '';
}

// Admin notice
add_action( 'welcome_panel', 'mini_program_api_admin_notice' );
function mini_program_api_admin_notice() {
  ?>
  <style type="text/css">
    .about-description a{
      text-decoration:none;
    }
  </style>
  <div class="notice notice-info">
  <p class="about-description">欢迎使用 Mini Program API 连接小程序。欢迎访问：<a target="_blank" rel="nofollow" href="https://www.weitimes.com">丸子小程序</a>, 查看 WordPress 小程序详情</p>
  </div>
  <?php
}

// Admin footer text
function mini_program_api_admin_footer_text($text) {
    $text = '<span id="footer-thankyou">感谢使用 <a href=http://cn.wordpress.org/ target="_blank">WordPress</a>进行创作，<a target="_blank" rel="nofollow" href="https://www.weitimes.com/">点击访问</a> WordPress 小程序专业版。</span>';
    return $text;
}

add_filter('admin_footer_text', 'mini_program_api_admin_footer_text');
