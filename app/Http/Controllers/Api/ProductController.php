<?php

namespace App\Http\Controllers\Api;

use App\Product;
use App\ProductsPhoto;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $product = new Product($request->all());
        $product->user_id = Auth::user()->id;

        if ($product->save()) {

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
        } else {
            $result['status'] = false;
            $result['message'] = 'Ocurrio un error, no se pudo guardar.';
        }

        return response()->json($result);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $productRequest = new Product($request->all());
        $product = Product::find($id);
        $product->id = $id;
        $product->nombre = $productRequest->nombre;
        $product->precio = $productRequest->precio;
        $product->categoriaId = $productRequest->categoriaId;
        $product->local = $productRequest->local;
        $product->descripcion = $productRequest->descripcion;


        return response()->json($product);
        if ($product->update()) {

            //Guarda imagenes
            if($request->image != null){
                foreach ($request->image as $photo) {
                    $filename = $photo->store('photos');
                    ProductsPhoto::create([
                        'product_id' => $product->id,
                        'filename' => $filename
                    ]);
                }
            }

            $result['status'] = true;
            $result['message'] = 'Modificado correctamente.';
        } else {
            $result['status'] = false;
            $result['message'] = 'Ocurrio un error, no se pudo modificar.';
        }

        return response()->json($result);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Obtains pagination from the products
     **/
    public function getList($total)
    {
        $products = Product::where('activo', 1)->skip($total)->take(10)->orderBy('created_at', 'desc')->with('photos')->get();
        return response()->json($products);
    }

    /* Obtain products from user_id */
    public function getListByUser($total, $user_id)
    {
        $products = Product::where('activo', 1)->where("user_id", $user_id)->skip($total)->take(10)->orderBy('created_at', 'desc')->with('photos')->get();
        return response()->json($products);
    }
}
