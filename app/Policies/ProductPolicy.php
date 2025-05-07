<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Product;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    public function viewAny(Admin $admin)
    {
        return true;
    }

    public function view(Admin $admin, Product $product)
    {
        return $admin->id === $product->admin_id;
    }

    public function create(Admin $admin)
    {
        return true;
    }

    public function update(Admin $admin, Product $product)
    {
        return $admin->id === $product->admin_id;
    }

    public function delete(Admin $admin, Product $product)
    {
        return $admin->id === $product->admin_id;
    }
}