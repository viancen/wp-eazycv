<?php
if(!function_exists('dd')){
	function dd($var, $exit = false){
		echo '<pre>';
		var_dump($var);
		echo '</pre>';
		if($exit) die();
	}
}