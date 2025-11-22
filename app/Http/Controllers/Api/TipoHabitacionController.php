<?php
// app/Http/Controllers/Api/TipoHabitacionController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TipoHabitacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipoHabitacionController extends Controller
{
    public function index(Request $request)
    {
        $query = TipoHabitacion::query();

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $tipos = $query->get();

        return response()->json([
            'success' => true,
            'data' => $tipos
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'capacidad' => 'required|integer|min:1',
            'precio_base' => 'required|numeric|min:0',
            'estado' => 'in:ACTIVO,INACTIVO',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $tipo = TipoHabitacion::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tipo de habitación creado exitosamente',
            'data' => $tipo
        ], 201);
    }

    public function show($id)
    {
        $tipo = TipoHabitacion::with('habitaciones')->find($id);

        if (!$tipo) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de habitación no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tipo
        ]);
    }

    public function update(Request $request, $id)
    {
        $tipo = TipoHabitacion::find($id);

        if (!$tipo) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de habitación no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'string|max:100',
            'descripcion' => 'nullable|string',
            'capacidad' => 'integer|min:1',
            'precio_base' => 'numeric|min:0',
            'estado' => 'in:ACTIVO,INACTIVO',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $tipo->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tipo de habitación actualizado exitosamente',
            'data' => $tipo
        ]);
    }

    public function destroy($id)
    {
        $tipo = TipoHabitacion::find($id);

        if (!$tipo) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de habitación no encontrado'
            ], 404);
        }

        // Verificar si tiene habitaciones asociadas
        if ($tipo->habitaciones()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un tipo con habitaciones asignadas'
            ], 422);
        }

        $tipo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tipo de habitación eliminado exitosamente'
        ]);
    }
}