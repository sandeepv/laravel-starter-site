<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Article;
use App\ArticleCategory;
use App\Language;
use Illuminate\Support\Facades\Input;
use App\Http\Requests\Admin\ArticleRequest;
use Illuminate\Support\Facades\Auth;
use Datatables;

class ArticleController extends AdminController {

    public function __construct()
    {
        view()->share('type', 'article');
    }
     /*
    * Display a listing of the resource.
    *
    * @return Response
    */
    public function index()
    {
        // Show the page
        return view('admin.article.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $languages = Language::lists('name', 'id')->toArray();
        $articlecategories = ArticleCategory::lists('title', 'id')->toArray();
        return view('admin.article.create_edit', compact('languages', 'articlecategories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(ArticleRequest $request)
    {
        $article = new Article($request->except('image'));
        $article -> user_id = Auth::id();

        $picture = "";
        if(Input::hasFile('image'))
        {
            $file = Input::file('image');
            $filename = $file->getClientOriginalName();
            $extension = $file -> getClientOriginalExtension();
            $picture = sha1($filename . time()) . '.' . $extension;
        }
        $article -> picture = $picture;
        $article -> save();

        if(Input::hasFile('image'))
        {
            $destinationPath = base_path() . '/images/article/'.$article->id.'/';
            Input::file('image')->move($destinationPath, $picture);
        }
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit(Article $article)
    {
        $languages = Language::lists('name', 'id')->toArray();
        $articlecategories = ArticleCategory::lists('title', 'id')->toArray();
        return view('admin.article.create_edit',compact('article','languages','articlecategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(ArticleRequest $request, Article $article)
    {
        $article -> user_id = Auth::id();
        $picture = "";
        if(Input::hasFile('image'))
        {
            $file = Input::file('image');
            $filename = $file->getClientOriginalName();
            $extension = $file -> getClientOriginalExtension();
            $picture = sha1($filename . time()) . '.' . $extension;
        }
        $article -> picture = $picture;
        $article -> update($request->except('image'));

        if(Input::hasFile('image'))
        {
            $destinationPath = base_path() . '/images/article/'.$article->id.'/';
            Input::file('image')->move($destinationPath, $picture);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return Response
     */

    public function delete(Article $article)
    {
        return view('admin.article.delete', compact('article'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return Response
     */
    public function destroy(Article $article)
    {
        $article->delete();
    }


    /**
     * Show a list of all the languages posts formatted for Datatables.
     *
     * @return Datatables JSON
     */
    public function data()
    {
        $article = Article::join('languages', 'languages.id', '=', 'articles.language_id')
            ->join('article_categories', 'article_categories.id', '=', 'articles.article_category_id')
            ->select(array('articles.id','articles.title','article_categories.title as category', 'languages.name', 
                'articles.created_at'));

        return Datatables::of($article)
            ->add_column('actions', '<a href="{{{ URL::to(\'admin/article/\' . $id . \'/edit\' ) }}}" class="btn btn-success btn-sm iframe" ><span class="glyphicon glyphicon-pencil"></span>  {{ trans("admin/modal.edit") }}</a>
                    <a href="{{{ URL::to(\'admin/article/\' . $id . \'/delete\' ) }}}" class="btn btn-sm btn-danger iframe"><span class="glyphicon glyphicon-trash"></span> {{ trans("admin/modal.delete") }}</a>
                    <input type="hidden" name="row" value="{{$id}}" id="row">')
            ->remove_column('id')

            ->make();
    }
}
