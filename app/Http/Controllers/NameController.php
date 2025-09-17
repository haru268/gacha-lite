<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NameController extends Controller
{
    public function edit(Request $request)
    {
        $name = $request->session()->get('username', '');
        return view('auth.name', compact('name'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'username' => ['required','string','max:50'],
        ]);
        $request->session()->put('username', $data['username']);
        return redirect()->route('gacha.home')->with('ok', 'ユーザー名を保存しました');
    }
}

