<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function find($id)
    {
        return User::find($id);
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function update($id, array $data)
    {
        return User::where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return User::where('id', $id)->delete();
    }
    public function getAll()
    {
        $role = auth()->user()->getRoleNames()->first();
        return User::whereNotIn('id', User::role('User')->pluck('id'))->whereNull('deleted_at')->get();
    }

    public function getAllSiteUser()
    {
        return User::whereIn('id', User::role('User')->pluck('id'))->whereNull('deleted_at')->get();

        // return User::role('User')->whereNull('deleted_at')->get();
    }
}
