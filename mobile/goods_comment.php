<?php

/**
 * 鸿宇多用户商城 生成验证码
 * ============================================================================
 * 版权所有 2015-2018 鸿宇科技有限公司，并保留所有权利。
 * 网站地址: http://www.hongyuvip.com；
 * ----------------------------------------------------------------------------
 * 仅供学习交流使用，如需商用请购买正版版权。鸿宇不承担任何法律责任。
 * 踏踏实实做事，堂堂正正做人。
 * ============================================================================
 * $Author: Shadow & 鸿宇
 * $Id: goods_comment.php 17217 2016-01-19 06:29:08Z Shadow & 鸿宇
*/

define('IN_ECS', true);

require_once(dirname(__FILE__) . '/includes/init.php');
include_once(dirname(__FILE__) . '/includes/cls_json.php');
include_once(dirname(__FILE__) . '/includes/lib_comment.php');



if ($_REQUEST['act'] == 'list_json')
{
	$goods_id = intval($_REQUEST['goods_id']);
	$type = intval($_REQUEST['type']);
	$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
	
    $json = new JSON;
    $result = array('error' => '', 'content' => '');
		
	$smarty->assign('comments',get_my_comments($goods_id,$type, $page));
	$result['content']     = $smarty->fetch('library/my_comments_list.lbi');
	
    echo $json->encode($result);
    exit;
}


if ($_REQUEST['act'] == 'good_json')
{
	$comment_id = intval($_REQUEST['comment_id']);
	
    $json = new JSON;
    $result = array('error' => '', 'good_num' => '', 'comment_id'=>$comment_id);

	if ($_SESSION['comments-'.$comment_id] == 1)
	{
		$result['error'] = 1;
	}
	else
	{
		$db->query("UPDATE ".$ecs->table('comment')." SET good_num = good_num + 1 WHERE comment_id = '$comment_id'");
		$result['good_num'] = $db->getOne("SELECT good_num FROM ".$ecs->table('comment')." WHERE comment_id = '$comment_id'");
		clear_cache_files();
		$_SESSION['comments-'.$comment_id] = 1;
	}


	
    echo $json->encode($result);
    exit;
}



if ($_REQUEST['act'] == 'view')
{
	$rec_id = intval($_REQUEST['id']);

	$cache_id = sprintf('%X', crc32($_REQUEST['id'] . 'goods_comment_view'));
	if (!$smarty->is_cached('goods_comment_view.dwt', $cache_id))
	{
		$comment = $db->getRow("SELECT * FROM ".$ecs->table('comment')." WHERE rec_id = '$rec_id'");
		$goods_id = $comment['id_value'];
		
		$res = $GLOBALS['db']->getAll("SELECT * FROM ".$GLOBALS['ecs']->table('goods_tag')." WHERE goods_id = '$goods_id'");	
		foreach ($res as $v)
		{
			$tags[$v['tag_id']] = $v['tag_name'];	
		}
		
		$comment['add_time_str'] = date("Y-m-d", $comment['add_time']);
		$comment['buy_time_str'] = date("Y-m-d", $comment['buy_time']);
		$comment['user_rank'] = get_user_rank($comment['user_id']);
		if ($comment['shaidan_id'] > 0)
		{
			$comment['shaidan_imgs'] = $GLOBALS['db']->getAll("SELECT * FROM ".$GLOBALS['ecs']->table('shaidan_img')." WHERE shaidan_id = '$comment[shaidan_id]'");	
		}
		if ($comment['comment_tag'])
		{
			$comment_tag = explode(',',$comment['comment_tag']);	
			foreach ($comment_tag as $tag_id)
			{
				$comment['tags'][] = $tags[$tag_id];
			}
		}
		$smarty->assign('comment',          $comment);
		
		/* 获得商品的信息 */
		$goods = get_goods_info($goods_id);
		if ($goods === false)
		{
			/* 如果没有找到任何记录则跳回到首页 */
			ecs_header("Location: ./\n");
			exit;
		}
		
        $catlist = array();
        foreach(get_parent_cats($goods['cat_id']) as $k=>$v)
        {
            $catlist[] = $v['cat_id'];
        }
        assign_template('c', $catlist);
		
        /* meta */
        $smarty->assign('keywords',           htmlspecialchars($goods['keywords']));
        $smarty->assign('description',        htmlspecialchars($goods['goods_brief']));
		
        $position = assign_ur_here($goods['cat_id'], $goods['goods_name']);
		$position['ur_here'] .= ' <code>&gt;</code> 商品评论';
		
        /* current position */
        $smarty->assign('page_title',          $position['title']);                    // 页面标题
        $smarty->assign('ur_here',             $position['ur_here']);                  // 当前位置
		
        $smarty->assign('goods',              $goods);
        $smarty->assign('goods_id',           $goods['goods_id']);
		$smarty->assign('categories',       get_categories_tree());  // 分类树
		$smarty->assign('helps',            get_shop_help()); // 网店帮助
		$smarty->assign('page_title',   $position['title']);    // 页面标题
		$smarty->assign('ur_here',      $position['ur_here']);  // 当前位置
		
		assign_dynamic('goods');
	}
	$smarty->display('goods_comment_view.dwt');
}
?>