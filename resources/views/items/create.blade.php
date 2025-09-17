@extends('layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/edit.css') }}">
@endpush

@section('content')
<h1 class="edit-title">アイテム追加</h1>

<form method="POST" action="{{ route('items.store') }}" enctype="multipart/form-data" class="edit-card app-card">
  @csrf

  <label class="edit-label">名前
    <input name="name" value="{{ old('name') }}" class="edit-input">
  </label>
  @error('name') <p class="app-tag" style="background:#a33">{{ $message }}</p> @enderror

  <label class="edit-label">レア度
    <select name="rarity" class="edit-input">
      @foreach(['UR','SSR','SR','R','N'] as $r)
        <option value="{{ $r }}" @selected(old('rarity')===$r)>{{ $r }}</option>
      @endforeach
    </select>
  </label>

  <label class="edit-label">当たりやすさ
    <input type="number" name="weight" value="{{ old('weight', 10) }}" class="edit-input">
  </label>
  @error('weight') <p class="app-tag" style="background:#a33">{{ $message }}</p> @enderror

  <fieldset class="edit-fieldset">
    <legend>画像</legend>
    <label class="edit-label">ファイルをアップロード
      <input type="file" name="image" accept="image/*" class="edit-input">
    </label>
    <div class="edit-or">— or —</div>
    <label class="edit-label">画像URLを直接指定（/storage/... も可）
      <input name="image_url" value="{{ old('image_url') }}" class="edit-input">
    </label>
    @error('image')     <p class="app-tag" style="background:#a33">{{ $message }}</p> @enderror
    @error('image_url') <p class="app-tag" style="background:#a33">{{ $message }}</p> @enderror
  </fieldset>

  <div class="edit-actions">
    <button class="app-btn">追加する</button>
    <a href="{{ route('items.index') }}" class="edit-cancel">戻る</a>
  </div>
</form>
@endsection
