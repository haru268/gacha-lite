<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Pull;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RankingController extends Controller
{
    public function index(Request $request)
    {
        // 期間パラメータ（YYYY-MM-DD）※未指定なら全期間
        $from = $request->query('from');
        $to   = $request->query('to');

        // Carbonに変換（to は終日の 23:59:59 に補正）
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : null;
        $toDate   = $to   ? Carbon::parse($to)->endOfDay()   : null;

        // ベースの Pull クエリ（期間絞りユーティリティ）
        $scoped = function($q) use ($fromDate, $toDate) {
            if ($fromDate) $q->where('pulls.created_at', '>=', $fromDate);
            if ($toDate)   $q->where('pulls.created_at', '<=', $toDate);
        };

        $allCount = Item::count();

        // 🎲 ガチャ回数ランキング
        $countRanking = Pull::query()
            ->whereNotNull('username')
            ->when(true, $scoped)
            ->select('username', DB::raw('COUNT(*) as cnt'))
            ->groupBy('username')
            ->orderByDesc('cnt')
            ->limit(10)
            ->get();

        // 💎 UR 獲得数ランキング
        $urRanking = Pull::query()
            ->whereNotNull('username')
            ->when(true, $scoped)
            ->join('items', 'pulls.item_id', '=', 'items.id')
            ->where('items.rarity', 'UR')
            ->select('username', DB::raw('COUNT(*) as ur_count'))
            ->groupBy('username')
            ->orderByDesc('ur_count')
            ->limit(10)
            ->get();

        // 📖 図鑑完成率（期間内に「初取得したアイテム種数」で計算する版）
        // ※「期間に関係なく累計の完成率」にしたい場合は when(true,$scoped) を外せばOK
        $completionRaw = Pull::query()
            ->whereNotNull('username')
            ->when(true, $scoped)
            ->select('username', DB::raw('COUNT(DISTINCT item_id) as owned'))
            ->groupBy('username')
            ->get();

        $completionRanking = $completionRaw
            ->map(function ($row) use ($allCount) {
                $row->rate  = $allCount > 0 ? round($row->owned / $allCount * 100, 1) : 0;
                $row->total = $allCount;
                return $row;
            })
            ->sortByDesc('rate')
            ->values()
            ->take(10);

        return view('ranking.index', [
            'countRanking'      => $countRanking,
            'urRanking'         => $urRanking,
            'completionRanking' => $completionRanking,
            'allCount'          => $allCount,
            'from'              => $from,
            'to'                => $to,
        ]);
    }

    // app/Http/Controllers/RankingController.php のクラス内に追記
public function csv(Request $request)
{
    $type = $request->query('type', 'count'); // count|ur|completion|pulls
    $from = $request->query('from');
    $to   = $request->query('to');

    $fromDate = $from ? \Illuminate\Support\Carbon::parse($from)->startOfDay() : null;
    $toDate   = $to   ? \Illuminate\Support\Carbon::parse($to)->endOfDay()   : null;

    $scoped = function($q) use ($fromDate, $toDate) {
        if ($fromDate) $q->where('pulls.created_at', '>=', $fromDate);
        if ($toDate)   $q->where('pulls.created_at', '<=', $toDate);
    };

    $filename = match ($type) {
        'count'      => 'count_ranking.csv',
        'ur'         => 'ur_ranking.csv',
        'completion' => 'completion_ranking.csv',
        'pulls'      => 'pulls.csv',
        default      => 'export.csv',
    };

    return response()->streamDownload(function () use ($type, $scoped) {
        $out = fopen('php://output', 'w');
        // Excel で文字化けしづらいよう BOM を付与（不要なら外してOK）
        fwrite($out, chr(0xEF).chr(0xBB).chr(0xBF));

        if ($type === 'count') {
            // username, count
            fputcsv($out, ['username', 'count']);
            $rows = \App\Models\Pull::query()
                ->whereNotNull('username')
                ->when(true, $scoped)
                ->select('username', DB::raw('COUNT(*) as cnt'))
                ->groupBy('username')
                ->orderByDesc('cnt')
                ->get();
            foreach ($rows as $r) fputcsv($out, [$r->username, $r->cnt]);

        } elseif ($type === 'ur') {
            // username, ur_count
            fputcsv($out, ['username', 'ur_count']);
            $rows = \App\Models\Pull::query()
                ->whereNotNull('username')
                ->when(true, $scoped)
                ->join('items','pulls.item_id','=','items.id')
                ->where('items.rarity', 'UR')
                ->select('username', DB::raw('COUNT(*) as ur_count'))
                ->groupBy('username')
                ->orderByDesc('ur_count')
                ->get();
            foreach ($rows as $r) fputcsv($out, [$r->username, $r->ur_count]);

        } elseif ($type === 'completion') {
            // username, owned_distinct, rate(%)
            fputcsv($out, ['username', 'owned_distinct', 'rate_percent']);
            $allCount = \App\Models\Item::count();
            $rows = \App\Models\Pull::query()
                ->whereNotNull('username')
                ->when(true, $scoped)
                ->select('username', DB::raw('COUNT(DISTINCT item_id) as owned'))
                ->groupBy('username')
                ->get();
            foreach ($rows as $r) {
                $rate = $allCount > 0 ? round($r->owned / $allCount * 100, 1) : 0;
                fputcsv($out, [$r->username, $r->owned, $rate]);
            }

        } else { // pulls
            // pulls 全件（期間内） username, item_id, created_at
            fputcsv($out, ['id','username','session_key','item_id','created_at']);
            $rows = \App\Models\Pull::query()
                ->when(true, $scoped)
                ->orderBy('id')
                ->get(['id','username','session_key','item_id','created_at']);
            foreach ($rows as $r) fputcsv($out, [$r->id, $r->username, $r->session_key, $r->item_id, $r->created_at]);
        }

        fclose($out);
    }, $filename, [
        'Content-Type' => 'text/csv; charset=UTF-8',
    ]);
}

}
