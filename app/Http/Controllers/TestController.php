<?php

namespace App\Http\Controllers;

use App\Models\test;
use App\Http\Requests\StoretestRequest;
use App\Http\Requests\UpdatetestRequest;
use Illuminate\Http\Request;

class TestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $test = test::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'title' =>"required|max:255",
            'body'=>"required"
        ]);

        $post = test::create($fields);

        return [ 'post' => $post];
    }

    /**
     * Display the specified resource.
     */
    public function show(test $test)
    {
        return [ 'post' => $test];  
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, test $test)
    {
        $fields = $request->validate([
            'title' =>"required|max:255",
            'body'=>"required"
        ]);

        $test->update($fields);


        return $test;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(test $test)
    {
        $test -> delete();
        return ['message' => 'mesage was deleted '];
    }
}
