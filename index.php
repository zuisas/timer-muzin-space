<link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon" />
<link rel="icon" href="img/favicon.ico" type="image/x-icon" />

<style>
	#title, #auth {
		font-family:Roboto;
	}
	.fines {
		width: 150px;
		background-color: rgba(0, 0, 0, 70%);
		padding-bottom: 10px;
	}
	.fines h3 {
		color: red;
		text-align: center;
		font-size: 30px;
		font-family:Roboto;
	}
	.fines p {
		color: white;
		text-align: center;
		font-size: 30px;
		margin-bottom: -10px;
		font-family:Tahoma,serif;
	}
	footer {
		padding-top: 3px;
		font-family: Roboto;
	}
	footer a, #auth a {
		color: teal;
	}
	#sub {
		margin-top: 5px;
	}
</style>

<meta charset="utf-8">
<title>таймер.музин.спейс</title>
<h1 id="title">До окончания подписки осталось:</h1>
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
	require_once 'timer/tools/iddb.class.php'; // Класс для работы с IDDB (текстовая база данных в одно поле)
	require_once 'timer/tools/timer.class.php'; // Класс для работы самого таймера
	require_once 'timer/tools/config.php'; // Файл конфигураций

	/* Создаем экземпляры классов */	
	$iddb = new IDDB;
	$timer = new TIMER($config);	

	/* Получаем значение таймера (в секундах) */
	$timer_value = $timer->getTime();
		
	/* Делаем проверку на отображение разных страниц */
	if($config['page_html']==1) {
		if($timer_value>0) { // проверяем активный ли таймер или время уже истекло
			if($config['page_on']=='') { die('Ошибка! Не задана страница с активным таймером!'); } // обработка ошибок
			$file = $config['page_on'];
		} else {
			if($config['page_off']=='') { die('Ошибка! Не задана страница с выключенным таймером!'); }
			$file = $config['page_off'];
		}
		if(eregi('.php$',$file) && !eregi('http:$',$file)) { // прореяем находится ли файл на текущем сервере и имеет ли расширение *.php
			// если файл на нашем сервере, используем буффер вывода для исполнения его кода
			ob_start(); // включаем буффер вывода
			require_once $file; // подключаем текущий файл
			$file = ob_get_contents(); // записываем результат выполенения
			ob_end_clean(); // очищаем буффер
		} else {
			// если файл на другом сервере, получаем его содержание
			$file = @file_get_contents($file);
		}
		echo $file; // выводим результат
		die(); // прерываем выполнение скрипта
	}
	
	/* Делаем проверку на включенный редирект при неактивном таймере */
	if($config['redirect_end']==1 && $timer_value<1) {
		if($config['redirect_url']=='') { die('Ошибка! Не указана ссылка для перенаправления!'); }
		header('Location: '.$config['redirect_url']); // делаем редирект на указанную страницу
		die(); // прерываем выполнение скрипта
	}
	
	/* Выводим стандартный код таймера */
	echo '<iframe id="timer_iframe" src="timer/iframe_timer.php" frameborder="0" style="width:100%;"></iframe>';
?>
<?php
	require_once "timer/tools/config.php";

	$fines = $config['fines'];
	// echo $fines['hours'];

	if($fines != 0) : ?>
		<div class="fines">
			<h3>Штраф:</h3>
			<p><?php echo $fines; ?>ч</p>
		</div>
	<?php endif; ?>
<img src="img/sub.jpg" id="sub">
<footer>
	<img src="img/high-voltage.png" width="20px" alt="⚡">pwrd by <a href="https://vk.com/id580474336">Себал Подёбович</a>
	<br>
	source code @ <a href="https://valerykoretsky.com/blog/sd-timer-v01/">http://valerykoretsky.com/</a>
	<br>
	also check <a href="https://soundcloud.com/m00zin/">https://soundcloud.com/m00zin/</a>
	<br><br>
	<h3>При поддержке свободной энциклопедии Республики Мухосранции <a href="https://wikimuzin.space">WikiMuzin</a> и <a href="http://jedimu.site">Форума Мухосранских Джедаев</a></h3>
	<br><br>
</footer>