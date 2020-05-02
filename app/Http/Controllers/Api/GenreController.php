<?php

namespace App\Http\Controllers\Api;

use App\Models\Genre;

class GenreController extends BasicCrudController
{
    private $rules = [
        'name' => 'required|max:255',
        'is_active' => 'boolean'
    ];
    protected function model()
    {
        return Genre::class;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }

//    public function index(Request $request)
//    {
//        if ($request->has('only_trashed')) {
//            return Genre::onlyTrashed()->get();
//        }
//        return Genre::all();
//    }
//
//    public function store(Request $request)
//    {
//        $this->validate($request, $this->rules);
//
//        $genre = Genre::query()->create($request->all());
//        $genre->refresh();
//
//        return $genre;
//    }
//
//    public function show(Genre $genre)
//    {
//        return $genre;
//    }
//
//    public function update(Request $request, Genre $genre)
//    {
//        $this->validate($request, $this->rules);
//        $genre->update($request->all());
//        return $genre;
//    }
//
//    public function destroy(Genre $genre)
//    {
//        $genre->delete();
//        return \Response::noContent();
//    }
}
