<?php

/**
 * My Application bootstrap file.
 */
use Nette\Application\Routers\Route,
    Nette\Diagnostics\Debugger;


// Load Nette Framework
require LIBS_DIR . '/Nette/loader.php';


// Configure application
$configurator = new Nette\Config\Configurator;

// Enable Nette Debugger for error visualisation & logging
//$configurator->setDebugMode($configurator::AUTO);
$configurator->enableDebugger(__DIR__ . '/../log');

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(APP_DIR)
	->addDirectory(LIBS_DIR)
	->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config.neon');
$configurator->addConfig(__DIR__ . '/config.local.neon');

$container = $configurator->createContainer();

// Setup router
$container->router[] = new Route('index.php', 'Cekarna:view', Route::ONE_WAY);
$container->router[] = new Route('cekarna/view[/<date>]', 'Cekarna:view');
$container->router[] = new Route('<presenter>/<action>[/<id>]', 'Cekarna:view');

//$container->application->errorPresenter = 'Error';

// Configure and run the application!
$container->application->run();
//Debugger::enable(Debugger::DEVELOPMENT);
//setlocale(LC_ALL, 'cs_CZ.utf8');
