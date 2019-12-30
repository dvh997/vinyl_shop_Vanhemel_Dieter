<?php

namespace App\Http\Controllers;

use App\Genre;
use App\Record;
use Facades\App\Helpers\Json;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    // Master Page: http://vinyl_shop.test/shop or http://localhost:3000/shop
    public function index(Request $request)
    {
        $records = Record::with('genre')->paginate(12);
        foreach ($records as $record) {
            $record->cover = $record->cover ?? "https://coverartarchive.org/release/$record->title_mbid/front-250.jpg";
        }
        $genres = Genre::orderBy('name', 'asc')
            ->has('records')
            ->withCount('records')
            ->get()
            ->transform(function ($item, $key) {
                // Set first letter of name to uppercase and add the counter
                $item->name = ucfirst($item->name) . ' (' . $item->records_count . ')';
                // Remove all fields that you don't use inside the view
                unset($item->created_at, $item->updated_at, $item->records_count);
                return $item;
            });
        $genre_id = $request->input('genre_id') ?? '%'; //OR $genre_id = $request->genre_id ?? '%';
        $artist_title = '%' . $request->input('artist') . '%'; //OR $artist_title = '%' . $request->artist . '%';
        $records = Record::with('genre')
            ->where(function ($query) use ($artist_title, $genre_id) {
                $query->where('artist', 'like', $artist_title)
                    ->where('genre_id', 'like', $genre_id);
            })
            ->orWhere(function ($query) use ($artist_title, $genre_id) {
                $query->where('title', 'like', $artist_title)
                    ->where('genre_id', 'like', $genre_id);
            })
            ->paginate(12)
            ->appends(['artist'=> $request->input('artist'), 'genre_id' => $request->input('genre_id')]);
        //OR ->appends(['artist' => $request->artist, 'genre_id' => $request->genre_id]);


        $result = compact('genres', 'records');     // $result = ['genres' => $genres, 'records' => $records]

        Json::dump($result);                    // open http://vinyl_shop.test/shop?json
        return view('shop.index', $result);

    }

public function shopAlt()
{
    $records = Record::orderBy('artist')->get();
    $genres = Genre::orderBy('name')
        ->has('records')
        ->get()
        ->transform(function($item, $key){
            $item->name = ucfirst($item->name);
            unset($item->created_at, $item->updated_at, $item->records_count);
            return $item;
        });
        $result = compact('genres', 'records');     // $result = ['genres' => $genres, 'records' => $records]
        Json::dump($result);                    // open http://vinyl_shop.test/shop?json
        return view('shop.shop_alt', $result);

}

    // Detail Page: http://vinyl_shop.test/shop/{id} or http://localhost:3000/shop/{id}
    public function show($id)
    {
        return "Details for record $id";
    }
}
