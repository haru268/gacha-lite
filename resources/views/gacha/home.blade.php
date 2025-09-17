@extends('layouts.app')

@push('styles')
  <link href="{{ asset('css/home.css') }}" rel="stylesheet">
  {{-- ★ 演出CSS（STEP1） --}}
  <link rel="stylesheet" href="{{ asset('css/home-anim.css') }}">
@endpush

@section('content')
<div class="app-card home-card">
  <h1 class="home-title">1回ガチャを引く</h1>
  <p class="app-tag home-counter">履歴：{{ $count }} 回</p>

  <form action="{{ route('gacha.draw') }}" method="post" class="home-form">
    @csrf
    <button class="app-btn home-btn">🎲 ガチャを引く</button>
  </form>

  @if(session('result_id') || $last)
    @php $item = $last?->item; @endphp
    @if($item)
      {{-- ★ ガチャ結果カード（演出＆シェア＆効果音対応） --}}
      <div id="draw-card"
           class="home-result draw-card rarity-{{ $item->rarity }}"
           data-rarity="{{ $item->rarity }}"
           data-name="{{ $item->name }}">
        <img class="app-img home-result__img draw-card__img"
             src="{{ $item->image_url }}" alt="{{ $item->name }}">

        <div class="draw-card__meta">
          <span class="rarity-badge rarity-{{ $item->rarity }}">{{ $item->rarity }}</span>
          <h2 class="home-result__name draw-card__name">{{ $item->name }}</h2>
        </div>

        {{-- 演出レイヤ --}}
        <div class="draw-card__burst"></div>
        <div class="draw-card__sparkles"></div>
      </div>

      {{-- シェアボタン（X/Twitter） --}}
      <div class="draw-actions">
        <button id="share-btn" type="button" class="app-btn">結果をシェア</button>
      </div>
    @endif
  @else
    <p class="home-empty">まだ引いていません。運試ししてみましょう！</p>
  @endif
</div>
@endsection

@push('scripts')
  {{-- ★ 効果音＆シェアJS（STEP1） --}}
  <script src="{{ asset('js/gacha.js') }}"></script>
@endpush
