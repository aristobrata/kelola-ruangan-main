<?php

namespace App\Controllers;

use App\Models\UserModel;

class ProfileController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function edit()
    {
        $user = $this->userModel->find(session()->get('user_id'));
        if (!$user) {
            return redirect()->to(base_url('login'));
        }

        return view('profile/edit', [
            'title' => 'Akun Saya',
            'user'  => $user,
        ]);
    }

    public function updatePassword()
    {
        $userId = session()->get('user_id');
        $user   = $this->userModel->find($userId);
        if (!$user) {
            return redirect()->to(base_url('login'));
        }

        $rules = [
            'password_lama'     => 'required',
            'password_baru'     => 'required|min_length[6]',
            'konfirmasi_password' => 'required|matches[password_baru]',
        ];
        $messages = [
            'konfirmasi_password' => [
                'matches' => 'Konfirmasi password tidak cocok dengan password baru.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            return view('profile/edit', [
                'title'      => 'Akun Saya',
                'user'       => $user,
                'validation' => $this->validator,
            ]);
        }

        if (!password_verify($this->request->getPost('password_lama'), $user['password'])) {
            return view('profile/edit', [
                'title' => 'Akun Saya',
                'user'  => $user,
                'error' => 'Password lama yang Anda masukkan salah.',
            ]);
        }

        $this->userModel->skipValidation(true)->update($userId, [
            'password' => password_hash($this->request->getPost('password_baru'), PASSWORD_DEFAULT),
        ]);

        return redirect()->to(base_url('profile'))->with('success', 'Password berhasil diubah. Silakan gunakan password baru Anda saat login berikutnya.');
    }
}
