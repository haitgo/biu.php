<?php
namespace  mysql;
class engine {
    /**
     * 链接对象
     * @var array
     */
    private static $engines=[];

    /**
     * 单例模式创建数据库对象
     * @param $engine_name
     * @param $conf=['host'=>'','dbname'=>'','port'=>'','user'=>'','password'=>'','prefix'=>'表前缀','debug'=>'调试']
     * @return engine
     */
    public static function init($engine_name,$conf=[]){
        if(!isset(self::$engines[$engine_name]) && $conf) {
            try {
                $connect = new \PDO ("mysql:host={$conf['host']};dbname={$conf['dbname']};port={$conf['port']}", $conf['user'], $conf['password']);
                $connect->exec('set names utf8');
                self::$engines[$engine_name] = new self($connect,$conf);
            } catch (\PDOException $e) {
                trigger_error('数据库链接失败');
            }
        }
        return self::$engines[$engine_name];
    }

    public static $sql_strings=[];//执行的sql语句记录

    /**
     * 实例化引擎
     * engine constructor.
     * @param \PDO $connect
     * @param $conf
     */
    private function __construct(\PDO $connect,$conf){
        $this->connect=$connect;
        $this->conf=$conf;
    }
    private $connect=null;//pdo连接对象
    private $table='';
    private $cols='*';
    private $sql=[];
    private $order=[];
    private $data=[];//insert或者update的数据；
    private $conf=[];//配置

    /**
     * 开启事务
     */
    public function beginTransaction() {
        return $this->connect->beginTransaction();
    }

    /**
     * 事务提交
     * 传入多个提交信息，如果有一个错误则事务回滚
     */
    public function commit() {
        foreach(func_get_args() as $ok){
            if(!$ok){
                return $this->rollBack();
            }
        }
        return $this->connect->commit();
    }

    /**
     * 事务回滚
     */
    public  function rollBack() {
        return $this->connect->rollBack();
    }

    /**
     * 保存的数据（增改用)
     * @param $data:数组
     * @return $this
     */
    public function data($data){
        $this->data=$data;
        return $this;
    }
    /**
     * 设置表格名
     * @param $table
     * @return $this
     */
    public function table($table){
        $table_arr=explode(',',$table);
        $table_brr=[];
        foreach($table_arr as $tb){
            $table_brr[]=$this->conf['prefix'].$tb;
        }
        $this->table=implode(',',$table_brr);
        return $this;
    }

    /**
     * 查询列名
     * @param $cols=array|string
     * @return $this
     */
    public function field($cols){
        if(is_array($cols)){
            $this->cols=implode(',',$cols);
        }else{
            $this->cols=$cols;
        }
        return $this;
    }

    /**
     * 查询条件
     * @param $sql
     * @param array $args
     * @return $this
     */
    public function where($sql,$args=[]){
        $sql=self::safe_sql(func_get_args());
        $this->sql[]="where ({$sql})";
        return $this;
    }

    /**
     * and条件
     * @param $sql
     * @param array $args
     * @return $this
     */
    public function _and($sql,$args=[]){
        $sql=self::safe_sql(func_get_args());
        $this->sql[]="and ({$sql})";
        return $this;
    }

    /**
     * or条件
     * @param $sql
     * @param array $args
     * @return $this
     */
    public function _or($sql,$args=[]){
        $sql=self::safe_sql(func_get_args());
        $this->sql[]="or ({$sql})";
        return $this;
    }

    /**
     * in条件
     * @param $col
     * @param array $args
     * @return $this
     */
    public function _in($col,$args=[]){
        foreach($args as $a=>$b){
            $args[$a]='"'.addslashes($b).'"';
        }
        $arg_str=implode(',',$args);
        $and=(count($this->sql))?'and':'';
        $this->sql[]="{$and} {$col} in ( {$arg_str} )";
        return $this;
    }

    /**
     * desc
     * @param $col
     * @return $this
     */
    public function desc($col){
        if(trim($col)!=''){
            $this->order[]="{$col} desc";
        }
        return $this;
    }

    /**asc
     * @param $col
     * @return $this
     */
    public function asc($col){
        if(trim($col)!=''){
            $this->order[]="{$col} asc";
        }
        return $this;
    }

    /**
     * 数据量
     * @return int
     */
    public function count(){
        $now_sql ="select count(*) as num from `{$this->table}`  ".$this->merge_sql();
        $res=$this->query($now_sql);
        if(isset($res[0])){
             return $res[0]['num'];
        }
        return 0;
    }
    /**
     * 查询多条数据
     * @return array
     */
    public function select(){
        $now_sql ="select {$this->cols} from `{$this->table}`  ".$this->merge_sql();
        return $this->query($now_sql);
    }

    /**
     * 查询单一数据
     * @return array|mixed
     */
    public function select_one(){
        $now_sql ="select {$this->cols} from `{$this->table}`  ".$this->merge_sql()." limit  1";
        $row=$this->query($now_sql);
        if(isset($row[0])){
            return $row[0];
        }
        return [];
    }

    /**
     * 分页查询
     * @param $page
     * @param $size
     * @return array [rows=>数据,page=>[page=>页码,size=>每页数量,data_nums=>数据数量,page_nums=>页码数量]];
     */
    public function select_page($page,$size){
        $where=$this->merge_sql();
        $cols=$this->cols;
        $table=$this->table;
        $now_sql = "select count(*) as num from `{$this->table}`  {$where}";
        $result  = $this->query($now_sql);
        $data_nums = $result [0] ['num']; // 数据总数;
        $page_nums= ceil( $data_nums / $size ); // 向上取整(页码数)
        if ($page<=1) { // 当页码小于等于1时默认为0
            $start = 0;
        } elseif ($page>= $page_nums + 1) { // 当页码大于
            $start = ($page_nums - 1) * $size;
        }else{
            $start = ($page - 1) * $size;
        }
        $start=$start<0?0:$start;
        $page_ret = ['page' => intval($page),'size' => $size,'data_nums' => intval($data_nums),'page_nums' => intval($page_nums)];
        $now_sql ="select {$cols} from `{$table}` {$where}  limit {$start},{$size}";
        $rows=$this->query($now_sql);
        return ['rows'=>$rows,'page'=>$page_ret];
    }

    /**
     * 插入数据
     * @return array
     */
    public function insert(){
        $sql=self::parse_insert_sql($this->data);
        $where=$this->merge_sql();
        $now_sql = "insert into  `{$this->table}`  {$sql}  {$where}";
        $this->exec($now_sql);
        return $this->connect->lastInsertId();
    }

    /**
     * 修改数据
     * @param string $and_sql
     * @return mixed
     */
    public function update($and_sql=''){
        $sql=self::parse_update_sql($this->data);
        $where=$this->merge_sql();
        $now_sql = "update  `{$this->table}` set {$sql} {$where} {$and_sql}";
        return $this->exec($now_sql);
    }

    /**
     * 修改单条数据
     * @return mixed
     */
    public function update_one(){
        return $this->update('limit 1');
    }

    /**
     * 删除数据
     * @param string $and_sql
     * @return mixed
     */
    public function delete($and_sql=''){
        $where=$this->merge_sql();
        $now_sql = "delete from `{$this->table}` {$where} {$and_sql}";
        return $this->exec($now_sql);
    }

    /**
     * 删除单条数据
     * @return mixed
     */
    public function delete_one(){
        return $this->delete('limit 1');
    }

    /**
     * 执行insert,update,delete语句
     * @param $sql
     * @return mixed
     */
    public function exec($sql){
        $res=$this->connect->exec($sql);
        $this->free_and_check_err($sql);
        return  $res;
    }

    /**
     * 执行查询语句
     * @param $sql
     * @return array
     */
    public function query($sql){
        $result	=$this->connect->query($sql);
        $this->free_and_check_err($sql);
        $res=$result?$result->fetchAll(\PDO::FETCH_ASSOC ):[];
        return $res;
    }

    /**
     * 释放资源，同时判断有无sql错误
     * @param $sql
     */
    private function free_and_check_err($sql){
        $this->table='';
        $this->cols='*';
        $this->sql=[];
        $this->order=[];
        $this->data=[];
        $err=$this->connect->errorInfo();
        if($err[1]){
            trigger_error("Sql error code {$err[1]},Sql is \"{$sql}\", {$err[2]}",E_USER_ERROR);
        }
        if($this->conf['debug']){
            self::$sql_strings[]=$sql;
        }
    }

    /**
     * sql语句安全过滤
     * @param $args
     * @return string
     */
    private static function safe_sql($args=[]){
        $sql =$args[0];
        $args=array_slice($args,1);
        $sql_arr=explode('?',$sql);
        $sql='';
        foreach($sql_arr as $a=>$b){
            if(!trim($b))continue;
            $sql.=$b;
            if(isset($args[$a])){
                $sql.='"'.addslashes($args[$a]).'"';
            }
        }
        return $sql;
    }

    /**
     * sql条件组成
     */
    private  function merge_sql(){
        $sql=implode(" ",$this->sql);
        if($this->order){
            $sql.=' order by '.implode(" ",$this->order);
        }
        return $sql;
    }

    /**
     * 将数组转换为插入sql语句
     * @param array $data
     * @return string
     */
    private static function parse_insert_sql($data=[]){
        $sql_key   = "";
        $sql_value = "";
        foreach ($data as $key => $value ) {
            if($value ===null)continue;
            $sql_key .= "`{$key}`,";
            $value=addslashes($value);
            $sql_value .="'{$value}',";
        }
        $sql_key = rtrim ( $sql_key, "," );
        $sql_value = rtrim ( $sql_value, "," );
        return "({$sql_key}) values ({$sql_value})";
    }

    /**
     * 将数组转换为修改sql语句
     * @param array $data
     * @return string
     */
    private static function parse_update_sql($data=[]){
        $sql_update = "";
        foreach ( $data as $key => $value ) {
            if($value ===null)continue;
            $value=addslashes($value);
            $sql_update .="`{$key}`='{$value}',";
        }
       return rtrim ( $sql_update, "," );
    }
}
?>