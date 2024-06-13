<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function index($id)
    {
        // $post = Post::where('id', $id)->with('likes')->get();
        $post = Like::where('post_id',$id)->with('user')->get();

        /// get only user
        $user = $post->pluck('user');

        return response()->json([
            'status' => 'success',
            'data' => $user,
        ], 200);
    }

    public function toggleLike($id)
    {
        $user  = auth()->user();
        $post = Post::find($id);
        $liked = $post->likes->contains('user_id',$user->id);
        if($liked){
            $like = Like::where('user_id',$user->id)->where('post_id', $post->id)->first();
            $like->delete();
            return response()->json([
                'status' => 'unlike',
            ],200);
        }else{
            $like = Like::create([
                'user_id' => $user->id,
                'post_id' => $post->id,

            ]);
            return response()->json([
                'status' => 'like',
                'data' => $like,
            ],200);

        }


    }
}
