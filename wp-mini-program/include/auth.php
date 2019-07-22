<?php
/**
 * REST API: WP_REST_Authentication_Controller class
 *
 * @package WordPress
 * @subpackage REST_API
 * @since 4.7.0
 */
if ( !defined( 'ABSPATH' ) ) exit;

class MP_Auth {
	
    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encryptedData string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功 0，失败返回对应的错误码
      */
    public static function decryptData( $appid, $session, $encryptedData, $iv, &$data ) {
		
		$ErrorCode = array(
            'OK'                => 0,
            'IllegalAesKey'     => -41001,
            'IllegalIv'         => -41002,
            'IllegalBuffer'     => -41003,
            'DecodeBase64Error' => -41004
        );
		
        if (strlen($session) != 24) {
            return array('code'=>$ErrorCode['IllegalAesKey'],'message'=>'session_key 长度不合法','session_key'=>$session);
        }
        $aesKey = base64_decode($session);
        if (strlen($iv) != 24) {
            return array('code'=>$ErrorCode['IllegalIv'],'message'=>'iv 长度不合法','iv'=>$iv);
        }
        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        $result = openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $data_decode = json_decode( $result );
        if( $data_decode  == NULL ) {
            return array('code'=>$ErrorCode['IllegalBuffer'],'message'=>'解密失败，非法缓存');
        }
        if( $data_decode->watermark->appid != $appid ) {
            return array('code'=>$ErrorCode['IllegalBuffer'],'message'=>'解密失败，AppID 不正确');
        }
        $data = $result;
        return $ErrorCode['OK'];
    }
	
	public static function generate_session() {
        date_default_timezone_set(get_option('timezone_string'));
        $session_str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $session_key = substr( str_shuffle($session_str), mt_rand( 0, strlen($session_str) - 17 ), 16 );
        $expire_in = date('Y-m-d H:i:s',time()+7200);
        $session = array(
            'session_key' => $session_key,
            'expire_in' => $expire_in
        );
        return $session;
    }
    
	public static function login( $session ) {
		if( $session ) {
            $user_query = new WP_User_Query( array( 'meta_key' => 'session_key', 'meta_value' => $session ) );
		    $users = $user_query->get_results();
            if( ! empty( $users ) ) {
                return $users[0];
            } else {
                return false;
            }
		}
		return false;
    }
    
}