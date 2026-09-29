<?php

namespace Myth\Auth\Config;

use CodeIgniter\Router\RouteCollection;
use Myth\Auth\Config\Auth as AuthConfig;

/** @var RouteCollection $routes */

// Myth:Auth routes file.
$routes->group('', ['namespace' => 'Myth\Auth\Controllers'], static function ($routes) {
    // Load the reserved routes from Auth.php
    $config         = config(AuthConfig::class);
    $reservedRoutes = $config->reservedRoutes;

    // Login/out
    $routes->get($reservedRoutes['login'], 'AuthController::login', ['as' => $reservedRoutes['login']]);
    $routes->post($reservedRoutes['login'], 'AuthController::attemptLogin');
    $routes->get($reservedRoutes['logout'], 'AuthController::logout');

    // Registration
    $routes->get($reservedRoutes['register'], 'AuthController::register', ['as' => $reservedRoutes['register']]);
    $routes->post($reservedRoutes['register'], 'AuthController::attemptRegister');

    // Activation
    $routes->get($reservedRoutes['activate-account'], 'AuthController::activateAccount', ['as' => $reservedRoutes['activate-account']]);
    $routes->get($reservedRoutes['resend-activate-account'], 'AuthController::resendActivateAccount', ['as' => $reservedRoutes['resend-activate-account']]);

    // Forgot/Resets
// Forgot/Resets
$routes->get($reservedRoutes['forgot'], 'AuthController::forgotPassword', ['as' => $reservedRoutes['forgot']]);
$routes->post($reservedRoutes['forgot'], 'AuthController::attemptForgot');
$routes->get($reservedRoutes['reset-password'], 'AuthController::resetPassword', ['as' => $reservedRoutes['reset-password']]);
$routes->post($reservedRoutes['reset-password'], 'AuthController::attemptReset');

// Activación de cuenta
$routes->get($reservedRoutes['activate-account'], 'AuthController::activateAccount', ['as' => $reservedRoutes['activate-account']]);

// Resend Activación de cuenta
$routes->get($reservedRoutes['resend-activate-account'], 'AuthController::resendActivateAccount', ['as' => $reservedRoutes['resend-activate-account']]);


    // list/edit
    $routes->get('list-users', 'AuthController::list_users', ['as' => 'list_users', 'filter' => 'permiso: Admin']);
    $routes->get('list-permisos/(:any)', 'AuthController::list_permisos/$1', ['as' => 'permisos_list', 'filter' => 'permiso: Admin']);    
    $routes->get('crear_permiso/(:any)', 'AuthController::create_permiso/$1', ['as' => 'permiso_create', 'filter' => 'permiso: Admin']);
    $routes->post('permiso', 'AuthController::store_permiso', ['as' => 'store_permiso', 'filter' => 'permiso: Admin']);
    $routes->delete('permiso', 'AuthController::destroy_permiso', ['as' => 'destroy_permiso', 'filter' => 'permiso: Admin']);

    $routes->get('list-group-user/(:any)', 'AuthController::list_group_user/$1', ['as' => 'group_list_user', 'filter' => 'permiso: Admin']);
    $routes->get('crear-group-user/(:any)', 'AuthController::create_group_user/$1', ['as' => 'group_create_user', 'filter' => 'permiso: Admin']);
    $routes->post('group-user', 'AuthController::store_group_user', ['as' => 'store_group_user', 'filter' => 'permiso: Admin']);
    $routes->delete('group-user', 'AuthController::destroy_group_user', ['as' => 'destroy_group_user', 'filter' => 'permiso: Admin']);

    $routes->get('list-groups', 'AuthController::list_groups', ['as' => 'groups_list', 'filter' => 'permiso: Admin']);
    $routes->get('crear-groups', 'AuthController::create_group', ['as' => 'group_create', 'filter' => 'permiso: Admin']);
    $routes->post('groups', 'AuthController::store_group', ['as' => 'store_group', 'filter' => 'permiso: Admin']);
    $routes->delete('group-user', 'AuthController::destroy_group', ['as' => 'destroy_group', 'filter' => 'permiso: Admin']);

    $routes->get('list-groups-permisos/(:any)', 'AuthController::list_groups_permisos/$1', ['as' => 'groups_permisos_list', 'filter' => 'permiso: Admin']);
    $routes->get('crear-group-permiso/(:any)', 'AuthController::create_group_permiso/$1', ['as' => 'group_permiso_create', 'filter' => 'permiso: Admin']);
    $routes->post('group-permiso', 'AuthController::store_group_permiso', ['as' => 'store_group_permiso', 'filter' => 'permiso: Admin']);
    $routes->delete('group-permiso', 'AuthController::destroy_group_permiso', ['as' => 'destroy_group_permiso', 'filter' => 'permiso: Admin']);    
});
