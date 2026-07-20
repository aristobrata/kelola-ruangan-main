<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run()
    {
        $rooms = [
            [
                'kode_ruangan' => 'R-001',
                'nama_ruangan' => 'Ruang Rapat Utama',
                'kapasitas'    => 30,
                'lokasi'       => 'Gedung A, Lantai 1',
                'fasilitas'    => 'Proyektor, AC, Whiteboard, Sound System, WiFi',
                'deskripsi'    => 'Ruang rapat utama dengan kapasitas besar, cocok untuk rapat besar dan presentasi.',
                'status'       => 'available',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'kode_ruangan' => 'R-002',
                'nama_ruangan' => 'Ruang Seminar',
                'kapasitas'    => 100,
                'lokasi'       => 'Gedung B, Lantai 2',
                'fasilitas'    => 'Proyektor, AC, Microphone, Sound System, WiFi, Podium',
                'deskripsi'    => 'Ruang seminar berkapasitas besar untuk acara seminar dan workshop.',
                'status'       => 'available',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'kode_ruangan' => 'R-003',
                'nama_ruangan' => 'Ruang Diskusi Kecil',
                'kapasitas'    => 10,
                'lokasi'       => 'Gedung A, Lantai 2',
                'fasilitas'    => 'Whiteboard, AC, TV, WiFi',
                'deskripsi'    => 'Ruang diskusi kecil untuk meeting tim atau diskusi kelompok kecil.',
                'status'       => 'available',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'kode_ruangan' => 'R-004',
                'nama_ruangan' => 'Aula Serbaguna',
                'kapasitas'    => 200,
                'lokasi'       => 'Gedung C, Lantai 1',
                'fasilitas'    => 'Sound System, Proyektor, Microphone, AC, Panggung, WiFi',
                'deskripsi'    => 'Aula serbaguna untuk acara besar seperti konferensi, wisuda, dan acara resmi.',
                'status'       => 'available',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'kode_ruangan' => 'R-005',
                'nama_ruangan' => 'Ruang Training',
                'kapasitas'    => 25,
                'lokasi'       => 'Gedung B, Lantai 1',
                'fasilitas'    => 'Komputer, Proyektor, AC, Whiteboard, WiFi',
                'deskripsi'    => 'Ruang training dengan fasilitas komputer untuk pelatihan dan workshop IT.',
                'status'       => 'maintenance',
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('rooms')->insertBatch($rooms);
    }
}
