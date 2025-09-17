@extends('layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endpush

@section('content')
<div class="index-header">
  <h1 class="index-title">アイテム一覧（管理）</h1>

  <div class="index-actions">
    {{-- ソートフォーム --}}
    <form method="GET" action="{{ route('items.index') }}" class="index-sort">
      <select name="sort" onchange="this.form.submit()">
        <option value="rarity" @selected($sort==='rarity')>レア度順</option>
        <option value="name"   @selected($sort==='name')>名前順</option>
        <option value="weight" @selected($sort==='weight')>当たりやすさ順</option>
        <option value="new"    @selected($sort==='new')>追加が新しい順</option>
      </select>
    </form>

    {{-- ＋追加ボタン --}}
    <a class="index-btn" href="{{ route('items.create') }}">＋ 追加</a>

    {{-- CSVエクスポートボタン --}}
    <a class="index-btn" href="{{ route('admin.export.history') }}">CSVエクスポート</a>
  </div>
</div>

@if(session('ok'))
  <p class="index-flash">{{ session('ok') }}</p>
@endif

<div class="index-grid">
  @foreach($items as $item)
    <div class="index-card">
      <img class="index-thumb" src="{{ $item->image_url }}" alt="">
      <div class="index-body">
        <h3 class="index-name">{{ $item->name }}</h3>
        <p class="index-meta">
          レア度：
          <span class="rarity-badge rarity-{{ $item->rarity }}">
            {{ $item->rarity }}
          </span>
          <span class="index-sep">/</span>
          当たりやすさ：
          <span class="index-weight">{{ $item->weight }}</span>
        </p>
        <div class="index-actions">
          <a class="index-btn" href="{{ route('items.edit', $item) }}">編集</a>
        </div>
      </div>
    </div>
  @endforeach
</div>

<div class="index-pagination">
  {{ $items->links() }}
</div>
@endsection
