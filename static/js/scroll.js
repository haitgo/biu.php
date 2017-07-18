// JavaScript Document
(function($){
	$.fn.myScroll = function(options){
	//默认配置
	var defats = {
		speed:40,  //滚动速度,值越大速度越慢
		
	};
	
	var oppts = $.extend({}, defats, options),intId = [];
	
	function marqueea(obj, step){
	
		obj.find(".daheiln").animate({
			marginTop: '-=1'
		},0,function(){
				var s = Math.abs(parseInt($(this).css("margin-top")));
				if(s >= step){
					$(this).find("li").slice(0, 1).appendTo($(this));
					$(this).css("margin-top", 0);
				}
			});
		}
		
		this.each(function(i){
			var sh = oppts["rowHeight"],speed = oppts["speed"],_this = $(this);
			intId[i] = setInterval(function(){
				if(_this.find(".daheiln").height()<=_this.height()){
					clearInterval(intId[i]);
				}else{
					marqueea(_this, sh);
				}
			}, speed);

			_this.hover(function(){
				clearInterval(intId[i]);
			},function(){
				intId[i] = setInterval(function(){
					if(_this.find(".daheiln").height()<=_this.height()){
						clearInterval(intId[i]);
					}else{
						marqueea(_this, sh);
					}
				}, speed);
			});
		
		});

	}

})(jQuery);