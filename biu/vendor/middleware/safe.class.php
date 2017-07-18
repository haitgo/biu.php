<?php
/**
 * 安全过滤模块
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-06-23
 * Time: 10:20
 */
namespace middleware;
class safe {
    /**
     * 过滤提交的数据
     */
    public  function filter_input(){
        $getfilter="'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
        $postfilter="\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
        foreach($_GET as $key=>$value){
            self::request_filter($key,$value,$getfilter);
            $value = is_array ( $value ) ? $value : addslashes ( trim ( $value ) );
            $_GET [$key] = $value;
        }
        foreach($_POST as $key=>$value){
            self::request_filter($key,$value,$postfilter);
            $value = is_array ($value ) ? $value : addslashes ( trim ( $value ) );
            $_POST [$key] = $value;
        }
    }

    /**
     * 循环过滤
     * @param $StrFiltKey
     * @param $StrFiltValue
     * @param $ArrFiltReq
     * @return bool
     */
    private	static function  request_filter($StrFiltKey,$StrFiltValue,$ArrFiltReq){
        if(is_array($StrFiltValue)){
            $StrFiltValue=implode($StrFiltValue);
        }
        if (preg_match("/".$ArrFiltReq."/is",$StrFiltValue)==1){
            return false;
        }
    }

}