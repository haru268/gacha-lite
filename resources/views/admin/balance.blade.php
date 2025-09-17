@extends('layouts.app')

@section('content')
<h1 class="index-title">レア度倍率の調整</h1>
@if(session('ok')) <p class="index-flash">{{ session('ok') }}</p> @endif

<form method="POST" action="{{ route('balance.update') }}" class="app-card" style="max-width:560px">
  @csrf
  <p style="opacity:.9;margin-bottom:12px">
    ガチャ抽選時の実効重み = アイテムの当たりやすさ × 下記倍率。<br>
    例）URに 1.2 を入れると、UR全体が少し出やすくなります。
  </p>

  @foreach (['UR','SSR','SR','R','N'] as $r)
    <label style="display:block;margin-bottom:10px">
      <strong style="display:inline-block;width:60px">{{ $r }}</strong>
      <input type="number" step="0.1" name="{{ $r }}" value="{{ old($r, $multipliers[$r]) }}"
             style="width:120px;padding:6px;border-radius:8px;background:var(--card);color:var(--text);border:1px solid #223">
    </label>
    @error($r) <p class="app-tag" style="background:#a33">{{ $message }}</p> @enderror
  @endforeach

  <div style="text-align:right;margin-top:12px">
    <button class="app-btn">保存</button>
  </div>
</form>
@endsection
