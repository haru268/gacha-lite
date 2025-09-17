<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Gacha Lite</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- 共通CSS --}}
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">
  <link href="/css/home.css" rel="stylesheet">
  <link href="/css/catalog.css" rel="stylesheet">
  <link href="/css/history.css" rel="stylesheet">
  <link href="{{ asset('css/edit.css') }}" rel="stylesheet">
  <link href="{{ asset('css/index.css') }}" rel="stylesheet">
  <link href="{{ asset('css/common.css') }}" rel="stylesheet">

  @stack('styles')
</head>
@stack('scripts')
<body class="app-body">
  <header class="app-wrap app-header">
    <a class="app-logo" href="{{ route('gacha.home') }}"><strong>🎰 Gacha Lite</strong></a>
    <nav class="app-nav">
      <a class="app-nav__link" href="{{ route('gacha.catalog') }}">図鑑</a>
      <a class="app-nav__link" href="{{ route('gacha.history') }}">履歴</a>
      <a class="app-nav__link" href="{{ route('ranking.index') }}">ランキング</a>
      <a class="app-nav__link" href="{{ route('name.edit') }}">
      👤 {{ session('username','ゲスト') }}
    </a>
    </nav>
  </header>

  <main class="app-wrap">
    @yield('content')
  </main>
</body>
</html>
