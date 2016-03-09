<?php
class System{

	# Retorna o valor do provider
	public static function getProvider($name){
		if(file_exists(__DIR__.'/Provider/'.$name.'.php')){
			include_once __DIR__.'/Provider/'.$name.'.php';
			$providerName = $methodParam.'Provider';
			$provider = $providerName();
			return $provider;
		}
	}

	# Atualiza o .htaccess ( necessário executar a cada login / logout )
	public static function updateAccess(){
		include ROOT_PATH.'/htaccess.php';
	}


	# Para a execução retornando o status e mensagem chamados na função
	public static function halt($code,$message=''){
		http_response_code($code);
		die($message);
	}

	# retorna o nome dos módulos instalados
	public static function getModules(){
		$db = System::getDB();
		return $db->module()->select('name');
	}

}
?>