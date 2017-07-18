<?php
namespace biu;
/**
 * 请求上下文内容
 * Class context
 * @package pia
 */
class context {
    /**
     * 不允许直接实例化
     * context constructor.
     */
    private function __construct(){

    }

    /**
     * 单例模式对象
     * @var null
     */
    private static $obj=null;

    /**
     * 单例模式实例化
     */
    public static function instance(){
        if(!self::$obj){
            self::$obj=new self();
        }
        return self::$obj;
    }

    /**
     * 输出json字符串
     * @param int $code
     * @param array $arr
     */
    public function json($code=0,$arr=[]){
        header('Content-type:text/json;charset=utf-8');
        echo json_encode(array_merge(['code'=>$code],$arr));
    }


    /**
     * 输出html内容
     * @param $tpl
     * @param $arr
     */
    public function html($tpl,$arr=[]){
        header('Content-type:text/html;charset=utf-8');
        if(file_exists($tpl)){
            extract($arr);
            require($tpl);
        }else{
            echo $tpl;
        }
    }

    /**
     * 输出字符串
     * @param $str
     */
    public function string($str){
        header('Content-type:text/plain;charset=utf-8');
        echo $str;
    }

    /**
     * 返回状态码
     * @param $code
     * @return $this
     */
    public function http_code($code){
        header('HTTP/1.1 '.$code.' ');
        return $this;
    }

    /**
     * 读取get
     * @param $key
     * @param string $def
     * @return string
     */
    public function get($key,$def=''){
        return isset($_GET[$key])?trim($_GET[$key]):$def;
    }

    /**
     * 读取post
     * @param $key
     * @param string $def
     * @return string
     */
    public function post($key,$def=''){
        return isset($_POST[$key])?trim($_POST[$key]):$def;
    }

    /**
     * url匹配的参数
     * @var
     */
    public $params;

    /**
     * 读取url里面自定义匹配的参数
     * @param $key
     * @param string $def
     * @return string
     */
    public function param($key,$def=''){
        return isset($this->params[$key])?$this->params[$key]:$def;
    }

    /**
     * 获取当前访问的ip
     * @return string
     */
    public function client_ip(){
        $ip='';
        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'),'unknown')){
            $ip=getenv('HTTP_CLIENT_IP');
        }else if(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'),'unknown')){
            $ip=getenv('HTTP_X_FORWARDED_FOR');
        } else if(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'),'unknown')){
            $ip=getenv('REMOTE_ADDR');
        }else if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'],'unknown')){
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * 当前访问的url
     * @return string
     */
    public function request_uri(){
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * 保存cookie
     * @param $key
     * @param $value
     * @param int $time
     * @return bool
     */
    public function set_cookie($key,$value,$time=7200){
        return setcookie($key,$value,$time);
    }
    /**
     * 读取cookie
     * @param $key
     * @param string $def
     * @return string
     */
    public function cookie($key,$def=''){
        return isset($_COOKIE[$key])?$_COOKIE[$key]:$def;
    }

    /**
     * 保存session;
     * @param $key
     * @param $value
     */
    public function set_session($key,$value){
        $key.='_';
        $_SESSION[$key]=$value;
    }
    /**
     * 读取session
     * @param $key
     * @param string $def
     * @return string
     */
    public function session($key,$def=''){
        $key.='_';
        return isset($_SESSION[$key])?$_SESSION[$key]:$def;
    }

    /**
     * 让出空间给下一个中间件执行;
     */
    public function next(){
        route::handle_request();
    }

    /**
     * 终止
     * @throws \Exception
     */
    public function abort(){
        throw new \Exception(602);
    }

    /**
     * 重定向
     * @param $url
     */
    public function redirect($url){
        header('location:'.$url);
    }

    /**
     * 请求类型
     * @return string
     */
    public function method(){
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * 是否为某个请求
     * @return bool
     */
    public function is_method($method){
        return $_SERVER['REQUEST_METHOD']==$method;
    }

}

?>