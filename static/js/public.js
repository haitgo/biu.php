$(function(){
	$(".display_f_in .fourcp_1").first().css({display:"block"}).siblings().css({display:"none"});
	$(".sizdzh_f ul li").click(function(){
		$(this).addClass("addys_f").siblings().removeClass("addys_f");
		$(".display_f_in .fourcp_1").eq($(this).index()).css({display:"block"}).siblings().css({display:"none"});
		});
	$(".xfdsctz_f ul li").click(function(){
		$(this).addClass("addys_f2").siblings().removeClass("addys_f2");
		$(".display_f_in .fourcp_1").eq($(this).index()).css({display:"block"}).siblings().css({display:"none"});
		});
	$(".fourcp_1 .novice_zs_f_in").css({marginBottom:"6px"})	
	$(".footerdhl_f a").click(function(){
		$(this).find("b").addClass("movek_f");
		$(this).siblings().find("b").removeClass("movek_f");
		})
	$(".sxdymy_f_in ul li").last().css({borderBottom:"none"});
	$(".xsiqy_f_in .xsiqy_f_in_1").last().css({display:"none"});
	$(".zslgxzx_f_in ul li").click(function(){
		$(this).addClass("addys_f3").siblings().removeClass("addys_f3");
		$(".xsiqy_f_in .xsiqy_f_in_1").eq($(this).index()).css({display:"block"}).siblings().css({display:"none"});
		});
	
			
	})	
	
	