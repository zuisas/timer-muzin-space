var timer_canvasArray = new Array();
$(document).ready(function (){
	$(".countdown_block").each(function() {
       $(this).prepend('<canvas id="canvas_'+$(this).attr("id")+'" width="100" height="100">Обновите браузер</canvas>');
	   timer_canvasArray.push($(this).attr("id"));
    });
	setInterval(setPaintTimer,750);
});

function paintCircle(a,b,c,id,r) {
	canvas=document.getElementById(id);
	canvas=canvas.getContext("2d");
	PI = Math.PI;
	canvas.strokeStyle = a;
	canvas.fillStyle = a;
	canvas.beginPath();
	canvas.arc(50,50,50,0,2*PI,true);
	canvas.closePath();
	canvas.fill();
	
	canvas.fillStyle = b;
	canvas.beginPath();
	canvas.moveTo(50,50);
	canvas.arc(50,50,49,-PI/2,-PI/2+2*PI*(r/100),false);
	canvas.closePath();
	canvas.fill();
	
	canvas.fillStyle = c;
	canvas.beginPath();
	canvas.arc(50,50,43,0,2*PI,false);
	canvas.closePath();
	canvas.fill();
	return false;
}


function setPaintTimer() {
	for(i=0;i<timer_canvasArray.length;i++) {
		timer_radius = $("#"+timer_canvasArray[i]).data("percent");
		paintCircle("#DFF1EE","#4ebdc0","#ffffff","canvas_"+timer_canvasArray[i],timer_radius);
	}
	return false;
}
