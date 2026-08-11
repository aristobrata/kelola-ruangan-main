<?php

namespace App\Controllers;

use App\Models\RoomModel;
use App\Models\BookingModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $roomModel    = new RoomModel();
        $bookingModel = new BookingModel();
        $bookingModel->expireOverduePending();

        $isAdmin      = is_admin_role();
        // User (pembooking) hanya melihat statistik & jadwal booking miliknya sendiri.
        $filterUserId = $isAdmin ? null : (int) session()->get('user_id');

        $rooms        = $roomModel->findAll();
        $bookingStats = $bookingModel->getStats($filterUserId);
        $todayBookings= $bookingModel->getTodayBookings($filterUserId);

        $totalRooms      = count($rooms);
        $availableRooms  = count(array_filter($rooms, fn($r) => $r['status'] === 'available'));
        $maintenanceRooms= count(array_filter($rooms, fn($r) => $r['status'] === 'maintenance'));

        $roomsWithStatus = $roomModel->getRoomsWithBookingStatus();
        $occupiedRooms   = count(array_filter($roomsWithStatus, fn($r) => ($r['booking_status'] ?? '') === 'occupied'));

        return view('dashboard/index', [
            'title'            => 'Dashboard',
            'isAdmin'          => $isAdmin,
            'totalRooms'       => $totalRooms,
            'availableRooms'   => $availableRooms,
            'maintenanceRooms' => $maintenanceRooms,
            'occupiedRooms'    => $occupiedRooms,
            'bookingStats'     => $bookingStats,
            'todayBookings'    => $todayBookings,
            'roomsWithStatus'  => $roomsWithStatus,
        ]);
    }
}
