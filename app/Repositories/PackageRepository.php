<?php

namespace App\Repositories;

use App\Models\Package;

class PackageRepository
{
    protected $package;

    public function __construct(Package $package)
    {
        $this->package = $package;
    }

    public function index()
    {
        $packages = $this->package::all();
        return $packages;
    }

    /**
     * @return Package
     */
    public function store($data)
    {
        $package = $this->package::create($data);
        return $package;
    }

    public function show($id)
    {
        $package = $this->package::findOrFail($id);
        return $package;
    }

    public function update($data, $id)
    {
        $package = $this->package::findOrFail($id);
        $package->fill($data);
        $package->save();
        return $package;
    }

    public function destroy($id)
    {
        $package = $this->package::findOrFail($id);
        $package->delete();
        return $package;
    }
}
