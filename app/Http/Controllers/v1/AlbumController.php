<?php

namespace App\Http\Controllers\v1;

use App\Http\Requests\StoreAlbumRequest;
use App\Http\Requests\UpdateAlbumRequest;
use App\Models\Album;
use App\Http\Controllers\Controller;
use App\Http\Resources\v1\AlbumResource;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Album $album,Request $req)
    {   $where=["user_id"=>$req->user()->id];
        $data=$album::where($where)->paginate();
        return AlbumResource::collection($data);
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAlbumRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAlbumRequest $req)
    {
        $album=Album::create([
            'name'=>$req->name,
            'user_id'=>$req->user()->id
        ]);
        $album->save();
        // for single item use AlbumResource Instace
        return new AlbumResource($album);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function show(Album $album)
    {
        
        return new AlbumResource($album);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAlbumRequest  $request
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAlbumRequest $req, Album $album)
    {
        //
        $album::where('user_id',$req->user()->id)->update($req->all());
        return new AlbumResource($album);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function destroy(Album $album,Request $req)
    {
        //
        $album::where('user_id',$req->user()->id)->delete();
        return response('Data Deleted ',200);
    }
}
