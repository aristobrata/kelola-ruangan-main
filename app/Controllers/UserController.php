<?php

namespace App\Controllers;

use App\Models\UserModel;

class UserController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $users = $this->userModel->orderBy('id', 'ASC')->findAll();

        return view('users/index', [
            'title' => 'Manajemen User',
            'users' => $users,
        ]);
    }

    public function create()
    {
        return view('users/form', [
            'title'  => 'Tambah User',
            'user'   => null,
            'action' => base_url('users/store'),
        ]);
    }

    public function store()
    {
        $rules = [
            'nama'     => 'required|max_length[100]',
            'username' => 'required|max_length[50]|is_unique[users.username]',
            'password' => 'required|min_length[6]',
            'role'     => 'required|in_list[admin,user]',
        ];

        if (!$this->validate($rules)) {
            return view('users/form', [
                'title'      => 'Tambah User',
                'user'       => null,
                'action'     => base_url('users/store'),
                'validation' => $this->validator,
            ]);
        }

        $this->userModel->insert([
            'nama'     => $this->request->getPost('nama'),
            'username' => $this->request->getPost('username'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'     => $this->request->getPost('role'),
        ]);

        return redirect()->to(base_url('users'))->with('success', 'User berhasil ditambahkan!');
    }

    public function edit(int $id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User tidak ditemukan.');
        }

        return view('users/form', [
            'title'  => 'Edit User',
            'user'   => $user,
            'action' => base_url("users/update/{$id}"),
        ]);
    }

    public function update(int $id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User tidak ditemukan.');
        }

        $rules = [
            'nama'     => 'required|max_length[100]',
            'username' => "required|max_length[50]|is_unique[users.username,id,{$id}]",
            'role'     => 'required|in_list[admin,user]',
        ];
        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[6]';
        }

        if (!$this->validate($rules)) {
            return view('users/form', [
                'title'      => 'Edit User',
                'user'       => $user,
                'action'     => base_url("users/update/{$id}"),
                'validation' => $this->validator,
            ]);
        }

        // Jangan biarkan admin terakhir diturunkan menjadi user biasa
        if ($user['role'] === 'admin' && $this->request->getPost('role') !== 'admin') {
            $adminCount = $this->userModel->where('role', 'admin')->countAllResults();
            if ($adminCount <= 1) {
                return redirect()->back()->withInput()
                    ->with('error', 'Tidak dapat mengubah role. Minimal harus ada 1 admin.');
            }
        }

        $data = [
            'nama'     => $this->request->getPost('nama'),
            'username' => $this->request->getPost('username'),
            'role'     => $this->request->getPost('role'),
        ];
        if ($this->request->getPost('password')) {
            $data['password'] = password_hash($this->request->getPost('password'), PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $data);
        return redirect()->to(base_url('users'))->with('success', 'User berhasil diperbarui!');
    }

    public function delete(int $id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User tidak ditemukan.');
        }

        if ((int)$id === (int)session()->get('user_id')) {
            return redirect()->to(base_url('users'))->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user['role'] === 'admin') {
            $adminCount = $this->userModel->where('role', 'admin')->countAllResults();
            if ($adminCount <= 1) {
                return redirect()->to(base_url('users'))->with('error', 'Tidak dapat menghapus admin terakhir.');
            }
        }

        $this->userModel->delete($id);
        return redirect()->to(base_url('users'))->with('success', 'User berhasil dihapus!');
    }
}
