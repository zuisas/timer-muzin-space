<?php
/*****************************
Разработчик: Валерий Корецкий
Сайт: http://spydec.com/
Продукт: Таймер/Счетчик обратного отсчета
Дата выпуска: 22.07.2014
Статус: Free
*****************************/

class IDDB { 
	/* Оглашения внутренних переменных класса */
	private $root = '/'; // абсолютный путь к корневому каталогу
	private $base = '/iddb/table.php'; // файл со списком идентификаторов ID
	private $path = '/iddb/value/'; // папка с файлами-значениями
	private $format = '.php'; // формат текстовых файлов в базе
	private $separator = '||'; // разделитель ID
	private $default_table = '
		<?php 
			/* */
			$host = strip_tags($_SERVER[\'HTTP_HOST\']);
			if($host>\'\') { header(\'Location: /404\'); }
			/* */ 
		?>';  // Стандартное значения файлов базы
		
	/* Конструктор класса */
	public function __construct() {
		$this->root = dirname( __FILE__ ); 
		$this->path = $this->root.$this->path;
		$this->base = $this->root.$this->base ;
		return false;
	}
	
	/* Поиск/наличие элемента в базе */
	public function search($id) { 
		$separator_id = $this->separator.$id.$this->separator;
		if(file_exists($this->base)) { // проверяем существования файла
			$file = fopen($this->base,"r"); // открытие файла для чтения
			$content = fread($file, filesize($this->base)); // считываем весь файл
			fclose($file);
			
			$search = strpos($content, $separator_id); // поиск требуемого ID
			if($search > 0) { return true; }
			
			unset($search);
			unset($content);
		}
		unset($separator_id); // удаляем переменную
		
		return false;
	}
	
	/* Запись в базу */
	public function write($id,$value) { 
		if(!$this->search($id)) {
			$separator_id = $this->separator.$id.$this->separator; // генерация ID для записи в файл идентификаторов
			$file = fopen($this->base,"a"); // открытие файла для дописывания 
			fwrite($file,$separator_id);
			fclose($file);
			
			$file = fopen($this->path.$id.$this->format,"w");
			fwrite($file,$this->default_table.$value);
			fclose($file);
			
			unset($separator_id);
		}
		
		return false;
	}
	
	/* Обновление значения в базе */
	public function update($id,$value) { 
		if($this->search($id)) { // проверяем существование файла
			$file = fopen($this->path.$id.$this->format,"w"); // открытие файла для записи 
			fwrite($file,$this->default_table.$value); // затераем текущее значение файла новыми данными
			fclose($file);
		}
		
		return false;
	}
	
	/* Чтение из базы */
	public function read($id,$time) {
		if($this->search($id)) { // проверяем существование файла
			$file = fopen($this->path.$id.$this->format,"r");
			$content = fread($file, filesize($this->path.$id.$this->format));
			$content = str_replace($this->default_table,'',$content);
			fclose($file);
			if((time()-$content)>$time) { $this->delete($id); }; // сравниваем входящие данные с содержимым файла, если текущее значение больше входящего, удаляем запись
			return $content;
		}
		
		return false;
	}
	
	/* Удаление элемента */
	public function delete($id) { 
		$separator_id = $this->separator.$id.$this->separator;
		$file = fopen($this->base,"r");
		$content = fread($file, filesize($this->base));
		fclose($file);
		
		$content = str_replace($separator_id,'',$content); // фильтруем полученные данные от защитного кода
		
		$file = fopen($this->base,"w");
		fwrite($file,$content);
		fclose($file);
		
		@unlink($this->path.$id.$this->format); // удаление файла
		
		unset($content);
		unset($separator_id);
		
		return false;
	}
	
	/* Очистка базы */
	public function clear() {
		$file = fopen($this->base,"w");
		fwrite($file,$this->default_table); // очищаем файл списка ID
		fclose($file);
		
		if($dir = opendir($this->path)) { // очистка директории
			while(false !== ($file = readdir($dir))) { 
				if ($file != "." && $file != "..") { 
					@unlink($this->path.$file); 
				} 
			}
			closedir($dir); 
		} 
		
		return false;
	}
} 

?>