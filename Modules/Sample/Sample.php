<?php
	spl_autoload_register(function($class_name){
			$folders = array(
				'controller',
				'middleware'
				);
			foreach ($folders as $folder) {
				if(file_exists(__DIR__."/$folder/$class_name.php")){
					include_once(__DIR__."/$folder/$class_name.php");
				}
			}
		});

		class Sample extends Module{

		public function __construct(){
			$this->addExtend(new SampleController);
		}

	}
?>