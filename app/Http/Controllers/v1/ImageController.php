<?php

namespace App\Http\Controllers\v1;

use App\Http\Resources\v1\ImageResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImageRequest;
use App\Http\Requests\UpdateImageRequest;
use App\Models\Image;
use App\Models\Album;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image as ima;
class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Image $image,Request $req)
    {   
        $where=["user_id"=>$req->user()->id];
        $data=$image::where($where)->paginate();
        return ImageResource::collection($data);
    }

    public function byalbum(Image $image,$albumid,Request $req){
        $where=[
            'album_id'=>$albumid,
            'user_id'=>$req->user()->id
        ];
        return ImageResource::collection($image::where($where)->paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreImageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function resize(StoreImageRequest $req)
    {
        $all=$req->all();
        // seting image in another variable and unset it to $all. because when data will save unmanuputed image will be store
        $image=$all['image'];
        $album_id=$all['album_id'];
        unset($all['image']);
        unset($all['album_id']);

        $data=[
            'type'=>Image::TYPE_RESIZE,
            'data'=>json_encode($all),
            'user_id'=>$req->user()->id,
            'album_id'=>$album_id
        ];

        // if(isset($all['album_id'])){
        //     $data['album_id']=$all['album_id'];
        // };

        $dir='images/'.Str::random().'/';
        $absolutepath=public_path($dir);
        File::makeDirectory($absolutepath);

        if($image instanceof UploadedFile){
            $data['name']=$image->getClientOriginalName();
            $filename=pathinfo($image->getClientOriginalName(),PATHINFO_FILENAME);
            $extension=$image->getClientOriginalExtension();
            $originalpath=$absolutepath;
            $image->move($originalpath,$data['name']);
            $data['path']=$dir.$data['name'];

        }else{
            $data['name']=pathinfo($image,PATHINFO_BASENAME);
            $filename=pathinfo($image,PATHINFO_FILENAME);
            $extension=pathinfo($image,PATHINFO_EXTENSION);
            $data['path']=$dir.$data['name'];
            $originalpath=$absolutepath.$data['name'];
            copy($image,$originalpath);
        }
        
        $w=$all['w'];
        $h=$all['h'] ?? false;

        list($width,$height,$image)=$this->getresizeimage($w,$h,$originalpath);
        $resizefilename=$filename.'-resize'.'.'.$extension;
        $image->resize($width,$height)->save($absolutepath.$resizefilename);

        $data['output_path']=$dir.$resizefilename;
        
        $imagemanupulation=Image::create($data);

        return new ImageResource($imagemanupulation);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Image  $image
     * @return \Illuminate\Http\Response
     */
    public function show(Image $image,$id,Request $req)
    {
        $where=[
            "id"=>$id,
            'user_id'=>$req->user()->id
        ];
        $isExist=ImageResource::collection($image->where($where)->get());

        if($isExist){
            return $isExist;
        }else{
            return "Data Not Found";
        }
       
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Image  $image
     * @return \Illuminate\Http\Response
     */
    public function destroy(Image $image,$imageid,Request $req)
    {
        $where=[
            "id"=>$imageid,
            'user_id'=>$req->user()->id
        ];
        $image->where($where)->delete();
        return response()->json(['message'=>'Image Deleted']);
    
    }

    private function getresizeimage($w,$h,string $originalpath){
        $newimage=ima::make($originalpath);
        $originalwidth=$newimage->width();
        $originalheight=$newimage->height();

        if(str_ends_with($w,'%')){
            $ratioW=(float)str_replace('%','',$w);
            $ratioH=$h ? (float)str_replace('%','',$h):$ratioW;
            $newwidth=$originalwidth * $ratioW / 100;
            $newheight=$originalheight * $ratioH / 100;

        }else{
            $newwidth=(float)$w;
            // if h not present
            // $newheight=$originalheight * $newwidth / $originalwidth
            $newheight=$h ? (float)$h:$originalheight * $newwidth / $originalwidth;
        }
        return [$newwidth,$newheight,$newimage];
    }   
}
