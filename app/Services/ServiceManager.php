<?php

namespace App\Services;

use App\Models\Service;

class ServiceManager
{
    public function getAll()
    {
        return Service::latest()->get();
    }

    public function create(array $data)
    {
        return Service::create($data);
    }

    public function update(Service $service, array $data)
    {
        $service->update($data);
        return $service;
    }

    public function toggleActive(Service $service)
    {
        $service->update(['is_active' => !$service->is_active]);
        return $service;
    }

    public function delete(Service $service)
    {
        return $service->delete();
    }
}
