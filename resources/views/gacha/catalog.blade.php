@extends('layouts.app')

@push('styles')
  <link href="{{ asset('css/catalog.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="catalog-header">
  <h1 class="catalog-title">図鑑</h1>

  <div class="catalog-controls">
    {{-- ソート --}}
    <form method="GET" action="{{ route('gacha.catalog') }}" class="catalog-sort">
      <select name="sort" onchange="this.form.submit()">
        <option value="rarity"          @selected(($sort ?? 'rarity')==='rarity')>レア度順</option>
        <option value="name"            @selected(($sort ?? 'rarity')==='name')>名前順</option>
        <option value="implemented_new" @selected(($sort ?? 'rarity')==='implemented_new')>実装順（新しい順）</option>
        <option value="implemented_old" @selected(($sort ?? 'rarity')==='implemented_old')>実装順（古い順）</option>
      </select>
      {{-- 検索語とフィルタ状態を維持 --}}
      <input type="hidden" name="q" value="{{ $q ?? '' }}">
      <input type="hidden" name="only_unowned" value="{{ $only ? 1 : 0 }}">
    </form>

    {{-- 検索 --}}
    <form method="GET" action="{{ route('gacha.catalog') }}" class="catalog-search">
      <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="名前で検索">
      <input type="hidden" name="sort" value="{{ $sort ?? 'rarity' }}">
      <input type="hidden" name="only_unowned" value="{{ $only ? 1 : 0 }}">
      <button class="catalog-btn">検索</button>
      @if(!empty($q))
        <a href="{{ route('gacha.catalog', ['sort' => $sort, 'only_unowned' => $only ? 1 : 0]) }}"
           class="catalog-clear">クリア</a>
      @endif
    </form>

    {{-- 未取得のみ --}}
    <form method="GET" action="{{ route('gacha.catalog') }}" class="catalog-filter">
      {{-- 現在のソートと検索を保持 --}}
      <input type="hidden" name="sort" value="{{ $sort ?? 'rarity' }}">
      <input type="hidden" name="q" value="{{ $q ?? '' }}">
      <label class="catalog-check">
        <input type="checkbox" name="only_unowned" value="1" onchange="this.form.submit()"
               @checked($only)>
        未取得のみ
      </label>
    </form>
  </div>
</div>

{{-- 完成率 --}}
<div class="catalog-progress">
  <div class="catalog-progress__bar">
    <div class="catalog-progress__fill" style="width: {{ $completion }}%"></div>
  </div>
  <div class="catalog-progress__text">
    図鑑完成率：{{ $ownedCount }} / {{ $totalCount }}（{{ $completion }}%）
  </div>
</div>

<p class="catalog-note">枠が光っていれば所持済み（＝一度でも引いた）</p>

<div class="app-grid app-grid-3 catalog-grid">
  @foreach($items as $item)
    <div class="app-card catalog-card {{ in_array($item->id, $ownedIds) ? 'app-owned catalog-card--owned' : '' }}">
      <img class="app-img catalog-card__img" src="{{ $item->image_url }}" alt="">
      <h3 class="catalog-card__name">{{ $item->name }}</h3>
      <p class="catalog-card__rarity">
        レア度：
        <span class="rarity-badge rarity-{{ $item->rarity }}">{{ $item->rarity }}</span>
      </p>
    </div>
  @endforeach
</div>
@endsection
