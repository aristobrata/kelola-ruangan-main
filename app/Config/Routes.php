<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Auth
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::doLogin');
$routes->get('logout', 'AuthController::logout');

$routes->get('/', 'Dashboard::index');

// Rooms — melihat data ruangan bisa diakses admin & user (pembooking),
// mengelola (tambah/ubah/hapus) hanya untuk admin.
$routes->get('rooms', 'RoomController::index');
$routes->get('jadwal', 'ScheduleController::index');
$routes->get('rooms/create', 'RoomController::create', ['filter' => 'adminOnly']);
$routes->post('rooms/store', 'RoomController::store', ['filter' => 'adminOnly']);
$routes->get('rooms/(:num)', 'RoomController::show/$1');
$routes->get('rooms/edit/(:num)', 'RoomController::edit/$1', ['filter' => 'adminOnly']);
$routes->post('rooms/update/(:num)', 'RoomController::update/$1', ['filter' => 'adminOnly']);
$routes->post('rooms/delete/(:num)', 'RoomController::delete/$1', ['filter' => 'adminOnly']);
$routes->post('rooms/photo/delete/(:num)', 'RoomController::deletePhoto/$1', ['filter' => 'adminOnly']);

// Bookings — user membuat & mengelola booking miliknya sendiri,
// admin melihat semua booking serta menyetujui/menolak/menandai selesai.
$routes->get('bookings', 'BookingController::index');
$routes->get('bookings/create', 'BookingController::create');
$routes->post('bookings/store', 'BookingController::store');
$routes->get('bookings/(:num)', 'BookingController::show/$1');
$routes->get('bookings/edit/(:num)', 'BookingController::edit/$1');
$routes->post('bookings/update/(:num)', 'BookingController::update/$1');
$routes->post('bookings/approve/(:num)', 'BookingController::approve/$1', ['filter' => 'adminOnly']);
$routes->post('bookings/reject/(:num)', 'BookingController::reject/$1', ['filter' => 'adminOnly']);
$routes->post('bookings/confirm-konsumsi/(:num)', 'BookingController::confirmKonsumsi/$1', ['filter' => 'adminOnly']);
$routes->post('bookings/cancel/(:num)', 'BookingController::cancel/$1');
$routes->post('bookings/selesai/(:num)', 'BookingController::selesai/$1', ['filter' => 'adminOnly']);

// Laporan — riwayat booking & export Excel, khusus admin.
$routes->get('reports/bookings', 'ReportController::bookings', ['filter' => 'adminOnly']);
$routes->get('reports/bookings/export', 'ReportController::exportExcel', ['filter' => 'adminOnly']);

// Profil — setiap user yang login bisa mengubah password akunnya sendiri.
$routes->get('profile', 'ProfileController::edit');
$routes->post('profile/update-password', 'ProfileController::updatePassword');

// Users — manajemen akun (admin & pembooking), khusus admin.
$routes->get('users', 'UserController::index', ['filter' => 'adminOnly']);
$routes->get('users/create', 'UserController::create', ['filter' => 'adminOnly']);
$routes->post('users/store', 'UserController::store', ['filter' => 'adminOnly']);
$routes->get('users/edit/(:num)', 'UserController::edit/$1', ['filter' => 'adminOnly']);
$routes->post('users/update/(:num)', 'UserController::update/$1', ['filter' => 'adminOnly']);
$routes->post('users/delete/(:num)', 'UserController::delete/$1', ['filter' => 'adminOnly']);
