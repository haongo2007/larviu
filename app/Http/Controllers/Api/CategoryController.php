<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Larviu\Models\Category;
use App\Http\Resources\CategoryCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Larviu\JsonResponse;
use Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Larviu\Models\StorageFile;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
	const ITEM_PER_PAGE = 15;
    const ORDER = 'DESC';
    const BY = 'id';
    const ACTIVE = -1;

    public $categoryTmp = [];
    public $id_disabled;
    /**
     * @param bool $isNew
     * @return array
     */
    private function getValidationRules($isNew = true)
    {
        return [
            'name' => 'required',
        ];
    }

	/**
     * Display a listing of the user resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|ResourceCollection
     */
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $categoryQuery = Category::with('Creator:name,id','Parent:name,id','Banner:url,id');
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $sort = Arr::get($searchParams, 'sort', static::ORDER);
        $by = Arr::get($searchParams, 'by', static::BY);
        $active = intval(Arr::get($searchParams, 'is_active',static::ACTIVE));
        $name = Arr::get($searchParams, 'name', '');
        
        if (!empty($name)) {
            $categoryQuery->where('name', 'LIKE', '%' . $name . '%');
        }
        if ($active >= 0) {
            $categoryQuery->where('is_active', $active);
        }
        if (!empty($sort)) {
            $categoryQuery->orderBy($by, $sort);
        }
        $list = $categoryQuery->paginate($limit);
        return CategoryCollection::collection($list);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                $this->getValidationRules()
            )
        );
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $slug = Str::slug($params['name'], '-');
            $params['parent'] = last(explode(',', $params['parent']));
            /* UPLOAD BANNER */
            if ($params['banner'] && !is_numeric($params['banner'])) {
                $path = 'category/'.$slug;
                $image = $params['banner'];
                $filename  = $image->hashName();
                $extension = $image->getClientOriginalExtension();
                $image->storeAs($path, $filename);
                $url = 'api/getFile?disk='.env('FILESYSTEM_DRIVER', 'local').'&path='.urlencode($path.'/'.$filename);
                $storagefile = StorageFile::create([
                    'name' => $filename,
                    'url' => $url,
                    'type' => 'category',
                    'extension' => $extension
                ]);
            }elseif (is_numeric($params['banner'])){
                $storagefile = StorageFile::find($params['banner']);
                $storagefile->type = 'category';
                $storagefile->save();
            }
            $category = Category::create([
                'name' => $params['name'],
                'slug' => $slug,
                'order' => $params['order'],
                'is_active' => $params['is_active'],
                'id_parent' => $params['parent'],
                'id_banner' => $storagefile->id,
                'creator' => Auth::user()->id,
            ]);
            $category = $category->load('Creator:name,id','Parent:name,id',"Banner:url,id")->toArray();
            $category['creator'] = $category['creator']['name'];
            return $category;
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User    $user
     * @return UserCollection|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Category $category)
    {
        $params = $request->all();
        $validator = Validator::make(
            $params,
            array_merge(
                $this->getValidationRules(),
            )
        );
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $slug = Str::slug($params['name'], '-');
            if (is_array($params['parent'])) {
                $params['parent'] = last($params['parent']);
            }else{
                $params['parent'] = last(explode(',', $params['parent']));
            }
            if($request->hasFile('banner')){
                $category->Banner()->delete();
                $path = 'category/'.$slug;
                $image = $params['banner'];
                $filename  = $image->hashName();
                $extension = $image->getClientOriginalExtension();
                $image->storeAs($path, $filename);
                $url = 'api/getFile?disk='.env('FILESYSTEM_DRIVER', 'local').'&path='.urlencode($path.'/'.$filename);
                $storagefile = StorageFile::create([
                    'name' => $filename,
                    'url' => $url,
                    'type' => 'category',
                    'extension' => $extension
                ]);
                $category->id_banner = $storagefile->id;
            }elseif (is_numeric($params['banner'])){
                $storagefile = StorageFile::find($params['banner']);
                $storagefile->type = 'category';
                $storagefile->save();
                $category->id_banner = $storagefile->id;
            }
            $category->name      = $params['name'];
            $category->slug      = $slug;
            $category->order     = $params['order'];
            $category->is_active = $params['is_active'];
            $category->id_parent = $params['parent'];
            $category->save();
        }

        $category = $category->load('Creator:name,id','Parent:name,id',"Banner:url,id")->toArray();
        $category['creator'] = $category['creator']['name'];
        return $category;
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  category $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        if ($category->Children()->count() > 0) {
            return response()->json(['error' => 'This record is currently linked to another that cannot be deleted'], 403);
        }else{
            try {
                $category->Banner()->delete();
                $category->delete();
            } catch (\Exception $ex) {
                response()->json(['error' => $ex->getMessage()], 403);
            }
        }
        return response()->json(null, 204);
    }
    /**
     * Get Recursive category.
     *
     * @param  null
     * @return \Illuminate\Http\Response
     */
    public function Recursive(Request $request){
        if ($request->id) {
            $this->id_disabled = $request->id;
        }
        $data = $this->ExcuteRecursive();
        return $data;
    }
    public function ExcuteRecursive($id_parent = 0)
    {
        $data = [];
        foreach ($this->getCategory($id_parent) as $group)
        {
            $group['children'] = $this->ExcuteRecursive($group['id']);
            if (empty($group['children'])) {
                unset($group['children']);
            }
            $data[] = $group;
        }
        return $data;
    }
    public function getCategory($id_parent)
    {
        $tmp = [];
        $category = $this->categoryTmp;
        if (empty($category)) {
            $category = Category::select('name','id','id_parent')->get();
            $this->categoryTmp = $category;
        }
        foreach ($category as $key => $value) {
            if ($value['id_parent'] == $id_parent) {
                if ($this->id_disabled && $id_parent != 0) {
                    if ($this->id_disabled == $value['id'] || $this->id_disabled == $value['id_parent']) {
                        $value['disabled'] = true;
                    }
                }
                $tmp[] = $value->toArray();
            }
        }
        return $tmp;
    }
}
