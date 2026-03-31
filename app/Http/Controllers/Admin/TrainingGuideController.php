<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TrainingGuideController extends Controller
{
    private const ROLES = [
        'super_admin',
        'finance',
        'admin_data',
        'admin_collection',
        'treasury',
        'pengawas',
        'legal',
    ];

    public function index()
    {
        return view('admin.guide.index', ['roles' => self::ROLES]);
    }

    public function show(string $role)
    {
        $role = strtolower($role);

        if (!in_array($role, self::ROLES)) {
            abort(404);
        }

        return view("admin.guide.{$role}");
    }
}
