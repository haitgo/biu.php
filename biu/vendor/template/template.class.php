<?php
/**
 * 模板处理
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-06-30
 * Time: 9:46
 * 使用方法
 * template::init('/sdf','/sdf/sdf');
 */
namespace template;
class template{
    /**
     * 模板加载目录
     * @var
     */
    private static $tpl_path;

    /**
     * 缓存目录
     * @var
     */
    private static $compile_path;

    /**
     * 模板初始化，设置模板目录和缓存目录
     * @param $tpl_path
     * @param $compile_path //目录必须可写;
     */
    public static function init($tpl_path,$compile_path){
        self::$tpl_path=rtrim($tpl_path,'/').'/';
        self::$compile_path=rtrim($compile_path,'/').'/';
    }

    /**
     * 模板变量
     * @var
     */
    private static  $tpl_vars=[];

    /**
     * @param $key
     * @param $value
     */
    public static function assign($key,$value){
        self::$tpl_vars[$key]=$value;
    }

    /**
     * 批量增加模板变量
     * @param $data
     */
    public static function append($data){
        self::$tpl_vars=array_merge(self::$tpl_vars,$data);
    }

    /**
     * 加载模板
     * @param $tpl
     * @return bool
     */
    public static function display($tpl){
        header('Content-type:text/html;charset=utf-8');
        $tpl_file=self::$tpl_path.$tpl;     //到指定的目录中寻找模板文件
        if(!file_exists($tpl_file)) {                 //如果需要处理的模板文件不存在
            return false;                       //结果该函数执行返回false
        }
        //获取编译过的模板文件，该文件中的内容都是被替换过的
        $compile_file=self::$compile_path."com_".basename($tpl_file);
        //判断替换后的文件是否存在或是存在但有改动，都需要重新创建
        if(!file_exists($compile_file) || filemtime($compile_file) < filemtime($tpl_file)) {
            $repContent=self::tpl_replace(file_get_contents($tpl_file));  //调用内部替换模板方法
            $handle=fopen($compile_file, 'w+');  //打开一个用来保存编译过的文件
            fwrite($handle, $repContent);         //向文件中写入内容
            fclose($handle);                    //关闭打开的文件
        }
        include($compile_file);		         //包含处理后的模板文件输出给客户端
    }


    /*  该方法使用正则表达式将模板文件'<{ }>'中的语句替换为对应的值或PHP代码 */
    /*  参数content: 提供从模板文件中读入的全部内容字符串                     */
    private static function tpl_replace($content){
        $pattern=array(        //匹配模板中各种标识符的正则表达式的模式数组
            '/<\{\s*\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*\}>/i',  //匹配模板中变量
            '/<\{\s*if\s*(.+?)\s*\}>(.+?)<\{\s*\/if\s*\}>/ies',             //匹配模板中if标识符
            '/<\{\s*else\s*if\s*(.+?)\s*\}>/ies',                        //匹配elseif标识符
            '/<\{\s*else\s*\}>/is',                                  //匹配else标识符
            '/<\{\s*loop\s+\$(\S+)\s+\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*\}>(.+?)<\{\s*\/loop\s*\}>/is',                //用来匹配模板中的loop标识符，用来遍历数组中的值
            '/<\{\s*loop\s+\$(\S+)\s+\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*=>\s*\$(\S+)\s*\}>(.+?)<\{\s*\/loop\s*\}>/is',   //用来匹配模板中的loop标识符，用来遍历数组中的键和值
            '/<\{\s*include\s+[\"\']?(.+?)[\"\']?\s*\}>/ie'                //匹配include标识符
        );
        $replacement=array(   //替换从模板中使用正则表达式匹配到的字符串数组
            '<?php echo self::$tpl_vars["${1}"]; ?>',                 //替换模板中的变量
            '$this->stripvtags(\'<?php if(${1}) { ?>\',\'${2}<?php } ?>\')', //替换模板中的if字符串
            '$this->stripvtags(\'<?php } elseif(${1}) { ?>\',"")',         //替换elseif的字符串
            '<?php } else { ?>',                                  //替换else的字符串
            '<?php foreach(self::$tpl_vars["${1}"] as self::$tpl_vars["${2}"]) { ?>${3}<?php } ?>',
            '<?php foreach(self::$tpl_vars["${1}"] as self::$tpl_vars["${2}"] => self::$tpl_vars["${3}"]) { ?>${4}<?php } ?>',    //这两条用来替换模板中的loop标识符为foreach格式
            'file_get_contents(self::$tpl_path."${1}")'         //替换include的字符串
        );
        @$repContent=preg_replace($pattern, $replacement, $content);  //使用正则替换函数处理
        if(preg_match('/<\{([^(\}>)]{1,})\}>/', $repContent)) {       //如果还有要替换的标识
            $repContent=self::tpl_replace($repContent);         //递归调用自己再次替换
        }
        return $repContent;                                   //返回替换后的字符串
    }

    /* 该方法用来将条件语句中使用的变量替换为对应的值             */
    /* 参数expr: 提供模板中条件语句的开始标记                     */
    /* 参数statement: 提供模板中条件语句的结束标记                 */
    private function stripvtags($expr, $statement='') {
        $var_pattern='/\s*\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\s*/is';  //匹配变量的正则
        @$expr = preg_replace($var_pattern, 'self::$tpl_vars["${1}"]', $expr);   //将变量替换为值
        $expr = str_replace("\\\"", "\"", $expr);             //将开始标记中的引号转义替换
        $statement = str_replace("\\\"", "\"", $statement);     //替换语句体和结束标记中的引号
        return $expr.$statement;                         //将处理后的条件语句相连后返回
    }
}
?>