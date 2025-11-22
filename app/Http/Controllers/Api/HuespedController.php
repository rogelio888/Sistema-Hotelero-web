<?php
// app/Http/Controllers/Api/HuespedController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Huesped;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HuespedController extends Controller
{
    public function index(Request $request)
    {
        $query = Huesped::query();

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('buscar')) {
            $query->buscar($request->buscar);
        }

        $huespedes = $query->get();

        return response()->json([
            'success' => true,
            'data' => $huespedes
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'apellido' => 'required|string|max:150',
            'ci' => 'required|string|max:30|unique:huespedes,ci',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'estado' => 'in:ACTIVO,INACTIVO',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $huesped = Huesped::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Huésped registrado exitosamente',
            'data' => $huesped
        ], 201);
    }

    public function show($id)
    {
        $huesped = Huesped::with(['reservasPrincipales', 'reservasAdicionales'])
            ->find($id);

        if (!$huesped) {
            return response()->json([
                'success' => false,
                'message' => 'Huésped no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $huesped
        ]);
    }

    public function update(Request $request, $id)
    {
        $huesped = Huesped::find($id);

        if (!$huesped) {
            return response()->json([
                'success' => false,
                'message' => 'Huésped no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'string|max:150',
            'apellido' => 'string|max:150',
            'ci' => 'string|max:30|unique:huespedes,ci,' . $id,
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'estado' => 'in:ACTIVO,INACTIVO',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $huesped->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Huésped actualizado exitosamente',
            'data' => $huesped
        ]);
    }

    public function destroy($id)
    {
        $huesped = Huesped::find($id);

        if (!$huesped) {
            return response()->json([
                'success' => false,
                'message' => 'Huésped no encontrado'
            ], 404);
        }

        $huesped->estado = 'INACTIVO';
        $huesped->save();

        return response()->json([
            'success' => true,
            'message' => 'Huésped inactivado exitosamente'
        ]);
    }

    /**
     * Buscar huésped por CI
     */
    public function buscarPorCi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ci' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $huesped = Huesped::where('ci', $request->ci)->first();

        if (!$huesped) {
            return response()->json([
                'success' => false,
                'message' => 'Huésped no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $huesped
        ]);
    }
}