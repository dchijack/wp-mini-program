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

    /**
     * 数据解密：低版本使用mcrypt库（PHP < 5.3.0），高版本使用openssl库（PHP >= 5.3.0）。
     *
     * @param string $ciphertext    待解密数据，返回的内容中的data字段
     * @param string $iv            加密向量，返回的内容中的iv字段
     * @param string $app_key       创建小程序时生成的app_key
     * @param string $session_key   登录的code换得的
     * @return string | false
     */
    public static function decrypt($ciphertext, $iv, $app_key, $session_key) {
        $session_key = base64_decode($session_key);
        $iv = base64_decode($iv);
        $ciphertext = base64_decode($ciphertext);
        $plaintext = false;
        if (function_exists("openssl_decrypt")) {
            $plaintext = openssl_decrypt($ciphertext, "AES-192-CBC", $session_key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
        } else {
            $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, null, MCRYPT_MODE_CBC, null);
            mcrypt_generic_init($td, $session_key, $iv);
            $plaintext = mdecrypt_generic($td, $ciphertext);
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);
        }
        if ($plaintext == false) {
            return false;
        }
        // trim pkcs#7 padding
        $pad = ord(substr($plaintext, -1));
        $pad = ($pad < 1 || $pad > 32) ? 0 : $pad;
        $plaintext = substr($plaintext, 0, strlen($plaintext) - $pad);
        // trim header
        $plaintext = substr($plaintext, 16);
        // get content length
        $unpack = unpack("Nlen/", substr($plaintext, 0, 4));
        // get content
        $content = substr($plaintext, 4, $unpack['len']);
        // get app_key
        $app_key_decode = substr($plaintext, $unpack['len'] + 4);
        return $app_key == $app_key_decode ? $content : false;
    }
	
	public static function generate_session() {
        $session_str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $session_key = substr( str_shuffle($session_str), mt_rand( 0, strlen($session_str) - 17 ), 16 );
        $expire_in = date('Y-m-d H:i:s',time()+7200);
        $session = array(
            'session_key' => $session_key,
            'expire_in' => $expire_in
        );
        return $session;
    }

    public static function we_miniprogram_access_token() {
		$appid 		= wp_miniprogram_option('appid');
        $secret 	= wp_miniprogram_option('secretkey');
        if( $appid && $secret ) {
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
            $body = wp_remote_get($url);
            if( !is_array( $body ) || is_wp_error( $body ) || $body['response']['code'] != '200' ) {	
                return false;
            }
            $access_token = json_decode( $body['body'], true );
            return $access_token;
        } else {
            return false;
        }
    }

    public static function qq_miniprogram_access_token() {
		$appid 		= wp_miniprogram_option('qq_appid');
        $secret 	= wp_miniprogram_option('qq_secret');
        if( $appid && $secret ) {
            $url = 'https://api.q.qq.com/api/getToken?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
            $body = wp_remote_get($url);
            if( !is_array( $body ) || is_wp_error( $body ) || $body['response']['code'] != '200' ) {	
                return false;
            }
            $access_token = json_decode( $body['body'], true );
            return $access_token;
        } else {
            return false;
        }
    }

    public static function bd_miniprogram_access_token( ) {
        $appkey 		= wp_miniprogram_option('bd_appkey');
        $secret 		= wp_miniprogram_option('bd_secret');
        if( $appkey && $secret ) {
            $url = 'https://openapi.baidu.com/oauth/2.0/token?grant_type=client_credentials&client_id='.$appkey.'&client_secret='.$secret.'&scope=smartapp_snsapi_base';
            $body = wp_remote_get($url);
            if( !is_array( $body ) || is_wp_error( $body ) || $body['response']['code'] != '200' ) {	
                return false;
            }
            $access_token = json_decode( $body['body'], true );
            return $access_token;
        } else {
            return false;
        }
    }
    
	public static function login( $session ) {
		if( $session ) {
            $user_query = new WP_User_Query( array( 'meta_key' => 'session_key', 'meta_value' => $session ) );
		    $users = $user_query->get_results();
            if( ! empty( $users ) ) {
                if( count( $users ) == 1 ) {
                    return $users[0];
                } else {
                    return false;
                }
            } else {
                return false;
            }
		}
		return false;
    }
    
}