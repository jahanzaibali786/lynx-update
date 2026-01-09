<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\ProductServiceSubCategory;
use Illuminate\Http\Request;

class ProductServiceSubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (\Auth::user()->can('manage constant category')) {
                $categories = ProductServiceSubCategory::with('categories')->where('created_by', '=', \Auth::user()->creatorId())->paginate(10);
            return view('productServiceSubCategory.index', compact('categories'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (\Auth::user()->can('create constant category')) {
            $categories = ProductServiceCategory::get()->pluck('name', 'id');
            return view('productServiceSubCategory.create', compact('categories'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('create constant category')) {

            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required|max:200',
                    'category_id' => 'required',
                    'color' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $category = new ProductServiceSubCategory();
            $category->name = $request->name;
            $category->color = $request->color;
            $category->category_id = $request->category_id;
            $category->created_by = \Auth::user()->creatorId();
            $category->save();

            return redirect()->route('product-sub-category.index')->with('success', __('SubCategory successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductServiceSubCategory  $productServiceSubCategory
     * @return \Illuminate\Http\Response
     */
    public function show(ProductServiceSubCategory $productServiceSubCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductServiceSubCategory  $productServiceSubCategory
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (\Auth::user()->can('edit constant category')) {
            $categories = ProductServiceCategory::get()->pluck('name', 'id');
            $subcategories = ProductServiceSubCategory::find($id);
            return view('productServiceSubCategory.edit', compact('categories','subcategories'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductServiceSubCategory  $productServiceSubCategory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        if (\Auth::user()->can('edit constant category')) {
            $category = ProductServiceSubCategory::find($id);
            if ($category->created_by == \Auth::user()->creatorId()) {
                $validator = \Validator::make(
                    $request->all(), [
                        'name' => 'required|max:200',
                        'category_id' => 'required',
                        'color' => 'required',
                    ]
                );
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $category->name = $request->name;
                $category->color = $request->color;
                $category->category_id = $request->category_id;
                $category->save();

                return redirect()->route('product-sub-category.index')->with('success', __('SubCategory successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductServiceSubCategory  $productServiceSubCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (\Auth::user()->can('delete constant category')) {
            $category = ProductServiceSubCategory::find($id);
            if ($category->created_by == \Auth::user()->creatorId()) {

                if ($category) {
                    $categories = ProductService::where('sub_category_id', $category->id)->first();
                    // $categories_b = Bill::where('category_id', $category->id)->first();
                }


                if (!empty($categories) ) {
                    return redirect()->back()->with('error', __('this subcategory is already assign so please move or remove this subcategory related data.'));
                }

                $category->delete();

                return redirect()->route('product-sub-category.index')->with('success', __('SubCategory successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function sub_category(Request $request)
    {
        $category = ProductServiceSubCategory::where('category_id', '=', $request->id)->get();
            // dd($request->id);
            if($category == null){
                $result = [
                    'status' => 'error',
                    'category' => 'null',

                ];
                return response()->json($result);
            }else{
                $result = [
                    'status' => 'success',
                    'category' => $category,
                ];
                return response()->json($result);
            }
    }

}
