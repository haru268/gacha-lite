<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Pull;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GachaApiController extends Controller
{
    /**
     * POST /api/gacha
     * body: { username?: string, session_key?: string }
     *  - username があればそれを所有者に
     *  - なければ session_key を使う（無ければ生成して返す）
     * 結果は JSON で返す
     */
    public function draw(Request $request)
    {
        // --- 所有者の決定（APIは stateless 想定） ---
        $username   = trim((string) $request->input('username', ''));
        $sessionKey = trim((string) $request->input('session_key', ''));

        if ($username !== '') {
            $owner = ['type' => 'username', 'value' => $username];
        } else {
            if ($sessionKey === '') {
                // セッションキー未指定なら生成して返す（クライアントは次回以降これを使う）
                $sessionKey = (string) Str::uuid();
            }
            $owner = ['type' => 'session_key', 'value' => $sessionKey];
        }

        // --- 抽選プール準備（倍率対応） ---
        $items = Item::select('id','name','rarity','image_url','weight')->get();

        $multipliers = ['UR'=>1.0,'SSR'=>1.0,'SR'=>1.0,'R'=>1.0,'N'=>1.0];
        if (Storage::disk('local')->exists('gacha_multipliers.json')) {
            $loaded = json_decode(Storage::disk('local')->get('gacha_multipliers.json'), true);
            if (is_array($loaded)) $multipliers = array_merge($multipliers, $loaded);
        }

        $pool = $items->map(function ($it) use ($multipliers) {
            $m = $multipliers[$it->rarity] ?? 1.0;
            $it->effective_weight = max(0, (int) round($it->weight * $m));
            return $it;
        })->filter(fn($it) => $it->effective_weight > 0);

        if ($pool->isEmpty()) {
            return response()->json([
                'ok' => false,
                'error' => 'no_items',
                'message' => '抽選可能なアイテムがありません'
            ], 400);
        }

        // --- 抽選 ---
        $total = $pool->sum('effective_weight');
        $rand  = random_int(1, max(1, $total));

        $running = 0;
        $picked = null;
        foreach ($pool as $it) {
            $running += $it->effective_weight;
            if ($rand <= $running) { $picked = $it; break; }
        }
        $picked = $picked ?: $pool->last();

        // --- 保存 ---
        DB::transaction(function () use ($picked, $owner) {
            $data = ['item_id' => $picked->id];
            if ($owner['type'] === 'username') {
                $data['username'] = $owner['value'];
            } else {
                $data['session_key'] = $owner['value'];
            }
            Pull::create($data); // Pullモデルは fillable に item_id, username, session_key を設定しておく
        });

        // 直近の回数（返すと便利）
        $countQ = Pull::query();
        if ($owner['type'] === 'username') {
            $countQ->where('username', $owner['value']);
        } else {
            $countQ->where('session_key', $owner['value']);
        }
        $count = (int) $countQ->count();

        // --- レスポンス ---
        return response()->json([
            'ok' => true,
            'owner' => $owner,                // {type: 'username'|'session_key', value: '...'}
            'session_key' => $sessionKey ?: null, // 生成したときクライアントが保持できるよう返す
            'result' => [
                'id'         => $picked->id,
                'name'       => $picked->name,
                'rarity'     => $picked->rarity,
                'image_url'  => $picked->image_url,
                'weight'     => $picked->weight,
                'effective_weight' => $picked->effective_weight,
            ],
            'stats' => [
                'total_draws' => $count,
            ],
        ]);
    }

    public function history(Request $request)
{
    // username または session_key を指定できるようにする
    $username   = $request->query('username');
    $sessionKey = $request->query('session_key');

    $query = \App\Models\Pull::with('item')->latest();

    if ($username) {
        $query->where('username', $username);
    } elseif ($sessionKey) {
        $query->where('session_key', $sessionKey);
    }

    // 最新20件くらいに絞る
    $pulls = $query->take(20)->get()->map(function ($p) {
        return [
            'id'        => $p->id,
            'item'      => [
                'id'    => $p->item->id,
                'name'  => $p->item->name,
                'rarity'=> $p->item->rarity,
                'image' => $p->item->image_url,
            ],
            'username'   => $p->username,
            'session_key'=> $p->session_key,
            'created_at' => $p->created_at->toDateTimeString(),
        ];
    });

    return response()->json([
        'ok'     => true,
        'count'  => $pulls->count(),
        'history'=> $pulls,
    ]);
}

public function ranking()
{
    // 1. 引いた回数ランキング
    $countRanking = \App\Models\Pull::select('username', 'session_key', DB::raw('COUNT(*) as total'))
        ->groupBy('username', 'session_key')
        ->orderByDesc('total')
        ->take(10)
        ->get()
        ->map(function ($row) {
            return [
                'owner' => $row->username ?? $row->session_key,
                'count' => $row->total,
            ];
        });

    // 2. URゲット数ランキング
    $urRanking = \App\Models\Pull::join('items', 'pulls.item_id', '=', 'items.id')
        ->where('items.rarity', 'UR')
        ->select('username', 'session_key', DB::raw('COUNT(*) as ur_count'))
        ->groupBy('username', 'session_key')
        ->orderByDesc('ur_count')
        ->take(10)
        ->get()
        ->map(function ($row) {
            return [
                'owner' => $row->username ?? $row->session_key,
                'ur_count' => $row->ur_count,
            ];
        });

    // 3. 完成率ランキング（各レアリティごとにコンプ率を出すイメージ）
    $allItems = \App\Models\Item::count();
    $completionRanking = \App\Models\Pull::select('username', 'session_key', DB::raw('COUNT(DISTINCT item_id) as got'))
        ->groupBy('username', 'session_key')
        ->orderByDesc('got')
        ->take(10)
        ->get()
        ->map(function ($row) use ($allItems) {
            return [
                'owner' => $row->username ?? $row->session_key,
                'got'   => $row->got,
                'completion_rate' => round($row->got / max(1, $allItems) * 100, 1) . '%',
            ];
        });

    return response()->json([
        'ok' => true,
        'ranking' => [
            'count'       => $countRanking,
            'ur'          => $urRanking,
            'completion'  => $completionRanking,
        ]
    ]);
}


}
