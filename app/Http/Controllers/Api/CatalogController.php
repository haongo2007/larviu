<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Larviu\Models\Catalog;
use App\Http\Resources\CatalogCollection;
use App\Larviu\JsonResponse;

class CatalogController extends Controller
{
	const ITEM_PER_PAGE = 15;
	/**
     * Display a listing of the user resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|ResourceCollection
     */
    public function index(Request $request)
    {
        $searchParams = $request->all();
        $catalogQuery = Catalog::with('Creator:name,id','Child:id,id_parent,name');
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $role = Arr::get($searchParams, 'role', '');
        $keyword = Arr::get($searchParams, 'keyword', '');
        if (!empty($keyword)) {
            $catalogQuery->where('name', 'LIKE', '%' . $keyword . '%');
        }
        $list = $catalogQuery->paginate($limit);
        return CatalogCollection::collection($list);
    }
    /**
     * A helper to Recursive catalog.
     *
     * @param  $data
     * @return array
     */
    public static function data_tree($data,$parent=0,$level=0,$name_parent='Is parent'){
        $result = [];
        foreach ($data as $key => $value) {
            if ($value['id_parent'] == $parent ) {
                $value['level'] = $level;
                $value['name_parent'] = $name_parent;
                $result[] = $value;
                $child = self::data_tree($data,$value['id'],$level + 1,$value['name']);
                unset($data[$value['id']]);
                $result = array_merge($result,$child);
            }
        }
        return $result;
    }
}
