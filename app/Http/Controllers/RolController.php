<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests; 
use App\Rol;
use App\User;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;
use App\Http\Middleware\Authenticate;

class RolController extends Controller
{
    public function _construct()
    {
        $this->middleware('auth');
    }
    public function index(request $request)
    {
        $word = $request->get('word_bs');
        $rows_pagina = array('10','25','50','100');
        $rows_page = $request->get('rows_per_page');

        if ($rows_page == '') {
            $rows_page = 10;
        }
        $roles = DB::table('rol')
        ->paginate($rows_page);
        return view('catalogos.rol.index',['roles'=>$roles,'request'=>$request, 'rows_pagina'=>$rows_pagina]);
    }
    public function store(request $request)
    {
        $rol = new Rol();
        $rol->rol = $request->get('rol');
        $rol->save();
        return redirect()->route('rol');
    }
    public function show($id)
    {
        $rol = DB::table('rol')
        ->where('id',$id)
        ->first();

        $users= DB::table('users')
        ->where('rol',$id)
        ->paginate(10);

        return view('catalogos.rol.show',['rol'=>$rol, 'users'=>$users]);
    }

    public function update(request $request, $id)
    {
        $rol = Rol::findOrFail($id);
        $rol->rol = $request->get('rol');
        $rol->update();
        return redirect()->route('rol');
    }

    public function destroy($id)
    {
        $rol = Rol::findOrFail($id);
        $rol->delete();
        return redirect()->route('rol');
    }
}
