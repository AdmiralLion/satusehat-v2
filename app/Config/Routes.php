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
$routes->get('download_data', 'SatuSehat::download_data');
$routes->post('send_encounter', 'SatuSehat::send_encounter');
$routes->post('send_condition', 'SatuSehat::send_condition');
$routes->post('send_observation', 'SatuSehat::send_observation');
$routes->post('send_procedure', 'SatuSehat::send_procedure');
$routes->post('send_medication', 'SatuSehat::send_medication');
$routes->post('send_medication_bundle', 'SatuSehat::send_medication_bundle');
$routes->match(['get', 'post'], 'send_bundle', 'SatuSehat::send_bundle');
$routes->get('encounter/(:segment)', 'SatuSehatEncounter::show/$1');
$routes->post('encounter', 'SatuSehatEncounter::create');
$routes->put('encounter/(:segment)', 'SatuSehatEncounter::update/$1');
