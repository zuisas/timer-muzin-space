<?php
/*****************************
Разработчик: Валерий Корецкий
Сайт: http://valerykoretsky.com/
Продукт: Таймер/Счетчик обратного отсчета
Дата выпуска: 22.07.2014
Статус: Free
*****************************/

date_default_timezone_set("UTC+3"); // выставляем часовой пояс один для всех

/* Подключение необходимых файлов */
require_once 'tools/admin.config.php'; // файл конфигурации админпанели
require_once 'tools/config.php'; // файл конфигураций таймера
require_once 'tools/iddb.class.php'; // класс для работы с внутренней базой данных
$iddb = new IDDB; // создаем экземпляр класса

/* Подключение необходимых файлов */
session_start(); // запуск сессии
$login = strip_tags($_SESSION['login']); // логин
$pass = strip_tags($_SESSION['pass']); // пароль
$do = trim(strip_tags($_REQUEST['do']));

/* Функции для подсчета времени и генерации полей */	
function time_left($type,$time) { // перевод данных из секунд в дни/часы/минуты
	if($type == "d") {
		$time = intval($time/86400); // количество дней
	} elseif($type == "h") {
		$time = intval(($time-86400*time_left("d",$time))/3600); // количество часов
	} elseif($type == "h3") {
		$time = intval(($time-86400*time_left("d",$time))/3600)+3; // количество часов UTC+3
	} elseif($type == "m") {
		$time = intval(($time-86400*time_left("d",$time)-3600*time_left("h",$time))/60); // количество минут
	} elseif($type == "s") {
		$time = intval($time-86400*time_left("d",$time)-3600*time_left("h",$time)-60*time_left("m",$time)); // количество секунд
	} 
	if($time<0) { $time = 0; }
	return $time;
}

function time_select($name,$time,$type,$max_value) { // генерируем поля формы
	$result = '<label><select name="'.$name.'">';
	for($i=0;$i<$max_value;$i++) {
		$minute = time_left($type,$time);
		$selected = '';
		if($i == $minute) { $selected = 'selected'; }
		if($i<10) { $i = '0'.$i; }
		$result .= '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
	}	
	$result .= '</select></label>';
	return $result;
}

function set_time($d,$h,$m,$s) { // перевод времени в секунды
	$result = $d*86400+$h*3600+$m*60+$s;
	return $result;
}

/* Авторизация в админпанеле */
if(isset($_POST['pass']) && isset($_POST['login'])) { // проверяем наличие POST данных
	$login = str_replace("'","",trim(strip_tags($_POST['login']))); // удаляем одинарные кавычки
	$pass = str_replace("'","",trim(strip_tags($_POST['pass'])));
	if($login>'' && $pass>'') {
		if($do == 'register') { // проверяем, не проходит ли пользователь регистрацию
			$admin = array('login' => $login, 'pass' => $pass);
			$admin_html = <<<HTML
				<?php
					\$admin = array('login' => '$login', 'pass' => '$pass');
				?>
HTML;
			$file = fopen(dirname( __FILE__ ).'/tools/admin.config.php',"w"); // при регистрации записываем данные для авторизации в файл настроек
			fwrite($file,trim($admin_html));
			fclose($file);
			$admin = array('login' => $login, 'pass' => $pass);
		}
		$_SESSION['login'] = $login; // сохраняем данные в сессию
		$_SESSION['pass'] = $pass;
	}
}

/* Подключение необходимых файлов */
if(!isset($admin['login'])) { // проверяем, существует ли логин администратора, если нету - выдаем форму для создания
	$html = <<<HTML
		<div class="register">
			<h1>Создаем пользователя для доступа в<br /> Admin Panel</h1>
			<div class="form auth-panel">
				<form action="" method="post" encrypt="text/plain">
					<input type="text" name="login" maxlegth="50" value="" placeholder="Введите логин" />
					<input type="password" name="pass" maxlegth="50" value="" placeholder="Введите пароль" />
					<input type="hidden" name="do" value="register" />
					<input type="submit" value="Создать" class="submit-button margintop" />			
				</form>
			</div>
		</div>
HTML;
} elseif($admin['login']!=$login || $admin['pass']!=$pass) { // проверяем входящие данные
	$html = <<<HTML
		<div class="register">
			<h1>Вход в Admin Panel</h1>
			<div class="form auth-panel">
				<form action="" method="post" encrypt="text/plain">
					<input type="text" name="login" maxlegth="50" value="" placeholder="Введите логин" />
					<input type="password" name="pass" maxlegth="50" value="" placeholder="Введите пароль" />
					<input type="hidden" name="do" value="auth" />
					<input type="submit" value="Войти" class="submit-button margintop" />			
				</form>
			</div>
		</div>
HTML;
} elseif($do=='exit') { // выход из админпанели
	unset($_SESSION['login']);
	unset($_SESSION['pass']);
	session_destroy();
	header('Location: admin.php');
} elseif($do=='setting') { // обработка отправленной формы
	$hour_value = (int) $_POST['hour_value'];
	$minute = (int) $_POST['minute'];
	$second = (int) $_POST['second'];
	
	$time_day = (int) $_POST['time_day'];
	$time_hour = (int) $_POST['time_hour'];
	$time_minute = (int) $_POST['time_minute'];
	$time_second = (int) $_POST['time_second'];
	
	$cookie_day = (int) $_POST['cookie_day'];
	$cookie_hour = (int) $_POST['cookie_hour'];
	$cookie_minute = (int) $_POST['cookie_minute'];
	$cookie_second = (int) $_POST['cookie_second'];
	
	$special_time_hour = (int) $_POST['special_time_hour'];
	$special_time_minute = (int) $_POST['special_time_minute'];
	$special_time_second = (int) $_POST['special_time_second'];
	
	$day_month = (int) $_POST['day_month'];
	
	$day_week = trim(strip_tags($_POST['day_week']));
	$type = trim(strip_tags($_POST['type']));
	$date_left = trim(strip_tags($_POST['date_left']));
	$special_type = trim(strip_tags($_POST['special_type']));
	$redirect_url = trim(strip_tags($_POST['redirect_url']));
	$page_on = trim(strip_tags($_POST['page_on']));
	$page_off = trim(strip_tags($_POST['page_off']));
	$template = trim(strip_tags($_POST['template']));
	$language = trim(strip_tags($_POST['language']));
	
	$cookie = isset( $_POST['cookie'] ) ? intval( $_POST['cookie'] ) : 0;
	$ip = isset( $_POST['ip'] ) ? intval( $_POST['ip'] ) : 0;
	$redirect_end = isset( $_POST['redirect_end'] ) ? intval( $_POST['redirect_end'] ) : 0;
	$page_html = isset( $_POST['page_html'] ) ? intval( $_POST['page_html'] ) : 0;
	
	$w = isset( $_POST['w'] ) ? intval( $_POST['w'] ) : 0;
	$d = isset( $_POST['d'] ) ? intval( $_POST['d'] ) : 0;
	$h = isset( $_POST['h'] ) ? intval( $_POST['h'] ) : 0;
	$i = isset( $_POST['i'] ) ? intval( $_POST['i'] ) : 0;
	$s = isset( $_POST['s'] ) ? intval( $_POST['s'] ) : 0;
	
	$st = strtotime($date_left);
	
	$date_left = strtotime($date_left)+set_time(0,$hour_value,$minute,$second);
	$time_left = set_time($time_day,$time_hour,$time_minute,$time_second);
	$special_time = set_time(0,$special_time_hour,$special_time_minute,$special_time_second);
	$repeattime = set_time($cookie_day,$cookie_hour,$cookie_minute,$cookie_second);

	$fines = (int) $_POST['fines'];
	
	$config['number']++;
	
	$config_code = <<<PHP
<?php

\$config = array(
	'type' 			=> '{$type}', /* date OR time, OR special */
	'cookie' 		=> {$cookie}, /* 1 - On, 0 - Off */
	'ip' 			=> {$ip}, /* 1 - On, 0 - Off */
	'repeat' 		=> 1, /* 1 - On, 0 - Off */
	'repeattime' 	=> {$repeattime}, /* repeattime cookie or ip (sec) */
	'timezone'	 	=> '+0', /* repeattime cookie or ip (sec) */
	
	'special_type'	=> {$special_type}, /* 1 - day, 2 - week, 3 - month */
	'special_time'	=> {$special_time}, /*  */
	'special_day'	=> '{$day_week}', /*  */
	'special_date'	=> '{$day_month}', /*  */
	
	'time_left'		=> {$time_left}, /* time type left (sec) */
	'date_left'		=> {$date_left}, /* Unix time */
	'template'		=> '{$template}', /*  */
	'blockvisible' 	=> '{$w}/{$d}/{$h}/{$i}/{$s}', /* w/d/h/i/s */
	'language' 		=> '{$language}', /* y/m/w/d/h/m/s */
	
	'redirect_end'	=> {$redirect_end}, /*  */
	'redirect_url'	=> '{$redirect_url}', /*  */
	
	'page_html'		=> {$page_html}, /*  */
	'page_on'		=> '{$page_on}', /*  */
	'page_off'		=> '{$page_off}', /*  */
	
	'number'		=> {$config['number']}, /* for cookie name */

	'fines'			=> {$fines}, /* */
);

?>
PHP;
	
	$iddb->clear(); // очищаем ранее сохраненные данные
		
	$file = fopen(dirname( __FILE__ ).'/tools/config.php',"w");
	fwrite($file,trim($config_code));
	fclose($file);
	header('Location: admin.php');
	
} else { // генерация формы редактирования таймера
	$day_value = array('','Monday','Tuesday','Wednesday','Thursday','Frider','Saturday','Sunday');
	$day_value_ru = array('','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота','Воскресенье');
	$day_form = '<select name="day_week">';
	for($i=1;$i<8;$i++) {
		$selected = '';
		if($day_value[$i] == $config['special_day']) { $selected = 'selected'; }
		$day_form .= '<option value="'.$day_value[$i].'" '.$selected.'>'.$day_value_ru[$i].'</option>';
	}
	$day_form .= '</select>';
	
	$day_month_form = '<label><select name="day_month">';
	for($i=1;$i<32;$i++) {
		$selected = '';
		if($i == $config['special_date']) { $selected = 'selected'; }
		$day_month_form .= '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
	}
	$day_month_form .= '</select></label>';
	
	$time_form = time_select("hour_value",$config['date_left'],"h3",24);
	$time_form .= time_select("minute",$config['date_left'],"m",60);
	$time_form .= time_select("second",$config['date_left'],"s",60);
		
	$time_form_2 = time_select("time_day",$config['time_left'],"d",50);
	$time_form_2 .= time_select("time_hour",$config['time_left'],"h",24);
	$time_form_2 .= time_select("time_minute",$config['time_left'],"m",60);
	$time_form_2 .= time_select("time_second",$config['time_left'],"s",60);
	
	$time_form_3 = time_select("cookie_day",$config['repeattime'],"d",50);
	$time_form_3 .= time_select("cookie_hour",$config['repeattime'],"h",24);
	$time_form_3 .= time_select("cookie_minute",$config['repeattime'],"m",60);
	$time_form_3 .= time_select("cookie_second",$config['repeattime'],"s",60);
	
	$time_form_4 = time_select("special_time_hour",$config['special_time'],"h",24);
	$time_form_4 .= time_select("special_time_minute",$config['special_time'],"m",60);
	$time_form_4 .= time_select("special_time_second",$config['special_time'],"s",60);

	/*  */
	$checked = array('','checked');
	
	$checked_type = array();
	$checked_type[$config['type']] = 'selected';
	
	$checked_template = array();
	$checked_template[$config['template']] = 'selected';
	
	$checked_lang = array();
	$checked_lang[$config['language']] = 'selected';
	
	$checked_special_type = array();
	$checked_special_type[$config['special_type']] = 'selected';
	
	$date_left_date = date('d.m.Y',$config['date_left']);
	//$date_left_date = date('m.d.Y',$config['date_left']);
	
	$day_config = explode('/',$config['blockvisible']);
	
	$template_array = '';
	$dir = 'themes/';
	$files = glob($dir."*",GLOB_ONLYDIR);
	foreach($files as $file) {
		$file = str_replace($dir,'',$file);
		$template_array .= '<option value="'.$file.'" '.$checked_template[$file].'>'.$file.'</option>';
	}
	
	$html = <<<HTML
		<div class="register">
			<h1>Настройка таймера</h1>
			<div class="form">
				<form action="" method="post" encrypt="text/plain">
					<fieldset>
						<legend>Настройка времени</legend>
						<div class="form-element long select">
							<span class="label">Тип таймера</span> 
							<div class="time-block select">
								<label>
									<select name="type" id="type_select">
										<option value="date" {$checked_type['date']}>Отсчет к дате</option>
										<option value="time" {$checked_type['time']}>Отсчет времени</option>
										<option value="special" {$checked_type['special']}>Специальный</option>
									</select>
								</label>
							</div>
						</div>
						<!-- type date -->
						<div class="form-element block_type_date">
							<span class="label">Укажите дату</span> 
							<input type="text" id="date_left" name="date_left" value="{$date_left_date}" />
						</div>
						<!-- special type -->
						<div class="form-element block_type_special long">
							<span class="label">Тип отсчета</span> 
							<div class="time-block select">
								<label>
									<select name="special_type" id="special_select">
										<option value="1" {$checked_special_type[1]}>до указаного времени</option>
										<option value="2" {$checked_special_type[2]}>до указаного дня недели</option>
										<option value="3" {$checked_special_type[3]}>до указаного дня месяца</option>
									</select>
								</label>
							</div>
						</div>
						<!-- special -->
						<div class="form-element block_type_special block_special_2 long">
							<span class="label">Выберите день недели</span> 
							<div class="time-block select">
								<label>
									{$day_form}
								</label>
							</div>
						</div>
						<!-- special -->
						<div class="form-element block_type_special block_special_3">
							<span class="label">Выберите число месяца</span> 
							<div class="time-block select">
								{$day_month_form}
							</div>
						</div>
						<!-- type date -->
						<div class="form-element block_type_date">
							<span class="label">Выберите время (часы/минуты/секунды)</span> 
							<div class="time-block select">
								{$time_form}
							</div>
						</div>
						<!-- type special -->
						<div class="form-element block_type_special">
							<span class="label">Выберите время (часы/минуты/секунды)</span> 
							<div class="time-block select">
								{$time_form_4}
							</div>
						</div>
						<!-- type time -->
						<div class="form-element block_type_time">
							<span class="label">Выберите время (дни/часы/минуты/секунды)</span> 
							<div class="time-block select">
								{$time_form_2}
							</div>
						</div>
						<div class="form-element not_block_type_date">
							<div class="checkbox">
								<input type="checkbox" name="cookie" id="cookie" value="1" {$checked[$config['cookie']]} />
								<label for="cookie"></label>
							</div>
							<span class="label">Использовать куки</span> 
							<div class="clear"></div>
						</div>
						<div class="form-element not_block_type_date">
							<div class="checkbox">
								<input type="checkbox" name="ip" id="ip" value="1" {$checked[$config['ip']]} />
								<label for="ip"></label>
							</div>
							<span class="label">Использовать IP пользователя</span> 
							<div class="clear"></div>
						</div>
						<div class="form-element not_block_type_date">
							<span class="label">Через какое время пользователю включайть таймер сначала? (дни/часы/минуты/секунды)</span> 
							<div class="time-block select">
								{$time_form_3}
							</div>
						</div>
						
					</fieldset>
					<fieldset>
						<legend>Настройка страниц</legend>
						<div class="form-element">
							<div class="checkbox">
								<input type="checkbox" name="redirect_end" id="redirect_end" value="1" {$checked[$config['redirect_end']]} />
								<label for="redirect_end"></label>
							</div>
							<span class="label">Перенаправить пользователя по завершению таймера на другую страницу</span>
							<div class="clear"></div>
						</div>
						<div class="form-element">
							<span class="label">Укажите ссылку, куда перенаправить пользователя</span>
							<input type="text" name="redirect_url" value="{$config['redirect_url']}" />
						</div>
						<div class="form-element">
							<div class="checkbox">
								<input type="checkbox" name="page_html" id="page_html" value="1" {$checked[$config['page_html']]} />
								<label for="page_html"></label>
							</div>
							<span class="label">Отображать разное содержания для страницы с активным таймером и после его окончания</span>
							<div class="clear"></div>
						</div>
						<div class="form-element">
							<span class="label">Укажите ссылку на страницу с таймером</span>
							<input type="text" name="page_on" value="{$config['page_on']}" />
						</div>
						<div class="form-element">
							<span class="label">Укажите ссылку на страницу без таймера (после окончания)</span>
							<input type="text" name="page_off" value="{$config['page_off']}" />
						</div>
					</fieldset>
					<fieldset>
						<legend>Настройка внешнего вида</legend>
						<div class="form-element long select">
							<span class="label">Выберите шаблон</span> 
							<div class="time-block select">
								<label>
									<select name="template">
										{$template_array}
									</select>
								</label>
							</div>
						</div>
						<div class="form-element long select">
							<span class="label">Язык таймера</span> 
							<div class="time-block select">
								<label>
									<select name="language">
										<option value="English" {$checked_lang['English']}>English</option>
										<option value="Russian" {$checked_lang['Russian']}>Русский</option>
										<option value="Ukrainian" {$checked_lang['Ukrainian']}>Українська</option>
									</select>
								</label>
							</div>
						</div>
						<div class="form-element">
							<span class="label">Какие цифро-блоки показывать</span> 
							<div class="checkbox-list">
								<div class="checkbox-block">
									<div class="checkbox">
										<input type="checkbox" value="1" name="w" id="w" {$checked[$day_config['0']]} />
										<label for="w"></label>
									</div>
									недель 
								</div>
								<div class="checkbox-block">
									<div class="checkbox">
										<input type="checkbox" value="1" name="d" id="d" {$checked[$day_config['1']]} />
										<label for="d"></label>
									</div>
									дней 
								</div>
								<div class="checkbox-block">
									<div class="checkbox">
										<input type="checkbox" value="1" name="h" id="h" {$checked[$day_config['2']]} />
										<label for="h"></label>
									</div>
									часов 
								</div>
								<div class="checkbox-block">
									<div class="checkbox">
										<input type="checkbox" value="1" name="i" id="i" {$checked[$day_config['3']]} />
										<label for="i"></label>
									</div>
									минут 
								</div>
								<div class="checkbox-block">
									<div class="checkbox">
										<input type="checkbox" value="1" name="s" id="s" {$checked[$day_config['4']]} />
										<label for="s"></label>
									</div>
									секунд 
								</div>
								<div class="clear"></div>
							</div>
						</div>
					</fieldset>
					<fieldset>
						<legend>Настройка штрафов</legend>
						<div class="form-element">
							<span class="label">Укажите кол-во часов (целое число)</span>
							<input type="number" name="fines" id="fines" value="{$config['fines']}" />
						</div>
					</fieldset>

					<input type="hidden" name="do" value="setting" />
					<input type="submit" value="Сохранить" class="submit-button " />
					
					<!-- Preview in version 2.0 -->
					<!--<div class="preview">
						<div class="preview-title">Предварительный просмотр</div>
						<div class="preview-value">
						
						</div>
					</div>-->
				</form>
			</div>
		</div>
HTML;
}

?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>Admin Panel muzin.space</title>

<link rel="stylesheet" href="//code.jquery.com/ui/1.11.0/themes/smoothness/jquery-ui.css">
<link rel="stylesheet" href="static/admin/css/style.css">
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.0/jquery-ui.js"></script>
<script src="static/admin/js/jquery.ui.datepicker-ru.js"></script>

<script src="http://static.spydec.com/tools/js/timer.v.0.1.js"></script>
<script>
	$(function() {
		$("#date_left").datepicker({regional:"fr",dateFormat:'dd.mm.yy'});
	});
</script>
<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
</head>
<body>
<div class="container">
	<div class="content">
		<?php echo $html; ?>
		<footer>
			<div class="copy">
				 <a href="readme.html" target="blank">Документация</a> | &copy; <?php echo date("Y"); ?> <a href="http://muzin.space" target="blank">muzin.space</a>
			</div>
		</footer>
	</div>
</div>
<div class="exit"><a href="admin.php?do=exit">x</a></div>
<script src="static/admin/js/functions.js"></script>
<script>timer_type_change('<?php echo $config['type']; ?>',<?php echo $config['special_type']; ?>);</script>
</body>
</html>