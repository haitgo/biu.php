<?php 
$e
$a=strip_tags($e);
echo $a;
?>
index=0;
for(i=0;i<10;i++){ 
	setTimeout(function(){
		index++;
		document.getElementById("chat_textarea").value="我也来玩玩，这是测试消息"+index; 
		document.getElementById("send_chat_btn").click();
	},100*i);
}
