@extends('layouts.app')

@section('content')
<h1 class="index-title">ユーザー名を設定</h1>
<form method="POST" action="{{ route('name.update') }}" class="app-card" style="max-width:520px">
  @csrf
  <label style="display:block">
    <span>ユーザー名（表示名）</span>
    <input name="username" value="{{ old('username', $name) }}"
           style="width:100%;padding:8px;border-radius:8px;background:var(--card);color:var(--text);border:1px solid #223">
  </label>
  @error('username') <p class="app-tag" style="background:#a33">{{ $message }}</p> @enderror
  <div style="text-align:right;margin-top:12px">
    <button class="app-btn">保存</button>
  </div>
</form>
@endsection
