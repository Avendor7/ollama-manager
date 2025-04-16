<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class ModelManagementController extends Controller
{
    public function index()
    {
        return Inertia::render('ModelManagement');
    }
}
