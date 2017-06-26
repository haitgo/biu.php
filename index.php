<?php
include("biu/biu.class.php");
biu\biu::run(function(biu\route $r) {
    $r->middleware(['\app\common\init','begin']);//配置初始化
    $r->middleware(['\middleware\log', 'init']);//日志记录
    $r->middleware(['\middleware\safe', 'filter_input']);//安全过滤
    //$r->middleware(['\middleware\debug', 'init']);//调试模块
    $r->use_namespace('\app\controller');
    $r->domain(['127.0.0.1'],function(\biu\route $r){
        $r->get('/aaa',function(\biu\context $c){
            //
        });
        $r->get('/test',['\user','info']);
        $r->middleware(['\admin','check_login']);//登录验证器
        $r->get('/',['\admin','main']);          //后台首页
        $r->get('/login',['\admin','login_get']);//登录页面
        $r->post('/login',['\admin','login_post']);//登录提交
    });
});
?>