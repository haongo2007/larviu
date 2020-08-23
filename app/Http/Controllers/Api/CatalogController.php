<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Larviu\Models\Catalog;
use App\Http\Resources\CatalogCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Larviu\JsonResponse;
use Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Larviu\Models\StorageFile;
use Illuminate\Support\Facades\Storage;

class CatalogController extends Controller
{
	const ITEM_PER_PAGE = 15;
    const ORDER = 'DESC';

    public $catalogTmp = [];

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
        $catalogQuery = Catalog::with('Creator:name,id','Parent:name,id','Banner:url,id,extension');
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $sort = Arr::get($searchParams, 'sort', static::ORDER);
        $name = Arr::get($searchParams, 'name', '');
        if (!empty($name)) {
            $catalogQuery->where('name', 'LIKE', '%' . $name . '%');
        }
        if (!empty($sort)) {
            $catalogQuery->orderBy('id', $sort);
        }
        $list = $catalogQuery->paginate($limit);
        return CatalogCollection::collection($list);
    }
    /**
     * Display the specified resource.
     *
     * @param  Catalog
     * @return \Illuminate\Http\Response
     */
    public function show(Catalog $catalog)
    {
        return new CatalogCollection($catalog);
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
                $this->getValidationRules(),
                [
                    'name' => ['required', 'min:3'],
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $slug = Str::slug($params['name'], '-');
            if (is_array($params['catalog'])) {
                $params['catalog'] = last($params['catalog']);
            }
            $catalog = Catalog::create([
                'name' => $params['name'],
                'slug' => $slug,
                'order' => $params['order'],
                'is_active' => $params['status'],
                'id_parent' => $params['catalog'],
                'creator' => Auth::user()->id,
            ]);
            /* UPLOAD BANNER */
            if ($image = $params['banner']) {
                $filename  = $image->hashName();
                $path = 'catalog/'.$slug;
                $image->storeAs($path, $filename);
                $url = 'api/getFile?disk='.env('FILESYSTEM_DRIVER', 'local').'&path='.urlencode($path.'/'.$filename);
                StorageFile::create([
                    'name' => $filename,
                    'url' => $url,
                    'type' => 'catalog',
                    'extension' => $catalog->id
                ]);
            }
            $catalog = $catalog->load('Creator:name,id','Parent:name,id',"Banner:url,id,extension")->toArray();
            $catalog['creator'] = $catalog['creator']['name'];
            return $catalog;
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  Catalog $catalog
     * @return \Illuminate\Http\Response
     */
    public function destroy(Catalog $catalog)
    {
        if ($catalog->Children()->count() > 0) {
            return response()->json(['error' => 'This record is currently linked to another that cannot be deleted'], 403);
        }else{
            try {
                $catalog->Banner()->delete();
                $catalog->delete();
            } catch (\Exception $ex) {
                response()->json(['error' => $ex->getMessage()], 403);
            }
        }
        return response()->json(null, 204);
    }
    /**
     * Get Recursive catalog.
     *
     * @param  null
     * @return \Illuminate\Http\Response
     */
    public function Recursive( $id_parent = 0){
        $data = [];
        foreach ($this->getCatalog($id_parent) as $group)
        {
            $group['children'] = $this->Recursive($group['id']);
            if (empty($group['children'])) {
                unset($group['children']);
            }
            $data[] = $group;
        }
        return $data;
    }
    public function getCatalog($id_parent)
    {
        $tmp = [];
        $catalog = $this->catalogTmp;
        if (empty($catalog)) {
            $catalog = Catalog::select('name','id','id_parent')->get();
            $this->catalogTmp = $catalog;
        }
        foreach ($catalog as $key => $value) {
            if ($value['id_parent'] == $id_parent) {
                $tmp[] = $value->toArray();
            }
        }
        return $tmp;
    }
}
