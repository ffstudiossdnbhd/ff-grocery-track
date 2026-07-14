<?php

namespace App\Http\Controllers;

use App\Models\LogAktiviti;
use Illuminate\Support\Facades\Auth;

class LogAktivitiController extends Controller
{


    /**
     * Paparkan log aktiviti sistem.
     */
    public function index()
    {
        $logs = LogAktiviti::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('log_aktiviti.index', compact('logs'));
    }
}
