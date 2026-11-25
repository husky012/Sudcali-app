<?php

namespace App\Http\Controllers\PuntoVenta\Admin;

use App\Enums\PuestoEmpleado;
use App\Http\Controllers\Controller;
use App\Http\Requests\PuntoVenta\Admin\GuardarEmpleadoRequest;
use App\Models\Empleado;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmpleadoController extends Controller
{
    /**
     * Listado de empleados con búsqueda y filtros.
     */
    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'puesto' => ['nullable', Rule::enum(PuestoEmpleado::class)],
            'sucursal' => ['nullable', 'integer'],
            'estado' => ['nullable', 'in:activos,inactivos'],
        ]);

        $empleados = Empleado::query()
            ->with('sucursal')
            ->when($filtros['buscar'] ?? null, fn (Builder $consulta, string $buscar) => $consulta->where(
                fn (Builder $coincidencia) => $coincidencia
                    ->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('apellido_paterno', 'like', "%{$buscar}%")
                    ->orWhere('apellido_materno', 'like', "%{$buscar}%")
                    ->orWhere('correo', 'like', "%{$buscar}%")
                    ->orWhere('telefono', 'like', "%{$buscar}%")
            ))
            ->when($filtros['puesto'] ?? null, fn (Builder $consulta, string $puesto) => $consulta->where('puesto', $puesto))
            ->when($filtros['sucursal'] ?? null, fn (Builder $consulta, int $sucursal) => $consulta->where('sucursal_id', $sucursal))
            ->when(($filtros['estado'] ?? null) === 'activos', fn (Builder $consulta) => $consulta->where('activo', true))
            ->when(($filtros['estado'] ?? null) === 'inactivos', fn (Builder $consulta) => $consulta->where('activo', false))
            ->orderByDesc('activo')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('pos.admin.empleados.index', [
            'empleados' => $empleados,
            'filtros' => $filtros,
            'puestos' => PuestoEmpleado::cases(),
            'sucursales' => Sucursal::orderBy('nombre')->get(),
        ]);
    }

    /**
     * Formulario de alta.
     */
    public function create(): View
    {
        return view('pos.admin.empleados.form', [
            'empleado' => new Empleado(['activo' => true, 'fecha_contratacion' => today()]),
            ...$this->opcionesFormulario(),
        ]);
    }

    /**
     * Guarda un nuevo empleado.
     */
    public function store(GuardarEmpleadoRequest $request): RedirectResponse
    {
        $datos = $request->safe()->except(['foto', 'password']);
        $datos['password'] = $request->validated('password');

        if ($request->hasFile('foto')) {
            $datos['foto'] = $request->file('foto')->store('empleados', 'local');
        }

        $empleado = Empleado::create($datos);

        return to_route('pos.admin.empleados.index')
            ->with('exito', "Se dio de alta a {$empleado->nombreCompleto()}.");
    }

    /**
     * Formulario de edición.
     */
    public function edit(Empleado $empleado): View
    {
        return view('pos.admin.empleados.form', [
            'empleado' => $empleado,
            ...$this->opcionesFormulario(),
        ]);
    }

    /**
     * Entrega la foto, que vive fuera de la carpeta pública, solo a quien puede administrar.
     */
    public function foto(Empleado $empleado): StreamedResponse
    {
        abort_unless($empleado->foto && Storage::disk('local')->exists($empleado->foto), 404);

        return Storage::disk('local')->response($empleado->foto, headers: ['Cache-Control' => 'private, no-store']);
    }

    /**
     * Actualiza un empleado. La contraseña solo cambia si se captura una nueva.
     */
    public function update(GuardarEmpleadoRequest $request, Empleado $empleado): RedirectResponse
    {
        $datos = $request->safe()->except(['foto', 'password']);

        if ($request->filled('password')) {
            $datos['password'] = $request->validated('password');
        }

        if ($empleado->is($request->user('empleado'))) {
            // Nadie puede quitarse a sí mismo el acceso o el puesto de administración.
            $datos['activo'] = true;
            $datos['puesto'] = $empleado->puesto->value;
        }

        if ($request->hasFile('foto')) {
            $this->borrarFoto($empleado);
            $datos['foto'] = $request->file('foto')->store('empleados', 'local');
        }

        $empleado->update($datos);

        return to_route('pos.admin.empleados.index')
            ->with('exito', "Se actualizaron los datos de {$empleado->nombreCompleto()}.");
    }

    /**
     * Elimina al empleado; si ya tiene ventas o cortes solo se da de baja para conservar el historial.
     */
    public function destroy(Request $request, Empleado $empleado): RedirectResponse
    {
        if ($empleado->is($request->user('empleado'))) {
            return back()->with('aviso', 'No puedes darte de baja a ti mismo.');
        }

        if ($empleado->ventas()->exists() || $empleado->cortesCaja()->exists()) {
            $empleado->update(['activo' => false]);

            return to_route('pos.admin.empleados.index')
                ->with('aviso', "{$empleado->nombreCompleto()} tiene ventas registradas: se dio de baja en lugar de eliminarlo.");
        }

        $this->borrarFoto($empleado);
        $empleado->delete();

        return to_route('pos.admin.empleados.index')
            ->with('exito', "Se eliminó a {$empleado->nombreCompleto()}.");
    }

    /**
     * Catálogos para los selects del formulario.
     *
     * @return array{puestos: array<int, PuestoEmpleado>, sucursales: Collection<int, Sucursal>}
     */
    private function opcionesFormulario(): array
    {
        return [
            'puestos' => PuestoEmpleado::cases(),
            'sucursales' => Sucursal::where('activa', true)->orderBy('nombre')->get(),
        ];
    }

    /**
     * Borra la foto guardada en el disco privado, si existe.
     */
    private function borrarFoto(Empleado $empleado): void
    {
        if ($empleado->foto) {
            Storage::disk('local')->delete($empleado->foto);
        }
    }
}
