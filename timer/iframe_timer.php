<?php 
/*****************************
Разработчик: Валерий Корецкий
Сайт: http://valerykoretsky.com/
Продукт: Таймер/Счетчик обратного отсчета
Дата выпуска: 22.07.2014
Статус: Free
*****************************/

	/* Отключеняем вывод ошибок */
	error_reporting(0);

	/* Подключаем необходимые файлы */
	require_once 'tools/iddb.class.php'; // Класс для работы с IDDB (текстовая база данных в одно поле)
	require_once 'tools/timer.class.php'; // Класс для работы самого таймера
	require_once 'tools/config.php'; // Файл конфигураций

	/* Создаем экземпляры классов */	
	$iddb = new IDDB;
	$timer = new TIMER($config);

	/* Получаем значение таймера (в секундах) */	
	$timer_value = $timer->getTime();

	/* Получаем значение таймера (в секундах) */
	$timer_block = explode("/",$config['blockvisible']);
	$js = 'var blockvisible = new Array(';
	for($i=0;$i<count($timer_block);$i++) {
		if(($i+1)<count($timer_block)) { $sep = ','; } else { $sep = ''; }
		$js .= $timer_block[$i].$sep;
	}
	$js .= ');';
	
	/* Делаем проверку на отображение разных страниц */
	if($config['page_html']==1) {
		$redirect_html = "if(timeleft<0) { top.location.reload(); }"; // генерация JS-кода для обновления страницы по окончанию таймера
	}
	
	/* Делаем проверку на включенный редирект при неактивном таймере */
	if($config['redirect_end']==1 && $config['redirect_url']>'') {
		$redirect_html = "if(timeleft<0) { window.top.location='{$config['redirect_url']}'; }"; // генерация JS-кода для редиректа по окончанию таймера
	}
	
?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!--<meta http-equiv="refresh" content="300">-->
	<link rel="stylesheet" href="themes/<?php echo $config['template']; ?>/css/style.css">
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script type="text/javascript" src="static/js/language/<?php echo $config['language']; ?>.js"></script>
	<script type="text/javascript" src="themes/<?php echo $config['template']; ?>/js/functions.js"></script>
</head>
<body>
	<div id="countdown"></div>

<script>
	<?php 
		echo "var timeleft = ".$timer_value.";";
		echo $js;
	?>
	var time_config = new Array(604800,86400,3600,60,1);
	var time_setupstart = new Array(0,0,24,60,60);
	var timeleft_tmp = 0;
	var timeleft_array = new Array();
	function countdown_init() {
		for(i=0;i<blockvisible.length;i++) {
			if(blockvisible[i]==1) {
				html_txt = '<div id="countdown_block_'+i+'" class="countdown_block" data-percent="0"><div class="countdown_block_title">'+time_lang[i]+'</div><div class="countdown_block_value"></div></div><div class="separated"></div>';
				$("#countdown").append(html_txt);
			}
		}
		$('.separated:last').remove();
		return false;
	}

	function countdown_block(number) {
		timeleft_value = Math.floor(timeleft_tmp/time_config[number]);
		if(timeleft_value<0) { timeleft_value = 0; }
		timeleft_tmp -= timeleft_value*time_config[number];
		if(timeleft_value<10) { timeleft_value = '0'+timeleft_value; }
		timeleft_str = timeleft_value.toString();
		timeleft_array = timeleft_str.split("");
		txt = '<span class="digit-group">';
		for(j=0;j<timeleft_array.length;j++) {
			txt += '<span class="digit">'+timeleft_array[j]+'</span>';
		}
		txt += '</span>';
		$("#countdown #countdown_block_"+number+" .countdown_block_value").html(txt);
		switch(number) {
			case 0:
				if(timeleft_value>0) { percent = Math.round(100/timeleft_value*1); } else { percent = 100; }
				break
			case 1:
				if(timeleft_value>0) { percent = Math.round(100/timeleft_value*1); } else { percent = 100; }
				break
			case 2:
				percent = 100-Math.round((timeleft_value*100)/24);
				break
			case 3:
				percent = 100-Math.round((timeleft_value*100)/60);
				break
			case 4:
				percent = 100-Math.round((timeleft_value*100)/60);
				break
			default:
				alert('Blocked!');
		}
		$("#countdown_block_"+number).data("percent", percent).attr("data-percent", percent);
		return false;
	}
	function countdown_go() {
		timeleft_tmp = timeleft;
		for(i=0;i<blockvisible.length;i++) {
			if(blockvisible[i]==1) {
				countdown_block(i);
			}
		}
		timeleft-=1;
		<?php echo $redirect_html; ?>
		return false;
	}
	countdown_init();
	$(document).ready(function() {
		setInterval(countdown_go,1000);
		/*top.document.getElementById('timer_iframe').style.height = document.body.scrollHeight;*/
		return false;
	});
</script>
</body>
</html>