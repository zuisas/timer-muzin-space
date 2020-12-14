
		<?php 
			/* */
			$host = strip_tags($_SERVER['HTTP_HOST']);
			if($host>'') { header('Location: /404'); }
			/* */ 
		?>