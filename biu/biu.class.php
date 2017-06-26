<?php
/**----------------------------------------------------------------
 * biu框架，一款精简的restful框架;
 * 作者：悟道人
 * QQ:274944659
 *-----------------------------------------------------------------
 */
namespace biu;
class biu {
    /**
     * 本类不允许实例化
     * biu constructor.
     */
    private function __construct(){

    }

    /**
     *初始化参数
     */
    public static function init(){
        date_default_timezone_set('PRC');
        session_start();
        define("ROOT_PATH",'./');
        spl_autoload_register(['\biu\biu','autoload']);
    }

    /**
     * 配置变量
     * @var array
     */
    public static $config=[];

    /**
     * 保存配置文件
     * @param $key
     * @param $value
     */
    public static function set_config($key,$value){
        self::$config[$key]=$value;
    }

    /**
     * 读取配置数据
     * @param $key
     * @return mixed|null
     */
    public static function config($key){
        if(isset(self::$config[$key])){
            return self::$config[$key];
        }
        return null;
    }

    /**
     * 请求的路径
     * @var
     */
    public static $request_url;

    /**
     * 请求路由
     * @param $call
     */
    public static function run($call){
        $http_code=404;
        try {
            $url=self::parse_url();
            $call(route::init($url));
        }catch(\Exception $e){//捕捉异常
            $http_code=$e->getMessage();
        }
        $code=[
            200=>'',
            201=>'',
            404=>'404 not found.',
            500=>'500 server error.',
            600=>'600 execption.',
        ];
        $context=context::instance();
        if(isset($code[$http_code]) && $code[$http_code]){
            $context->http_code($http_code);
            echo $code[$http_code];
        }
    }
    /**
     * 解析当前路径
     * @return string
     */
    private static function parse_url(){
        if(!isset($_SERVER['REQUEST_URI'])){
            $_SERVER['REQUEST_URI']="/";
        }
        $query_str=$_SERVER['REQUEST_URI'];
        $query_arr=explode('?',rtrim($query_str,'/'));
        self::$request_url=trim($query_arr[0]);
        if(isset($query_arr[1])){
            parse_str($query_arr[1],$_GET);//重定义get
        }
        return self::$request_url;
    }

    /**
     * 自动加载(包含vendor目录)
     * @param $class_name
     */
    public static function autoload($class_name){
        $class_name=ltrim(str_replace('\\','/',$class_name),'/');
        $class_file=ROOT_PATH.$class_name.'.class.php';
        $vendor_class_file=ROOT_PATH.'vendor/'.$class_name.'.class.php';
        if(file_exists($class_file)){
            require_once($class_file);
        }elseif(file_exists($vendor_class_file)){
            require_once($vendor_class_file);
        }
    }

    /**
     * 自动实例化回调
     * @param $call
     * @param array $args
     * @throws \Exception
     */
    public static function call($call,$args=[]){
        if(is_object($call)){
            call_user_func_array($call,$args);
        }elseif(is_array($call) && count($call)==2){
            call_user_func_array([new $call[0],$call[1]],$args);
        }else{
            throw new \Exception(500);
        }
    }
}
biu::init();
?>