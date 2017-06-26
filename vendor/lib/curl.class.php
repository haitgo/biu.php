<?php
namespace biu\lib;
 /**
     GET调用事例：$url=new curl('localhost:4000/user/customer/info');
		 $url->get(['Uid'=>20,'ProId'=>15]);
 	DELETE调用事例：$url=new curl('localhost:4000/user/customer/del');
	     $url->delete('uid=8&pid=2');
    POST调用事例：$url=new curl('localhost:4000/user/customer/add');
        $url->post(['UserPhone=13698521470','Group=admin','UserName=admin']);
 */
class curl{
	protected $url;
    protected $query_data=[];
    protected $post_data=[];
    protected $cookie_jar_file='cookie.data';
	private $on_exec_call=null;
	public function __construct($url){
		$this->url=$url;
	}

	/**
	 * 设置cookie保持器的路径
	 * @param $file_path
	 */
	public function set_cookie_jar($file_path){
		$this->cookie_jar_file=$file_path;
	}

	/**
	 * 执行时回调
	 * @param $call
	 */
	public function on_exec($call){
		$this->on_exec_call=$call;
	}

	/**
	 * 添加查询参数
	 * @param $key
	 * @param $value
	 */
	public function query($key,$value){
		$this->query_data[$key]=$value;
	}

	/**
	 * 添加表单
	 * @param $key
	 * @param $value
	 */
	public function form($key,$value){
		$this->post_data[$key]=$value;
	}

	/**
	 * 使用get请求
	 * @param array $query_data
	 * @return mixed
	 */
	public function get($query_data=[]){
		$this->query_data=array_merge($this->query_data,$query_data);
		return $this->exec("GET"); 
	}

	/**
	 * 使用post请求
	 * @param array $post_data
	 * @return mixed
	 */
	public function post($post_data=[]){ 
		$this->post_data=array_merge($this->post_data,$post_data);
		return $this->exec("POST"); 
	}

	/**
	 * 使用delete请求
	 * @param array $post_data
	 * @return mixed
	 */
	public function delete($post_data=[]){
		$this->post_data=array_merge($this->post_data,$post_data);
		return $this->exec("DELETE"); 
	}

	/**
	 * 私有，执行请求
	 * method=请求类型 GET,POST,DELET
	 * @param $method
	 * @return mixed
	 */
	private function exec($method){
		$data=http_build_query($this->post_data);
		$url=$this->url;
		if(count($this->query_data)>0){
			$url=$this->url.'?'.http_build_query($this->query_data);
		} 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_jar);
 		$output = curl_exec($ch);
		curl_close($ch);
		$call=$this->on_exec_call;
		//echo $url;
        if($this->on_exec_call && is_callable($call)){
			return $call($output);
        }
        return $output;
	}
}
?>



