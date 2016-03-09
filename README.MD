# Packito framework
Um micro framework para criação de Api Rest em PHP / Apache de forma simples e modular

Funcionalidades:

* **Roteamento** - gerenciamento automático de rotas pelo .htaccess do apache, evitando que requests inválidas chegem ao php
* **Provedores** - provedores de informação baseados no Angular JS
* **Middlewares** - facilitadores das tarefas repetitivas
* **Extensível** - novos módulos são adicionados com apenas 1 comando!

## Conceito
Criado com foco na performance e modularização, as rotas são criadas em com base em classes com metódos estáticos, que ao serem executados resolvem suas próprias dependências.
Cada novo módulo deverá estar em uma nova pasta dentro de Modules com o nome da classe do módulo.

Exemplo de módulo:
```php

    // Dependendo do caso é necessário a insersão de um autoloader antes do módulo
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

        /*
            inicio de uma rota
            - toda rota iniciada com '_' terá os requerimentos processados antes da execução

        */
        public static function _sample(

            // configurações dessa rota:
            $_url = array( 'GET' => 'sample' ),

            // Esse método será chamado antes da execução do método
            $_middleware = 'Classe::Método',

            // Dependencias:
            $param1, $param2, $param3
        ){

            // Sua função aqui
            echo json_encode( $param1 + $param2 + $param3 );

        }
        // Fim da rota


        // É possivel extender classes para melhor organizar os arquivos do módulo
        public function __construct(){
            // A Classe alvo deve obedecer as mesmas regras de um módulo. porém não deve extender a classe Module
            $this->addExtend(new SampleController);
        }




    }
```


## Instalação
* Crie um novo schema no banco de dados
* Clone o reposiório
* Configure o banco no arquivo 'config.php'
* Rode 'php install.php' na pasta raiz
* É isso!

