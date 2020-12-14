<?php

$config = array(
	'type' 			=> 'date', /* date OR time, OR special */
	'cookie' 		=> 0, /* 1 - On, 0 - Off */
	'ip' 			=> 0, /* 1 - On, 0 - Off */
	'repeat' 		=> 1, /* 1 - On, 0 - Off */
	'repeattime' 	=> 4298400, /* repeattime cookie or ip (sec) */
	'timezone'	 	=> '+0', /* repeattime cookie or ip (sec) */
	
	'special_type'	=> 2, /* 1 - day, 2 - week, 3 - month */
	'special_time'	=> 64800, /*  */
	'special_day'	=> 'Frider', /*  */
	'special_date'	=> '15', /*  */
	
	'time_left'		=> 79200, /* time type left (sec) */
	'date_left'		=> 1598386920, /* Unix time */
	'template'		=> 'circle', /*  */
	'blockvisible' 	=> '0/1/1/1/1', /* w/d/h/i/s */
	'language' 		=> 'Russian', /* y/m/w/d/h/m/s */
	
	'redirect_end'	=> 0, /*  */
	'redirect_url'	=> '', /*  */
	
	'page_html'		=> 0, /*  */
	'page_on'		=> 'index_on.html', /*  */
	'page_off'		=> 'index_off.html', /*  */
	
	'number'		=> 37, /* for cookie name */

	'fines'			=> 9999999999, /* */
);

?>