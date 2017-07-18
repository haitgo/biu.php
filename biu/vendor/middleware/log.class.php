<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-06-23
 * Time: 10:30
 */
namespace middleware;
class log {
    public function init(\biu\context $c){
        $c->next();
    }
}