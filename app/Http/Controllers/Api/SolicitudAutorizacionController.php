<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SolicitudAutorizacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SolicitudAutorizacionController extends Controller
{
    /**
     * Listar solicitudes (filtradas por rol)
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            // Paso 1: Verificar Rol
            $mensajeRol = "Rol no cargado";
            if (!$user->relationLoaded('rol')) {
                $user->load('rol');
            }
            $nombreRol = $user->rol ? $user->rol->nombre : 'Sin Rol asignado';

            // Paso 2: Verificar Tabla de Solicitudes (Conteo simple)
            $cantidadSolicitudes = -1;
            try {
                $cantidadSolicitudes = SolicitudAutorizacion::count();
            } catch (\Exception $ex) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al acceder a la tabla solicitudes_autorizacion. Posiblemente falta la migración.',
                    'error_detail' => $ex->getMessage()
                ], 500);
            }

            // Si llegamos aquí, la tabla existe. Intentemos la consulta real pero limitada.
            $query = SolicitudAutorizacion::with(['solicitante', 'autorizador']);

            if ($user->rol && $user->rol->nombre === 'Recepcionista') {
                $query->where('solicitante_id', $user->id);
            } else {
                $query->where('estado', 'PENDIENTE');
            }

            $solicitudes = $query->orderBy('created_at', 'desc')->take(5)->get();

            return response()->json([
                'success' => true,
                'debug_info' => [
                    'user_id' => $user->id,
                    'rol' => $nombreRol,
                    'table_count' => $cantidadSolicitudes
                ],
                'data' => $solicitudes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error General en Index: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Crear nueva solicitud
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'required|string',
            'modelo' => 'required|string',
            'modelo_id' => 'required|integer',
            'motivo' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $solicitud = SolicitudAutorizacion::create([
            'solicitante_id' => $request->user()->id,
            'tipo' => $request->tipo,
            'modelo' => $request->modelo,
            'modelo_id' => $request->modelo_id,
            'motivo' => $request->motivo,
            'datos_nuevos' => $request->datos_nuevos,
            'estado' => 'PENDIENTE',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud enviada correctamente',
            'data' => $solicitud->load('solicitante')
        ], 201);
    }

    /**
     * Aprobar solicitud
     */
    public function aprobar(Request $request, $id)
    {
        $solicitud = SolicitudAutorizacion::find($id);

        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada'
            ], 404);
        }

        if ($solicitud->estado !== 'PENDIENTE') {
            return response()->json([
                'success' => false,
                'message' => 'Esta solicitud ya fue procesada'
            ], 422);
        }

        $solicitud->update([
            'estado' => 'APROBADA',
            'autorizador_id' => $request->user()->id,
            'comentario_autorizador' => $request->comentario,
            'fecha_respuesta' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud aprobada',
            'data' => $solicitud->load(['solicitante', 'autorizador'])
        ]);
    }

    /**
     * Rechazar solicitud
     */
    public function rechazar(Request $request, $id)
    {
        $solicitud = SolicitudAutorizacion::find($id);

        if (!$solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada'
            ], 404);
        }

        if ($solicitud->estado !== 'PENDIENTE') {
            return response()->json([
                'success' => false,
                'message' => 'Esta solicitud ya fue procesada'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'comentario' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $solicitud->update([
            'estado' => 'RECHAZADA',
            'autorizador_id' => $request->user()->id,
            'comentario_autorizador' => $request->comentario,
            'fecha_respuesta' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud rechazada',
            'data' => $solicitud->load(['solicitante', 'autorizador'])
        ]);
    }
}
