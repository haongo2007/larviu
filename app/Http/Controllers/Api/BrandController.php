<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Larviu\Models\Brand;
use App\Larviu\Models\Products;
use App\Larviu\Models\StorageFile;
use Illuminate\Support\Arr;
use App\Http\Resources\BrandCollection;
use Illuminate\Support\Facades\Storage;
use Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class BrandController extends Controller
{
    const ITEM_PER_PAGE = 15;
    const ORDER = 'DESC';
    const BY = 'id';
    const ACTIVE = -1;
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $brandQuery = Brand::with('Creator:name,id','Banner:url,id');
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $sort = Arr::get($searchParams, 'sort', static::ORDER);
        $by = Arr::get($searchParams, 'by', static::BY);
        $active = intval(Arr::get($searchParams, 'is_active',static::ACTIVE));
        $name = Arr::get($searchParams, 'name', '');
        
        if (!empty($name)) {
            $brandQuery->where('name', 'LIKE', '%' . $name . '%');
        }
        if ($active >= 0) {
            $brandQuery->where('is_active', $active);
        }
        if (!empty($sort)) {
            $brandQuery->orderBy($by, $sort);
        }
        $list = $brandQuery->paginate($limit);
        return BrandCollection::collection($list);
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
            /* UPLOAD BANNER */
            if ($params['banner'] && !is_numeric($params['banner'])) {
                $path = 'brand/'.$slug;
                $image = $params['banner'];
                $filename  = $image->hashName();
                $extension = $image->getClientOriginalExtension();
                $image->storeAs($path, $filename);
                $url = 'api/getFile?disk='.env('FILESYSTEM_DRIVER', 'local').'&path='.urlencode($path.'/'.$filename);
                $storagefile = StorageFile::create([
                    'name' => $filename,
                    'url' => $url,
                    'type' => 'brand',
                    'extension' => $extension
                ]);
            }elseif (is_numeric($params['banner'])){
                $storagefile = StorageFile::find($params['banner']);
                $storagefile->type = 'brand';
                $storagefile->save();
            }
            $brand = Brand::create([
                'name' => $params['name'],
                'slug' => $slug,
                'order' => $params['order'],
                'is_active' => $params['is_active'],
                'id_banner' => $storagefile->id,
                'creator_id' => Auth::user()->id,
            ]);
            $brand = $brand->load('Creator:name,id',"Banner:url,id")->toArray();
            $brand['creator'] = $brand['creator']['name'];
            return $brand;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Brand $brand)
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
            if($request->hasFile('banner')){
                $brand->Banner()->delete();
                $path = 'brand/'.$slug;
                $image = $params['banner'];
                $filename  = $image->hashName();
                $extension = $image->getClientOriginalExtension();
                $image->storeAs($path, $filename);
                $url = 'api/getFile?disk='.env('FILESYSTEM_DRIVER', 'local').'&path='.urlencode($path.'/'.$filename);
                $storagefile = StorageFile::create([
                    'name' => $filename,
                    'url' => $url,
                    'type' => 'brand',
                    'extension' => $extension
                ]);
                $brand->id_banner = $storagefile->id;
            }elseif (is_numeric($params['banner'])){
                $brand->Banner()->delete();
                $storagefile = StorageFile::find($params['banner']);
                $storagefile->type = 'brand';
                $storagefile->save();
                $brand->id_banner = $storagefile->id;
            }
            $brand->name      = $params['name'];
            $brand->slug      = $slug;
            $brand->order     = $params['order'];
            $brand->is_active = $params['is_active'];
            $brand->save();
        }

        $brand = $brand->load('Creator:name,id',"Banner:url,id")->toArray();
        $brand['creator'] = $brand['creator']['name'];
        return $brand;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Brand $brand)
    {
        if ($brand->productCheck()) {
            return response()->json(['error' => 'This record is currently linked to another that cannot be deleted'], 403);
        }else{
            try {
                $brand->Banner()->delete();
                $brand->delete();
            } catch (\Exception $ex) {
                response()->json(['error' => $ex->getMessage()], 403);
            }
        }
        return response()->json(null, 204);
    }
}
