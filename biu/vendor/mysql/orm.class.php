<?php
/**
 * 一个简易的orm，并带数据绑定校验
 */
namespace  mysql;
abstract class orm {
    protected $table_name;
    protected $obj;
    protected $pk;
    protected $engine=null;
    /**
     * orm constructor.
     * @param $obj
     * @param $pri_key
     * @param string $engine_name
     */
    public function __construct(orm &$obj,$pri_key,$engine_name='default') {
        $this->table_name=basename(get_class($obj));
        $this->obj=$obj;
        $this->pk=$pri_key;
        $this->engine=engine::init($engine_name);
        self::init_pattens();
    }

    /**
     * 数据库引擎（查询使用)
     * @return engine|null
     */
    public function engine(){
        return $this->engine;
    }

    /**
     * 设置模型数据库表名，并返回数据库操作对象
     * @param $table_name
     * @return  mixed
     */
    public function table($table_name){
        $this->table_name=$table_name;
        return $this->engine->table($table_name);
    }

    /**
     * 获取信息,并映射到对象属性,返回查询数组
     * @return array|mixed
     */
    public function info(){
        $res=$this->engine->table($this->table_name)->field('*')->where("{$this->pk}=?",$this->pk_value())->select_one();
        if($res){
            foreach ($res as $cols => $value) {
                $name = '_' . $cols;
                if (property_exists($this->obj, $name)) {
                    $this->obj->$name = $value;
                }
            }
        }
        return $res;
    }

    /**
     * 添加
     * @return array
     */
    public function add(){
        $id=$this->engine->table($this->table_name)->data($this->obj_arr())->insert();
        if($id){
            $pk='_'.$this->pk;
            $this->obj->$pk=$id;
        }
        return $id;
    }

    /**
     * 修改
     * @return mixed
     */
    public function mod(){
        return $this->engine->table($this->table_name)->data($this->obj_arr())->where("{$this->pk}=?",$this->pk_value())->update_one();
    }

    /**
     * 对象转换为数组
     * @return array
     */
    private function obj_arr(){
        $arr=get_object_vars($this->obj);
        $data=[];
        foreach($arr as $name=>$value){
            if(substr($name,0,1)=='_'){
                $data[substr($name,1)]=$value;
            }
        }
        return $data;
    }

    /**
     * 删除
     * @return mixed
     */
    public function del(){
        return  $this->engine->table($this->table_name)->where("{$this->pk}=?",$this->pk_value())->delete_one();
    }

    /**
     * 获取主键值
     * @return mixed
     */
    private function pk_value(){
        $pk='_'.$this->pk;
        return $this->obj->$pk;
    }

    /**
     * 验证规则
     * @return mixed
     */
    abstract function bind_match();

    /**
     * 绑定arr数据
     * @param $array
     * @param array $need  必须的字段
     * @param array $omit  忽略的字段
     * @return array
     */
    public function bind($array,$need=[],$omit=[]){
        $validate=$this->obj->bind_match();
        $vars=get_object_vars($this->obj);
        foreach($validate as $name=>$pattens){
            if($omit && in_array($name,$omit)){
                continue;
            }
            if($need && in_array($name,$need) && !isset($array[$name])){
                return ['name'=>$name,'msg'=>'不能为空'];
            }
            if(isset($array[$name])){
                $value=$array[$name];
                foreach ($pattens as $patten => $msg) {
                    if (!$this->match($value, $patten)) {
                        return ['name' => $name, 'msg' => $msg];
                    }
                }
                $name1='_'.$name;//数据库字段
                $name2=$name.'_';//其它字段
                if(array_key_exists($name1,$vars)){
                    $this->obj->$name1=$value;
                }elseif(array_key_exists($name2,$vars)){
                    $this->obj->$name2=$value;
                }
            }
        }
        return [];
    }

    /**
     * 正则式集合
     * @var array
     */
    private static $pattens=[];

    /**
     * 初始化验证方法
     */
    private static function init_pattens(){
        self::$pattens=[
            'any'  =>'/^[\s\S]*$/',
            'url'  => '/^[\w\d\.]+\.(com|cn|org|net)$/',
            'phone'=> '/^1[34578]\d{9}$/',
            'ip'   => '/^((25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])\.){1,3}(25[0-5]|2[0-4][0-9]|1[0-9][0-9]|[1-9]?[0-9])?$/',
            'int'  => '/^\d+$/',
            'email'=> '/^\w*@\w*\.(com|cn|org|net|com\.cn)$/',
            'pswd' => '/^[a-zA-Z0-9\.@#\?\+]{6,32}$/',
            'chi'  => '/^[\x7f-\xff]+$/',
            'eng'  => '/^[a-zA-Z]+$/',
            'str'  => '/^[\S]+$/',
            'len'  => function($data,$type,$len){//长度判断，使用方法  len|>|3
                switch($type){
                    case '=':
                        return $len==mb_strlen($data);
                        break;
                    case '<':
                        return $len<mb_strlen($data);
                        break;
                    case '>':
                        return $len>mb_strlen($data);
                        break;
                }
               return false;
            },
            'date' => function($data){//日期判断
                return strtotime($data)?true:false;
            }
        ];
    }

    /**
     * 添加验证方法
     * @param $key
     * @param $value
     */
    public function reg_validate($key,$value){
        self::$pattens[$key]=$value;
    }

    /**
     * 数据过滤
     * string 输入字符串
     * patten 基本类型 | 正则式 | 回调函数
     */
    private function match($data,$patten='') {
        $data_args=[$data];
        $call_arg=explode('|',$patten);
        if(count($call_arg)>1){
            $patten=trim($call_arg[0]);
            $data_args=array_merge($data_args,array_slice($call_arg,1));
        }
        if(isset(self::$pattens[$patten])){
            $patten=self::$pattens[$patten];
        }
        if(is_callable($patten)){//如果是回调函数
            return call_user_func_array($patten,$data_args);
        }
        return preg_match($patten,$data)?true:false;
    }
}
?>