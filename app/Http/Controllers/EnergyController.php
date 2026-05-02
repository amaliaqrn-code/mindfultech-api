<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EnergyController extends Controller
{
    public function store(Request $request)
{
    $request->validate([
        'energy_level' => 'required|in:low,medium,high'
    ]);

    $energy = $request->user()->energyLogs()->create([
        'energy_level' => $request->energy_level
    ]);

    return response()->json([
        'message' => 'Energy berhasil disimpan',
        'data' => $energy
    ]);
}
}
