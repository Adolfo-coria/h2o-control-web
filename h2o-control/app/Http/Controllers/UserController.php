<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rol; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Helper privado para verificar si el usuario tiene permisos administrativos.
     */
    private function esAdministrador(): bool
    {
        $user = auth()->user();
        return $user && (
            $user->rol_id == 1 || 
            $user->rol_id == 3 || 
            in_array($user->email, ['adolfo@example.com', 'jonh@example.com'])
        );
    }

    public function index()
    {
        // 🔒 BLINDAJE DE SEGURIDAD
        if (!$this->esAdministrador()) {
            return redirect()->route('socio.consumo')->with('error', 'Acceso denegado: No tiene permisos administrativos.');
        }

        $usuarios = User::with('rol')->get(); 
        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        // 🔒 BLINDAJE DE SEGURIDAD CONTRA ACCESO POR URL DIRECTA
        if (!$this->esAdministrador()) {
            return redirect()->route('socio.consumo')->with('error', 'Acceso denegado.');
        }

        $roles = Rol::all();
        return view('usuarios.create', compact('roles'));
    }
    
    public function store(Request $request)
    {
        // 🔒 BLINDAJE DE SEGURIDAD EN EL ENVÍO DE FORMULARIOS
        if (!$this->esAdministrador()) {
            return redirect()->route('socio.consumo')->with('error', 'Acceso denegado.');
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'rol_id'   => 'required|integer',
            'ci'       => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:20',
        ]);

        User::create([
            'name'     => $request->name,
            'ci'       => $request->ci,
            'telefono' => $request->telefono,
            'email'    => $request->email,
            'password' => Hash::make($request->password), 
            'rol_id'   => $request->rol_id,
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Socio registrado con éxito.');
    }

    public function show(string $id) { }

    public function edit(string $id)
    {
        if (!$this->esAdministrador()) {
            return redirect()->route('socio.consumo')->with('error', 'Acceso denegado.');
        }

        $usuario = User::findOrFail($id); 
        $roles = Rol::all(); 
        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(Request $request, string $id)
    {
        if (!$this->esAdministrador()) {
            return redirect()->route('socio.consumo')->with('error', 'Acceso denegado.');
        }

        $usuario = User::findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $id,
            'rol_id'   => 'required|integer',
            'ci'       => 'nullable|string|max:20',
            'telefono' => 'nullable|string|max:20',
        ]);

        $usuario->name = $request->name;
        $usuario->ci = $request->ci;
        $usuario->telefono = $request->telefono;
        $usuario->email = $request->email;
        $usuario->rol_id = $request->rol_id;

        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->password);
        }

        $usuario->save();

        return redirect()->route('usuarios.index')->with('success', 'Socio actualizado con éxito.');
    }

    public function destroy(string $id)
    {
        if (!$this->esAdministrador()) {
            return redirect()->route('socio.consumo')->with('error', 'Acceso denegado.');
        }

        $usuario = User::findOrFail($id);
        
        if ($usuario->id === auth()->id()) {
            return redirect()->route('usuarios.index')->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->delete(); 

        return redirect()->route('usuarios.index')->with('success', 'Socio eliminado correctamente.');
    }

    // 4. EXPORTA LA LISTA COMPLETA DE SOCIOS A CSV / EXCEL
    public function exportar()
    {
        if (!$this->esAdministrador()) {
            return redirect()->route('socio.consumo')->with('error', 'Acceso denegado: No tiene permisos para exportar datos.');
        }

        // Limpia cualquier salida o búfer residual para prevenir descargas corruptas
        if (ob_get_level()) {
            ob_end_clean();
        }

        $fileName = 'lista_socios_' . date('Y-m-d_H-i') . '.csv';

        $headers = [
            "Content-Type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"$fileName\"",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return response()->stream(function () {
            $file = fopen('php://output', 'w');
            
            // BOM UTF-8 para garantizar codificación correcta de acentos y ñ
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Indicamos explícitamente a Excel que el delimitador de columnas es ';'
            fwrite($file, "sep=;\n");

            // Encabezados con delimitador ';'
            fputcsv($file, ['ID', 'Nombre Completo', 'Email', 'Carnet de Identidad', 'Telefono', 'Rol'], ';');

            User::with('rol')->chunk(200, function ($usuarios) use ($file) {
                foreach ($usuarios as $socio) {
                    $nombreRol = $socio->rol->nombre ?? ($socio->rol->nombre_rol ?? 'Sin Rol');

                    // Filas con delimitador ';'
                    fputcsv($file, [
                        $socio->id,
                        $socio->name,
                        $socio->email,
                        $socio->ci ?? 'N/A',
                        $socio->telefono ?? 'N/A',
                        $nombreRol
                    ], ';');
                }
            });

            fclose($file);
        }, 200, $headers);
    }
}