<?php

namespace App\Http\Controllers\Api;

use App\Product;
use App\ProductsPhoto;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $product = new Product($request->all());
        
        if ($product->save()){

            //Guarda imagenes
            foreach ($request->image as $photo) {
                $filename = $photo->store('photos');
                ProductsPhoto::create([
                    'product_id' => $product->id,
                    'filename' => $filename
                ]);
            }

            $result['status'] = true;
            $result['message'] = 'Agregado correctamente.';
        }else{
            $result['status'] = false;
            $result['message'] = 'Ocurrio un error, no se pudo guardar.';
        }

        return response()->json($result);
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     Obtains pagination from the products
     **/
    public function getList($total){
        $products = Product::skip($total)->take(10)->orderBy('created_at', 'desc')->get();
        return response()->json($products);
    }
}
