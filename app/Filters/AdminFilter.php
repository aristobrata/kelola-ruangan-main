<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * Membatasi akses halaman/aksi tertentu hanya untuk role 'admin' & 'super_admin'.
 * Dipasang SETELAH filter 'auth', jadi session pasti sudah login
 * saat filter ini dievaluasi.
 */
class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!is_admin_role()) {
            return redirect()->to(base_url('bookings'))
                ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
