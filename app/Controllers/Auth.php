<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends PklController
{
    public function index()
    {
        if (session('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function attempt()
    {
        $email    = (string) $this->request->getPost('email');
        $password = (string) $this->request->getPost('password');

        $user = model(UserModel::class)->where('email', $email)->first();

        if (! $user || ! password_verify($password, $user['password'])) {
            return $this->fail('Email atau password salah.');
        }

        session()->regenerate();
        session()->set(['user_id' => $user['id'], 'role' => $user['role'], 'name' => $user['name']]);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login');
    }
}
