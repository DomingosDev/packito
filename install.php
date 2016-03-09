<?php
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

R::wipe( 'route' );
foreach (new DirectoryIterator('Modules') as $fileInfo) {
    if($fileInfo->isDot()) continue;
    if(!$fileInfo->isDir()) continue;
    $cn = $fileInfo->getFilename();
    echo "Installing $cn module\n";
    $n = $cn::install();
    echo "$n routes installed\n";
}

include_once 'htaccess.php';

?>