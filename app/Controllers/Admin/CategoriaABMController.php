<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class CategoriaABMController extends BaseController
{
    // ═══════════════════════════════════════════════════════════════
    // CATEGORIA
    // ═══════════════════════════════════════════════════════════════

    public function indexCategoria()
    {
        $categorias = model('CategoriaModel')->orderBy('nombre', 'ASC')->findAll();

        return view('consulta_reclamo/categoria_abm_list', [
            'categorias' => $categorias,
            'seccion'    => 'categoria',
        ]);
    }

    public function createCategoria()
    {
        helper('form');
        return view('consulta_reclamo/categoria_form', ['seccion' => 'categoria']);
    }

    public function storeCategoria()
    {
        if (!$this->validaCategoria()) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        $nombre = strtoupper(trim($this->request->getPost('nombre')));

        $existe = model('CategoriaModel')->where('nombre', $nombre)->first();
        if ($existe) {
            return redirect()->back()->withInput()
                ->with('errors', ['nombre' => 'Ya existe una categoría con ese nombre.'])
                ->with('msg', ['type' => 'danger', 'body' => 'Ya existe una categoría con ese nombre.']);
        }

        model('CategoriaModel')->insert(['nombre' => $nombre]);

        return redirect()->to(route_to('categoria_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Categoría guardada correctamente']);
    }

    public function showCategoria($id)
    {
        helper('form');

        $categoria = model('CategoriaModel')->find($id);
        if (!$categoria) {
            return redirect()->to(route_to('categoria_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        $categoria->setCampoOculto();

        return view('consulta_reclamo/categoria_form', [
            'categoria' => $categoria,
            'seccion'   => 'categoria',
        ]);
    }

    public function updateCategoria()
    {
        if (!$this->validaCategoria()) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        $id     = $this->request->getPost('categoria_id');
        $nombre = strtoupper(trim($this->request->getPost('nombre')));

        model('CategoriaModel')->update($id, ['nombre' => $nombre]);

        return redirect()->to(route_to('categoria_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Categoría actualizada correctamente']);
    }

    public function destroyCategoria()
    {
        $id    = $this->request->getVar('id');
        $enUso = model('ConsultaReclamoModel')->where('categoria_id', $id)->first();
        if ($enUso) {
            return redirect()->to(route_to('categoria_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'No se puede eliminar: la categoría está en uso.']);
        }
        model('CategoriaModel')->delete($id);
        return redirect()->to(route_to('categoria_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Categoría eliminada correctamente']);
    }

    private function validaCategoria()
    {
        return $this->validate([
            'nombre' => 'required|max_length[250]',
        ], [
            'nombre' => ['required' => 'El nombre es obligatorio.'],
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // SUB CATEGORIA
    // ═══════════════════════════════════════════════════════════════

    public function indexSubCategoria()
    {
        $sub_categorias = model('SubCategoriaModel')->orderBy('nombre', 'ASC')->findAll();

        return view('consulta_reclamo/categoria_abm_list', [
            'sub_categorias' => $sub_categorias,
            'seccion'        => 'sub_categoria',
        ]);
    }

    public function createSubCategoria()
    {
        helper('form');
        return view('consulta_reclamo/categoria_form', ['seccion' => 'sub_categoria']);
    }

    public function storeSubCategoria()
    {
        if (!$this->validaSubCategoria()) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        $nombre = strtoupper(trim($this->request->getPost('nombre')));

        $existe = model('SubCategoriaModel')->where('nombre', $nombre)->first();
        if ($existe) {
            return redirect()->back()->withInput()
                ->with('errors', ['nombre' => 'Ya existe una sub categoría con ese nombre.'])
                ->with('msg', ['type' => 'danger', 'body' => 'Ya existe una sub categoría con ese nombre.']);
        }

        model('SubCategoriaModel')->insert(['nombre' => $nombre]);

        return redirect()->to(route_to('sub_categoria_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Sub Categoría guardada correctamente']);
    }

    public function showSubCategoria($id)
    {
        helper('form');

        $sub_categoria = model('SubCategoriaModel')->find($id);
        if (!$sub_categoria) {
            return redirect()->to(route_to('sub_categoria_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'Registro no encontrado']);
        }

        $sub_categoria->setCampoOculto();

        return view('consulta_reclamo/categoria_form', [
            'sub_categoria' => $sub_categoria,
            'seccion'       => 'sub_categoria',
        ]);
    }

    public function updateSubCategoria()
    {
        if (!$this->validaSubCategoria()) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors())
                ->with('msg', ['type' => 'danger', 'body' => 'Corrija los errores']);
        }

        $id     = $this->request->getPost('sub_categoria_id');
        $nombre = strtoupper(trim($this->request->getPost('nombre')));

        model('SubCategoriaModel')->update($id, ['nombre' => $nombre]);

        return redirect()->to(route_to('sub_categoria_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Sub Categoría actualizada correctamente']);
    }

    public function destroySubCategoria()
    {
        $id    = $this->request->getVar('id');
        $enUso = model('ConsultaReclamoModel')->where('sub_categoria_id', $id)->first();
        if ($enUso) {
            return redirect()->to(route_to('sub_categoria_list'))
                ->with('msg', ['type' => 'danger', 'body' => 'No se puede eliminar: la sub categoría está en uso.']);
        }
        model('SubCategoriaModel')->delete($id);
        return redirect()->to(route_to('sub_categoria_list'))
            ->with('msg', ['type' => 'success', 'body' => 'Sub Categoría eliminada correctamente']);
    }

    private function validaSubCategoria()
    {
        return $this->validate([
            'nombre' => 'required|max_length[250]',
        ], [
            'nombre' => ['required' => 'El nombre es obligatorio.'],
        ]);
    }
}