<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Pull;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class GachaController extends Controller
{
    // ✅ ログインなしでも“あなた”を識別：セッションにUUIDを保存
    private function sessionKey(Request $request): string
    {
        if (!$request->session()->has('gacha_session')) {
            $request->session()->put('gacha_session', Str::uuid()->toString());
        }
        return $request->session()->get('gacha_session');
    }

    // ✅ ユーザー識別子を返す（username優先、無ければsession_key）
    private function ownerKey(Request $request): array
    {
        $username = trim((string)$request->session()->get('username', ''));
        if ($username !== '') {
            return ['type' => 'username', 'value' => $username];
        }
        $session = $this->sessionKey($request);
        return ['type' => 'session_key', 'value' => $session];
    }

    // ✅ ホーム画面：最後に引いた結果と回数を表示
    public function home(Request $request)
{
    $owner = $this->ownerKey($request);

    $q = Pull::with('item');
    if ($owner['type'] === 'username') {
        $q->where('username', $owner['value']);
    } else {
        $q->where('session_key', $owner['value']);
    }

    $last = $q->latest()->first();

    $countQ = Pull::query();
    if ($owner['type'] === 'username') {
        $countQ->where('username', $owner['value']);
    } else {
        $countQ->where('session_key', $owner['value']);
    }
    $count = $countQ->count();

    return view('gacha.home', compact('last','count'));
}


    // ✅ 抽選：重み付きランダム（倍率対応）
    public function draw(Request $request)
    {
        $owner = $this->ownerKey($request);

        // アイテム取得
        $items = Item::select('id','name','rarity','weight')->get();

        // 倍率をロード
        $multipliers = ['UR'=>1.0,'SSR'=>1.0,'SR'=>1.0,'R'=>1.0,'N'=>1.0];
        if (Storage::disk('local')->exists('gacha_multipliers.json')) {
            $loaded = json_decode(Storage::disk('local')->get('gacha_multipliers.json'), true);
            if (is_array($loaded)) {
                $multipliers = array_merge($multipliers, $loaded);
            }
        }

        // 実効重みを合成
        $pool = $items->map(function($it) use($multipliers) {
            $m = $multipliers[$it->rarity] ?? 1.0;
            $it->effective_weight = max(0, (int) round($it->weight * $m));
            return $it;
        })->filter(fn($it) => $it->effective_weight > 0);

        // 抽選
        $total = $pool->sum('effective_weight');
        $rand  = random_int(1, max(1, $total));

        $running = 0;
        $picked = null;
        foreach ($pool as $it) {
            $running += $it->effective_weight;
            if ($rand <= $running) { $picked = $it; break; }
        }
        $picked = $picked ?: $pool->last();

        // 保存
        DB::transaction(function () use ($picked, $owner) {
            $data = ['item_id' => $picked->id];
            if ($owner['type'] === 'username') {
                $data['username'] = $owner['value'];
            } else {
                $data['session_key'] = $owner['value'];
            }
            Pull::create($data);
        });

        return redirect()->route('gacha.home')->with('result_id', $picked->id);
    }

    // ✅ 履歴
    public function history(Request $request)
    {
        $owner = $this->ownerKey($request);
        $sort  = $request->get('sort', 'obtained_new');
        $qtxt  = trim((string)$request->get('q', ''));

        $rarityCase = "CASE items.rarity
            WHEN 'UR'  THEN 1
            WHEN 'SSR' THEN 2
            WHEN 'SR'  THEN 3
            WHEN 'R'   THEN 4
            WHEN 'N'   THEN 5
            ELSE 6 END";

        $query = Pull::join('items','pulls.item_id','=','items.id')
                     ->select('pulls.*');

        // 所有者で絞り込み
        if ($owner['type'] === 'username') {
            $query->where('pulls.username', $owner['value']);
        } else {
            $query->where('pulls.session_key', $owner['value']);
        }

        // 名前検索
        if ($qtxt !== '') {
            $query->where('items.name','like','%'.$qtxt.'%');
        }

        // ソート
        switch ($sort) {
            case 'rarity':       $query->orderByRaw($rarityCase)->orderBy('items.name'); break;
            case 'name':         $query->orderBy('items.name'); break;
            case 'obtained_old': $query->orderBy('pulls.created_at','asc'); break;
            default:             $query->orderBy('pulls.created_at','desc'); break;
        }

        $pulls = $query->with('item')->paginate(20)->appends(['sort'=>$sort,'q'=>$qtxt]);

        return view('gacha.history', compact('pulls','sort','qtxt'));
    }

    // ✅ 図鑑
    public function catalog(Request $request)
    {
        $owner = $this->ownerKey($request);

        $sort = $request->get('sort', 'rarity');
        $qtxt = trim((string)$request->get('q', ''));
        $only = $request->boolean('only_unowned');

        // 所持済みID
        $ownedQ = Pull::query();
        if ($owner['type'] === 'username') {
            $ownedQ->where('username', $owner['value']);
        } else {
            $ownedQ->where('session_key', $owner['value']);
        }
        $ownedIds = $ownedQ->pluck('item_id')->unique()->toArray();

        // 完成率
        $totalCount = Item::count();
        $ownedCount = count($ownedIds);
        $completion = $totalCount > 0 ? round($ownedCount / $totalCount * 100) : 0;

        $rarityCase = "CASE rarity
            WHEN 'UR'  THEN 1
            WHEN 'SSR' THEN 2
            WHEN 'SR'  THEN 3
            WHEN 'R'   THEN 4
            WHEN 'N'   THEN 5
            ELSE 6 END";

        $query = Item::query();
        if ($qtxt !== '') $query->where('name','like','%'.$qtxt.'%');
        if ($only)        $query->whereNotIn('id', $ownedIds);

        switch ($sort) {
            case 'name':            $query->orderBy('name'); break;
            case 'implemented_new': $query->orderByRaw("COALESCE(strftime('%s', created_at), 0) DESC, id DESC"); break;
            case 'implemented_old': $query->orderByRaw("COALESCE(strftime('%s', created_at), 0) ASC, id ASC"); break;
            default:                $query->orderByRaw($rarityCase)->orderBy('name'); break;
        }

        $items = $query->get();

        return view('gacha.catalog', compact(
            'items','ownedIds','sort','qtxt','only','totalCount','ownedCount','completion'
        ));
    }
}
