<?php
/**
* 	[ Module.php ]
*	Base para outros módulos
*	Considerações:
*	Métodos deverão ser criados de maneira estática.
* 	Métodos com iniciados com underline ('_') terão seus parametros compilados pelos providers e request.
**/

class Module
{

	public $includes = [];
	public function addExtend($class){
		$this->includes[] = $class;
	}

    public static function __callStatic($name, $arguments)
    {

    	$className = get_called_class();
		$module = R::findOne( 'module', ' name = ? ',[$className]);
		//var_dump($module);
		//$route = R::find( 'route', ' module_id = ? AND method = ? ',[$module['id'], $_GET['method']]);
		$route = $module->withCondition('method = ? AND type = ?', [@$_GET['method'], @$_SERVER['REQUEST_METHOD']])->ownRouteList;
		if(!count($route)){
			System::halt(400);
		}
		$route = reset($route);
		$AuthKey = @$_COOKIE['auth'];
		$lastTime = @$_COOKIE['lt'];
		$auth = R::findOne('auth',"token = ?", [$AuthKey]);



		###################### Verificar se o método existe #######################
    	$parsedParams = [];
    	$className = get_called_class();
    	if(method_exists($className,'_'.$name)){
			$method = new ReflectionMethod(get_called_class(), '_'.$name);
		}else{
			$moduleInstance = new $className();
			foreach ($moduleInstance->includes as $include) {
				if(method_exists($include, $name)){
					$method = new ReflectionMethod($include, $name);
				}elseif(method_exists($include, '_'.$name)){
					$method = new ReflectionMethod($include, '_'.$name);
				}
			}
		}
		if(!$method){
			System::halt(500,'');
		}

		############### Verificando o level do usuário #########################

		if(count($route->ownRuleList)){
			if(!count($auth->ownUser->withCondition('route_id = ?',[$route->id])->ownRuleList)){
				System::halt(401,'You Shall Not Pass!');
			}
		}

		#########################  Resolvendo URL da função   ##################

		$methodParams = $method->getParameters();
		foreach ($methodParams as $methodParam) {
			if($methodParam->name == '_url'){
				$urls = $methodParam->getDefaultValue()[$_SERVER['REQUEST_METHOD']];
				if(!is_array($urls)){$urls = array($urls);};
			}
			if($methodParam->name == '_middleware'){
				$middle = explode('::',$methodParam->getDefaultValue());
				$middle[0]::$middle[1];
			}
		}


    	# Resolvendo Parametros URI
    	$index = implode("",explode( "index.php", $_SERVER['SCRIPT_NAME']) );
		if($index != '/'){
			$uri = implode( "",explode( $index, $_SERVER['REQUEST_URI']) );
		}else{
			$uri = substr($_SERVER['REQUEST_URI'], 1);
		}
		$uriParams = explode('/',$uri);

		foreach ($urls as $url) {
			$urlParams = explode('/',$url);
			for($i=0;$i<count($urlParams);$i++){
				if($urlParams[$i] !== ""){
					if($urlParams[$i][0] == ":"){
						$paramName = implode("",explode(":",$urlParams[$i]));
						$parsedParams[$paramName] = @$uriParams[$i];
					}
				}
			}
		}

		# Resolvendo Dados passados por formulário
		$data = array();
		$postData = file_get_contents('php://input');
		$json = json_decode($postData);
		if($json){
			$data = $json;
		}else{
			parse_str($postData,$data);
		}
		foreach ($data as $key => $value) {
				$parsedParams[$key] = $value;
		}

		# Resolvendo arquivos passados por formulário
		if(count($_FILES)){
			foreach ($_FILES as $name => $file) {
				$parsedParams[$name] = $file;
			}
		}


		# Resolvendo Parametros Providers e Organizando os Parametros
		$methodParams = [];
		foreach($method->getParameters() as $methodParam){
			if(isset($parsedParams[$methodParam->name])){
				$methodParams[] = $parsedParams[$methodParam->name];
			}else{
				$provider = false;
				$providerName = $methodParam->name.'Provider';

				if(file_exists(__DIR__.'/Provider/'.$providerName.'.php')){
					if(!is_callable($providerName)){
						include_once __DIR__.'/Provider/'.$providerName.'.php';
					}
					$provider = $providerName();
				}
				if($provider){
					$methodParams[] = $provider;
				}else{
					$methodParams[] = NULL;
				}
			}
		};
		# Executando a Função
        $method->invokeArgs(null,$methodParams);
    }

    #instala o módulo no banco de dados, e atualiza o .htaccess
    public static function install()
    {
		$className = get_called_class();

		$module = R::findOne( 'module', ' name = ? ',[$className]);
		if(!$module)
		{
			$module = R::dispense( 'module' );
			$module['name'] = $className;
			R::store( $module );
		}


		$class = new ReflectionClass($className);
		$methods = $class->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_FINAL);
		$installStack = [];
		$installValidate = [];
		foreach ($methods as $method) {
			$params = $method->getParameters();
			$install_formula = '';
			$auth_formula = '';
			foreach ($params as $param) {
				if($param->getName() == "_url"){
					$install_formula = $param->getDefaultValue();
				}
			}
			if($install_formula !== ''){
				foreach ($install_formula as $type => $urls) {
					if(!is_array($urls)){
						$urls = array($urls);
					}
					foreach($urls as $url){
						$urlResult = Module::createUrl($url);
						$name = $method->getName();
						if($name[0] == '_'){
							$name =substr($name,1);
						}
						$installStack[] = array(
								"type" => $type,
								"url" => $url,
								"path" => $urlResult['path'],
								"method" => $name,
								"score" => $urlResult['score']
							);
						$installValidate[] = $urlResult['path'];
					}
				}
			}
		}

		$moduleInstance = new $className();
		foreach ($moduleInstance->includes as $include) {
			$class = new ReflectionClass($include);
			$methods = $class->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_FINAL);
			foreach ($methods as $method) {
			$params = $method->getParameters();
			$install_formula = '';
			$auth_formula = '';
			foreach ($params as $param) {
				if($param->getName() == "_url"){
					$install_formula = $param->getDefaultValue();
				}
			}
				if($install_formula !== ''){
					foreach ($install_formula as $type => $urls) {
						if(!is_array($urls)){
							$urls = array($urls);
						}
						foreach($urls as $url){
							$urlResult = Module::createUrl($url);
							$name = $method->getName();
							if($name[0] == '_'){
								$name =substr($name,1);
							}
							$installStack[] = array(
									"type" => $type,
									"url" => $url,
									"path" => $urlResult['path'],
									"method" => $name,
									"score" => $urlResult['score']
								);
							$installValidate[] = $urlResult['path'];
						}
					}
				}
			}
		}


		// [ Adicionar novas rotas ]
		foreach ($installStack as  $route) {
			$troute = R::dispense('route');
			$troute->import($route);
			$module->xownRouteList[] = $troute;
		}
		R::store($module);
	}

	public static function uninstall()
	{
		$className = get_called_class();
		$module = R::find( 'module', ' name = ? ',[$className]);
		R::trash($module);
	}

	public static function createUrl($url)
	{
		$score = 0; // Dita a preferencia da URL... quanto menos variáveis mais importante é a URL
		$urlParts = explode("/",$url);
		foreach ($urlParts as $key => $value) {
			if($value != ""){
				if($value[0] == ":"){
					$urlParts[$key] = "[^/]+";
					$score++;
				}
			}
		}
		$path = '^'.implode("/",$urlParts).'$';
		return array("path"=>$path,"score"=>$score);
	}
}
?>