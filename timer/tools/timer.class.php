<?php
/*****************************
Разработчик: Валерий Корецкий
Сайт: http://spydec.com/
Продукт: Таймер/Счетчик обратного отсчета
Дата выпуска: 22.07.2014
Статус: Free
*****************************/

class TIMER {
	/* Оглашения внутренних переменных класса */
	private $default_ip = '127.0.0.1'; // стандартное значение IP адреса
	private $array_ip_var = array('HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_CLUSTER_CLIENT_IP','HTTP_FORWARDED_FOR','HTTP_X_FORWARDED','HTTP_FORWARDED','HTTP_VIA', 'HTTP_X_COMING_FROM','HTTP_X_COMING_FROM','HTTP_COMING_FROM','HTTP_X_REAL_IP','HTTP_CLIENT_IP','HTTP_PROXY_CONNECTION','REMOTE_ADDR');  // массив переменных для определения IP пользователя
	private $cookie_name = '_tv_v'; // препикс имен cookie
	private $config = array(); // массив конфигураций таймера
	
	/* Конструктор класса */
	public function __construct($config) {
		$this->config = $config;
		$this->cookie_name .= $config['number']; // генерации имени cookie
		return false;
	}
	
	/* Определение IP пользователя */
	public function getIP() {
		foreach($this->array_ip_var AS $var) { // обход массив с индексами
			$ip = $_SERVER[$var];
			if(isset($ip) && $ip!=$this->default_ip) { // если IP существует и отличный от стандартного, возвращаем его
				return $ip;
			}
		}
		
		return false;
	}
	
	/* Получения значения cookie */
	public function getCookie() {
		$cookie = (int) $_COOKIE[$this->cookie_name]; // считывания cookie
		if($cookie > 0) { return $cookie; }
		
		return false;
	}
	
	/* Оглашение cookie */
	public function setCookie($value,$time,$path = "/") {
		setcookie($this->cookie_name, $value, time()+$time, $path); // создание cookie
		
		return false;
	}
	
	/* Функция для редиректа */
	public function redirect($link) {
		header('Location: '.$link);
		
		return false;
	}
	
	/* Получение текущего значения таймера */
	public function getTime() {
		global $iddb; // подключение переменной вне класса
		$ip = $this->getIP(); // определение IP пользователя
		$time = time(); // текущее UNIX-время
		$time_all = $time; // дублирующая переменная
		if($this->config['type']=='date') { // определяем тип таймера
			$time = $this->config['date_left'] - $time; 
		} elseif($this->config['type']=='time') {
			$time = $this->config['time_left'];
			$time_cookie = 'none';
			$time_ip = 'none';
			if($this->config['cookie']) { // проверяем включены ли куки
				$cookie = $this->getCookie();
				if($cookie) {
					$time_cookie = $time-($time_all-$cookie); // результующее время равно разнице конфигурационному времени и текущего времени без значения cookie
				} else {
					$time_cookie = $time;
					$this->setCookie($time_all,$this->config['repeattime']+$time_cookie);
				}
			}
			if($this->config['ip']) { // проверяем включена ли проверка IP
				$ip_value = $iddb->read($ip,$this->config['repeattime']+$time);
				if($ip_value) {
					$time_ip = $time-($time_all-$ip_value);
				} else {
					$time_ip = $time;
					$iddb->write($ip,$time_all); // запись времения в базу с идентификатор IP пользователя
					$this->setCookie($time_all,$this->config['repeattime']+$time_ip);
				}
			}
			if($time_ip != 'none') {
				$time = $time_ip;
			} elseif($time_cookie != 'none') {
				$time = $time_cookie;
			} else {
				$time = $this->config['time_left']; // если проверка cookie и IP отключена, выводим установленное время
			}
		} elseif($this->config['type']=='special') {
			if($this->config['special_type']==1) { // определяем тип специального таймера
				$time_this = mktime(0, 0, 0, date("n"), date("d"), date("Y"));
				$repeat = 86400; // 86000 сек = 24 часа, период таймера
				$time_end = $time_this+$this->config['special_time'];
				if($time_end<$time) { // если время в текущем периоде уже закончилось, переходим к следующему периоду
					$time_end += $repeat;
				}
			} elseif($this->config['special_type']==2) {
				$time_end = strtotime("next ".$this->config['special_day'])+$this->config['special_time'];
			} elseif($this->config['special_type']==3) {
				$day_count = date("t");
				if($day_count>$this->config['special_date']) { // проверка текущего дня месяца
					$this_date = $this->config['special_date'];
				} else {
					$this_date = $day_count;
				}
				$time_this = mktime(0, 0, 0, date("n"), $this_date, date("Y"));
				$time_end = $time_this+$this->config['special_time'];
				if($time_end<$time) { 
					$next_month = mktime(0, 0, 0, date("n")+1, $this_date, date("Y")); // UNIX-время полуночи следующего месяца
					$day_count = date("t",$next_month);
					if($day_count>$this->config['special_date']) {
						$this_date = $this->config['special_date'];
					} else {
						$this_date = $day_count;
					}
					$time_this = mktime(0, 0, 0, date("n")+1, $this_date, date("Y"));
					$time_end = $time_this+$this->config['special_time'];
				}
			}
			$time_cookie = 'none';
			$time_ip = 'none';
			if($this->config['cookie']) {
				$cookie = $this->getCookie();
				if($cookie) {
					$time_cookie = $cookie-$time_end;
				} else {
					$time_cookie = $time_end-$time;
					$this->setCookie($time_end,$this->config['repeattime']+$time_cookie);
				}
			}
			if($this->config['ip']) {
				$ip_value = $iddb->read($ip,$this->config['repeattime']+$time_end-$time_all);
				if($ip_value) {
					$time_ip = $ip_value-$time_end;
				} else {
					$time_ip = $time_end-$time;
					$iddb->write($ip,$time_end);
					$this->setCookie($time_end,$this->config['repeattime']+$time_ip); // время жизни файла равняется сумме оставшегося времени и указаному времени в настройках
				}
			}
			if($time_ip != 'none') {
				$time = $time_ip;
			} elseif($time_cookie != 'none') {
				$time = $time_cookie;
			} else {
				$time = $time_end-$time;
			}
		} else {
			$time = false; // возвращаем false, если возникла ошибка и тип таймера обрабатывается неправильно
		}
		
		return $time;
	}
}

?>