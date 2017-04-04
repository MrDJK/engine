<?php 
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Medoo\Medoo;
use Passwordless\Passwordless;
use Player\Player;

session_name ( 'GameEngine' );
session_start();
/* 

	Config Array - to separate later

*/


$config = [
	'displayErrorDetails' => true,
	'addContentLengthHeader' => false,
	'database' => [
		'database_type' => 'mysql',
		'database_name' => 'engine',
		'server' => 'localhost',
		'username' => 'root',
		'password' => '',
		'charset' => 'utf8'
	],
];


/* All Required Engine Files */
require ( '../vendor/autoload.php' );
require ( '../Passwordless.php' );
require ( '../Player.php' );
require ( '../middleware/AuthenticationMiddleware.php' );

/* Create Slim App & set Slim Container */
$app = new \Slim\App ( ['settings' => $config ] );
$container = $app->getContainer();

/* Load in Database using Medoo Component */
$container['database'] = function ( $c ) {
	$database = new Medoo( $c->settings['database'] ); 
	return $database;
};
/* open PHPMailer to the app container */
$container['mailer'] = function ( $c ) {
	$mailer = new PHPMailer;
	return $mailer;
};
/* open the passwordless class to the app container */
$container['passwordless'] = function ( $c ) {
	$passwordless = new Passwordless( $c->database, $c->mailer );
	return $passwordless;
};

/* Register the player class to the app container */
$container['player'] = function ( $c ) {
	$player = new Player ( $c->database );
	return $player;
};


/* ----------------------------------------------
		Quick Module Loader
-----------------------------------------------*/
$modules = []; //List of Modules
$viewsDirList = [
	'base' => 'views/' // Engine Views
];

/* Loop over modules directory */
$loadedModules = new RecursiveIteratorIterator ( new RecursiveDirectoryIterator ( '../modules' ), RecursiveIteratorIterator::SELF_FIRST );
foreach ( $loadedModules as $loaded ) {

	if ( $loadedModules->isDot() ) { //Check against . & ..
		continue;
	} else {
		$router = false;
		$views = false;

		$path = $loadedModules->getPathName();
		if ( file_exists ( $path.'/routes.php' ) ) { //Load router if exists
			$router = $path.'/routes.php';
		}
		if ( is_dir ( $path.'/views' ) ) { //Load view if exists
			$views = $path.'/views';
			$viewsDirList[$loadedModules->getSubPathName()] = $path.'/views'; //set the view path to the module name for twig namespaces
		}

		$modules[$path] = [
			'router' => $router,
			'views' => $views,
		];

	}
}
/* Set up Twig View to App Controller */
$container['view'] = function ( $c ) use ( $viewsDirList ) {
	$view = new \Slim\Views\Twig ( $viewsDirList );
	$basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath)); 

    return $view;
};


//Load Modules

//Load Landing Module separatly think of it as a base.
require_once ( '../modules/landing/index.php' );
require_once ( '../modules/landing/routes.php' );
//

$app->group('', function() use ( $app, $modules ) {

	foreach($modules as $path => $set){

		if ( strtolower ( $path ) === 'landing' ) {
			continue;
		} else {

			if ( $set['router'] && file_exists ( $set['router'] ) ) {
				require_once ( $set['router'] );
			}
			if ( file_exists ( $path.'/index.php' ) ) {
				require_once ( $path.'/index.php');
			}
		}
	}
})->add ( new AuthenticationMiddleware() );

$app->run();

