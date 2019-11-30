<?php
/*
 * WeChat Subscribe Message Template
 */

if( !class_exists('MP_Subscribe') ) {
	class MP_Subscribe {

		public static function mp_insert_subscribe_user( $data ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'applets_subscribe_user';
			$result = $wpdb->insert(
				$table_name,
				$data
			);
			if( $result ) {
				return $wpdb->insert_id;
			} else {
				return false;
			}
		}
	
		public static function mp_update_subscribe_user( $openid, $template, $data ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'applets_subscribe_user';
			if( $template ) {
				$args = array( 'openid' => $openid, 'template' => $template );
			} else {
				$args = array( 'openid' => $openid );
			}
			$result = $wpdb->update(
				$table_name,
				$data,
				$args
			);
			return $result;
		}
	
		public static function mp_insert_subscribe_message_send( $data ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'applets_subscribe_message';
			$result = $wpdb->insert(
				$table_name,
				$data
			);
			if( $result ) {
				return $wpdb->insert_id;
			} else {
				return false;
			}
		}
	
		public static function mp_list_subscribe_message_send( $offset = 0, $limit = 0 ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'applets_subscribe_message';
			if( $limit && $offset ) {
				$limits = "LIMIT $limit , $offset";
			} else if( !$limit && $offset ) {
				$limits = "LIMIT $offset";
			} else {
				$limits = "";
			}
			$sql = "SELECT * FROM $table_name ORDER BY date DESC $limits";
			$result = $wpdb->get_results($sql);
			return $result;
		}
	
		public static function mp_subscribe_message_send_task(  ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'applets_subscribe_message';
			$sql = "SELECT * FROM $table_name ORDER BY date DESC LIMIT 1";
			$result = $wpdb->get_row($sql);
			return $result?$result:'';
		}
	
		public static function mp_count_subscribe_message_send( $template = '' ) {
	
			global $wpdb;
			$table_name = $wpdb->prefix . 'applets_subscribe_message';
			if( $template ) {
				$where = $wpdb->prepare("AND template = %s",esc_sql($template));
			} else {
				$where = "";
			}
			if( $where ) {
				$sql = "SELECT COUNT(*) FROM $table_name WHERE $where";
				$sql = @str_replace('WHERE AND','WHERE',$sql);
			} else {
				$sql = "SELECT COUNT(*) FROM $table_name";
			}
			$count = $wpdb->get_var( $sql );
			return $count ? (int)$count : 0;
			
		}
	
		public static function mp_user_subscribe_template_count( $openid, $template ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'applets_subscribe_user';
			$where = $wpdb->prepare("AND openid = %s AND template = %s ",esc_sql($openid),esc_sql($template));
			$sql = "SELECT * FROM $table_name WHERE $where";
			$sql = @str_replace('WHERE AND','WHERE',$sql);
			$result = $wpdb->get_row($sql);
			return $result;
		}
	
		public static function mp_list_subscribe_user_by_template( $template ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'applets_subscribe_user';
			$where = $wpdb->prepare("AND count > 0 AND template = %s ",esc_sql($template));
			$order = "ORDER BY date ASC";
			$sql = "SELECT openid FROM $table_name WHERE $where $order";
			$sql = @str_replace('WHERE AND','WHERE',$sql);
			$result = $wpdb->get_results($sql);
			return $result;
		}
	
		public static function mp_count_subscribe_user( $template = '' ) {
	
			global $wpdb;
			$table_name = $wpdb->prefix . 'applets_subscribe_user';
			if( $template ) {
				$where = $wpdb->prepare("AND template = %s",esc_sql($template));
			} else {
				$where = "";
			}
			$order = "GROUP BY openid";
			if( $where ) {
				$sql = "SELECT * FROM $table_name WHERE $where $order";
				$sql = @str_replace('WHERE AND','WHERE',$sql);
			} else {
				$sql = "SELECT * FROM $table_name $order";
			}
			$rows = $wpdb->get_results( $sql );
			return $rows ? (int)count($rows) : 0;
			
		}
	
		public static function mp_count_user_subscribes( $openid = '' ) {
	
			global $wpdb;
			date_default_timezone_set('PRC');
			$table_name = $wpdb->prefix . 'applets_subscribe_user';
			if( $openid ) {
				$where = $wpdb->prepare("AND openid = %s",esc_sql($openid));
				$sql = "SELECT sum( count )  FROM $table_name WHERE $where";
			} else {
				$sql = "SELECT sum( count )  FROM $table_name";
			}
			$sql = @str_replace('WHERE AND','WHERE',$sql);
			$count = $wpdb->get_var( $sql );
			return $count ? (int)$count : 0;
			
		}
	
		public static function mp_count_today_subscribe_message( ) {
	
			global $wpdb;
			date_default_timezone_set('PRC');
			$table_name = $wpdb->prefix . 'applets_subscribe_message';
			$count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE year(date)=year(now()) AND month(date)=month(now()) AND day(date)=day(now())" );
			return $count ? (int)$count : 0;
			
		}
	
		public static function mp_count_subscribe_message_success( $admin = '' ) {
	
			global $wpdb;
			date_default_timezone_set('PRC');
			$table_name = $wpdb->prefix . 'applets_subscribe_message';
			if( $admin ) {
				$where = $wpdb->prepare("AND errcode = %s AND year(date)=year(now()) AND month(date)=month(now()) AND day(date)=day(now())",0);
			} else {
				$where = $wpdb->prepare("AND errcode = %s",0);
			}
			$sql = "SELECT COUNT(*) FROM $table_name WHERE $where";
			$sql = @str_replace('WHERE AND','WHERE',$sql);
			$count = $wpdb->get_var( $sql );
			return $count ? (int)$count : 0;
			
		}
	
	}
}