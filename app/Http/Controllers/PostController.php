<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

use function PHPUnit\Framework\fileExists;

class PostController extends Controller
{

    /// get all post data

    public function index()
    {
        $posts = Post::with('user')->latest()->paginate(10);
        // dd($posts);
        /// count number for each post
        foreach($posts as $post){
            $post['likes_count'] = $post->likes->count();
            // // dd($post);
            $post['comments_count'] = $post->comments->count();
            $post['liked'] = $post->likes->contains('user_id',auth()->user()->id);
        }
        // dd($posts);
        return response()->json([
            // dd($post),
            'status' => 'success',
            'data' => $posts,
        ], 200);
    }

    /// store function

    public function store(Request $request)
    {
        $user = auth()->user();
        $post =  $request->all();
        $post['user_id'] = $user->id;

        if($request->hasFile('photo')){
            $image = $request->file('photo');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/post');
            $image->move($destinationPath,$name);
            // $imaneName = $name;
            $post['image_url'] = $name;

        }

        $store = Post::create($post);

        return response()->json([
            'status' => 'success',
            'data' => $post,
        ]);
    }

    public function show($id)
    {
        $post = Post::with(['user', 'comments.user', 'likes'])->find($id);
        $post['like_count'] = $post->likes->count();
        $post['comment_count'] = $post->comments->count();
        $post['liked'] = $post->likes->contains(auth()->user()->id);

        return response()->json([
            'status' => 'success',
            'data' => $post
        ],200);
    }
    /// update post

    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if(!$post){
            return response([
                'status' => 'error',
                'data' => 'Post not found!'
            ],404);

        }
        $data = $request->all();

        if(auth()->user()->id != $post->user_id){
            return response()->json([
                'status' => 'error',
                'data' => 'you are not authorized to update this post!',
            ],401);
        }

        if($request->hasFile('photo')){
            $image = $request->file('photo');
            $name = time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('/post');
            $image->move($destinationPath,$name);
            // $imaneName = $name;
            $data['image_url'] = $name;
            $oldImage = public_path('/post/').$post->image_url;
            if(fileExists($oldImage)){
                unlink($oldImage);
            }
        }
        $post->update($data);

        return response()->json([
            'status' => 'success',
            'data' => $post,
        ],200);

    }

    public function distroy($id){
        $post = Post::find($id);
        if($post->image_url){
            $oldImage = public_path('/post/').$post->image_url;
            if(fileExists($oldImage)){
                unlink($oldImage);
            }
        }

        if(!$post){
            return response()->json([
                'status' => 'error',
                'data' => 'Post not found!',
            ],404);
        }
        if(auth()->user()->id != $post->user_id){
            return response()->json([
                'status' => 'error',
                'data' => 'you are not authrized to delete this post!',
            ],401);
        }
        $post->delete();
        return response()->json([
            'status' => 'success',
            'data' => 'This post has beed deleted',
        ],200);
    }

}
