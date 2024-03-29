<?php

/**
 * 鸿宇多用户商城 广告处理文件
 * ============================================================================
 * 版权所有 2015-2018 鸿宇科技有限公司，并保留所有权利。
 * 网站地址: http://www.hongyuvip.com；
 * ----------------------------------------------------------------------------
 * 仅供学习交流使用，如需商用请购买正版版权。鸿宇不承担任何法律责任。
 * 踏踏实实做事，堂堂正正做人。
 * ============================================================================
 * $Author: Shadow & 鸿宇
 * $Id: affiche.php 17217 2016-01-19 06:29:08Z Shadow & 鸿宇
*/

define('IN_ECS', true);
define('INIT_NO_SMARTY', true);
require(dirname(__FILE__) . '/includes/init.php');

/* 是否指定广告的id及跳转地址 */

// safety_20150629 change_start

if (!empty($_GET['ad_id']) && preg_match('/^-?[1-9]\d*$/', $_REQUEST['ad_id']))
{
	$ad_id = intval($_GET['ad_id']);
}
else
{
    ecs_header("Location: index.php\n");
    exit;
}

// safety_20150629 change_end

/* act 操作项的初始化*/
$_GET['act'] = !empty($_GET['act']) ? trim($_GET['act']) : '';

if ($_GET['act'] == 'js')
{
    /* 编码转换 */
    if (empty($_GET['charset']))
    {
        $_GET['charset'] = 'UTF8';
    }

    header('Content-type: application/x-javascript; charset=' . ($_GET['charset'] == 'UTF8' ? 'utf-8' : $_GET['charset']));

    $url = $ecs->url();
    $str = "";

    /* 取得广告的信息 */
    $sql = 'SELECT ad.ad_id, ad.ad_name, ad.ad_link, ad.ad_code '.
           'FROM ' . $ecs->table('ad') . ' AS ad ' .
           'LEFT JOIN ' . $ecs->table('ad_position') . ' AS p ON ad.position_id = p.position_id '.
           "WHERE ad.ad_id = '$ad_id' and " . gmtime() . " >= ad.start_time and " . gmtime() . "<= ad.end_time";

    $ad_info = $db->getRow($sql);

    if (!empty($ad_info))
    {
        /* 转换编码 */
        if ($_GET['charset'] != 'UTF8')
        {
            $ad_info['ad_name'] = ecs_iconv('UTF8', $_GET['charset'], $ad_info['ad_name']);
            $ad_info['ad_code'] = ecs_iconv('UTF8', $_GET['charset'], $ad_info['ad_code']);
        }

        /* 初始化广告的类型和来源 */
        $_GET['type'] = !empty($_GET['type']) ? intval($_GET['type'])    : 0;
        $_GET['from'] = !empty($_GET['from']) ? urlencode($_GET['from']) : '';

        $str = '';
        switch ($_GET['type'])
        {
            case '0':
                /* 图片广告 */
                $src = (strpos($ad_info['ad_code'], 'http://') === false && strpos($ad_info['ad_code'], 'https://') === false) ? $url . DATA_DIR . "/afficheimg/$ad_info[ad_code]" : $ad_info['ad_code'];
                $str = '<a href="' .$url. 'affiche.php?ad_id=' .$ad_info['ad_id']. '&from=' .$_GET['from']. '&uri=' .urlencode($ad_info['ad_link']). '" target="_blank">' .
                        '<img src="' . $src . '" border="0" alt="' . $ad_info['ad_name'] . '" /></a>';
                break;

            case '1':
                /* Falsh广告 */
                $src = (strpos($ad_info['ad_code'], 'http://') === false && strpos($ad_info['ad_code'], 'https://') === false) ? $url . DATA_DIR . '/afficheimg/' . $ad_info['ad_code'] : $ad_info['ad_code'];
                $str = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0"> <param name="movie" value="'.$src.'"><param name="quality" value="high"><embed src="'.$src.'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed></object>';
                break;

            case '2':
                /* 代码广告 */
                $str = $ad_info['ad_code'];
                break;

            case 3:
                /* 文字广告 */
                $str = '<a href="' .$url. 'affiche.php?ad_id=' .$ad_info['ad_id']. '&from=' .$_GET['from']. '&uri=' .urlencode($ad_info['ad_link']). '" target="_blank">' . nl2br(htmlspecialchars(addslashes($ad_info['ad_code']))). '</a>';
                break;
        }
    }
    echo "document.writeln('$str');";
}
else
{
    /* 获取投放站点的名称 */
    $site_name = !empty($_GET['from']) ? addslashes($_GET['from']) : addslashes($_LANG['self_site']);

    /* 商品的ID */
    $goods_id = !empty($_GET['goods_id']) ? intval($_GET['goods_id']) : 0;

    /* 存入SESSION中,购物后一起存到订单数据表里 */
    $_SESSION['from_ad'] = $ad_id;
    $_SESSION['referer'] = stripslashes($site_name);

    /* 如果是商品的站外JS */
    if ($ad_id == '-1')
    {
        $sql = "SELECT count(*) FROM " . $ecs->table('adsense') . " WHERE from_ad = '-1' AND referer = '" . $site_name . "'";
        if($db->getOne($sql) > 0)
        {
            $sql = "UPDATE " . $ecs->table('adsense') . " SET clicks = clicks + 1 WHERE from_ad = '-1' AND referer = '" . $site_name . "'";
        }
        else
        {
            $sql = "INSERT INTO " . $ecs->table('adsense') . "(from_ad, referer, clicks) VALUES ('-1', '" . $site_name . "', '1')";
        }
        $db->query($sql);
        //$db->autoReplace($ecs->table('adsense'), array('from_ad' => -1, 'referer' => $site_name, 'clicks' => 1), array('clicks' => 1));
        $sql = "SELECT goods_name FROM " .$ecs->table('goods'). " WHERE goods_id = $goods_id";
        $res = $db->query($sql);

        $row = $db->fetchRow($res);

        $uri = build_uri('goods', array('gid' => $goods_id), $row['goods_name']);

        ecs_header("Location: $uri\n");

        exit;
    }
    else
    {
        /* 更新站内广告的点击次数 */
        $db->query('UPDATE ' . $ecs->table('ad') . " SET click_count = click_count + 1 WHERE ad_id = '$ad_id'");

        $sql = "SELECT count(*) FROM " . $ecs->table('adsense') . " WHERE from_ad = '" . $ad_id . "' AND referer = '" . $site_name . "'";
        if($db->getOne($sql) > 0)
        {
            $sql = "UPDATE " . $ecs->table('adsense') . " SET clicks = clicks + 1 WHERE from_ad = '" . $ad_id . "' AND referer = '" . $site_name . "'";
        }
        else
        {
            $sql = "INSERT INTO " . $ecs->table('adsense') . "(from_ad, referer, clicks) VALUES ('" . $ad_id . "', '" . $site_name . "', '1')";
        }
        $db->query($sql);

        /* 跳转到广告的链接页面 */
        if (!empty($_GET['uri']))
        {
            $uri = (strpos($_GET['uri'], 'http://') === false && strpos($_GET['uri'], 'https://') === false) ? $ecs->http() . urldecode($_GET['uri']) : urldecode($_GET['uri']);
        }
        else
        {
            $uri = $ecs->url();
        }

        ecs_header("Location: $uri\n");
        exit;
    }
}

?>