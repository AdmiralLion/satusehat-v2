<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/dbtest', 'Dbtest::index');

// Satu Sehat API Proxy (Guzzle)
$routes->get('satusehat-api/token', 'SatuSehatApi::getToken');
$routes->get('satusehat-api/get_ihs_pasien', 'SatuSehatApi::get_ihs_pasien');
$routes->get('satusehat-api/get_ihs_dokter', 'SatuSehatApi::get_ihs_dokter');
$routes->get('satusehat-api/proxy', 'SatuSehatApi::proxyGet');
$routes->post('satusehat-api/proxy', 'SatuSehatApi::proxyPost');
$routes->put('satusehat-api/proxy', 'SatuSehatApi::proxyPut');

// Satu Sehat Encounter
$routes->get('satusehat', 'SatuSehat::index');
$routes->post('send_encounter', 'SatuSehat::send_encounter');
$routes->get('encounter/(:segment)', 'SatuSehatEncounter::show/$1');
$routes->post('encounter', 'SatuSehatEncounter::create');
$routes->put('encounter/(:segment)', 'SatuSehatEncounter::update/$1');
