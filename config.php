<?php
/**
*	[ Config.php ]
*	Arquivo de configuração!
*	incluir apenas definições de variáveis e constantes
**/

// [ISSO NÃO DEVERIA ESTAR AQUI]
// TODO: encontrar lugar apropriado para inicialização do banco de dados.
include 'vendor/rb.php';
include 'Util/UUIDWriterMySQL.php';
R::setup( 'mysql:host=localhost;dbname=kanban;port=3306', 'producao', '' );
$oldToolBox = R::getToolBox();
$oldAdapter = $oldToolBox->getDatabaseAdapter();
$uuidWriter = new UUIDWriterMySQL( $oldAdapter );
$newRedBean = new RedBeanPHP\OODB( $uuidWriter );
$newToolBox = new RedBeanPHP\ToolBox( $newRedBean, $oldAdapter, $uuidWriter );
R::configureFacadeWithToolbox( $newToolBox );


define('ROOT_PATH',__DIR__);
?>