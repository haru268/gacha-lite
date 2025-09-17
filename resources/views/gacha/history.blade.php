@extends('layouts.app')

@push('styles')
  <link href="{{ asset('css/history.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="history-header">
  <h1 class="history-title">履歴</h1>

  <div class="history-controls">
    {{-- ソート --}}
    <form method="GET" action="{{ route('gacha.history') }}" class="history-sort">
      <select name="sort" onchange="this.form.submit()">
        <option value="rarity"       @selected(($sort ?? 'obtained_new')==='rarity')>レア度順</option>
        <option value="name"         @selected(($sort ?? 'obtained_new')==='name')>名前順</option>
        <option value="obtained_new" @selected(($sort ?? 'obtained_new')==='obtained_new')>取得が新しい順</option>
        <option value="obtained_old" @selected(($sort ?? 'obtained_new')==='obtained_old')>取得が古い順</option>
      </select>
      {{-- 検索語を維持 --}}
      <input type="hidden" name="q" value="{{ $q ?? '' }}">
    </form>

    {{-- 検索 --}}
    <form method="GET" action="{{ route('gacha.history') }}" class="history-search">
      <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="名前で検索">
      <input type="hidden" name="sort" value="{{ $sort ?? 'obtained_new' }}">
      <button class="history-btn">検索</button>
      @if(!empty($q))
        <a href="{{ route('gacha.history', ['sort' => $sort]) }}" class="history-clear">クリア</a>
      @endif
    </form>
  </div>
</div>


<div class="app-grid app-grid-3 history-grid">
  @foreach($pulls as $pull)
    <div class="app-card history-card">
      <img class="app-img history-card__img" src="{{ $pull->item->image_url }}" alt="">
      <h3 class="history-card__name">{{ $pull->item->name }}</h3>

      <p class="app-tag history-card__rarity">
        レア度：
        <span class="rarity-badge rarity-{{ $pull->item->rarity }}">
          {{ $pull->item->rarity }}
        </span>
      </p>

      <p class="history-card__time">
        {{ $pull->created_at->format('Y-m-d H:i') }}
      </p>
    </div>
  @endforeach
</div>

<div class="history-pagination">
  {{-- ビュー指定はそのまま、ソート保持はコントローラで appends 済み --}}
  {{ $pulls->links('vendor.pagination.default') }}
</div>
@endsection
