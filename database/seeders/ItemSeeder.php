<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data=[
        ['name'=>'木の枝','rarity'=>'N','weight'=>500,'image_url'=>'https://picsum.photos/seed/branh/300/300'],
        ['name' => 'こんがり肉', 'rarity' => 'R',  'weight' => 300, 'image_url' => 'https://picsum.photos/seed/meat/300/300'],
            ['name' => '魔法の石',   'rarity' => 'SR', 'weight' => 150, 'image_url' => 'https://picsum.photos/seed/stone/300/300'],
            ['name' => '伝説の剣',   'rarity' => 'SSR','weight' => 40,  'image_url' => 'https://picsum.photos/seed/sword/300/300'],
            ['name' => '世界樹の葉', 'rarity' => 'UR', 'weight' => 10,  'image_url' => 'https://picsum.photos/seed/leaf/300/300'],
        ];
        foreach ($data as $d){
            \App\Models\Item::updateOrCreate(['name'=>$d['name']],$d);
        }
    }
}
