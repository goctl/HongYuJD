<?php

/**
 * 店铺分类文件
 * ============================================================================
 * 版权所有 2015-2018 鸿宇科技有限公司，并保留所有权利。
 * 网站地址: http://www.hongyuvip.com；
 * ----------------------------------------------------------------------------
 * 仅供学习交流使用，如需商用请购买正版版权。鸿宇不承担任何法律责任。
 * 踏踏实实做事，堂堂正正做人。
 * ============================================================================
 * $Author: Shadow & 鸿宇
 * $Id: category.php 17217 2016-01-19 06:29:08Z Shadow & 鸿宇
*/


/* 获得请求的分类 ID */
if (isset($_REQUEST['cid']))
{
   // $cat_id = intval($_REQUEST['cid']);
}
else
{
    /* 如果分类ID为0，则返回首页 */
  //  ecs_header("Location: ./\n");

  //  exit;
}


/* 初始化分页信息 */
$cat_id = isset($_REQUEST['cid'])   && intval($_REQUEST['cid'])  > 0 ? intval($_REQUEST['cid'])  : 0;
$keywords = isset($_REQUEST['keywords']) && !empty($_REQUEST['keywords']) ? htmlspecialchars($_REQUEST['keywords']) : '';
$keywords = ($keywords == '请输入你要查找的商品') ? '' : $keywords;
$page = isset($_REQUEST['page'])   && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
$size = isset($_CFG['page_size'])  && intval($_CFG['page_size']) > 0 ? intval($_CFG['page_size']) : 10;
$display  = (isset($_REQUEST['display']) && in_array(trim(strtolower($_REQUEST['display'])), array('list', 'grid', 'text'))) ? trim($_REQUEST['display'])  : (isset($_COOKIE['ECS']['display']) ? $_COOKIE['ECS']['display'] : $default_display_type);
$display  = in_array($display, array('list', 'grid', 'text')) ? $display : 'text';
/*------------------------------------------------------ */
//-- PROCESSOR
/*------------------------------------------------------ */

/* 页面的缓存ID */
$cache_id = sprintf('%X', crc32($cat_id . '-' . $display . '-' . $sort  .'-' . $order  .'-' . $page . '-' . $size . '-' . $_SESSION['user_rank'] . '-' .
    $_CFG['lang'] .'-'. $brand. '-' . $price_max . '-' .$price_min . '-' . $filter_attr_str.'-'.$_GET['suppId']));

if (!$smarty->is_cached('category.dwt', $cache_id))
{
    assign_template();
    assign_template_supplier();
    $position = assign_ur_here();
    
   
    $smarty->assign('page_title',      $position['title']);    // 页面标题
    //$smarty->assign('ur_here',         $ur_here);  // 当前位置

    $smarty->assign('categories',      get_categories_tree_supplier()); // 分类树
    
    $children = get_cattype_supplier($cat_id,$keywords);
    if($children === false){
    	ecs_header("Location: supplier.php?suppId=".$_GET['suppId']);
    	exit;
    }
    
    $count = get_cagtegory_goods_count($children,$keywords);
    $max_page = ($count> 0) ? ceil($count / $size) : 1;
	if ($page > $max_page)
    {
        $page = $max_page;
    }
    $goodslist = category_get_goods($children, $size, $page,$keywords);

    if($display == 'grid')
    {
        if(count($goodslist) % 2 != 0)
        {
            $goodslist[] = array();
        }
    }
    assign_pager('supplier',            $cat_id, $count, $size, '', '', $page, $keywords, '', '', '', $display, ''); // 分页
    $smarty->assign('goods_list',       $goodslist);
    assign_dynamic('category');
}

$smarty->display('category.dwt', $cache_id);

/*------------------------------------------------------ */
//-- PRIVATE FUNCTION
/*------------------------------------------------------ */

/**
 * 获得指定商品属性所属的分类的ID
 *
 * @access  public
 * @param   integer     $cat        (1=>'精品推荐',2=>'新品上市',3=>'热卖商品')
 * @param	string		$keywords    关键字
 * @return  string
 */
function get_cattype_supplier($cat = 0,$keywords='')
{
	if(empty($keywords)){
		$where = "supplier_id=".$_GET['suppId'];
		if($cat > 0){
			$where .= " AND recommend_type=".$cat;
		}
		//$sql = "select cat_id  from ". $GLOBALS['ecs']->table('supplier_cat_recommend') ." where ".$where;
                $sql = "select cat_id  from ". $GLOBALS['ecs']->table('supplier_category') ." where ".$where;
		$res = $GLOBALS['db']->getAll($sql);
		$arr = array();
		if(count($res)>0){
			foreach($res as $k => $v){
				$arr[$v['cat_id']] = $v['cat_id'];
			}
		}
		if(empty($arr)){
			return false;
		}
	    return 'sgc.cat_id ' . db_create_in(array_keys($arr));
	}else{
		return "g.goods_name like '%".$keywords."%'";
	}
}

/**
 * 获得分类下的商品
 *
 * @access  public
 * @param   string  $children
 * @return  array
 */
function category_get_goods($children, $size, $page,$keywords='')
{
    $display = $GLOBALS['display'];
    $where = "g.is_on_sale = 1 AND g.is_alone_sale = 1 AND ".
            "g.is_delete = 0 AND g.is_virtual=0 AND ($children)";


    /* 获得商品列表 */
    if(empty($keywords)){
    	$where .= " AND sgc.supplier_id=".$_GET['suppId'];
    	$sql = 'SELECT DISTINCT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price, ' .
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price, g.goods_type, " .
                'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img ' .
    		'FROM ' . $GLOBALS['ecs']->table('supplier_goods_cat') . ' AS sgc ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
    			"ON sgc.goods_id = g.goods_id " .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' .
                "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
            "WHERE $where ORDER BY g.goods_id desc";
    }else{
    	$where .= " AND g.supplier_id=".$_GET['suppId'];
    	$sql = 'SELECT DISTINCT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.is_new, g.is_best, g.is_hot, g.shop_price AS org_price, ' .
                "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price, g.goods_type, " .
                'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb , g.goods_img ' .
    		'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' .
                "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
            "WHERE $where ORDER BY g.goods_id desc";
    }

    $res = $GLOBALS['db']->selectLimit($sql, $size, ($page - 1) * $size);

    $arr = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        if ($row['promote_price'] > 0)
        {
            $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
        }
        else
        {
            $promote_price = 0;
        }

        /* 处理商品水印图片 */
        $watermark_img = '';

        if ($promote_price != 0)
        {
            $watermark_img = "watermark_promote_small";
        }
        elseif ($row['is_new'] != 0)
        {
            $watermark_img = "watermark_new_small";
        }
        elseif ($row['is_best'] != 0)
        {
            $watermark_img = "watermark_best_small";
        }
        elseif ($row['is_hot'] != 0)
        {
            $watermark_img = 'watermark_hot_small';
        }

        if ($watermark_img != '')
        {
            $arr[$row['goods_id']]['watermark_img'] =  $watermark_img;
        }

        $arr[$row['goods_id']]['goods_id']         = $row['goods_id'];
        if($display == 'grid')
        {
            $arr[$row['goods_id']]['goods_name']       = $GLOBALS['_CFG']['goods_name_length'] > 0 ? sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
        }
        else
        {
            $arr[$row['goods_id']]['goods_name']       = $row['goods_name'];
        }
        $arr[$row['goods_id']]['name']             = $row['goods_name'];
        $arr[$row['goods_id']]['goods_brief']      = $row['goods_brief'];
        $arr[$row['goods_id']]['goods_style_name'] = add_style($row['goods_name'],$row['goods_name_style']);
        $arr[$row['goods_id']]['market_price']     = price_format($row['market_price']);
        $arr[$row['goods_id']]['shop_price']       = price_format($row['shop_price']);
        $arr[$row['goods_id']]['type']             = $row['goods_type'];
        $arr[$row['goods_id']]['promote_price']    = ($promote_price > 0) ? price_format($promote_price) : '';
        $arr[$row['goods_id']]['goods_thumb']      =  '../'.get_image_path($row['goods_id'], $row['goods_thumb'], true);
        $arr[$row['goods_id']]['goods_img']        =  '../'.get_image_path($row['goods_id'], $row['goods_img']);
        $arr[$row['goods_id']]['url']              = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);
    }

    return $arr;
}

/**
 * 获得分类下的商品总数
 *
 * @access  public
 * @param   string     $children  
 * @param   string		$keywords   商品名称查找
 * @return  integer
 */
function get_cagtegory_goods_count($children,$keywords='')
{
   

    /* 返回商品总数 */
    if(empty($keywords)){
    	 $where  = "sgc.supplier_id=".$_GET['suppId']." AND $children";
    	$sql = 'SELECT count(DISTINCT g.goods_id) FROM ' . $GLOBALS['ecs']->table('supplier_goods_cat') . ' AS sgc LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
    			'ON sgc.goods_id = g.goods_id AND g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_virtual=0 WHERE '.$where;
    }else{
    	$where  = "g.supplier_id=".$_GET['suppId']." AND $children";
    	$sql = 'SELECT count(DISTINCT g.goods_id) FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
    			'WHERE g.is_on_sale = 1 AND g.is_alone_sale = 1 AND g.is_delete = 0 AND g.is_virtual=0 AND '.$where;
    }
    return $GLOBALS['db']->getOne($sql);
}

?>
