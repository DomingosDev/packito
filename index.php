<?php

/**
* 	[ index.php ]
*	Arquivo de Inicialização
**/

# Includes .-
$benchmark_init_time = microtime();
include 'config.php';

# Autoload .-
spl_autoload_register(function($class_name){
	$folders = array(
		'Core',
		'Util',
		'Provider',
		"Modules/$class_name"
	);
	foreach ($folders as $folder) {
		if(file_exists(__DIR__."/$folder/$class_name.php")){
			include_once(__DIR__."/$folder/$class_name.php");
		}
	}
});



ob_start();

if(isset($_GET['module'] ) && isset($_GET['method'])){
	if(isset($_GET['param']) && ($_GET['method'] == 'getEditorScript' || $_GET['method'] == 'getAppScript')){
		System::$_GET['method']($_GET['param']);
	}else{
		$_GET['module']::$_GET['method']();
	}
}else{
	System::halt('400','');
}
if(@$_COOKIE['benchmark'] != 'true'){
	ob_end_flush();
}else{
	ob_end_clean();
	echo (microtime() - $benchmark_init_time)*1000 . 'ms';
}



?>