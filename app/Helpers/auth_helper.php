<?php

if (!function_exists('is_admin_role')) {
    /**
     * True jika role yang sedang login adalah 'admin' ATAU 'super_admin'.
     * Dipakai untuk fitur yang hak aksesnya sama antara admin & super admin
     * (kelola ruangan, approve/reject booking, laporan, dsb).
     */
    function is_admin_role(): bool
    {
        return in_array(session()->get('role'), ['admin', 'super_admin'], true);
    }
}

if (!function_exists('is_super_admin')) {
    /**
     * True hanya jika role yang sedang login adalah 'super_admin'.
     * Dipakai khusus untuk fitur yang membedakan admin & super admin,
     * yaitu membuat/mengubah akun menjadi admin.
     */
    function is_super_admin(): bool
    {
        return session()->get('role') === 'super_admin';
    }
}

if (!function_exists('role_label')) {
    /** Label tampilan yang enak dibaca untuk sebuah nilai role. */
    function role_label(?string $role): string
    {
        $labels = [
            'super_admin' => 'Super Admin',
            'admin'       => 'Admin',
            'user'        => 'User',
        ];
        return $labels[$role] ?? ucfirst((string) $role);
    }
}
