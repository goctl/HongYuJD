<?php

/**
 * 鸿宇多用户商城 商品分类管理程序
 * ============================================================================
 * 版权所有 2005-2010 鸿宇多用户商城科技有限公司，并保留所有权利。
 * 网站地址: http://www.hongyuvip.com；
 * ----------------------------------------------------------------------------
 * 仅供学习交流使用，如需商用请购买正版版权。鸿宇不承担任何法律责任。
 * 踏踏实实做事，堂堂正正做人。
 * ============================================================================
 * $Author: liuhui $
 * $Id: category.php 17063 2010-03-25 06:35:46Z liuhui $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
$exc = new exchange($ecs->table("category"), $db, 'cat_id', 'cat_name');

/* act操作项的初始化 */
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}


/* 代码增加_start  By jdy */
include_once(ROOT_PATH . 'includes/cls_image.php');
$image = new cls_image($_CFG['bgcolor']);
$catimg_dir="category_img";
$smarty->assign('data_dir',    '/'.DATA_DIR. '/');
if($_REQUEST['cat_id']){
	$parent_id=$GLOBALS['db']->getOne("select parent_id from ". $GLOBALS['ecs']->table('category') ." where cat_id='$_REQUEST[cat_id]' ");
	$is_topcat= $parent_id==0 ? '1' : '0';
	$smarty->assign('is_topcat',    $is_topcat);
}
if ($_REQUEST['act'] == 'drop_adimg_1')
{
	$cat_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    /* 取得 adimg 名称 */
    $sql = "SELECT cat_adimg_1 FROM " .$ecs->table('category'). " WHERE cat_id = '$cat_id'";
    $adimg_name = $db->getOne($sql);

    if (!empty($adimg_name))
    {
        @unlink(ROOT_PATH . DATA_DIR . '/'. $catimg_dir .'/' .$adimg_name);
        $sql = "UPDATE " .$ecs->table('category'). " SET cat_adimg_1 = '' WHERE cat_id = '$cat_id'";
        $db->query($sql);
    }
    $link= array(array('text' => '返回商品分类编辑页', 'href' => 'category.php?act=edit&cat_id=' . $cat_id), array('text' => '返回商品分类列表页', 'href' => 'category.php?act=list'));
    sys_msg('成功删除子分类广告图1', 0, $link);
}
elseif ($_REQUEST['act'] == 'drop_adimg_2')
{
	$cat_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    /* 取得 adimg 名称 */
    $sql = "SELECT cat_adimg_2 FROM " .$ecs->table('category'). " WHERE cat_id = '$cat_id'";
    $adimg_name = $db->getOne($sql);

    if (!empty($adimg_name))
    {
        @unlink(ROOT_PATH . DATA_DIR . '/'. $catimg_dir .'/' .$adimg_name);
        $sql = "UPDATE " .$ecs->table('category'). " SET cat_adimg_2 = '' WHERE cat_id = '$cat_id'";
        $db->query($sql);
    }
    $link= array(array('text' => '返回商品分类编辑页', 'href' => 'category.php?act=edit&cat_id=' . $cat_id), array('text' => '返回商品分类列表页', 'href' => 'category.php?act=list'));
    sys_msg('成功删除子分类广告图2', 0, $link);
}
/* 代码增加_end  By jdy */

/*------------------------------------------------------ */
//-- 商品分类列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    /* 获取分类列表 */
    $cat_list = cat_list(0, 0, false);
    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['04_category_list']);
    $smarty->assign('action_link',  array('href' => 'category.php?act=add', 'text' => $_LANG['04_category_add']));
    $smarty->assign('full_page',    1);

    $smarty->assign('cat_info',     $cat_list);

    /* 列表页面 */
    assign_query_info();
    $smarty->display('category_list.htm');
}

if ($_REQUEST['act'] == 'virtual_list')
{
    /* 获取分类列表 */
    $cat_list = cat_list1(0, 0, false);

    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['virtual_category_list']);
    $smarty->assign('action_link',  array('href' => 'category.php?act=add_virtual', 'text' => $_LANG['04_category_add']));
    $smarty->assign('full_page',    1);

    $smarty->assign('cat_info',     $cat_list);

    /* 列表页面 */
    assign_query_info();
    $smarty->display('category_virtual_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
        $is_virtual = empty($_REQUEST['is_virtual'])? 0 : intval($_REQUEST['is_virtual']);
	if(empty($_POST['cat_name']))
	{
		/* 获取分类列表 */
		$cat_list = $is_virtual ? cat_list1(0, 0, false) : cat_list(0, 0, false);
	}
	// 如果查询条件不为空
	else {
		$cat_list = search_cat($_POST['cat_name']);
	}
	
    $smarty->assign('cat_info',     $cat_list);

    $is_virtual ? make_json_result($smarty->fetch('category_virtual_list.htm')): make_json_result($smarty->fetch('category_list.htm'));
}
/*------------------------------------------------------ */
//-- 添加商品分类
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'add')
{
    /* 权限检查 */
    admin_priv('cat_manage');



    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['04_category_add']);
    $smarty->assign('action_link',  array('href' => 'category.php?act=list', 'text' => $_LANG['04_category_list']));

    $smarty->assign('goods_type_list',  goods_type_list(0)); // 取得商品类型
    $smarty->assign('attr_list',        get_attr_list()); // 取得商品属性
    $smarty->assign('is_virtual',        0); //  分类属性
    $smarty->assign('cat_select',   cat_list(0, 0, true));
    $smarty->assign('form_act',     'insert');
    $smarty->assign('cat_info',     array('is_show' => 1));



    /* 显示页面 */
    assign_query_info();
    $smarty->display('category_info.htm');
}


if ($_REQUEST['act'] == 'add_virtual')
{
    /* 权限检查 */
    admin_priv('cat_manage');



    /* 模板赋值 */
    $smarty->assign('ur_here',     '添加虚拟商品分类');
    $smarty->assign('action_link',  array('href' => 'category.php?act=virtual_list', 'text' => $_LANG['04_category_list']));

    $smarty->assign('goods_type_list',  goods_type_list(0)); // 取得商品类型
    $smarty->assign('attr_list',        get_attr_list()); // 取得商品属性
    $smarty->assign('cat_select',   get_virtual_cat_select());
    $smarty->assign('is_virtual',        1); //  分类属性
    $smarty->assign('form_act',     'insert');
    $smarty->assign('cat_info',     array('is_show' => 1));



    /* 显示页面 */
    assign_query_info();
    $smarty->display('category_virtual_info.htm');
}
/*------------------------------------------------------ */
//-- 商品分类添加时的处理
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'insert')
{
    /* 权限检查 */
    admin_priv('cat_manage');
 
 /* 初始化变量 */
 /* 代码增加_start By szy */   
 $cat['is_virtual']       = !empty($_POST['is_virtual'])  ? intval($_POST['is_virtual'])     : 0;
 /* 代码增加_end By szy */       
 $cat['cat_id']       = !empty($_POST['cat_id'])       ? intval($_POST['cat_id'])     : 0;
 $cat['parent_id']    = !empty($_POST['parent_id'])    ? intval($_POST['parent_id'])  : 0;
 $cat['sort_order']   = !empty($_POST['sort_order'])   ? intval($_POST['sort_order']) : 0;
 $cat['keywords']     = !empty($_POST['keywords'])     ? trim($_POST['keywords'])     : '';
 $cat['cat_desc']     = !empty($_POST['cat_desc'])     ? $_POST['cat_desc']           : '';
 $cat['measure_unit'] = !empty($_POST['measure_unit']) ? trim($_POST['measure_unit']) : '';
 $cat['show_in_nav']  = !empty($_POST['show_in_nav'])  ? intval($_POST['show_in_nav']): 0;
 $cat['style']        = !empty($_POST['style'])        ? trim($_POST['style'])        : '';
 $cat['is_show']      = !empty($_POST['is_show'])      ? intval($_POST['is_show'])    : 0;
 $cat['grade']        = !empty($_POST['grade'])        ? intval($_POST['grade'])      : 0;
 $cat['filter_attr']  = !empty($_POST['filter_attr'])  ? implode(',', array_unique(array_diff($_POST['filter_attr'],array(0)))) : 0;
 /* 代码增加_start Byjdy */
	$cat['category_index']       =  !empty($_POST['category_index'])  ? $_POST['category_index'] : '0';
	$cat['category_index_dwt'] = !empty($_POST['category_index_dwt'])  ? $_POST['category_index_dwt'] : '0';
	$cat['index_dwt_file']     = !empty($_POST['index_dwt_file'])  ? $_POST['index_dwt_file'] : '';
	$cat['show_in_index']       =  !empty($_POST['show_in_index'])  ? $_POST['show_in_index'] : '0';
	$cat['cat_nameimg']        =   basename($image->upload_image($_FILES['cat_nameimg'], $catimg_dir ));
	$cat['cat_nameimg']		= $cat['cat_nameimg'] ?  $catimg_dir. '/' . $cat['cat_nameimg']  : '';
	$cat['cat_adimg_1']        =   basename($image->upload_image($_FILES['cat_adimg_1'], $catimg_dir));
	$cat['cat_adimg_1']		=	 $cat['cat_adimg_1']  ?   $catimg_dir. '/' . $cat['cat_adimg_1']  :  '';
	$cat['cat_adurl_1']        = !empty($_POST['cat_adurl_1']) ?  trim($_POST['cat_adurl_1'])      : '';
	$cat['cat_adimg_2']        =   basename($image->upload_image($_FILES['cat_adimg_2'], $catimg_dir));
	$cat['cat_adimg_2']		=	 $cat['cat_adimg_2']  ?   $catimg_dir. '/' . $cat['cat_adimg_2']  :  '';
	$cat['cat_adurl_2']        = !empty($_POST['cat_adurl_2']) ?  trim($_POST['cat_adurl_2'])      : '';
	$cat['cat_index_rightad']        =   basename($image->upload_image($_FILES['cat_index_rightad'], $catimg_dir));
	$cat['cat_index_rightad']		=	 $cat['cat_index_rightad']  ?   $catimg_dir. '/' . $cat['cat_index_rightad']  :  '';
	/* 代码增加_end Byjdy */

   $cat['cat_recommend']  = !empty($_POST['cat_recommend'])  ? $_POST['cat_recommend'] : array();$cat['filter_attr']  = !empty($_POST['filter_attr'])  ? implode(',', array_unique(array_diff($_POST['filter_attr'],array(0)))) : 0;
 
   $cat['cat_name']     = !empty($_POST['cat_name'])     ? trim($_POST['cat_name'])     : '';
   $arrCatName = explode("," ,$cat['cat_name']);
  /*  代码增加_start By www.hongyuvip.com */
   $cat['brand_qq']  = !empty($_POST['brand_wwwecshop68com']) ? $_POST['brand_wwwecshop68com'] : '';
   $cat['attr_wwwecshop68com']  = !empty($_POST['attr_qq']) ? $_POST['attr_qq'] : '';
   
	/* 代码增加_start  By   www.hongyuvip.com  */
	$cat['path_name']     = !empty($_POST['path_name'])     ? trim($_POST['path_name'])     : '';
	if($cat['path_name'] != '')
	{
		$is_have = $db->getOne("select cat_id  from ". $ecs->table('category') ." where path_name='$cat[path_name]' "); 
		if ($is_have)
		{
		   $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		   sys_msg('对不起，已经存在同名目录', 0, $link);
		}
	}
	/* 代码增加_end  By   www.hongyuvip.com  */
   
 /*  代码增加_end By www.hongyuvip.com */
 foreach($arrCatName as $arrCatNameValue)
 {
  $cat['cat_name'] = $arrCatNameValue;

  if (cat_exists($cat['cat_name'], $cat['parent_id']))
  {
   /* 同级别下不能有重复的分类名称 */
     $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
     sys_msg($_LANG['catname_exist'], 0, $link);
  }

  if($cat['grade'] > 10 || $cat['grade'] < 0)
  {
   /* 价格区间数超过范围 */
     $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
     sys_msg($_LANG['grade_error'], 0, $link);
  }

  /* 入库的操作 */
  if ($db->autoExecute($ecs->table('category'), $cat) !== false)
  {
   $cat_id = $db->insert_id();
   if($cat['show_in_nav'] == 1)
   {
    $vieworder = $db->getOne("SELECT max(vieworder) FROM ". $ecs->table('nav') . " WHERE type = 'middle'");
    $vieworder += 2;
    //显示在自定义导航栏中
    $sql = "INSERT INTO " . $ecs->table('nav') .
     " (name,ctype,cid,ifshow,vieworder,opennew,url,type)".
     " VALUES('" . $cat['cat_name'] . "', 'c', '".$db->insert_id()."','1','$vieworder','0', '" . build_uri('category', array('cid'=> $cat_id), $cat['cat_name']) . "','middle')";
    $db->query($sql);
   }
   insert_cat_recommend($cat['cat_recommend'], $cat_id);
  }
 }
 
 admin_log($_POST['cat_name'], 'add', 'category');   // 记录管理员操作
 clear_cache_files();    // 清除缓存

 /*添加链接*/
 $link[0]['text'] = $_LANG['continue_add'];
 $link[0]['href'] = $cat['is_virtual']? 'category.php?act=add_virtual':'category.php?act=add';

 $link[1]['text'] = $_LANG['back_list'];
 $link[1]['href'] = $cat['is_virtual']? 'category.php?act=virtual_list': 'category.php?act=list';

 sys_msg($_LANG['catadd_succed'], 0, $link);

}


/*------------------------------------------------------ */
//-- 编辑商品分类信息
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'edit')
{
    admin_priv('cat_manage');   // 权限检查
    $cat_id = intval($_REQUEST['cat_id']);
    $cat_info = get_cat_info($cat_id);  // 查询分类信息数据
    $attr_list = get_attr_list();
    $filter_attr_list = array();

    if ($cat_info['filter_attr'])
    {
        $filter_attr = explode(",", $cat_info['filter_attr']);  //把多个筛选属性放到数组中

        foreach ($filter_attr AS $k => $v)
        {
            $attr_cat_id = $db->getOne("SELECT cat_id FROM " . $ecs->table('attribute') . " WHERE attr_id = '" . intval($v) . "'");
            $filter_attr_list[$k]['goods_type_list'] = goods_type_list($attr_cat_id);  //取得每个属性的商品类型
            $filter_attr_list[$k]['filter_attr'] = $v;
            $attr_option = array();
            
            if(!empty($attr_list)){
	            foreach ($attr_list[$attr_cat_id] as $val)
	            {
	                $attr_option[key($val)] = current ($val);
	            }
            }

            $filter_attr_list[$k]['option'] = $attr_option;
        }

        $smarty->assign('filter_attr_list', $filter_attr_list);
    }
    else
    {
        $attr_cat_id = 0;
    }

    /* 模板赋值 */
    $smarty->assign('is_virtual' , $cat_info['is_virtual']); //分类类型
    $smarty->assign('attr_list',        $attr_list); // 取得商品属性
    $smarty->assign('attr_cat_id',      $attr_cat_id);
    $smarty->assign('ur_here',     $_LANG['category_edit']);
    $smarty->assign('action_link', array('text' => $_LANG['04_category_list'], 'href' => $cat_info['is_virtual']?'category.php?act=virtual_list':'category.php?act=list'));

    //分类是否存在首页推荐
    $res = $db->getAll("SELECT recommend_type FROM " . $ecs->table("cat_recommend") . " WHERE cat_id=" . $cat_id);
    if (!empty($res))
    {
        $cat_recommend = array();
        foreach($res as $data)
        {
            $cat_recommend[$data['recommend_type']] = 1;
        }
        $smarty->assign('cat_recommend', $cat_recommend);
    }

    $smarty->assign('cat_info',    $cat_info);
    $smarty->assign('form_act',    'update');
    $smarty->assign('cat_select',  $cat_info['is_virtual']?get_virtual_cat_select():cat_list(0, $cat_info['parent_id'], true));
    $smarty->assign('goods_type_list',  goods_type_list(0)); // 取得商品类型

    /* 显示页面 */
    assign_query_info();
        //如果是虚拟商品则用虚拟分类模板
    $cat_info['is_virtual']?$smarty->display('category_virtual_info.htm'):$smarty->display('category_info.htm');
}


elseif($_REQUEST['act'] == 'add_category')
{
    $parent_id = empty($_REQUEST['parent_id']) ? 0 : intval($_REQUEST['parent_id']);
    $category = empty($_REQUEST['cat']) ? '' : json_str_iconv(trim($_REQUEST['cat']));

    if(cat_exists($category, $parent_id))
    {
        make_json_error($_LANG['catname_exist']);
    }
    else
    {
        $sql = "INSERT INTO " . $ecs->table('category') . "(cat_name, parent_id, is_show)" .
               "VALUES ( '$category', '$parent_id', 1)";

        $db->query($sql);
        $category_id = $db->insert_id();

        $arr = array("parent_id"=>$parent_id, "id"=>$category_id, "cat"=>$category);

        clear_cache_files();    // 清除缓存

        make_json_result($arr);
    }
}

/*------------------------------------------------------ */
//-- 编辑商品分类信息
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'update')
{
    /* 权限检查 */
    admin_priv('cat_manage');

    /* 初始化变量 */
    $is_virtual = !empty($_POST['is_virtual']) ? intval($_POST['is_virtual'])     : 0;
    $cat_id              = !empty($_POST['cat_id'])       ? intval($_POST['cat_id'])     : 0;
    $old_cat_name        = $_POST['old_cat_name'];
    $cat['parent_id']    = !empty($_POST['parent_id'])    ? intval($_POST['parent_id'])  : 0;
    $cat['sort_order']   = !empty($_POST['sort_order'])   ? intval($_POST['sort_order']) : 0;
    $cat['keywords']     = !empty($_POST['keywords'])     ? trim($_POST['keywords'])     : '';
    $cat['cat_desc']     = !empty($_POST['cat_desc'])     ? $_POST['cat_desc']           : '';
    $cat['measure_unit'] = !empty($_POST['measure_unit']) ? trim($_POST['measure_unit']) : '';
    $cat['cat_name']     = !empty($_POST['cat_name'])     ? trim($_POST['cat_name'])     : '';
    $cat['is_show']      = !empty($_POST['is_show'])      ? intval($_POST['is_show'])    : 0;
    $cat['show_in_nav']  = !empty($_POST['show_in_nav'])  ? intval($_POST['show_in_nav']): 0;
    $cat['style']        = !empty($_POST['style'])        ? trim($_POST['style'])        : '';
    $cat['grade']        = !empty($_POST['grade'])        ? intval($_POST['grade'])      : 0;
    $cat['filter_attr']  = !empty($_POST['filter_attr'])  ? implode(',', array_unique(array_diff($_POST['filter_attr'],array(0)))) : 0;
    $cat['cat_recommend']  = !empty($_POST['cat_recommend'])  ? $_POST['cat_recommend'] : array();
	 /*  代码增加_srat By www.hongyuvip.com */
	$cat['brand_qq']  = !empty($_POST['brand_wwwecshop68com']) ? $_POST['brand_wwwecshop68com'] : '';
	$cat['attr_wwwecshop68com']  = !empty($_POST['attr_qq']) ? $_POST['attr_qq'] : '';
	 /*  代码增加_end By www.hongyuvip.com */
	/* 代码增加_start Byjdy */
	
	
	/* 代码增加_start  By   www.hongyuvip.com  */
	$cat['path_name']     = !empty($_POST['path_name'])     ? trim($_POST['path_name'])     : '';
	if($cat['path_name'] != '')
	{
		$is_have = $db->getOne("select count(*)  from ". $ecs->table('category') ." where cat_id !='$cat_id' and path_name='$cat[path_name]' "); 
		if ($is_have)
		{
		   $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		   sys_msg('对不起，已经存在同名目录', 0, $link);
		}
	}
	/* 代码增加_end  By   www.hongyuvip.com  */
	
	
	
	$cat_imgs=$db->getOne("select cat_nameimg, cat_index_rightad, cat_adimg_1, cat_adimg_2  from ".$ecs->table("category")." where cat_id='$cat_id'");
	if ($_FILES['cat_nameimg']['tmp_name'] != '' && $_FILES['cat_nameimg']['tmp_name'] != 'none')
	{
		$cat['cat_nameimg'] = $catimg_dir. '/' .  basename($image->upload_image($_FILES['cat_nameimg'], $catimg_dir));
		/* 删除旧图片 */
		if (!empty($cat_imgs['cat_nameimg'])){
			@unlink(ROOT_PATH . DATA_DIR . '/'. $catimg_dir .'/' . $cat_imgs['cat_nameimg']);
		}
	}
	if ($_FILES['cat_index_rightad']['tmp_name'] != '' && $_FILES['cat_index_rightad']['tmp_name'] != 'none')
	{
		$cat['cat_index_rightad'] = $catimg_dir. '/' .  basename($image->upload_image($_FILES['cat_index_rightad'], $catimg_dir));
		/* 删除旧图片 */
		if (!empty($cat_imgs['cat_index_rightad'])){
			@unlink(ROOT_PATH . DATA_DIR . '/'. $catimg_dir .'/' . $cat_imgs['cat_index_rightad']);
		}
	}
	if ($_FILES['cat_adimg_1']['tmp_name'] != '' && $_FILES['cat_adimg_1']['tmp_name'] != 'none')
	{
		$cat['cat_adimg_1'] = $catimg_dir. '/' .  basename($image->upload_image($_FILES['cat_adimg_1'], $catimg_dir));
		/* 删除旧图片 */
		if (!empty($cat_imgs['cat_adimg_1'])){
			@unlink(ROOT_PATH . DATA_DIR . '/' .$catimg_dir. '/' . $cat_imgs['cat_adimg_1']);
		}
	}
	if ($_FILES['cat_adimg_2']['tmp_name'] != '' && $_FILES['cat_adimg_2']['tmp_name'] != 'none')
	{
		$cat['cat_adimg_2'] = $catimg_dir. '/' .  basename($image->upload_image($_FILES['cat_adimg_2'], $catimg_dir));
		/* 删除旧图片 */
		if (!empty($cat_imgs['cat_adimg_2'])){
			@unlink(ROOT_PATH . DATA_DIR . '/' .$catimg_dir. '/' . $cat_imgs['cat_adimg_2']);
		}
	}
	$cat['cat_adurl_1']        = !empty($_POST['cat_adurl_1'])        ? sanitize_url(trim($_POST['cat_adurl_1']))      : '';
	$cat['cat_adurl_2']        = !empty($_POST['cat_adurl_2'])        ? sanitize_url(trim($_POST['cat_adurl_2']))      : '';
	$cat['category_index']       =  !empty($_POST['category_index'])  ? $_POST['category_index'] : '0';
	$cat['category_index_dwt'] = (!empty($_POST['category_index_dwt']) && $cat['category_index'] != 0 && !empty($_POST['index_dwt_file'])) ? $_POST['category_index_dwt'] : '0';
	$cat['index_dwt_file']     = (!empty($_POST['index_dwt_file']) && $cat['category_index'] != 0 && $cat['category_index_dwt'] != 0) ? $_POST['index_dwt_file'] : '';
	$cat['show_in_index']       =  !empty($_POST['show_in_index'])  ? $_POST['show_in_index'] : '0';
	/* 代码增加_end Byjdy */

	/* 代码增加_start  By   www.hongyuvip.com  */
	$cat['path_name']     = !empty($_POST['path_name'])     ? trim($_POST['path_name'])     : '';
	if($cat['path_name'] != ''){
		$is_have = $db->getOne("select count(*)  from ". $ecs->table('category') ." where cat_id !='$cat_id' and path_name='$cat[path_name]' "); 
		if ($is_have)
		{
		   $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
		   sys_msg('对不起，已经存在同名目录', 0, $link);
		}
	}
	/* 代码增加_end  By   www.hongyuvip.com  */

    /* 判断分类名是否重复 */

    if ($cat['cat_name'] != $old_cat_name)
    {
        if (cat_exists($cat['cat_name'],$cat['parent_id'], $cat_id))
        {
           $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
           sys_msg($_LANG['catname_exist'], 0, $link);
        }
    }

    /* 判断上级目录是否合法 */
    $children = array_keys(cat_list($cat_id, 0, false));     // 获得当前分类的所有下级分类
    if (in_array($cat['parent_id'], $children))
    {
        /* 选定的父类是当前分类或当前分类的下级分类 */
       $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
       sys_msg($_LANG["is_leaf_error"], 0, $link);
    }

    if($cat['grade'] > 10 || $cat['grade'] < 0)
    {
        /* 价格区间数超过范围 */
       $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
       sys_msg($_LANG['grade_error'], 0, $link);
    }

    $dat = $db->getRow("SELECT cat_name, show_in_nav FROM ". $ecs->table('category') . " WHERE cat_id = '$cat_id'");

    if ($db->autoExecute($ecs->table('category'), $cat, 'UPDATE', "cat_id='$cat_id'"))
    {
        if($cat['cat_name'] != $dat['cat_name'])
        {
            //如果分类名称发生了改变
            $sql = "UPDATE " . $ecs->table('nav') . " SET name = '" . $cat['cat_name'] . "' WHERE ctype = 'c' AND cid = '" . $cat_id . "' AND type = 'middle'";
            $db->query($sql);
        }
        if($cat['show_in_nav'] != $dat['show_in_nav'])
        {
            //是否显示于导航栏发生了变化
            if($cat['show_in_nav'] == 1)
            {
                //显示
                $nid = $db->getOne("SELECT id FROM ". $ecs->table('nav') . " WHERE ctype = 'c' AND cid = '" . $cat_id . "' AND type = 'middle'");
                if(empty($nid))
                {
                    //不存在
                    $vieworder = $db->getOne("SELECT max(vieworder) FROM ". $ecs->table('nav') . " WHERE type = 'middle'");
                    $vieworder += 2;
                    $uri = build_uri('category', array('cid'=> $cat_id), $cat['cat_name']);

                    $sql = "INSERT INTO " . $ecs->table('nav') . " (name,ctype,cid,ifshow,vieworder,opennew,url,type) VALUES('" . $cat['cat_name'] . "', 'c', '$cat_id','1','$vieworder','0', '" . $uri . "','middle')";
                }
                else
                {
                    $sql = "UPDATE " . $ecs->table('nav') . " SET ifshow = 1 WHERE ctype = 'c' AND cid = '" . $cat_id . "' AND type = 'middle'";
                }
                $db->query($sql);
            }
            else
            {
                //去除
                $db->query("UPDATE " . $ecs->table('nav') . " SET ifshow = 0 WHERE ctype = 'c' AND cid = '" . $cat_id . "' AND type = 'middle'");
            }
        }

        //更新首页推荐
        insert_cat_recommend($cat['cat_recommend'], $cat_id);
        /* 更新分类信息成功 */
        clear_cache_files(); // 清除缓存
        admin_log($_POST['cat_name'], 'edit', 'category'); // 记录管理员操作

        /* 提示信息 */
        $link[] = array('text' => $_LANG['back_list'], 'href' => $is_virtual?'category.php?act=virtual_list':'category.php?act=list');
        sys_msg($_LANG['catedit_succed'], 0, $link);
    }
}

/*------------------------------------------------------ */
//-- 批量转移商品分类页面
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'move')
{
    /* 权限检查 */
    admin_priv('cat_drop');

    $cat_id = !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;

    /* 模板赋值 */
    $smarty->assign('ur_here',     $_LANG['move_goods']);
    $smarty->assign('action_link', array('href' => 'category.php?act=list', 'text' => $_LANG['04_category_list']));

    $smarty->assign('cat_select', cat_list(0, $cat_id, true));
    $smarty->assign('form_act',   'move_cat');

    /* 显示页面 */
    assign_query_info();
    $smarty->display('category_move.htm');
}


/*------------------------------------------------------ */
//-- 批量转移虚拟商品分类页面
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'move_virtual')
{
    /* 权限检查 */
    admin_priv('cat_drop');

    $cat_id = !empty($_REQUEST['cat_id']) ? intval($_REQUEST['cat_id']) : 0;

    /* 模板赋值 */
    $smarty->assign('ur_here',     $_LANG['move_goods']);
    $smarty->assign('action_link', array('href' => 'category.php?act=virtual_list', 'text' => $_LANG['04_category_list']));

    $smarty->assign('cat_select', cat_list1(0, $cat_id, true));
    $smarty->assign('form_act',   'move_virtual_cat');

    /* 显示页面 */
    assign_query_info();
    $smarty->display('category_move.htm');
}

/*------------------------------------------------------ */
//-- 处理批量转移虚拟商品分类的处理程序
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'move_virtual_cat')
{
    /* 权限检查 */
    admin_priv('cat_drop');

    $cat_id        = !empty($_POST['cat_id'])        ? intval($_POST['cat_id'])        : 0;
    $target_cat_id = !empty($_POST['target_cat_id']) ? intval($_POST['target_cat_id']) : 0;

    /* 商品分类不允许为空 */
    if ($cat_id == 0 || $target_cat_id == 0)
    {
        $link[] = array('text' => $_LANG['go_back'], 'href' => 'category.php?act=move_virtual');
        sys_msg($_LANG['cat_move_empty'], 0, $link);
    }

    /* 更新商品分类 */
    $sql = "UPDATE " .$ecs->table('goods'). " SET cat_id = '$target_cat_id' ".
           "WHERE cat_id = '$cat_id'";
    if ($db->query($sql))
    {
        /* 清除缓存 */
        clear_cache_files();

        /* 提示信息 */
        $link[] = array('text' => $_LANG['go_back'], 'href' => 'category.php?act=virtual_list');
        sys_msg($_LANG['move_cat_success'], 0, $link);
    }
}

/*------------------------------------------------------ */
//-- 处理批量转移商品分类的处理程序
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'move_cat')
{
    /* 权限检查 */
    admin_priv('cat_drop');

    $cat_id        = !empty($_POST['cat_id'])        ? intval($_POST['cat_id'])        : 0;
    $target_cat_id = !empty($_POST['target_cat_id']) ? intval($_POST['target_cat_id']) : 0;

    /* 商品分类不允许为空 */
    if ($cat_id == 0 || $target_cat_id == 0)
    {
        $link[] = array('text' => $_LANG['go_back'], 'href' => 'category.php?act=move');
        sys_msg($_LANG['cat_move_empty'], 0, $link);
    }

    /* 更新商品分类 */
    $sql = "UPDATE " .$ecs->table('goods'). " SET cat_id = '$target_cat_id' ".
           "WHERE cat_id = '$cat_id'";
    if ($db->query($sql))
    {
        /* 清除缓存 */
        clear_cache_files();

        /* 提示信息 */
        $link[] = array('text' => $_LANG['go_back'], 'href' => 'category.php?act=list');
        sys_msg($_LANG['move_cat_success'], 0, $link);
    }
}

/*------------------------------------------------------ */
//-- 编辑排序序号
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'edit_sort_order')
{
    check_authz_json('cat_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if (cat_update($id, array('sort_order' => $val)))
    {
        clear_cache_files(); // 清除缓存
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}

/*------------------------------------------------------ */
//-- 编辑数量单位
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'edit_measure_unit')
{
    check_authz_json('cat_manage');

    $id = intval($_POST['id']);
    $val = json_str_iconv($_POST['val']);

    if (cat_update($id, array('measure_unit' => $val)))
    {
        clear_cache_files(); // 清除缓存
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}

/*------------------------------------------------------ */
//-- 编辑排序序号
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'edit_grade')
{
    check_authz_json('cat_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if($val > 10 || $val < 0)
    {
        /* 价格区间数超过范围 */
        make_json_error($_LANG['grade_error']);
    }

    if (cat_update($id, array('grade' => $val)))
    {
        clear_cache_files(); // 清除缓存
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}

/*------------------------------------------------------ */
//-- 切换是否显示在导航栏
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'toggle_show_in_nav')
{
    check_authz_json('cat_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if (cat_update($id, array('show_in_nav' => $val)) != false)
    {
        if($val == 1)
        {
            //显示
            $vieworder = $db->getOne("SELECT max(vieworder) FROM ". $ecs->table('nav') . " WHERE type = 'middle'");
            $vieworder += 2;
            $catname = $db->getOne("SELECT cat_name FROM ". $ecs->table('category') . " WHERE cat_id = '$id'");
            //显示在自定义导航栏中
            $_CFG['rewrite'] = 0;
            $uri = build_uri('category', array('cid'=> $id), $catname);

            $nid = $db->getOne("SELECT id FROM ". $ecs->table('nav') . " WHERE ctype = 'c' AND cid = '" . $id . "' AND type = 'middle'");
            if(empty($nid))
            {
                //不存在
                $sql = "INSERT INTO " . $ecs->table('nav') . " (name,ctype,cid,ifshow,vieworder,opennew,url,type) VALUES('" . $catname . "', 'c', '$id','1','$vieworder','0', '" . $uri . "','middle')";
            }
            else
            {
                $sql = "UPDATE " . $ecs->table('nav') . " SET ifshow = 1 WHERE ctype = 'c' AND cid = '" . $id . "' AND type = 'middle'";
            }
            $db->query($sql);
        }
        else
        {
            //去除
            $db->query("UPDATE " . $ecs->table('nav') . "SET ifshow = 0 WHERE ctype = 'c' AND cid = '" . $id . "' AND type = 'middle'");
        }
        clear_cache_files();
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}

/*------------------------------------------------------ */
//-- 切换是否显示
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'toggle_is_show')
{
    check_authz_json('cat_manage');

    $id = intval($_POST['id']);
    $val = intval($_POST['val']);

    if (cat_update($id, array('is_show' => $val)) != false)
    {
        clear_cache_files();
        make_json_result($val);
    }
    else
    {
        make_json_error($db->error());
    }
}

/*------------------------------------------------------ */
//-- 删除商品分类
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'remove')
{
    check_authz_json('cat_manage');

    /* 初始化分类ID并取得分类名称 */
    $cat_id   = intval($_GET['id']);
    $sql = "select is_virtual from ".$ecs->table('category')." where cat_id = $cat_id";
    $is_virtual = $db -> getOne($sql);
    
    $cat_name = $db->getOne('SELECT cat_name FROM ' .$ecs->table('category'). " WHERE cat_id='$cat_id'");

    /* 当前分类下是否有子分类 */
    $cat_count = $db->getOne('SELECT COUNT(*) FROM ' .$ecs->table('category'). " WHERE parent_id='$cat_id'");

    /* 当前分类下是否存在商品 */
    $goods_count = $db->getOne('SELECT COUNT(*) FROM ' .$ecs->table('goods'). " WHERE cat_id='$cat_id'");

    /* 如果不存在下级子分类和商品，则删除之 */
    if ($cat_count == 0 && $goods_count == 0)
    {
        /* 删除分类 */
        $sql = 'DELETE FROM ' .$ecs->table('category'). " WHERE cat_id = '$cat_id'";
        if ($db->query($sql))
        {
            $db->query("DELETE FROM " . $ecs->table('nav') . "WHERE ctype = 'c' AND cid = '" . $cat_id . "' AND type = 'middle'");
            clear_cache_files();
            admin_log($cat_name, 'remove', 'category');
        }
    }
    else
    {
        make_json_error($cat_name .' '. $_LANG['cat_isleaf']);
    }

    $url = 'category.php?act=query&is_virtual='.$is_virtual.'&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}



/*------------------------------------------------------ */
//-- PRIVATE FUNCTIONS
/*------------------------------------------------------ */
//
///**
// * 检查分类是否已经存在
// *
// * @param   string      $cat_name       分类名称
// * @param   integer     $parent_cat     上级分类
// * @param   integer     $exclude        排除的分类ID
// *
// * @return  boolean
// */
//function cat_exists($cat_name, $parent_cat, $exclude = 0)
//{
//    $sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('category').
//           " WHERE parent_id = '$parent_cat' AND cat_name = '$cat_name' AND cat_id<>'$exclude'";
//    return ($GLOBALS['db']->getOne($sql) > 0) ? true : false;
//}

/**
 * 获得商品分类的所有信息
 *
 * @param   integer     $cat_id     指定的分类ID
 *
 * @return  mix
 */
function get_cat_info($cat_id)
{
    $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('category'). " WHERE cat_id='$cat_id' LIMIT 1";
    return $GLOBALS['db']->getRow($sql);
}

/**
 * 添加商品分类
 *
 * @param   integer $cat_id
 * @param   array   $args
 *
 * @return  mix
 */
function cat_update($cat_id, $args)
{
    if (empty($args) || empty($cat_id))
    {
        return false;
    }

    return $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('category'), $args, 'update', "cat_id='$cat_id'");
}


/**
 * 获取属性列表
 *
 * @access  public
 * @param
 *
 * @return void
 */
function get_attr_list()
{
    $sql = "SELECT a.attr_id, a.cat_id, a.attr_name ".
           " FROM " . $GLOBALS['ecs']->table('attribute'). " AS a,  ".
           $GLOBALS['ecs']->table('goods_type') . " AS c ".
           " WHERE  a.cat_id = c.cat_id AND c.enabled = 1 ".
           " ORDER BY a.cat_id , a.sort_order";

    $arr = $GLOBALS['db']->getAll($sql);

    $list = array();

    foreach ($arr as $val)
    {
        $list[$val['cat_id']][] = array($val['attr_id']=>$val['attr_name']);
    }

    return $list;
}

/**
 * 插入首页推荐扩展分类
 *
 * @access  public
 * @param   array   $recommend_type 推荐类型
 * @param   integer $cat_id     分类ID
 *
 * @return void
 */
function insert_cat_recommend($recommend_type, $cat_id)
{
    //检查分类是否为首页推荐
    if (!empty($recommend_type))
    {
        //取得之前的分类
        $recommend_res = $GLOBALS['db']->getAll("SELECT recommend_type FROM " . $GLOBALS['ecs']->table("cat_recommend") . " WHERE cat_id=" . $cat_id);
        if (empty($recommend_res))
        {
            foreach($recommend_type as $data)
            {
                $data = intval($data);
                $GLOBALS['db']->query("INSERT INTO " . $GLOBALS['ecs']->table("cat_recommend") . "(cat_id, recommend_type) VALUES ('$cat_id', '$data')");
            }
        }
        else
        {
            $old_data = array();
            foreach($recommend_res as $data)
            {
                $old_data[] = $data['recommend_type'];
            }
            $delete_array = array_diff($old_data, $recommend_type);
            if (!empty($delete_array))
            {
                $GLOBALS['db']->query("DELETE FROM " . $GLOBALS['ecs']->table("cat_recommend") . " WHERE cat_id=$cat_id AND recommend_type " . db_create_in($delete_array));
            }
            $insert_array = array_diff($recommend_type, $old_data);
            if (!empty($insert_array))
            {
                foreach($insert_array as $data)
                {
                    $data = intval($data);
                    $GLOBALS['db']->query("INSERT INTO " . $GLOBALS['ecs']->table("cat_recommend") . "(cat_id, recommend_type) VALUES ('$cat_id', '$data')");
                }
            }
        }
    }
    else
    {
        $GLOBALS['db']->query("DELETE FROM ". $GLOBALS['ecs']->table("cat_recommend") . " WHERE cat_id=" . $cat_id);
    }
}

/**
 * 根据关键词搜索商品分类
 *
 * @access  public
 *
 * @return mix
 */
function search_cat()
{
	if(empty($_POST['cat_name']))
	{
		return;
	}
	
	$res = NULL;
	
	// 根据类别名称进行模糊查询
	$sql = "SELECT c.cat_id, c.cat_name, c.measure_unit, c.parent_id, c.is_show, c.show_in_nav, c.grade, c.sort_order, COUNT(s.cat_id) AS has_children, 1 AS result ".
                'FROM ' . $GLOBALS['ecs']->table('category') . " AS c ".
                "LEFT JOIN " . $GLOBALS['ecs']->table('category') . " AS s ON s.parent_id=c.cat_id ".
                "GROUP BY c.cat_id ".
                "HAVING c.cat_name LIKE '%".$_POST['cat_name']."%' ".
                'ORDER BY c.parent_id, c.sort_order ASC';
    $res = $GLOBALS['db']->getAll($sql);
    
    // 查询所有类别
    $sql = "SELECT c.cat_id, c.cat_name, c.measure_unit, c.parent_id, c.is_show, c.show_in_nav, c.grade, c.sort_order, COUNT(s.cat_id) AS has_children ".
                'FROM ' . $GLOBALS['ecs']->table('category') . " AS c ".
                "LEFT JOIN " . $GLOBALS['ecs']->table('category') . " AS s ON s.parent_id=c.cat_id ".
                "GROUP BY c.cat_id ".
                'ORDER BY c.parent_id, c.sort_order ASC';
    $res1 = $GLOBALS['db']->getAll($sql);
    
    // 构建一个全分类的Map集合<cat_id, cat>
    $cat_map = array();
    foreach ($res1 as $cat)
    {
    	if(!empty($cat)){
    		$cat_id = $cat['cat_id'];
	    	$cat_map[$cat_id] = $cat;
    	}
    }
    
    // 对商品类别进行排序
    $res1 = cat_options(0, $res1);
    
    // 获取查询结果的上级所有父类别
    $parents = array();
    $cat_result = array();
    foreach ($res as $cat)
    {
    	$cat_result[$cat['cat_id']] = 1;
    	array_push($parents, $cat);
    	get_cat_parents($parents, $cat_map, $cat['cat_id']);
    }
    
    // 重构集合，只包含将来返回结果所包含的类别
    $cat_map = array();
    foreach ($parents as $cat)
    {
    	$cat_map[$cat['cat_id']] = $cat;
    }
    
    // 移除与查询结果无关的类别
    $res = array();
    foreach ($res1 as $cat)
    {
    	if(!empty($cat_map[$cat['cat_id']])){
    		// 标识出匹配查询条件的结果
    		if(empty($cat_result[$cat['cat_id']])){
    			$cat['is_result'] = 2;
    		}else{
    			$cat['is_result'] = 1;
    		}
    		array_push($res, $cat);
    	}
    }

	return $res;
}

/**
 * 
 * 获取指定类别ID的所有上级类别并存放到指定数组中
 * 
 * @access private
 * @param $parents 存放所有上级类别
 * @param $cat_map 存放所有类别的Map集合
 * @param $cat_id 待获取上级类别的类别ID
 */
function get_cat_parents(&$parents, $cat_map, $cat_id)
{
	
	if(empty($parents)){
		$parents = array();
	}
	
	$cat = $cat_map[$cat_id];
	$parent_id = $cat['parent_id'];
	
	$cat = $cat_map[$parent_id];
	
	if($parent_id != 0)
	{
		// 递归
		get_cat_parents($parents, $cat_map, $parent_id);
	}
	
	array_push($parents, $cat);
    
}

/**
 * 获取虚拟团购分类一级列表
 * @return type
 */
function get_virtual_cat_select(){
    $sql = "select cat_id,cat_name from ".$GLOBALS['ecs']->table('category')." where is_virtual = 1 and parent_id = 0";
    $res = $GLOBALS['db']->getAll($sql);
    $cat_select = '';
    foreach($res as $k=>$v){
        $cat_select .= "<option value='$v[cat_id]]'>$v[cat_name]</option>";
    }
    $cat_select .="</option>";
    return $cat_select;
}
?>