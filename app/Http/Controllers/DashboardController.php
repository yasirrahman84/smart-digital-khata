<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private ReportService $reports) {}

    public function index(Request $request)
    {
        return view('dashboard', [
            'data' => $this->reports->dashboard($request->user()),
        ]);
    }
}
