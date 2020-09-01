<?php

use Illuminate\Database\Seeder;
use App\Larviu\Models\Category;
use Illuminate\Support\Str;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Category::class, 10)->create()->each(
		    function ($parent) {
		    	$order = ($parent->order + 10);
		        for ($i = 0; $i < 2; $i++) {
		            factory(Category::class)->create([
		                'name' => 'child '.$parent->name,
				        'slug' => Str::slug('child '.$parent->name, '-'),
				        'is_active' => 1,
				        'order' => $order++,
				        'id_parent' => $parent->id,
				        'creator' => 1,
		            ]);
		        }
		    }
		);
    }
}
