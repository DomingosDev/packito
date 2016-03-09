
<?php
# ------------------------------------------------------------------------------
# | Template do .HTACCESS                                                       |
# ------------------------------------------------------------------------------
# TODO: organizar essa bagaça
  ob_start();
  //$db = System::getDB();
  $routes = [];

  $routeBase = R::findAll( 'route' , ' ORDER BY score DESC' );
  foreach ($routeBase as $route) {
    $routes[$route['type']][] = array(
      'path'=>$route['path'],
      'method'=>$route['method'],
      'module'=>$route->module['name']
      );
  }
  if(!isset($routes['GET'])){
    $routes['GET'] = array();
  }
?>

<IfModule mod_suphp.c>
  suPHP_ConfigPath /home/username
  AddHandler application/x-httpd-php55 .php
  <Files php.ini>
    order allow,deny
    deny from all
  </Files>
</IfModule>

Header set Access-Control-Allow-Origin *
Header set Access-Control-Allow-Headers Content-Type

<?php
# ------------------------------------------------------------------------------
# | Acesso, Praticamente o Core do framework                                    |
# ------------------------------------------------------------------------------
?>
<IfModule mod_rewrite.c>
  RewriteEngine On

  RewriteRule \.(png|gif|jpe?g|svg) - [E=folder:images]
  RewriteRule \.(wav|mp3) - [E=folder:audio]
  RewriteRule \.(woff|svg|ttf|eot) - [E=folder:fonts]
  RewriteRule \.(html) - [E=folder:views]
  RewriteRule \.(css) - [E=folder:css]
  RewriteRule \.(js) - [E=folder:js]
  RewriteRule .? - [E=cityFolder:FALSE]


  RewriteCond %{REQUEST_METHOD} '!GET'
  RewriteRule .? - [S=<?=11+count($routes['GET'])?>]
      #  GET:

      RewriteRule ^static\/(.*)\.n=(.*)\.(.*) static/$1.$3 [L]
      RewriteRule ^static\/(.*)\/(.*)$ - [E=modulo:$1,E=arquivo:$2]
      RewriteRule ^static\/(.*)\/(.*)\.(.*)\.html$ - [E=modulo:$1,E=pasta:$2,E=arquivo:$3]
      RewriteRule ^static\/(.*)\/(.*)\.(.*)\.html$ Modules/%{ENV:modulo}/Assets/views/%{ENV:pasta}/%{ENV:cityFolder}/%{ENV:arquivo}.html
      RewriteCond %{REQUEST_FILENAME} !-f
      RewriteRule ^Modules\/(.*)\/Assets\/views\/(.*)\/(.*)\/(.*).html$  Modules/%{ENV:modulo}/Assets/views/%{ENV:pasta}/%{ENV:arquivo}.html

      <?php
        #TODO: Melhorar a dinâmica das regras e incluir outros formatos de arquivos para pastas de upload
      ?>

      RewriteCond %{REQUEST_FILENAME} !-f
      RewriteRule ^static\/(.*)\/(.*) Modules/$1/Assets/%{ENV:folder}/%{ENV:cityFolder}/$2
      RewriteCond %{REQUEST_FILENAME} !-f
      RewriteRule ^Modules\/(.*)\/Assets\/(.*)\/(.*)\/(.*) Modules/$1/Assets/%{ENV:folder}/$4

      RewriteCond %{REQUEST_FILENAME} !-f
      RewriteCond %{ENV:folder} images
      RewriteRule ^Modules\/(.*)\/Assets\/images\/(.*)$ Modules/%{ENV:modulo}/Upload/temp/%{ENV:arquivo}
      RewriteCond %{REQUEST_FILENAME} !-f
      RewriteRule ^Modules\/(.*)\/Upload\/temp\/(.*)$ Modules/%{ENV:modulo}/Upload/%{ENV:arquivo}


      RewriteRule ^(.*)\/editor.js$ index.php?module=System&method=getEditorScript&param=$1
      RewriteRule ^(.*)\/app.js$ index.php?module=System&method=getAppScript&param=$1


      # Instalação de Módulos ( Dinamicamente criado )

      <?php
        foreach($routes['GET'] as $route){
      ?>
RewriteRule <?=$route['path']?> index.php?module=<?=$route['module']?>&method=<?=$route['method']?> [QSA,L]
      <?php
        }
        unset($routes['GET']);
      ?>


  <?php
    foreach($routes as $method => $routeArray){
      ?>
      RewriteCond %{REQUEST_METHOD} '!<?=$method?>'
      RewriteRule .? - [S=<?=count($routeArray)?>]
        <?php
          foreach ($routeArray as $route) {
        ?>
        RewriteRule <?=$route['path']?> index.php?module=<?=$route['module']?>&method=<?=$route['method']?> [QSA,L]
        <?php
          }
    }
  ?>

</IfModule>

# Mudando os templates de erro padrões
#ErrorDocument 404 " "
#ErrorDocument 403 " "

# Não permitir listar diretórios
<IfModule mod_autoindex.c>
    Options -Indexes
</IfModule>

<?php
# ------------------------------------------------------------------------------
# | MIME TYPES                                                                  |
# ------------------------------------------------------------------------------
?>
<IfModule mod_mime.c>

  # Audio
    AddType audio/mp4                                   m4a f4a f4b
    AddType audio/ogg                                   oga ogg

  # JavaScript
    AddType application/javascript                      js jsonp
    AddType application/json                            json

  # Video
    AddType video/mp4                                   mp4 m4v f4v f4p
    AddType video/ogg                                   ogv
    AddType video/webm                                  webm
    AddType video/x-flv                                 flv

  # Font
    AddType application/font-woff                       woff
    AddType application/vnd.ms-fontobject               eot
    AddType application/x-font-ttf                      ttc ttf
    AddType font/opentype                               otf
    AddType     image/svg+xml                           svg svgz
    AddEncoding gzip                                    svgz

  # Outros
    AddType application/octet-stream                    safariextz
    AddType application/x-chrome-extension              crx
    AddType application/x-opera-extension               oex
    AddType application/x-shockwave-flash               swf
    AddType application/x-web-app-manifest+json         webapp
    AddType application/x-xpinstall                     xpi
    AddType application/xml                             atom rdf rss xml
    AddType image/webp                                  webp
    AddType image/x-icon                                ico
    AddType text/cache-manifest                         appcache manifest
    AddType text/vtt                                    vtt
    AddType text/x-component                            htc
    AddType text/x-vcard                                vcf

    # Adicionar o Charset utf-8
    AddCharset utf-8 .atom .css .js .json .rss .vtt .webapp .xml
</IfModule>
<?php
# ------------------------------------------------------------------------------
# | Compressão                                                                 |
# ------------------------------------------------------------------------------
?>
<IfModule mod_deflate.c>

    # Force compression for mangled headers.
    # http://developer.yahoo.com/blogs/ydn/posts/2010/12/pushing-beyond-gzipping
    <IfModule mod_setenvif.c>
        <IfModule mod_headers.c>
            SetEnvIfNoCase ^(Accept-EncodXng|X-cept-Encoding|X{15}|~{15}|-{15})$ ^((gzip|deflate)\s*,?\s*)+|[X~-]{4,13}$ HAVE_Accept-Encoding
            RequestHeader append Accept-Encoding "gzip,deflate" env=HAVE_Accept-Encoding
        </IfModule>
    </IfModule>

    # Compress all output labeled with one of the following MIME-types
    # (for Apache versions below 2.3.7, you don't need to enable `mod_filter`
    #  and can remove the `<IfModule mod_filter.c>` and `</IfModule>` lines
    #  as `AddOutputFilterByType` is still in the core directives).
    <IfModule mod_filter.c>
        AddOutputFilterByType DEFLATE application/atom+xml \
                                      application/javascript \
                                      application/json \
                                      application/rss+xml \
                                      application/vnd.ms-fontobject \
                                      application/x-font-ttf \
                                      application/x-web-app-manifest+json \
                                      application/xhtml+xml \
                                      application/xml \
                                      font/opentype \
                                      image/svg+xml \
                                      image/x-icon \
                                      text/css \
                                      text/html \
                                      text/plain \
                                      text/x-component \
                                      text/xml
    </IfModule>

</IfModule>
<?php
# ------------------------------------------------------------------------------
# | Internet Explorer                                                           |
# ------------------------------------------------------------------------------
?>
<IfModule mod_headers.c>
    Header set X-UA-Compatible "IE=edge"
    # `mod_headers` can't match based on the content-type, however, we only
    # want to send this header for HTML pages and not for the other resources
    <FilesMatch "\.(appcache|crx|css|eot|gif|htc|ico|jpe?g|js|m4a|m4v|manifest|mp4|oex|oga|ogg|ogv|otf|pdf|png|safariextz|svg|svgz|ttf|vcf|webapp|webm|webp|woff|xml|xpi)$">
        Header unset X-UA-Compatible
    </FilesMatch>
</IfModule>
<?php
  $htaccess = ob_get_contents();
  ob_end_clean();
  file_put_contents('.htaccess', $htaccess);
?>