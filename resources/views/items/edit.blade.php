@extends('layouts.app')

@section('content')
<h1>アイテム編集</h1>

{{-- URLを直接編集するフォーム --}}
<form method="post" action="{{ route('items.update',$item) }}" class="edit-card">
  @csrf @method('PUT')

  <label>名前
    <input name="name" value="{{ old('name',$item->name) }}" class="edit-input">
  </label>
  @error('name') <p class="edit-error">{{ $message }}</p> @enderror

  <label>レア度
    <select name="rarity" class="edit-input">
      @foreach(['UR','SSR','SR','R','N'] as $r)
        <option value="{{ $r }}" @selected(old('rarity',$item->rarity)===$r)>{{ $r }}</option>
      @endforeach
    </select>
  </label>

  <label>当たりやすさ
    <input type="number" name="weight" value="{{ old('weight',$item->weight) }}" class="edit-input">
  </label>
  @error('weight') <p class="edit-error">{{ $message }}</p> @enderror

  <hr class="edit-divider">

  <h2>画像をアップロード</h2>
  <form method="POST" action="{{ route('items.upload',$item) }}" enctype="multipart/form-data" class="edit-card">
    @csrf
    <input type="file" name="image" accept="image/*" required class="edit-input">
    @error('image')
      <p class="edit-error">{{ $message }}</p>
    @enderror
    <div class="edit-upload-actions">
      <button type="submit" class="edit-btn">アップロードして差し替え</button>
    </div>

    {{-- プレビューをアップロードの下に表示 --}}
    @if($item->image_url)
      <p class="edit-preview-label">現在の画像:</p>
      <img src="{{ $item->image_url }}" alt="プレビュー" class="edit-preview-img">
    @endif
  </form>
</form>

{{-- 保存・戻る（枠の外・右下寄せ） --}}
<div class="edit-actions">
  <button class="edit-btn">保存</button>
  <a href="{{ route('items.index') }}" class="edit-btn edit-btn-secondary">戻る</a>
</div>
@endsection
