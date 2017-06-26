<?php
/**
 * 调试时使用
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-06-23
 * Time: 10:41
 */
namespace middleware;
use mysql;
class debug{
    public function init(\biu\context $c){
        $tm1=microtime(true);
        $c->next();
        //输出sql语句
        $sqls=mysql\engine::$sql_strings;
        $tm2=microtime(true);
        $html='<div class="font-size:12px;list-style:none">';
        $use_time=$tm2-$tm1;
        $html.="<li>用时：{$use_time}</li>";
        foreach($sqls as $str){
            $html.="<li>{$str}</li>";
        }
        $html.='</div>';
        echo $html;
    }

    /**
     * 调试输出
     */
    public function dump(){
        echo '<pre>';
        $args=func_get_args();
        foreach($args as $b){
            print_r($b);
        }
    }
}