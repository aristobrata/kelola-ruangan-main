<?php

namespace App\Models;

use CodeIgniter\Model;

class RoomPhotoModel extends Model
{
    protected $table      = 'room_photos';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['room_id', 'filename', 'urutan'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    public function getForRoom(int $roomId): array
    {
        return $this->where('room_id', $roomId)->orderBy('urutan', 'ASC')->orderBy('id', 'ASC')->findAll();
    }
}
