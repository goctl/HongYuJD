<?php

/**
 * 鸿宇多用户商城 程序说明
 * ===========================================================
 * 版权所有 2015-2018 鸿宇科技有限公司，并保留所有权利。
 * 网站地址: http://www.hongyuvip.com；
 * ----------------------------------------------------------
 * 仅供学习交流使用，如需商用请购买正版版权。鸿宇不承担任何法律责任。
 * 踏踏实实做事，堂堂正正做人。
 * ==========================================================
 * $Author: Shadow & 鸿宇
 * $Id: search_log.php 17217 2016-01-19 06:29:08Z Shadow & 鸿宇
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
$_REQUEST['act'] = trim($_REQUEST['act']);

admin_priv('search_log');
if ($_REQUEST['act'] == 'list')
{
    $logdb = get_search_log();
    $smarty->assign('ur_here',      $_LANG['search_log']);
    $smarty->assign('full_page',      1);
    $smarty->assign('logdb',        $logdb['logdb']);
    $smarty->assign('filter',       $logdb['filter']);
    $smarty->assign('record_count', $logdb['record_count']);
    $smarty->assign('page_count',   $logdb['page_count']);
    $smarty->assign('start_date',   local_date('Y-m-d'));
    $smarty->assign('end_date',     local_date('Y-m-d'));
    assign_query_info();
    $smarty->display('search_log_list.htm');
}
elseif ($_REQUEST['act'] == 'query')
{

    $logdb = get_search_log();
    $smarty->assign('full_page',      0);
    $smarty->assign('logdb',        $logdb['logdb']);
    $smarty->assign('filter',       $logdb['filter']);
    $smarty->assign('record_count', $logdb['record_count']);
    $smarty->assign('page_count',   $logdb['page_count']);
    $smarty->assign('start_date',   local_date('Y-m-d'));
    $smarty->assign('end_date',     local_date('Y-m-d'));
    make_json_result($smarty->fetch('search_log_list.htm'), '',
        array('filter' => $logdb['filter'], 'page_count' => $logdb['page_count']));
}
function get_search_log()
{
    $where = '';
    if (isset($_REQUEST['start_dateYear']) && isset($_REQUEST['end_dateYear']))
    {
        $start_date = $_POST['start_dateYear']. '-' .$_POST['start_dateMonth']. '-' .$_POST['start_dateDay'];
        $end_date   = $_POST['end_dateYear']. '-' .$_POST['end_dateMonth']. '-' .$_POST['end_dateDay'];
        $where .= " AND date <= '$end_date' AND date >= '$start_date'";
        $filter['start_dateYear']  = $_REQUEST['start_dateYear'];
        $filter['start_dateMonth'] = $_REQUEST['start_dateMonth'];
        $filter['start_dateDay']   = $_REQUEST['start_dateDay'];

        $filter['end_dateYear']  = $_REQUEST['end_dateYear'];
        $filter['end_dateMonth'] = $_REQUEST['end_dateMonth'];
        $filter['end_dateDay']   = $_REQUEST['end_dateDay'];
    }

    $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('keywords') . " WHERE  searchengine='ecshop' $where";
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);
    $logdb = array();
    $filter = page_and_size($filter);
    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('keywords') .
           " WHERE  searchengine='ecshop' $where" .
           " ORDER BY date DESC, count DESC" .
           "  LIMIT $filter[start],$filter[page_size]";
    $query = $GLOBALS['db']->query($sql);

    while ($rt = $GLOBALS['db']->fetch_array($query))
    {
        $logdb[] = $rt;
    }
    $arr = array('logdb' => $logdb, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}
?>