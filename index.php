<?php
include("biu/biu.class.php");
use biu\biu;
use biu\route;
biu::run(function(route $r) {
    $r->middleware(['\app\common\init','begin']);//配置初始化
    $r->middleware(['\middleware\log', 'init']);//日志记录
    $r->middleware(['\middleware\safe', 'filter_input']);//安全过滤
    //$r->middleware(['\middleware\debug', 'init']);//调试模块
    $r->use_namespace('\app\controller');
    $r->domain(['127.0.0.1'],function(route $r){
        $r->get('/login',['\user','login_get']);//登录页面
        $r->post('/login',['\user','login_post']);//登录提交
        $r->middleware(['\user','check_login']);//登录验证器
        $r->get('',['\user','main']);          //后台首页
        //用户
        $r->get('/home',['\user','home']);
        $r->group('/user',function(route $r){
            $r->get('/list',['\user','list_']);
            $r->get('/info',['\user','info']);
            $r->post('/add',['\user','add']);
            $r->post('/mod',['\user','mod']);
            $r->post('/del',['\user','del']);
            $r->post('/set',['\user','set']);
            $r->post('/set/password',['\user','set_password']);
        });
        //企业
        $r->group('/compay',function(route $r){
            $r->get('/list',['\compay','list_']);
            $r->get('/info',['\compay','info']);
            $r->post('/add',['\compay','add']);
            $r->post('/mod',['\compay','mod']);
            $r->post('/del',['\compay','del']);
        });
        //电量
        $r->group('/elecbill',function(route $r){
            $r->get('/list',['\elecbill','list_']);
            $r->get('/info',['\elecbill','info']);
            $r->post('/add',['\elecbill','add']);
            $r->post('/mod',['\elecbill','mod']);
            $r->post('/del',['\elecbill','del']);
        });
        //合同
        $r->group('/contract',function(route $r){
            $r->get('/list',['\contract','list_']);
            $r->get('/info',['\contract','info']);
            $r->post('/add',['\contract','add']);
            $r->post('/mod',['\contract','mod']);
            $r->post('/del',['\contract','del']);
        });

    });
});
?>