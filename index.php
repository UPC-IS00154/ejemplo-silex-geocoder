<?php

// Ejemplo usando Silex+Twig+Geocoder
// ==================================

// uso del autoload generado por composer
require_once __DIR__.'/vendor/autoload.php';

// uso de las clases Request y Response de Symfony
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Crea la Aplicación
// ==================

$app = new Silex\Application();

// configurar el generador de URLs
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// configurar Twig en la Aplicación
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/views',
));

// si la ruta termina en "/", cambia la ruta a "/index.php"
if ( substr($_SERVER["REQUEST_URI"], -1) == '/' ) {
  header("Location: index.php");
  die();
}

// Rutas
// =====

// la ruta "/" muestra el formulario de login
$app->get('/', function() use($app) {

  // muestra la plantilla views/login.html.twig
  // usando como valor $user
  return $app['twig']->render('formulario.html.twig');

  // al usar bind define el nombre 'login'
  // en las plantillas es posible incluir un link usando url('login')
  // es posible hacer un redirect usando $app['url_generator']->generate('login')
})->bind('formulario');


// la ruta "/doGeocode" recibe los datos del formulario
// note que se recibe $request como parámetro
$app->post('/doGeocode', function(Request $request) use($app) {

  // toma los datos de la petición web
  $lugar = $request->get('lugar');

  // usa el Geocoder
  $geocoderAdapter  = new \Ivory\HttpAdapter\CurlHttpAdapter();
  $geocoder = new \Geocoder\ProviderAggregator();
  $geocoder->registerProvider(new \Geocoder\Provider\GoogleMaps($geocoderAdapter));

  $datos    = $geocoder->geocode($lugar);
  $latitud = $datos->first()->getLatitude();
  $longitud  = $datos->first()->getLongitude();

  // redirige el navegador a "/mapa"
  // enviando parametros
  return $app->redirect( $app['url_generator']->generate('mapa', array(
      'lugar'   => $lugar,
      'longitud'=> $longitud,
      'latitud' => $latitud
    )));

  // hace un bind con el nombre "doGeocode"
})->bind('doGeocode');


// la ruta "/mapa" muestra el mapa
$app->get('/mapa/{lugar}/{longitud}/{latitud}', function($lugar, $longitud, $latitud) use($app) {

  // muestra la plantilla views/menu.html.twig
  // envia los datos a la plantilla
  return $app['twig']->render('mapa.html.twig', array(
      'lugar'   => $lugar,
      'longitud'=> $longitud,
      'latitud' => $latitud
    ));

  // hace un bind con el nombre "mapa"
})->bind('mapa');


// Corre la Aplicación
// ===================

$app->run();