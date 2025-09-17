<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index(Request $request)
{
    $sort  = $request->get('sort', 'rarity'); // デフォルト：レア度順
    $query = Item::query();

    // SQLite でも動く並び替え：レア度の疑似順位
    $rarityCase = "CASE rarity
        WHEN 'UR'  THEN 1
        WHEN 'SSR' THEN 2
        WHEN 'SR'  THEN 3
        WHEN 'R'   THEN 4
        WHEN 'N'   THEN 5
        ELSE 6 END";

    switch ($sort) {
        case 'name':
            $query->orderBy('name');
            break;
        case 'weight':
            $query->orderBy('weight', 'desc');
            break;
        case 'new':
            $query->orderBy('created_at', 'desc');
            break;
        case 'rarity':
        default:
            // レア度順 → 同率は名前順
            $query->orderByRaw($rarityCase)->orderBy('name');
            break;
    }

    $items = $query->paginate(20);

    return view('items.index', compact('items', 'sort'));
}

    

    public function edit(Item $item)
    {
        return view('items.edit', compact('item'));
    }

    public function update(Request $request, Item $item)
    {
        $data = $request->validate([
            'name'      => ['required','string','max:100'],
            'rarity'    => ['required','in:N,R,SR,SSR,UR'],
            'weight'    => ['required','integer','min:1'],
            'image_url' => ['nullable','string','max:2000'], // URLや /storage/... もOK
        ]);

        $item->update($data);
        return redirect()->route('items.index')->with('ok', '更新しました');
    }

    

    public function upload(Request $request, Item $item)
{
    $request->validate([
        'image' => ['required', 'file', 'max:10240'], // 5MB
    ]);

    if (!$request->hasFile('image') || !$request->file('image')->isValid()) {
        return back()->withErrors(['image' => 'ファイルが選択されていないか、アップロードに失敗しました。'])->withInput();
    }

    $path = $request->file('image')->store('items', 'public'); // storage/app/public/items/...
    $item->update(['image_url' => '/storage/'.$path]);         // /storage/items/xxx.png

    return back()->with('ok', '画像をアップロードしました');
}

public function create()
{
    return view('items.create');
}

public function store(Request $request)
{
    $data = $request->validate([
        'name'      => ['required','string','max:100'],
        'rarity'    => ['required','in:N,R,SR,SSR,UR'],
        'weight'    => ['required','integer','min:1'],
        'image'     => ['nullable','file','image','max:10240'], // 10MB
        'image_url' => ['nullable','string','max:2000'],        // URL直指定も許可
    ]);

    // 画像は「アップロード優先」→「URL」の順で採用
    $imagePath = null;
    if ($request->hasFile('image') && $request->file('image')->isValid()) {
        $stored = $request->file('image')->store('items', 'public');
        $imagePath = '/storage/' . $stored;
    } elseif (!empty($data['image_url'])) {
        $imagePath = $data['image_url'];
    }

    $item = \App\Models\Item::create([
        'name'      => $data['name'],
        'rarity'    => $data['rarity'],
        'weight'    => $data['weight'],
        'image_url' => $imagePath ?? 'https://picsum.photos/seed/'.uniqid().'/300/300',
    ]);

    return redirect()->route('items.index')->with('ok', 'アイテムを追加しました');
}


}
