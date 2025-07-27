<?php

namespace App\Http\Controllers;

use App\Services\OllamaService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ModelManagementController extends Controller
{
    public OllamaService $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    public function index()
    {
        return Inertia::render('ModelManagement');
    }

    public function loadModel(Request $request)
    {
        $model = $request->input('model');
        $this->ollamaService->loadModel($model);

        return to_route('dashboard');
    }

    public function unloadModel(Request $request)
    {
        $model = $request->input('model');
        $this->ollamaService->unloadModel($model);

        return to_route('dashboard');
    }

    public function getRunningModel()
    {
        $runningList = $this->ollamaService->getRunningList();

        return response()->json($runningList);
    }
}
