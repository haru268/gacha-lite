{{-- resources/views/ranking/index.blade.php --}}
@extends('layouts.app')

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/ranking.css') }}">
@endpush

@section('content')
<h1 class="rank-title">ランキング</h1>

{{-- 期間フィルタ --}}
<form method="GET" action="{{ route('ranking.index') }}" 
      class="app-card rank-filter">
  <div class="rank-filter__range">
    <label>From
      <input type="date" name="from" value="{{ $from }}">
    </label>
    <label>To
      <input type="date" name="to" value="{{ $to }}">
    </label>
    <button class="app-btn">絞り込み</button>
  </div>

  <div class="rank-filter__csv">
    <a class="index-btn" href="{{ route('ranking.csv', array_filter(['type'=>'count','from'=>$from,'to'=>$to])) }}">回数CSV</a>
    <a class="index-btn" href="{{ route('ranking.csv', array_filter(['type'=>'ur','from'=>$from,'to'=>$to])) }}">UR CSV</a>
    <a class="index-btn" href="{{ route('ranking.csv', array_filter(['type'=>'completion','from'=>$from,'to'=>$to])) }}">完成率CSV</a>
    <a class="index-btn" href="{{ route('ranking.csv', array_filter(['type'=>'pulls','from'=>$from,'to'=>$to])) }}">全履歴CSV</a>
  </div>
</form>


{{-- 以下はそのまま（ランキング3種の表示） --}}
<h2 class="rank-heading">🎲 ガチャ回数ランキング</h2>
<ol class="rank-list">
  @forelse($countRanking as $i => $r)
    <li class="rank-item">
      <span class="rank-medal">@if($i===0) 👑 @elseif($i===1) 🥈 @elseif($i===2) 🥉 @else #{{ $i+1 }} @endif</span>
      <span class="rank-name">{{ $r->username }}</span>
      <span class="rank-value">{{ number_format($r->cnt) }} 回</span>
    </li>
  @empty <li class="rank-empty">データがありません</li> @endforelse
</ol>

<h2 class="rank-heading">💎 UR 獲得数ランキング</h2>
<ol class="rank-list">
  @forelse($urRanking as $i => $r)
    <li class="rank-item">
      <span class="rank-medal">@if($i===0) 👑 @elseif($i===1) 🥈 @elseif($i===2) 🥉 @else #{{ $i+1 }} @endif</span>
      <span class="rank-name">{{ $r->username }}</span>
      <span class="rank-value">{{ number_format($r->ur_count) }} 枚</span>
    </li>
  @empty <li class="rank-empty">データがありません</li> @endforelse
</ol>

<h2 class="rank-heading">📖 図鑑完成率ランキング</h2>
<p class="rank-note">全アイテム数：{{ $allCount }} 種</p>
<ol class="rank-list">
  @forelse($completionRanking as $i => $r)
    <li class="rank-item">
      <span class="rank-medal">@if($i===0) 👑 @elseif($i===1) 🥈 @elseif($i===2) 🥉 @else #{{ $i+1 }} @endif</span>
      <span class="rank-name">{{ $r->username }}</span>
      <span class="rank-value">{{ $r->rate }}%（{{ $r->owned }} / {{ $r->total }}）</span>
    </li>
  @empty <li class="rank-empty">データがありません</li> @endforelse
</ol>
@endsection
