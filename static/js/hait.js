/**
 * 使用元素的name生成一个以id为键，value为值的json数据
 * @param {Object} className
 */
function makeClassDomValueToJSON(className){
    var data={};
    var dom=document.getElementsByClassName(className);
    var len=dom.length;
    for(i=0;i<len;i++){
        var id=dom[i].id;
        var name=dom[i].getAttribute("son-name");
        if(name){
            data[id]=[];
            var sdom = document.getElementsByName(name);
            var k=0;
            for(n=0;n<sdom.length;n++){
                var pass=false;
                if(sdom[n].getAttribute("type")=="checkbox"){
                    pass=sdom[n].checked;
                }else{
                    pass=sdom[n].value;
                }
                if(pass){
                    data[id][k] = sdom[n].value;
                    k++;
                }
            }
        }else{
            data[id]=dom[i].value;
        }
    }
    return data;
}
/**
 * post表单ajax提交
 * @param {Object} url
 * @param {Object} data|className
 * @param {Object} success
 * @param {Object} error
 */
function ajaxPost(url,data,success,error){
    if(typeof(data)=="string"){
        data=makeClassDomValueToJSON(data);
    }
    return $.ajax({
        type:"POST",
        url:url,
        async:true,
        data:data,
        dataType:"json",
        success:function(d){
            parseResCall(d,success,error);
        },
        error:function(){
            if(error) error()
        }
    });
}

/**
 * get数据ajax获取，并映射到对应的id元素的value
 * 对应id如果给出了attr为mapping，则映射到对应的参数上去，默认参数inner,text，或者映射到mapping值的attr上面去
 * 同时出发自定义事件insert
 * <textarea type=text mapping="inner" id=test /></textarea>
 * $("#test").bind("inset",function(){
 * 	   alert('ok');
 * })
 * @param {Object} url
 * @param {Object} data
 * @param {Object} success
 * @param {Object} error
 */
function ajaxGet(url,data,success,error){
    return $.ajax({
        type:"Get",
        url:url,
        async:true,
        data:data,
        dataType:"json",
        success:function(d){
            parseResCall(d,success,error);
        },
        error:function(){
            if(error) error()
        }
    });
}

function parseResCall(d,succ,err){
    if(!d.code){
        if(err)err();
        return;
    }
    switch(d.code){
        case 1:
            succ(d||{});
            break;
        case 2:
            window.top.layer.open({content: d.msg,skin:'msg',time:2});
            $("#"+d.name).focus();
            break;
        case 3:
            window.top.layer.open({content: d.data||"未登陆",skin:'msg',time:2});
            break;
        case 5:
            window.top.layer.open({content: d.data||"无权限",skin:'msg',time:2});
            break;
    }
}
//创建下拉
function makeDropdown(){
    $(".ht-dropdown span").unbind("click").bind("click",function(){
        if(typeof(index)=="undefined"){
            index=999;
        }
        var dom=$(this).parent().find("ul");
        if(dom.attr("display")=="block"){
            dom.css({"display":"none"});
            dom.attr("display","none");
        }else{
            dom.css({"display":"block","z-index":index++});
            dom.attr("display","block");
        }
    });
    var closeCall=function(){
        $(".ht-dropdown ul").css({"display":"none"}).attr("display","none");;
    }
    $(".ht-dropdown ul").unbind("mouseup").bind("mouseup",closeCall);
    if(typeof(binding)!="undefined")return;
    binding=true;
    $("body").bind("mouseup",closeCall);
}
//创建不动表头
//fixed-tit
//fixed-bod
function makeFixedHeadTable(dom,top){
    if($(dom).attr('headtable-resize')!='used'){
        $(window).resize(function(){
            makeFixedHeadTable(dom,top);
        });
        var tab_tit=$(dom).find("thead").html();
        var tab_bod=$(dom).find("tbody").html();
        $(dom).html("<div style=\"position:absolute;left:0px;top:0px;width:100%;height:"+top+"px;z-index: 1\"><table class=\"fixed-tit\">"+tab_tit+"</table></div>");
        $(dom).append("<div style=\"position:absolute;top:"+top+"px;left:0px;width:100%;bottom:0px;z-index0;overflow: auto\"><table class=\"fixed-bod\">"+tab_bod+"</table></div>");
        $(dom).attr('headtable-resize','used');
    }
    var d=$(dom).find('.fixed-tit tr th');
    d.each(function(){
        var i=$(this).index();
        if(i+1>=d.length){
            return;
        }
        var w=$(this).css("width").replace("px","")*1+1;
        $(dom).find('.fixed-bod td').eq(i).css("width", w);
    });
}


/**
 * 自定义的一个javascript类构造器
 * __init     为构造方法
 * @param {Object} json
 * 例如：
 var user=Class({
		name:"",
		__init:function(name){
			this.name=name;
		},
		get_name:function(){
			alert(this.name);
		}
	})

 lili=new user("丽丽")
 lili.get_name()
 */
function Class(json){
    function newobj(){
        if(json.__init)json.__init.apply(this,arguments);
    }
    newobj.prototype=json;
    return newobj;
}

/**
 * 页面跳转
 * @param url
 * @param data
 */
function goto(url,data){
    if(typeof(data)=="string"){
        data=makeClassDomValueToJSON(data);
    }
    url+="?";
    for(k in data){
        url+=k+"="+data[k]+"&";
    }
    window.location=url;
}

//锁通讯
//get=读
//set=写
var chan=Class({
    _data:null,
    _time:null,
    wait:function(call){
        var _this=this;
        if(_this._time){
            clearInterval(_this._time);
        }
        _this._time=setInterval(function(data){
           if(_this._data){
               clearInterval(_this._time);
               return call(data);
           }
        },100);
    },
    send:function(data){
        this._data=data;
    }
})


/**
 * 分页
 * @param {Object} json
 * 参数    json.id       显示分页的容器
 *       json.pages    页码总数
 * 		 json.nums	      总数据量
 *       json.current  当前页码
 *       json.jump     页码跳转执行的函数 function(page)
 */
function small_page(json) {
    var d=$("#"+json.id);
    prev = json.current-1;//上一页
    next = json.current+1;//下一页
    html = '<div class="page">';
    html += '<li>' + json.pages + '/' + json.current + '</li>';
    html += '<li class="home hv" page="'+1+'">首页</li>';
    html += '<li class="prev hv" page="'+(prev)+'">上一页</li>';
    html += '<li class="next hv" page="'+(next)+'">下一页</li>';
    html += '<li class="end hv"  page="'+(json.pages)+'">尾页</li>';
    html += '<li class="text"><input type="text" value="' + json.current + '" class="input" /></li>';
    html += '<li class="jump hv"  page="'+(json.current)+'">跳转</li>'
    html += '</div>';
    d.html(html);
    d.find(".input").bind("blur",function(){
        d.find(".jump").attr("page",$(this).val());
    })
    d.find(".page li").bind("click",function(){
        page=$(this).attr("page")
        if(page){
            json.jump(page);
        }
    })
    if (json.current == 1) {//如果是首页
        d.find(".prev").addClass("disabled").unbind("click").removeClass("hv");
    }
    if(json.current>=json.pages){//如果是尾页
        d.find(".next").addClass("disabled").unbind("click").removeClass("hv");
    }
}

//回调注册
var global_call_back={};
function call_reg(k,call){
    global_call_back[k]=call;
}
//回调触发
function call_trigger(k){
    if(global_call_back[k]){
        global_call_back[k]();
    }
}

$(document).ready(function(){
    $('.refresh-btn').bind("click",function(){
       window.location.reload();
    });
})

//创建qrcode
function makeQrcode(url,l){
    l=parseInt(l)+1;
    for(i=1;i<l;i++){
        $('#qrcode_'+i).qrcode(url+"&gun_id="+i);
    }
}

function initValueToDoms(data){
    for(id in data){
        var v=data[id];
        var d=document.getElementById(id);
        if(d){
            if(d.getAttribute("type")=="checkbox"){
                if(v){
                    d.checked=true;
                }else{
                    d.checked=false;
                }
            }else{
                d.value=v;
            }
        }
    }
}
function getValueToJSON(className){
    var obj={};
    var sdom = document.getElementsByClassName(className);
    for(n=0;n<sdom.length;n++){
        var _t=sdom[n];
        obj[_t.id]=_t.value;
        if(_t.getAttribute("type")=="checkbox"){
            obj[_t.id]=_t.checked?1:0;
        }
    }
    return obj;
}