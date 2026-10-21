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

    // ------------------------------------------------------------------
    // Kunci lokasi tempat PKL
    // ------------------------------------------------------------------

    /** Halaman peta untuk menentukan dan mengunci lokasi. */
    public function location(int $id)
    {
        $c = model(CompanyModel::class)->find($id);

        return $c
            ? view('companies/location', ['c' => $c])
            : redirect()->to('/companies')->with('error', 'Data tidak ditemukan.');
    }

    public function saveLocation(int $id)
    {
        $model = model(CompanyModel::class);
        if (! $model->find($id)) {
            return redirect()->to('/companies')->with('error', 'Data tidak ditemukan.');
        }

        $rules = [
            'latitude'  => 'required|numeric|greater_than_equal_to[-90]|less_than_equal_to[90]',
            'longitude' => 'required|numeric|greater_than_equal_to[-180]|less_than_equal_to[180]',
            'radius_m'  => 'required|is_natural|greater_than_equal_to[20]|less_than_equal_to[5000]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model->update($id, [
            'latitude'  => round((float) $this->request->getPost('latitude'), 7),
            'longitude' => round((float) $this->request->getPost('longitude'), 7),
            'radius_m'  => (int) $this->request->getPost('radius_m'),
        ]);

        return redirect()->to('/companies')->with('success', 'Lokasi PKL dikunci.');
    }

    /** Membuka kunci: siswa tidak lagi divalidasi jaraknya sampai lokasi dikunci lagi. */
    public function resetLocation(int $id)
    {
        $model = model(CompanyModel::class);
        if ($model->find($id)) {
            $model->update($id, ['latitude' => null, 'longitude' => null]);
        }

        return redirect()->to('/companies')->with('success', 'Kunci lokasi dibuka.');
    }
}
