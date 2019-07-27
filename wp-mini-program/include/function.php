<?php
/*
 * function
 */
 
if ( !defined( 'ABSPATH' ) ) exit;
// 文章格式类型
add_theme_support( 'post-formats',function () {
    $formats = wp_miniprogram_option('formats');
    $post_formats = array();
    if( $formats && is_array($formats) ) {
        foreach($formats as $key => $format) {
            if($format) {
                $post_formats[] = $key;
            }
        }
    }
    return $post_formats;
});
// 描述清理HTML标签
function wp_delete_html_code($description) {
	$description = trim($description);
	$description = strip_tags($description,"");
	return ($description);
}
// 之前时间格式
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