<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index()
    {
        try {
            $posts = Post::orderBy('id', 'desc')->with('users')->get();
            return response()->json([
                'status' => true,
                'posts' => $posts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            //create validation
            $validate = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'thumbnail' => 'required',
                'body' => 'required|string',
                'published' => 'required',
                'user_id' => 'required|integer',
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validate->errors()
                ]);
            } else {
                $folderPath = 'assets/post_images/';
                $base64Image = explode(";base64,", $request->thumbnail);
                $explodeImage = explode("image/", $base64Image[0]);
                $imageType = $explodeImage[1];
                $image_base64 = base64_decode($base64Image[1]);
                $imageName = Str::random(40) . '.' . $imageType;

                Storage::disk('public')->put($folderPath . $imageName, $image_base64);

                $post = Post::create([
                    'title' => $request->title,
                    'slug' => Str::slug($request->title),
                    'body' => $request->body,
                    'published' => $request->published,
                    'user_id' => $request->user_id,
                    'thumbnail' => $folderPath . $imageName
                ]);
                if ($post) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Post created successfully',
                        'post' => $post
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Smoething went wrong'
                    ]);
                }
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        try {
            $post = Post::with('users')->findOrFail($id);
            return response()->json([
                'status' => true,
                'post' => $post
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function detailPost($slug)
    {
        try {
            $post = Post::with('users')
                ->where('slug', $slug)
                ->first();
            return response()->json([
                'status' => true,
                'post' => $post
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $post = Post::findOrFail($id);
            $validate = Validator::make($request->all(), [
                'title' => 'required | string | min:7',
                'body' => 'required | string ',
                'published' => 'required | max:1 | min:1',
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validate->errors(),
                ]);
            } else {
                if (request('thumbnail')) {
                    Storage::disk('public')->delete($post->getRawOriginal('thumbnail'));
                    $folderPath = 'assets/post_images/';
                    $base64Image = explode(";base64,", $request->thumbnail);
                    $explodeImage = explode("image/", $base64Image[0]);
                    $imageType = $explodeImage[1];
                    $image_base64 = base64_decode($base64Image[1]);
                    $imageName = Str::random(40) . '.' . $imageType;
                    $image_path = $folderPath . $imageName;
                    Storage::disk('public')->put($folderPath . $imageName, $image_base64);
                } elseif ($post->thumbnail) {
                    $image_path = $post->getRawOriginal('thumbnail');
                } else {
                    $image_path = null;
                }
                $result = $post->update([
                    'user_id' => $request->user_id,
                    'title' => $request->title,
                    'slug' => Str::slug($request->title),
                    'thumbnail' => $image_path,
                    'body' => $request->body,
                    'published' => $request->published,
                ]);
                if ($result) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Post updated successfully',
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Something went wrong',
                    ]);
                }
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $post = Post::find($id);
            if ($post->thumbnail) {
                Storage::disk('public')->delete($post->thumbnail);
            }
            $result = $post->delete();
            if ($result) {
                return response()->json([
                    'status' => true,
                    'message' => 'Post deleted successfully',
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong',
                ]);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function searchPost(Request $request, $search)
    {
        try {
            // $search = $request->input('search');
            $post = Post::where('title', 'like', '%' . $search . '%')
                ->orWhere('body', 'like', '%' . $search . '%')
                ->get();
            return response()->json([
                'status' => true,
                'posts' => $post
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
