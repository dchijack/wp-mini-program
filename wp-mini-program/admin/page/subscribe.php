<?php

if ( !defined( 'ABSPATH' ) ) exit;

if(!class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class MP_Subscribe_Message_Task extends WP_List_Table {

    function __construct( ) {
        global $page;
        parent::__construct(
            array(
                'singular'  => __( 'subscribe', 'imahui' ),
                'plural'    => __( 'subscribe', 'imahui' ),
                'ajax'      => false
            )
        );
    }

    function column_default( $item, $column_name ) {

        switch ( $column_name ) {
            case 'id':
            case 'task':
            case 'openid':
            case 'errcode':
            case 'pages':
            case 'msg':
            case 'date':
                return $item[ $column_name ];
            default:
                return print_r( $item, true );
        }

    }

    function get_columns( ) {
        $columns = array(
            'id'              => __('ID','imahui'),
            'task'            => __('任务ID','imahui'),
            'openid'      => __('OpenID','imahui'),
            'errcode'       => __('错误码','imahui'),
            'pages'     => __('页面','imahui'),
            'msg'     => __('错误信息','imahui'),
            'date'      => __('时间','imahui')
        );
        return $columns;
    }

    function prepare_items( ) {
        $per_page = 10;
        $columns = $this->get_columns();
        $hidden = array();
        $this->_column_headers = array( $columns, $hidden );
        $data = array();
        $results = MP_Subscribe::mp_list_subscribe_message_send( );

        if( $results ) {
            foreach ($results as $res) {
                $data[] = array(
                    'id'         => $res->id,
                    'task'       => $res->task,
                    'openid'     => $res->openid,
                    'errcode'    => $res->errcode,
                    'pages'      => $res->pages,
                    'msg'        => mp_subscribe_errcode_msg( $res->errcode ),
                    'date'       => $res->date
                );
            }
        }

        $current_page = $this->get_pagenum();
        $total_items = MP_Subscribe::mp_count_subscribe_message_send( );
        $data = array_slice( $data,( ($current_page-1)*$per_page ), $per_page );

        $this->items = $data;
        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => $per_page,
                'total_pages' => ceil( $total_items/$per_page )
            )
        );

    }

}

function miniprogram_subscribe_message_count( ) {
    $all = MP_Subscribe::mp_count_subscribe_user( );
    $total = MP_Subscribe::mp_count_user_subscribes( );
    $send = MP_Subscribe::mp_count_subscribe_message_send( );
    $today_send = MP_Subscribe::mp_count_today_subscribe_message( );
    $today_success = MP_Subscribe::mp_count_subscribe_message_success( 'today' );
    $total_success = MP_Subscribe::mp_count_subscribe_message_success( '' );
    $today_round = $today_send ? round($today_success/$today_send*100,2) : 0;
    $total_round = $send ? round($total_success/$send*100,2) : 0;
?>
    <div class="wrap">
        <h1 class="wp-heading-inline">订阅消息</h1>
        <p>微信小程序订阅消息,丸子小程序订阅插件(<a href="https://www.weitimes.com/doc/guide/530.html" target="_blank">点击查看帮助</a>)，丸子小程序专业版，<a href="https://www.weitimes.com/" target="_blank">点击这里查看详情</a></p>
        <hr class="wp-header-end">
        <div class="el-card">
            <h2 class="el-card__header"><span class="dashicons dashicons-info"></span> 订阅信息</h2>
            <div class="el-card__body">
                <div class="el-col">
                    <span>订阅用户</span><h1><?php echo $all; ?></h1>
                </div>
                <div class="el-col">
                    <span>订阅总数</span><h1><?php echo $total; ?></h1>
                </div>
                <div class="el-col">
                    <span>今天推送</span><h1><?php echo $today_send; ?></h1>
                </div>
                <div class="el-col">
                    <span>全部推送</span><h1><?php echo $send; ?></h1>
                </div>
            </div>
        </div>
        <div class="el-card">
            <h2 class="el-card__header"><span class="dashicons dashicons-info"></span> 推送任务</h2>
            <div class="el-card__body">
                <div class="el-col">
                    <span>今日成功推送</span><h1><?php echo $today_success; ?></h1>
                </div>
                <div class="el-col">
                    <span>今日成功率</span><h1><?php echo $today_round.'%'; ?></h1>
                </div>
                <div class="el-col">
                    <span>全部成功推送</span><h1><?php echo $total_success; ?></h1>
                </div>
                <div class="el-col">
                    <span>全部成功率</span><h1><?php echo $total_round.'%'; ?></h1>
                </div>
            </div>
        </div>
    </div>
    <style>
    th.column-id, td.column-id, th.column-task, td.column-task, th.column-errcode, td.column-errcode {
      width:8%;
    }
    th.column-date, td.column-date {
      width:15%!important;
    }
    </style>
<?php }

function miniprogram_subscribe_message_task_table( ) {
    $displayMsg = new MP_Subscribe_Message_Task();
    $displayMsg->prepare_items( );
?>
    <div class="wrap">
        <h1 class="wp-heading-inline">任务列表</h1>
        <hr class="wp-header-end">
        <form id="subscribe-message-filter" method="get">
            <input type="hidden" name="page" value="subscribe-message" />
            <?php $displayMsg->display(  ); ?>
        </form>
    </div>
    <style>
    th.column-id, td.column-id, th.column-task, td.column-task, th.column-errcode, td.column-errcode {
      width:8%;
    }
    th.column-date, td.column-date {
      width:15%!important;
    }
    </style>
<?php }