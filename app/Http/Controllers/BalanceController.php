<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BalanceController extends Controller
{
    private function path(): string
    {
        return 'gacha_multipliers.json'; // storage/app/...
    }

    private function defaults(): array
    {
        return ['UR'=>1.0, 'SSR'=>1.0, 'SR'=>1.0, 'R'=>1.0, 'N'=>1.0];
    }

    public function edit()
    {
        $json = Storage::disk('local')->exists($this->path())
            ? json_decode(Storage::disk('local')->get($this->path()), true)
            : $this->defaults();

        $multipliers = array_merge($this->defaults(), $json ?? []);
        return view('admin.balance', compact('multipliers'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'UR'  => ['required','numeric','min:0'],
            'SSR' => ['required','numeric','min:0'],
            'SR'  => ['required','numeric','min:0'],
            'R'   => ['required','numeric','min:0'],
            'N'   => ['required','numeric','min:0'],
        ]);

        Storage::disk('local')->put($this->path(), json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

        return redirect()->route('balance.edit')->with('ok', '倍率を保存しました');
    }
}
