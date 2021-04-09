<?php
namespace  elliot;
/**
 * auth yuehuaxw@gmail.com
 */
class HttpRequest
{

    /**
     * 执行一个 HTTP 请求
     *
     * @param string 	$url 	执行请求的URL
     * @param mixed	$params 表单参数
     * 							可以是array, 也可以是经过url编码之后的string
     * @param mixed	$cookie cookie参数
     * 							可以是array, 也可以是经过拼接的string
     * @param string	$method 请求方法 post / get
     * @param string	$protocol http协议类型 http / https
     * @return array 结果数组
     */
    static public function httpRequest($url, $params, $cookie = '', $method='post', $protocol='http')
    {
        $query_string = self::makeQueryString($params);
        $cookie_string = self::makeCookieString($cookie);
        $ch = curl_init();

        //这个参数很重要，设置这个才可以支持毫秒级的超时设置
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 5000);

        //解决error:1414D172:SSL routines:tls12_check_peer_sigalg:wrong signature type
        curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'DEFAULT@SECLEVEL=1');

	    if ('get' == $method)
	    {
		    curl_setopt($ch, CURLOPT_URL, "$url?$query_string");
	    }
	    else
        {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
        }

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

        // disable 100-continue
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

        if (!empty($cookie_string))
        {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie_string);
        }

        if ('https' == $protocol)
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSLVERSION , 1);
        }

        $ret = curl_exec($ch);
        $err = curl_error($ch);

        if (false === $ret || !empty($err))
        {
            $errno = curl_errno($ch);
            $info = curl_getinfo($ch);
            curl_close($ch);
            return array(
                'result' => false,
                'errno' => $errno,
                'msg' => $err,
                'info' => $info,
            );
        }

        curl_close($ch);

        return array(
            'result' => true,
            'msg' => $ret,
        );

    }

    static public function makeQueryString($params)
    {
        if (is_string($params)){

            return $params;
        }

        $query_string = array();
        foreach ($params as $key => $value)
        {
            array_push($query_string, rawurlencode($key) . '=' . rawurlencode($value));
        }
        $query_string = join('&', $query_string);
        return $query_string;
    }

    static public function makeCookieString($params)
    {
        if (is_string($params))
            return $params;

        $cookie_string = array();
        foreach ($params as $key => $value)
        {
            array_push($cookie_string, $key . '=' . $value);
        }
        $cookie_string = join('; ', $cookie_string);
        return $cookie_string;
    }
}