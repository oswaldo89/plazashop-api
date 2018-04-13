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
        $product->telephone = "";
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
    public function updateProduct(Request $request)
    {
        $product = Product::find($request->id);
        $product->nombre = $request->nombre;
        $product->precio = $request->precio;
        $product->categoriaId = $request->categoriaId;
        $product->local = $request->local;
        $product->telephone = $request->telephone;
        $product->descripcion = $request->descripcion;
        $product->activo = $request->activo;

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
        $products = Product::where("user_id", $user_id)->skip($total)->take(10)->orderBy('created_at', 'desc')->with('photos')->get();
        return response()->json($products);
    }
}
