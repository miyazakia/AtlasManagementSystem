<?php

namespace App\Http\Controllers\Authenticated\BulletinBoard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories\MainCategory;
use App\Models\Categories\SubCategory;
use App\Models\Posts\Post;
use App\Models\Posts\PostComment;
use App\Models\Posts\Like;
use App\Models\Users\User;
use App\Http\Requests\BulletinBoard\PostFormRequest;
use Auth;

class PostsController extends Controller
{
    public function show(Request $request){
        $posts = Post::with('user', 'postComments','subCategories')->get();
        $categories = MainCategory::get();
        $like = new Like;
        $post_comment = new Post;

        if(!empty($request->keyword)){// 検索ワードがある場合
            $posts = Post::with('user', 'postComments')
            ->where('post_title', 'like', '%'.$request->keyword.'%')
            ->orWhere('post', 'like', '%'.$request->keyword.'%')
            ->orWhereHas('subCategories', function($query) use($request){
                $query->where('sub_category', '=', $request->keyword);
            })
            ->get();

        }else if($request->category_word){ // カテゴリーが選択された場合
            $posts = Post::whereHas('subCategories',function($query) use($request){
                $query->where('sub_categories.id', '=', $request->category_word);  // どこのIDかわかるようにサブカテゴリーのIDを指定
            })
            ->with('user', 'postComments' ,'subCategories')
            ->get();

        }else if($request->like_posts){
            $likes = Auth::user()->likePostId()->get('like_post_id');
            $posts = Post::with('user', 'postComments')
            ->whereIn('id', $likes)
            ->get();

        }else if($request->my_posts){
            $posts = Post::with('user', 'postComments')
            ->where('user_id', Auth::id())
            ->get();
        }
        return view('authenticated.bulletinboard.posts', compact('posts', 'categories', 'like', 'post_comment'));
    }

    public function postDetail($post_id){
        $post = Post::with('user', 'postComments')->findOrFail($post_id);
        return view('authenticated.bulletinboard.post_detail', compact('post'));
    }

    public function postInput(){
        $main_categories = MainCategory::with('subCategories')->get();
        return view('authenticated.bulletinboard.post_create', compact('main_categories'));
    }

    public function postCreate(PostFormRequest $request){
        $post = Post::create([
            'user_id' => Auth::id(),
            'post_title' => $request->post_title,
            'post' => $request->post_body
        ]);

        // 投稿とサブカテゴリーを関連付け（中間テーブルにデータを追加）
        $post->subCategories()->attach($request->post_category_id);

        return redirect()->route('post.show');
    }

    public function postEdit(Request $request){

        $validatedData = $request->validate([
            'post_title' => 'required|string|max:100',
            'post_body' => 'required|string|max:5000',
        ],[
            'post_title.required' => 'タイトルは必須です',
            'post_title.max' => 'タイトルは100文字以内で入力してください',
            'post_body.required' => '本文は必須です',
            'post_body.max' => '本文は5000文字以内で入力してください',
        ]);

        Post::where('id', $request->post_id)->update([
            'post_title' => $request->post_title,
            'post' => $request->post_body,
        ]);
        return redirect()->route('post.detail', ['id' => $request->post_id]);
    }

    public function postDelete($id){
        Post::findOrFail($id)->delete();
        return redirect()->route('post.show');
    }
    public function mainCategoryCreate(Request $request){
        $validatedData = $request->validate([
            'main_category_name' => 'required|string|unique:main_categories,main_category|max:100',
        ],[
            'main_category_name.required' => 'メインカテゴリー名は必須です',
            'main_category_name.max' => 'メインカテゴリー名は100文字以内で入力してください',
            'main_category_name.unique' => 'そのメインカテゴリー名は既に登録されています',
        ]);

        MainCategory::create(['main_category' => $request->main_category_name]);
        return redirect()->route('post.input');
    }
// subCategoryCreateメソッドを追加
    public function subCategoryCreate(Request $request){
        $validatedData = $request->validate([
            'sub_category_name' => 'required|string|unique:sub_categories,sub_category|max:100',
        ],[
            'sub_category_name.required' => 'サブカテゴリー名は必須です',
            'sub_category_name.max' => 'サブカテゴリー名は100文字以内で入力してください',
            'sub_category_name.unique' => 'そのサブカテゴリー名は既に登録されています',
        ]);

    SubCategory::create([
        'main_category_id' => $request->main_category_id,
        'sub_category' => $request->sub_category_name
    ]);
    return redirect()->route('post.input');
    }

    public function commentCreate(Request $request){
        $validatedData = $request->validate([
            'comment' => 'required|string|max:250',
        ],[
            'comment.required' => 'コメントは必須です',
            'comment.max' => 'コメントは250文字以内で入力してください',
        ]);

        PostComment::create([
            'post_id' => $request->post_id,
            'user_id' => Auth::id(),
            'comment' => $request->comment
        ]);
        return redirect()->route('post.detail', ['id' => $request->post_id]);
    }

    public function myBulletinBoard(){
        $posts = Auth::user()->posts()->get();
        $like = new Like;
        return view('authenticated.bulletinboard.post_myself', compact('posts', 'like'));
    }

    public function likeBulletinBoard(){
        $like_post_id = Like::with('users')->where('like_user_id', Auth::id())->get('like_post_id')->toArray();
        $posts = Post::with('user')->whereIn('id', $like_post_id)->get();
        $like = new Like;
        return view('authenticated.bulletinboard.post_like', compact('posts', 'like'));
    }

    public function postLike(Request $request){
        $user_id = Auth::id();
        $post_id = $request->post_id;

        $like = new Like;

        $like->like_user_id = $user_id;
        $like->like_post_id = $post_id;
        $like->save();

        return response()->json();
    }

    public function postUnLike(Request $request){
        $user_id = Auth::id();
        $post_id = $request->post_id;

        $like = new Like;

        $like->where('like_user_id', $user_id)
             ->where('like_post_id', $post_id)
             ->delete();

        return response()->json();
    }
}
