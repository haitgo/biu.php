<?php
/**
 * 路由处理，捷径获取
 */
namespace biu;
class route {
    /**
     * 请求上下文对象
     * @var
     */
    private static $context;

    /**
     * 当前请求的url
     * @var
     */
    private static $url;

    /**
     * 回调处理集合
     * @var array
     */
    private static $handles=[];

    /**
     * 初始化路由器
     * @param $url
     * @return route
     */
    public static function init($url){
        self::$url=$url;
        self::$context=context::instance();
        return new self();
    }

    /**
     * 命名空间
     * @var
     */
    private $namespace;

    /**
     * route constructor.
     */
    public function __construct($namespace='') {
        $this->namespace=$namespace;
    }

    /**
     * 使用命名空间
     * @param $namespace
     */
    public function use_namespace($namespace){
        $this->namespace=$namespace;
    }

    /**
     *添加中间件
     * @param $call
     */
    public function middleware($call){
        self::$handles[]=$this->compile_call($call);
    }

    /**
     * 域名限制,多个域名使用数组
     * @param $domain string|array
     * @param $call
     * @throws \Exception
     */
    public function domain($domain,$call){
        $domains=$domain;
        if(!is_array($domain)){
            $domains=[$domain];
        }
        foreach($domains as $domain){
            $start=mb_strpos($_SERVER['HTTP_HOST'],$domain);
            if($start===0){
                $route = new self($this->namespace);
                biu::call($call,[$route]);
                return;
            }
        }
    }

    /**
     * 路由组
     * @param $route
     * @param $call
     * @throws \Exception
     */
    public function group($route,$call){
        $start=mb_strpos(self::$url,$route);
        if($start===0){
            self::$url=mb_substr(self::$url,mb_strlen($route));
            $route = new self($this->namespace);
            biu::call($call,[$route]);
            throw new \Exception(404);
        }
    }

    /**
     * get路由
     * @param $route
     * @param $call
     */
    public function get($route,$call){
        $this->route('GET',$route,$call);
    }

    /**
     * post路由
     * @param $route
     * @param $call
     */
    public function post($route,$call){
        $this->route('POST',$route,$call);
    }

    /**
     * delete路由
     * @param $route
     * @param $call
     */
    public function delete($route,$call){
        $this->route('DELETE',$route,$call);
    }

    /**
     * 任意路由,如果call为类路径字符串，则根据请求method调用类的相应method
     * @param $route
     * @param $call
     */
    public function any($route,$call){
        if(is_string($call)){
            $call=[$call,strtolower($_SERVER['REQUEST_METHOD'])];
        }
        $this->route('ANY',$route,$call);
    }

    /**
     * 路由匹配
     * @param $method
     * @param $route
     * @param $call
     * @throws \Exception
     */
    public function route($method,$route,$call){
        $request_method=$_SERVER['REQUEST_METHOD'];
        if(self::$url==$route && ($request_method==$method || $method=='ANY')){
            self::$handles[]=$this->compile_call($call);
            self::handle_request();
            throw new \Exception(200);
        }
    }

    /**
     * 给回调函数添加上命名空间
     * @param $call
     * @return array
     */
    private function compile_call($call){
        if(is_array($call) && count($call)==2){
            $call[0]=$this->namespace.$call[0];
        }
        return $call;
    }

    /**
     * 处理请求回调
     */
    public static function handle_request(){
        static $index=0;
        if(isset(self::$handles[$index])){
            $call=self::$handles[$index++];
            biu::call($call,[self::$context]);
            self::handle_request();
        }
    }
 }
?>