<?php

error_reporting(0); // 代码增加 By www.hongyuvip.com

//session_start();


header("Content-type:text/html; charset=UTF-8");


function random($length = 6, $numeric = 0)

{

    PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);

    if ($numeric) {

        $hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));

    } else {

        $hash = '';

        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';

        $max = strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {

            $hash .= $chars[mt_rand(0, $max)];

        }

    }

    return $hash;

}


function read_file($file_name)

{

    $content = '';

    $filename = date('Ymd') . '/' . $file_name . '.log';

    if (function_exists('file_get_contents')) {

        @$content = file_get_contents($filename);

    } else {

        if (@$fp = fopen($filename, 'r')) {

            @$content = fread($fp, filesize($filename));

            @fclose($fp);

        }

    }

    $content = explode("\r\n", $content);

    return end($content);

}


if ($_GET['act'] == 'check') {

    /* 代码修改_start BY www.hongyuvip.com */

    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';

    $mobile_code = isset($_POST['mobile_code']) ? trim($_POST['mobile_code']) : '';

    /* 代码修改_end BY www.hongyuvip.com */


    if (time() - $_SESSION['time'] > 30 * 60) {

        unset($_SESSION['mobile_code']);

        exit(json_encode(array(

            'msg' => '验证码超过30分钟。'

        )));

    } else {

        if ($mobile != $_SESSION['mobile'] or $mobile_code != $_SESSION['mobile_code']) {

            exit(json_encode(array(

                'msg' => '手机验证码输入错误。'

            )));

        } else {

            exit(json_encode(array(

                'code' => '2'

            )));

        }

    }


}


if ($_GET['act'] == 'send') {


    /* 代码修改_start BY www.hongyuvip.com */

    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';

    $mobile_code = isset($_POST['mobile_code']) ? trim($_POST['mobile_code']) : '';

    /* 代码修改_end BY www.hongyuvip.com */


    //session_start();

    if (empty($mobile)) {

        exit(json_encode(array(

            'msg' => '手机号码不能为空'

        )));

    }


    $preg = '/^1[0-9]{10}$/'; // 简单的方法

    if (!preg_match($preg, $mobile)) {

        exit(json_encode(array(

            'msg' => '手机号码格式不正确'

        )));

    }


    $mobile_code = random(6, 1);

    // 短信数组
    $content = array($GLOBALS['_CFG']['sms_register_tpl'], "{\"number\":\"$mobile_code\"}",$GLOBALS['_CFG']['sms_sign']);

    if ($_SESSION['mobile']) {

        if (strtotime(read_file($mobile)) > (time() - 60)) {

            exit(json_encode(array(

                'msg' => '获取验证码太过频繁，一分钟之内只能获取一次。'

            )));

        }

    }


    $num = sendSMS($mobile, $content);

    if ($num == true) {

        $_SESSION['mobile'] = $mobile;

        $_SESSION['mobile_code'] = $mobile_code;

        $_SESSION['time'] = time();

        exit(json_encode(array(

            'code' => 2

        )));

    } else {

        exit(json_encode(array(

            'msg' => '手机验证码发送失败。'

        )));

    }

}

/* 鸿宇独家修复 hongyuvip.com QQ:1527200768 by:Shadow & 鸿宇 start */

function sendSMS($mobile_phone, $content)
{
    date_default_timezone_set('Asia/Shanghai');

    include "hy_config.php";
    include 'lib/api_sdk/aliyun-php-sdk-core/Config.php';
    include_once 'lib/api_sdk/Dysmsapi/Request/V20170525/SendSmsRequest.php';
    include_once 'lib/api_sdk/Dysmsapi/Request/V20170525/QuerySendDetailsRequest.php';

    //此处需要替换成自己的AK信息
    $accessKeyId = $hy_appkey;
    $accessKeySecret = $hy_secretkey;
    //短信API产品名
    $product = "Dysmsapi";
    //短信API产品域名
    $domain = "dysmsapi.aliyuncs.com";
    //暂时不支持多Region
    $region = "cn-hangzhou";

    //初始化访问的acsCleint
    $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
    DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);
    $acsClient = new DefaultAcsClient($profile);

    $request = new Dysmsapi\Request\V20170525\SendSmsRequest;
    //必填-短信接收号码
    $request->setPhoneNumbers($mobile_phone);
    //必填-短信签名
    $request->setSignName($content[2]);
    //必填-短信模板Code
    $request->setTemplateCode($content[0]);
    //选填-假如模板中存在变量需要替换则为必填(JSON格式)
    $request->setTemplateParam($content[1]);
    //选填-发送短信流水号
    //$request->setOutId("1234");

    //发起访问请求
    $acsResponse = $acsClient->getAcsResponse($request);

    if ($acsResponse->Code === 'OK') {
        return true;
    } else {
        if ($hy_showbug == true) {
            if (empty($acsResponse->Message)) {
                echo "短信验证码发送失败！请检查：\n鸿宇管理中心->短信管理->Access Key ID、Access Key Secret\n商店设置->短信设置->短信签名、对应的模板编号是否正确？";
            } else {
                echo "发送失败：【" . $acsResponse->Message . "】";
            }
            exit;
        }
        return false;
    }
}

/* 鸿宇科技修复 hongyuvip.com QQ:1527200768 by:Shadow & 鸿宇 end */

function checkSMS($mobile, $mobile_code)
{
    $arr = array(
        'error' => 0, 'msg' => ''
    );
    if (time() - $_SESSION['time'] > 30 * 60) {
        unset($_SESSION['mobile_code']);
        $arr['error'] = 1;
        $arr['msg'] = '验证码超过30分钟。';
    } else {
        if ($mobile != $_SESSION['mobile'] or $mobile_code != $_SESSION['mobile_code']) {
            $arr['error'] = 1;
            $arr['msg'] = '手机验证码输入错误。';
        } else {
            $arr['error'] = 2;
        }
    }
    return $arr;
}
?>
