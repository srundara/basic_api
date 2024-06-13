<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index($id){

        $post = Post::find($id);
        ///if no post
        if(!$post){
            return response()->json([
                'status'=> 'error',
                'message'=>'Post not found'
            ],404);
        }
        $comments = $post->comments()->with('user')->get();
        // $user = $comments->pluck('user');
        // dd($comments);
        return response()->json([
            'status' => 'success',
            'data' => $comments,
        ], 200);
    }

    /// add comment to a post
    public function store(Request $request, $id){

        $user = auth()->user();
        $post = Post::find($id);
        if(!$post){
            return response()->json([
                'status' => 'error',
                'message'  => 'Post not found'
            ],404);
        }
        $data = $request->all();  /// get request data from user
        $data['user_id'] = $user->id; /// add user to data
        $comment = $post->comments()->create($data); /// store comment to db
        return response()->json([
            'status' => 'success',
            'data' => $comment
        ],200);
    }

    /// update comment
    public function update(Request $request, $id){

        $user = auth()->user();
        $validate = $this->validate($request,[
            'text' => 'required',
            // 'post_id' => 'required'

        ]);
        $comment = Comment::find($id);
        ///if no comment
        if(!$comment){
            return response()->json([
                'status' => 'error',
                'message'  => 'Comment not found'
            ],404);
        }
        /// if user is not owner
        if($user->id !== $comment->user_id){
            return response()->json([
                'status' => 'error',
                'message'  => 'You are not this owner this comment'
            ],401);
        }
        /// if user is owner
        $data = $request->all();
        $comment->update($data);
        return response()->json([
            'status' => 'updated',
            'data' => $comment
        ],200);
    }

    /// delete comment
    public function destroy($id)
    {
        $user = auth()->user();
        $comments = Comment::find($id);
        /// if no comment
        if(!$comments){
            return response()->json([
                'status' => 'error',
                'message' => 'Comment not found!',
            ],404);
        }
        /// if user is not owner
        if($user->id != $comments->user_id){
            return response()->json([
                'status' => 'error',
                'message' => 'You can are not the onwer this commet',
            ],401);
        }
        /// delete comment
        $comments->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Your comment has beed deleted',
        ],200);






    }

}
