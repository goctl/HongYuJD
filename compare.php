<?php

/**
 * 鸿宇多用户商城 商品比较程序
 * ============================================================================
 * 版权所有 2015-2018 鸿宇科技有限公司，并保留所有权利。
 * 网站地址: http://www.hongyuvip.com；
 * ----------------------------------------------------------------------------
 * 仅供学习交流使用，如需商用请购买正版版权。鸿宇不承担任何法律责任。
 * 踏踏实实做事，堂堂正正做人。
 * ============================================================================
 * $Author: Shadow & 鸿宇
 * $Id: compare.php 17217 2016-01-19 06:29:08Z Shadow & 鸿宇
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
if (!empty($_REQUEST['goods']) && is_array($_REQUEST['goods']) && count($_REQUEST['goods']) > 1)
{
    $where = db_create_in($_REQUEST['goods'], 'id_value');
    $sql = "SELECT id_value , AVG(comment_rank) AS cmt_rank, COUNT(*) AS cmt_count" .
           " FROM " .$ecs->table('comment') .
           " WHERE $where AND comment_type = 0".
           ' GROUP BY id_value ';
    $query = $db->query($sql);
    $cmt = array();
    while ($row = $db->fetch_array($query))
    {
        $cmt[$row['id_value']] = $row;
    }

    $where = db_create_in($_REQUEST['goods'], 'g.goods_id');
    $sql = "SELECT g.goods_id, g.goods_type, g.goods_name, g.shop_price, g.goods_weight, g.goods_thumb, g.goods_brief, ".
                "a.attr_name, v.attr_value, a.attr_id, b.brand_name, ".
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS rank_price " .
            "FROM " .$ecs->table('goods'). " AS g ".
            "LEFT JOIN " . $ecs->table('goods_attr'). " AS v ON v.goods_id = g.goods_id ".
            "LEFT JOIN " . $ecs->table('attribute') . " AS a ON a.attr_id = v.attr_id " .
            "LEFT JOIN " . $ecs->table('brand') . " AS b ON g.brand_id = b.brand_id " .
            "LEFT JOIN " . $ecs->table('member_price') . " AS mp ".
                    "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' ".
            "WHERE g.is_delete = 0 AND $where ".
            "ORDER BY a.attr_id";
    $res = $db->query($sql);
    $arr = array();
    $ids = $_REQUEST['goods'];
    $attr_name = array();
    $type_id = 0;
    while ($row = $db->fetchRow($res))
    {
        $goods_id = $row['goods_id'];
        $type_id = $row['goods_type'];
        $arr[$goods_id]['goods_id']     = $goods_id;
        $arr[$goods_id]['url']          = build_uri('goods', array('gid' => $goods_id), $row['goods_name']);
        $arr[$goods_id]['goods_name']   = $row['goods_name'];
        $arr[$goods_id]['shop_price']   = price_format($row['shop_price']);
        $arr[$goods_id]['rank_price']   = price_format($row['rank_price']);
        $arr[$goods_id]['goods_weight'] = (intval($row['goods_weight']) > 0) ?
        ceil($row['goods_weight']) . $_LANG['kilogram'] : ceil($row['goods_weight'] * 1000) . $_LANG['gram'];
        $arr[$goods_id]['goods_thumb']  = get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr[$goods_id]['goods_brief']  = $row['goods_brief'];
        $arr[$goods_id]['brand_name']   = $row['brand_name'];
        $properties = get_goods_properties($goods_id);
        $arr[$goods_id]['spe'] = $properties['spe']; 
        $arr[$goods_id]['properties'][$row['attr_id']]['name']  = $row['attr_name'];
        if (!empty($arr[$goods_id]['properties'][$row['attr_id']]['value']))
        {
            $arr[$goods_id]['properties'][$row['attr_id']]['value'] .= ',' . $row['attr_value'];
        }
        else
        {
            $arr[$goods_id]['properties'][$row['attr_id']]['value'] = $row['attr_value'];
        }

        if (!isset($arr[$goods_id]['comment_rank']))
        {
            $arr[$goods_id]['comment_rank']   = isset($cmt[$goods_id]) ? ceil($cmt[$goods_id]['cmt_rank']) : 0;
            $arr[$goods_id]['comment_number'] = isset($cmt[$goods_id]) ? $cmt[$goods_id]['cmt_count']      : 0;
            $arr[$goods_id]['comment_number'] = sprintf($_LANG['comment_num'], $arr[$goods_id]['comment_number']);
        }

        $tmp = $ids;
        $key = array_search($goods_id, $tmp);

        if ($key !== null && $key !== false)
        {
            unset($tmp[$key]);
        }

        $arr[$goods_id]['ids'] = !empty($tmp) ? "goods[]=" . implode('&amp;goods[]=', $tmp) : '';
       
    }
	
	/* 代码修改_start  By www.hongyuvip.com */
	$smarty->assign('back_url',   empty($_GET['back_url']) ? $_SERVER['HTTP_REFERER'] : $_GET['back_url']);
    $sql = "SELECT attr_id,attr_name,attr_group FROM " . $ecs->table('attribute') . " WHERE cat_id='$type_id' ORDER BY attr_id";
	
    $attribute = array();

    $attribute = $db->getAll($sql);
	$sql = 'select attr_group from ' . $ecs->table('goods_type') . ' where cat_id=' . $type_id;
	$attr_group = $db->getOne($sql);
	$group_list = explode("\n", str_replace("\r", '', $attr_group));
	
	$attr_list = array();
	foreach($group_list as $key => $group)
	{
		$attr_list[$key]['group']      = $key;
		$attr_list[$key]['group_name'] = $group;
	}
	foreach($attribute as $attr)
	{
		if(isset($attr_list[$attr['attr_group']]))
			$attr_list[$attr['attr_group']]['attribute'][] = $attr;
	}
	foreach($arr as $key => $goods)
	{
		foreach($attribute as $attr)
		{
			if(!isset($goods['properties'][$attr['attr_id']]))
				$arr[$key]['properties'][$attr['attr_id']] = array('value' => '-');
		}
	}
	for($i = count($arr); $i<4; $i++) 
		$arr[] = array();
    $smarty->assign('attr_list', $attr_list);
	/* 代码修改_end  By www.hongyuvip.com */
    $smarty->assign('goods_list', $arr);
}
else
{
    show_message($_LANG['compare_no_goods']);
    exit;
}

assign_template();
$position = assign_ur_here(0, $_LANG['goods_compare']);
$smarty->assign('page_title', $position['title']);    // 页面标题
$smarty->assign('ur_here',    $position['ur_here']);  // 当前位置

$smarty->assign('categories', get_categories_tree()); // 分类树
$smarty->assign('helps',      get_shop_help());       // 网店帮助

$url = $_SERVER['REQUEST_URI'];
$smarty->assign('url',      $url);

assign_dynamic('compare');

$smarty->display('compare.dwt');

?>