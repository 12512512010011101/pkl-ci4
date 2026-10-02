<?php

namespace App\Controllers;

use App\Models\CompanyModel;

class Companies extends PklController
{
    private array $rules = [
        'name'    => 'required|min_length[3]',
        'address' => 'permit_empty|string',
        'phone'   => 'permit_empty|max_length[30]',
        'pic'     => 'permit_empty|max_length[100]',
    ];

    public function index()
    {
        return view('companies/index', ['rows' => model(CompanyModel::class)->orderBy('name')->findAll()]);
    }

    public function add()
    {
        return view('companies/form', ['c' => null]);
    }

    public function create()
    {
        if (! $this->validate($this->rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        model(CompanyModel::class)->insert($this->request->getPost(['name', 'address', 'phone', 'pic']));

        return redirect()->to('/companies')->with('success', 'Perusahaan ditambahkan.');
    }

    public function edit(int $id)
    {
        $c = model(CompanyModel::class)->find($id);

        return $c ? view('companies/form', ['c' => $c]) : redirect()->to('/companies')->with('error', 'Data tidak ditemukan.');
    }

    public function update(int $id)
    {
        if (! $this->validate($this->rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        model(CompanyModel::class)->update($id, $this->request->getPost(['name', 'address', 'phone', 'pic']));

        return redirect()->to('/companies')->with('success', 'Perusahaan diperbarui.');
    }

    public function delete(int $id)
    {
        model(CompanyModel::class)->delete($id);

        return redirect()->to('/companies')->with('success', 'Perusahaan dihapus.');
    }
}
