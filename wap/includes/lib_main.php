<?php

/**
 * 鸿宇多用户商城 wap前台公共函数
 * ============================================================================
 * 版权所有 2015-2018 鸿宇科技有限公司，并保留所有权利。
 * 网站地址: http://www.hongyuvip.com；
 * ----------------------------------------------------------------------------
 * 仅供学习交流使用，如需商用请购买正版版权。鸿宇不承担任何法律责任。
 * 踏踏实实做事，堂堂正正做人。
 * ============================================================================
 * $Author: Shadow & 鸿宇
 * $Id: lib_main.php 17217 2016-01-19 06:29:08Z Shadow & 鸿宇
*/

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/**
 * 对输出编码
 *
 * @access  public
 * @param   string   $str
 * @return  string
 */
function encode_output($str)
{
    if (EC_CHARSET != 'utf-8')
    {
        $str = ecs_iconv(EC_CHARSET, 'utf-8', $str);
    }
    return htmlspecialchars($str);
}

/**
 * wap分页函数
 *
 * @access      public
 * @param       int     $num        总记录数
 * @param       int     $perpage    每页记录数
 * @param       int     $curr_page  当前页数
 * @param       string  $mpurl      传入的连接地址
 * @param       string  $pvar       分页变量
 */
function get_wap_pager($num, $perpage, $curr_page, $mpurl,$pvar)
{
    $multipage = '';
    if($num > $perpage)
    {
        $page = 2;
        $offset = 1;
        $pages = ceil($num / $perpage);
        $all_pages = $pages;
        $tmp_page = $curr_page;
        $setp = strpos($mpurl, '?') === false ? "?" : '&amp;';
        if($curr_page > 1)
        {
            $multipage .= "<a href=\"$mpurl${setp}${pvar}=".($curr_page-1)."\">上一页</a>";
        }
        $multipage .= $curr_page."/".$pages;
        if(($curr_page++) < $pages)
        {
            $multipage .= "<a href=\"$mpurl${setp}${pvar}=".$curr_page++."\">下一页</a><br/>";
        }
        //$multipage .= $pages > $page ? " ... <a href=\"$mpurl&amp;$pvar=$pages\"> [$pages] &gt;&gt;</a>" : " 页/".$all_pages."页";
        $url_array = explode("?" , $mpurl);
        $field_str = "";
        if (isset($url_array[1]))
        {
            $filed_array = explode("&amp;" , $url_array[1]);
            if (count($filed_array) > 0)
            {
                foreach ($filed_array AS $data)
                {
                    $value_array = explode("=" , $data);
                    $field_str .= "<postfield name='".$value_array[0]."' value='".encode_output($value_array[1])."'/>\n";
                }
            }
        }
        $multipage .= "跳转到第<input type='text' name='pageno' format='*N' size='4' value='' maxlength='2' emptyok='true' />页<anchor>[GO]<go href='{$url_array[0]}' method='get'>{$field_str}<postfield name='".$pvar."' value='$(pageno)'/></go></anchor>";
        //<postfield name='snid' value='".session_id()."'/>
    }
    return $multipage;
}

/**
 * 返回尾文件
 *
 * @return  string
 */
function get_footer()
{
    if (substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], '/')) == '/index.php')
    {
        $footer = "<br/>[".local_date('H:i')."]";
    }
    else
    {
        $footer = "<br/><select><option onpick='index.php'>快速通道</option><option onpick='goods_list.php?type=best'>精品推荐</option><option onpick='goods_list.php?type=promote'>商家促销</option><option onpick='goods_list.php?type=hot'>热门商品</option><option onpick='goods_list.php?type=new'>最新产品</option></select>";
    }

    return $footer;
}

?>