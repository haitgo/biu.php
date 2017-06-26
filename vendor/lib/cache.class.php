<?php
namespace biu\lib;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/13 0013
 * Time: 下午 4:48
 * 使用方法
 * $cache=new \lib\cache('user.php')
 * $cache->read();
 *
 * $cache->write(array(1,32));
 */
class cache {
    private $file='';
    public function __construct($file){
        $this->file=$file;
    }
    //读取
    public function read(){
        if(file_exists($this->file)){
            $content=file_get_contents($this->file);
            return unserialize($content);
        }
        return [];
    }
    //写如
    public function write($data){
        $data=serialize($data);
        file_put_contents($this->file,$data,LOCK_EX);
    }
}

?>