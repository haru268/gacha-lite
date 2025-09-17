<?php

namespace App\Http\Controllers;

use App\Models\Pull;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminExportController extends Controller
{
    public function historyCsv(Request $request)
    {
        $from = $request->date('from'); // Y-m-d 形式想定
        $to   = $request->date('to');   // Y-m-d 形式想定（含めたい日の翌日0時までに調整してもOK）

        $q = Pull::with('item');

        if ($from) $q->where('created_at', '>=', $from.' 00:00:00');
        if ($to)   $q->where('created_at', '<=', $to.' 23:59:59');

        $filename = 'gacha_history_'.now()->format('Ymd_His').'.csv';

        return new StreamedResponse(function() use ($q){
            $out = fopen('php://output', 'w');

            // UTF-8 with BOM（Excel対策）
            fwrite($out, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($out, ['pull_id','session_key','item_id','item_name','rarity','weight','created_at']);

            $q->orderBy('created_at','desc')->chunk(500, function($rows) use ($out){
                foreach ($rows as $p) {
                    fputcsv($out, [
                        $p->id,
                        $p->session_key,
                        $p->item_id,
                        optional($p->item)->name,
                        optional($p->item)->rarity,
                        optional($p->item)->weight,
                        $p->created_at,
                    ]);
                }
            });

            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
