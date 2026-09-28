<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class OperativoABMController extends BaseController
{
    // ═══════════════════════════════════════════════════════════════
    // TIPO OPERATIVO
    // ═══════════════════════════════════════════════════════════════

    public function indexTipo()
    {
        $tipos = model('TipoOperativoModel')->orderBy('nombre', 'ASC')->findAll();

        return view('operativo_abm/operativo_abm_list', [
            'tipos'      => $tipos,
            'seccion'    => 'tipo',
        ]);
    }

    public function createTipo()
    {
        helper('form');
        return view('operativo_abm/tipo_form', [
            'seccion' => 'tipo',
        ]);
    }

    public function storeTipo()
    {
        if (!$this->validaTipo()) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        $nombre = strtoupper(trim($this->request->getPost('nombre')));

        $existe = model('TipoOperativoModel')->where('nombre', $nombre)->first();
        if ($existe) {
            return redirect()->back()->withInput()
                ->with('errors', ['nombre' => 'Ya existe un tipo con ese nombre.'])
                ->with('msg', ['type' => 'danger', 'body' => 'Ya existe un tipo con ese nombre.']);
        }

        model('TipoOperativoModel')->insert(['nombre' => $nombre]);

        return redirect()->to(route_to('tipo_operativo_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Tipo guardado correctamente']);
    }

    public function showTipo($id)
    {
        helper('form');

        $tipo = model('TipoOperativoModel')->find($id);
        if (!$tipo) {
            return redirect()->to(route_to('tipo_operativo_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        $tipo->setCampoOculto();

        return view('operativo_abm/tipo_form', [
            'tipo'    => $tipo,
            'seccion' => 'tipo',
        ]);
    }

    public function updateTipo()
    {
        if (!$this->validaTipo()) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        $id     = $this->request->getPost('tipo_op_id');
        $nombre = strtoupper(trim($this->request->getPost('nombre')));

        model('TipoOperativoModel')->update($id, ['nombre' => $nombre]);

        return redirect()->to(route_to('tipo_operativo_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Tipo actualizado correctamente']);
    }

    public function destroyTipo()
    {
        $id = $this->request->getVar('id');

        // Verificar si está en uso
        $enUso = model('OperativoModel')->where('tipo_id', $id)->first();
        if ($enUso) {
            return redirect()->to(route_to('tipo_operativo_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'No se puede eliminar: el tipo está en uso por uno o más operativos.']);
        }

        model('TipoOperativoModel')->delete($id);

        return redirect()->to(route_to('tipo_operativo_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Tipo eliminado correctamente']);
    }

    private function validaTipo()
    {
        return $this->validate([
            'nombre' => 'required|max_length[100]',
        ], [
            'nombre' => [
                'required'   => 'El nombre es obligatorio.',
                'max_length' => 'El nombre no puede superar los 100 caracteres.',
            ],
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // OPERATIVO
    // ═══════════════════════════════════════════════════════════════

    public function indexOperativo()
    {
        $filtro_nombre = $this->request->getGet('nombre') ?? '';
        $filtro_tipo   = $this->request->getGet('tipo')   ?? '';

        $model = model('OperativoModel')->conTipo();

        if ($filtro_nombre) $model->like('operativo.nombre', $filtro_nombre);
        if ($filtro_tipo)   $model->where('operativo.tipo_id', $filtro_tipo);

        $operativos = $model->orderBy('operativo.nombre', 'ASC')
                            ->orderBy('tipo_operativo.nombre', 'ASC')
                            ->findAll();

        $tipos = model('TipoOperativoModel')->orderBy('nombre', 'ASC')->findAll();

        return view('operativo_abm/operativo_abm_list', [
            'operativos'    => $operativos,
            'tipos'         => $tipos,
            'seccion'       => 'operativo',
            'filtro_nombre' => $filtro_nombre,
            'filtro_tipo'   => $filtro_tipo,
        ]);
    }

    public function createOperativo()
    {
        helper('form');

        $tipos = model('TipoOperativoModel')->orderBy('nombre', 'ASC')->findAll();

        return view('operativo_abm/operativo_form', [
            'tipos'   => $tipos,
            'seccion' => 'operativo',
        ]);
    }

    public function storeOperativo()
    {
        if (!$this->validaOperativo()) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        $nombre  = strtoupper(trim($this->request->getPost('nombre')));
        $tipo_id = $this->request->getPost('tipo_id') ?: null;

        model('OperativoModel')->insert([
            'nombre'  => $nombre,
            'tipo_id' => $tipo_id,
        ]);

        return redirect()->to(route_to('operativo_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Operativo guardado correctamente']);
    }

    public function showOperativo($id)
    {
        helper('form');

        $operativo = model('OperativoModel')->conTipo()->find($id);
        if (!$operativo) {
            return redirect()->to(route_to('operativo_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        $tipos = model('TipoOperativoModel')->orderBy('nombre', 'ASC')->findAll();

        $operativo->setCampoOculto();

        return view('operativo_abm/operativo_form', [
            'operativo' => $operativo,
            'tipos'     => $tipos,
            'seccion'   => 'operativo',
        ]);
    }

    public function updateOperativo()
    {
        if (!$this->validaOperativo()) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        $id      = $this->request->getPost('operativo_id');
        $nombre  = strtoupper(trim($this->request->getPost('nombre')));
        $tipo_id = $this->request->getPost('tipo_id') ?: null;

        model('OperativoModel')->update($id, [
            'nombre'  => $nombre,
            'tipo_id' => $tipo_id,
        ]);

        return redirect()->to(route_to('operativo_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Operativo actualizado correctamente']);
    }

    public function destroyOperativo()
    {
        $id = $this->request->getVar('id');

        // Verificar si está en uso en cantidad_operativo
        $enUso = model('CantidadOperativoModel')->where('operativo_id', $id)->first();
        if ($enUso) {
            return redirect()->to(route_to('operativo_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'No se puede eliminar: el operativo está en uso en registros de cantidad.']);
        }

        model('OperativoModel')->delete($id);

        return redirect()->to(route_to('operativo_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Operativo eliminado correctamente']);
    }

    private function validaOperativo()
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