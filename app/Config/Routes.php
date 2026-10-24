<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'Auth::index');
$routes->get('login', 'Auth::index');
$routes->post('login', 'Auth::attempt');
$routes->get('logout', 'Auth::logout');
$routes->post('logout', 'Auth::logout');

// Semua user yang sudah login
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('attendance', 'Attendance::index');
    $routes->get('journals', 'Journals::index');
    $routes->get('leaves', 'Leaves::index');
});

// Khusus siswa
$routes->group('', ['filter' => 'auth:siswa'], static function ($routes) {
    $routes->post('attendance/checkin', 'Attendance::checkin');
    $routes->post('attendance/checkout', 'Attendance::checkout');
    $routes->get('journals/new', 'Journals::add');
    $routes->post('journals', 'Journals::create');
    $routes->post('journals/delete/(:num)', 'Journals::delete/$1');
    $routes->post('leaves', 'Leaves::create');
});

// Tempat PKL: pembimbing dan admin
$routes->group('', ['filter' => 'auth:pembimbing,admin'], static function ($routes) {
    $routes->get('companies', 'Companies::index');
    $routes->get('companies/new', 'Companies::add');
    $routes->post('companies', 'Companies::create');
    $routes->get('companies/edit/(:num)', 'Companies::edit/$1');
    $routes->post('companies/update/(:num)', 'Companies::update/$1');
    $routes->post('companies/delete/(:num)', 'Companies::delete/$1');

    // Kunci lokasi tempat PKL (absensi berbasis lokasi)
    $routes->get('companies/location/(:num)', 'Companies::location/$1');
    $routes->post('companies/location/(:num)', 'Companies::saveLocation/$1');
    $routes->post('companies/location/reset/(:num)', 'Companies::resetLocation/$1');
});

// Khusus pembimbing
$routes->group('', ['filter' => 'auth:pembimbing'], static function ($routes) {
    $routes->get('students', 'Students::index');
    $routes->get('students/new', 'Students::add');
    $routes->post('students', 'Students::create');
    $routes->get('students/edit/(:num)', 'Students::edit/$1');
    $routes->post('students/update/(:num)', 'Students::update/$1');
    $routes->post('students/delete/(:num)', 'Students::delete/$1');

    $routes->post('journals/review/(:num)', 'Journals::review/$1');
    $routes->post('leaves/review/(:num)', 'Leaves::review/$1');

    $routes->get('assessments', 'Assessments::index');
    $routes->get('assessments/(:num)', 'Assessments::edit/$1');
    $routes->post('assessments/(:num)', 'Assessments::save/$1');

    $routes->get('reports', 'Reports::index');
    $routes->get('reports/export', 'Reports::export');
});

// Khusus admin
$routes->group('admin', ['filter' => 'auth:admin'], static function ($routes) {
    // Rekap semua siswa
    $routes->get('recap', 'Admin::recap');
    $routes->get('recap/export', 'Admin::exportRecap');

    // Akun pembimbing dan admin (akun siswa lewat halaman Siswa)
    $routes->get('users', 'Admin::users');
    $routes->get('users/new', 'Admin::addUser');
    $routes->post('users', 'Admin::createUser');
    $routes->get('users/edit/(:num)', 'Admin::editUser/$1');
    $routes->post('users/update/(:num)', 'Admin::updateUser/$1');
    $routes->post('users/delete/(:num)', 'Admin::deleteUser/$1');

    // Data siswa (semua siswa, termasuk pilih pembimbing dan tempat PKL)
    $routes->get('students', 'Admin::students');
    $routes->get('students/new', 'Admin::addStudent');
    $routes->post('students', 'Admin::createStudent');
    $routes->get('students/edit/(:num)', 'Admin::editStudent/$1');
    $routes->post('students/update/(:num)', 'Admin::updateStudent/$1');
    $routes->post('students/delete/(:num)', 'Admin::deleteStudent/$1');
});
