(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var card = document.getElementById('draw-card');
        if (!card) return;

        // rarity と name を取得
        var rarity = card.getAttribute('data-rarity') || 'N';
        var name = card.getAttribute('data-name') || '';

        // 効果音のマップ
        var sounds = {
            'UR': '/audio/ur.mp3',
            'SSR': '/audio/ssr.mp3',
            'SR': '/audio/sr.mp3',
            'R': '/audio/r.mp3',
            'N': '/audio/n.mp3'
        };
        var src = sounds[rarity] || sounds['N'];

        // 再生（ユーザー操作後の再訪でも鳴るようにtry/catch）
        try {
            var audio = new Audio(src);
            audio.volume = 0.85; // 小さめスタートはお好みで
            audio.play().catch(function () { /* ブラウザの自動再生ブロック対策 */ });
        } catch (e) { }

        // CSSアニメーションを起動
        card.classList.add('is-animate');

        // シェア（X/Twitter）
        var btn = document.getElementById('share-btn');
        if (btn) {
            btn.addEventListener('click', function () {
                var text = `【ガチャ結果】${name}（${rarity}） #GachaLite`;
                var url = location.origin; // トップURL。個別詳細があれば差し替え可
                var intent = 'https://twitter.com/intent/tweet'
                    + '?text=' + encodeURIComponent(text)
                    + '&url=' + encodeURIComponent(url);
                window.open(intent, '_blank');
            });
        }
    });
})();
