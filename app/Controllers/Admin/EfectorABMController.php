<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class EfectorABMController extends BaseController
{
    // ─── Index ───────────────────────────────────────────────────────────
    public function index()
    {
        $filtro_nombre = $this->request->getGet('nombre') ?? '';
        $filtro_region = $this->request->getGet('region') ?? '';

        $model = model('EfectorModel');

        if ($filtro_nombre) $model->like('nombre', $filtro_nombre);
        if ($filtro_region) $model->where('region', $filtro_region);

        $efectores = $model->orderBy('nombre', 'ASC')->findAll();

        $regiones = array_column(
            model('EfectorModel')->select('region')->distinct()->orderBy('region', 'ASC')->findAll(),
            'region'
        );

        return view('efector_abm/efector_abm_list', [
            'efectores'     => $efectores,
            'regiones'      => $regiones,
            'filtro_nombre' => $filtro_nombre,
            'filtro_region' => $filtro_region,
        ]);
    }

    // ─── Formulario nuevo ────────────────────────────────────────────────
    public function create()
    {
        helper('form');
        return view('efector_abm/efector_form');
    }

    // ─── Store ───────────────────────────────────────────────────────────
    public function store()
    {
        if (!$this->valida()) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        model('EfectorModel')->insert([
            'nombre'            => strtoupper(trim($this->request->getPost('nombre'))),
            'nivel_complejidad' => strtoupper(trim($this->request->getPost('nivel_complejidad'))) ?: null,
            'departamento'      => strtoupper(trim($this->request->getPost('departamento')))      ?: null,
            'ubicacion'         => strtoupper(trim($this->request->getPost('ubicacion')))         ?: null,
            'region'            => strtoupper(trim($this->request->getPost('region')))            ?: null,
        ]);

        return redirect()->to(route_to('efector_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Efector guardado correctamente']);
    }

    // ─── Show / Edit ─────────────────────────────────────────────────────
    public function show($id)
    {
        helper('form');

        $efector = model('EfectorModel')->find($id);
        if (!$efector) {
            return redirect()->to(route_to('efector_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        $efector->setCampoOculto();

        return view('efector_abm/efector_form', [
            'efector' => $efector,
        ]);
    }

    // ─── Update ──────────────────────────────────────────────────────────
    public function update()
    {
        if (!$this->valida()) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        $id = $this->request->getPost('efector_id');

        model('EfectorModel')->update($id, [
            'nombre'            => strtoupper(trim($this->request->getPost('nombre'))),
            'nivel_complejidad' => strtoupper(trim($this->request->getPost('nivel_complejidad'))) ?: null,
            'departamento'      => strtoupper(trim($this->request->getPost('departamento')))      ?: null,
            'ubicacion'         => strtoupper(trim($this->request->getPost('ubicacion')))         ?: null,
            'region'            => strtoupper(trim($this->request->getPost('region')))            ?: null,
        ]);

        return redirect()->to(route_to('efector_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Efector actualizado correctamente']);
    }

    // ─── Destroy ─────────────────────────────────────────────────────────
    public function destroy()
    {
        $id = $this->request->getVar('id');

        $enUso = model('TransfusionModel')->where('efector_id', $id)->first();
        if ($enUso) {
            return redirect()->to(route_to('efector_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'No se puede eliminar: el efector tiene transfusiones registradas.']);
        }

        model('EfectorModel')->delete($id);

        return redirect()->to(route_to('efector_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Efector eliminado correctamente']);
    }

    // ─── Validación ──────────────────────────────────────────────────────
    private function valida()
    {
        return $this->validate([
            'nombre' => 'required|max_length[150]',
        ], [
            'nombre' => [
                'required'   => 'El nombre es obligatorio.',
                'max_length' => 'El nombre no puede superar los 150 caracteres.',
            ],
        ]);
    }
}