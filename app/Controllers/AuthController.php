<?php

namespace App\Controllers;

use App\Models\UserModel;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('logged_in')) {
            return redirect()->to(base_url('/'));
        }
        return view('auth/login', ['title' => 'Login']);
    }

    public function doLogin()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (!$username || !$password) {
            return redirect()->back()->with('error', 'Username dan password wajib diisi.');
        }

        // Batasi percobaan login per alamat IP untuk mencegah brute-force.
        // Maksimal 10 percobaan per menit (rate: 10 token, isi ulang tiap 60 detik).
        $throttler = \Config\Services::throttler();
        if ($throttler->check(md5($this->request->getIPAddress()), 10, 60) === false) {
            return redirect()->back()->with('error', 'Terlalu banyak percobaan login. Silakan coba lagi dalam beberapa saat.');
        }

        $userModel = new UserModel();
        $user = $userModel->where('username', $username)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return redirect()->back()->with('old_username', $username)->with('error', 'Username atau password salah.');
        }

        // Regenerasi session ID setelah login berhasil untuk mencegah session fixation.
        session()->regenerate();

        session()->set([
            'logged_in' => true,
            'user_id'   => $user['id'],
            'username'  => $user['username'],
            'nama'      => $user['nama'],
            'role'      => $user['role'],
        ]);

        return redirect()->to(base_url('/'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'))->with('success', 'Anda telah berhasil keluar.');
    }
}
