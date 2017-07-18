// JavaScript Document


$('.nav span').click(function(){
	$(this).css('color','#29a7e2').siblings().css('color','#666');
	})





$(".login").click(function(){
if($(".top_more").css("display")=="none"){
$(".top_more").show();
}else{
$(".top_more").hide();
}
});








$('.title_1').click(function(){
	$('.list_1').css('display','block');
	$('.list_2').css('display','none');
	$('.list_3').css('display','none');
	})


$('.title_2').click(function(){
	$('.list_1').css('display','none');
	$('.list_2').css('display','block');
	$('.list_3').css('display','none');
	})


$('.title_3').click(function(){
	$('.list_1').css('display','none');
	$('.list_2').css('display','none');
	$('.list_3').css('display','block');
	})





//帮助中心

$(document).ready(function() {
	$('.inactive').click(function(){
		if($(this).siblings('ul').css('display')=='none'){
			$(this).parent('li').siblings('li').removeClass('inactives');
			$(this).addClass('inactives');
			$(this).siblings('ul').slideDown(100).children('li');
			if($(this).parents('li').siblings('li').children('ul').css('display')=='block'){
				$(this).parents('li').siblings('li').children('ul').parent('li').children('a').removeClass('inactives');
				$(this).parents('li').siblings('li').children('ul').slideUp(100);

			}
		}else{
			//控制自身变成+号
			$(this).removeClass('inactives');
			//控制自身菜单下子菜单隐藏
			$(this).siblings('ul').slideUp(100);
			//控制自身子菜单变成+号
			$(this).siblings('ul').children('li').children('ul').parent('li').children('a').addClass('inactives');
			//控制自身菜单下子菜单隐藏
			$(this).siblings('ul').children('li').children('ul').slideUp(100);

			//控制同级菜单只保持一个是展开的（-号显示）
			$(this).siblings('ul').children('li').children('a').removeClass('inactives');
		}
	})
});



//债权转让


$('.cl_n1').click(function(){
	$('.cl_l1').css('display','block');
	$('.cl_l2').css('display','none');
	$('.cl_l3').css('display','none');
	$('.cl_l4').css('display','none');
	$('.cl_l5').css('display','none');
	$('.cl_l6').css('display','none');
	})

$('.cl_n2').click(function(){
	$('.cl_l1').css('display','none');
	$('.cl_l2').css('display','block');
	$('.cl_l3').css('display','none');
	$('.cl_l4').css('display','none');
	$('.cl_l5').css('display','none');
	$('.cl_l6').css('display','none');
	})


$('.cl_n3').click(function(){
	$('.cl_l1').css('display','none');
	$('.cl_l2').css('display','none');
	$('.cl_l3').css('display','block');
	$('.cl_l4').css('display','none');
	$('.cl_l5').css('display','none');
	$('.cl_l6').css('display','none');
	})


$('.cl_n4').click(function(){
	$('.cl_l1').css('display','none');
	$('.cl_l2').css('display','none');
	$('.cl_l3').css('display','none');
	$('.cl_l4').css('display','block');
	$('.cl_l5').css('display','none');
	$('.cl_l6').css('display','none');
	})

$('.cl_n5').click(function(){
	$('.cl_l1').css('display','none');
	$('.cl_l2').css('display','none');
	$('.cl_l3').css('display','none');
	$('.cl_l4').css('display','none');
	$('.cl_l5').css('display','block');
	$('.cl_l6').css('display','none');
	})


$('.cl_n6').click(function(){
	$('.cl_l1').css('display','none');
	$('.cl_l2').css('display','none');
	$('.cl_l3').css('display','none');
	$('.cl_l4').css('display','none');
	$('.cl_l5').css('display','none');
	$('.cl_l6').css('display','block');
	})


$('.zhuan').click(function(){
	$('.window').css('display','block');
	$('.f_zhuan').css('display','block');
	})

$('.cancel').click(function(){
	$('.window').css('display','none');
	$('.f_zhuan').css('display','none');
	})



