<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Property;

class PropertyController extends Controller
{
    public function apartments()
    {
        $query = Property::with('landlord', 'units');

        if (request('search')) {
            $search = request('search');
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('address', 'like', '%'.$search.'%');
            });
        }

        if (request('status')) {
            $query->where('status', request('status'));
        }

        if (request('landlord')) {
            $query->where('landlord_id', request('landlord'));
        }

        $properties = $query->latest()->paginate(15);
        $apartments = $properties;

        return view('super-admin.apartments', compact('apartments', 'properties'));
    }
}
