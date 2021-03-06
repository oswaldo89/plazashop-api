<?php

namespace App\Http\Controllers\Api;

use App\Chat;
use App\Product;
use App\ProductsPhoto;
use App\User;
use App\UserTopic;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Ixudra\Curl\Facades\Curl;
use Slack;

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
        $product->telephone = strlen($request->telephone) > 0 ? $request->telephone : "";
        $product->user_id = Auth::user()->id;
        $product->validate = false;

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
            Slack::send("*Nuevo masacota agregada:* " . "\n\n" . "```" . response()->json($product,200, array(), JSON_PRETTY_PRINT) . "```" );
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
        $product->validate = false;
        $product->nombre = $request->nombre;
        $product->precio = $request->precio;
        $product->categoriaId = $request->categoriaId;
        $product->local = $request->local;
        $product->telephone = strlen($request->telephone) > 0 ? $request->telephone : "";
        $product->descripcion = $request->descripcion;
        $product->activo = $request->activo;
        $product->cuantity = $request->cuantity;

        $product->latitude = $request->latitude;
        $product->longitude = $request->longitude;
        $product->Pais = $request->Pais;
        $product->Estado = $request->Estado;
        $product->Municipio = $request->Municipio;
        $product->Colonia = $request->Colonia;
        $product->isAdoption = $request->isAdoption;
        $product->enableChat = $request->enableChat;



        if ($product->update()) {

            //Guarda imagenes
            if ($request->image != null) {
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
            Slack::send("*Mascota modificada:* " . "\n\n" . "```" . response()->json($product,200, array(), JSON_PRETTY_PRINT) . "```" );
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
    public function getList($total, $user_id)
    {
        if ($user_id != 0) {
            $products = Product::where('activo', 1)
                ->where("user_id", '!=', $user_id)
                ->where("validate", true)
                ->skip($total)->take(10)
                ->orderBy('created_at', 'desc')
                ->with('photos')->get();
        } else {
            $products = Product::where('activo', 1)
                ->where("validate", true)
                ->skip($total)->take(10)
                ->orderBy('created_at', 'desc')
                ->with('photos')->get();
        }

        return response()->json($products);
    }

    /** Delete image **/
    public function deleteImage($id)
    {
        $photo = ProductsPhoto::find($id);
        if ($photo->delete()) {
            $result['status'] = true;
            $result['message'] = 'Eliminada correctamente.';
        } else {
            $result['status'] = false;
            $result['message'] = 'Ocurrio un error inesperado.';
        }
        return response()->json($result);
    }

    /* Obtain products from user_id */
    public function getListByUser($total, $user_id)
    {
        $products = Product::where("user_id", $user_id)->skip($total)->take(10)->orderBy('created_at', 'desc')->with('photos')->get();
        return response()->json($products);
    }

    public function sendMessage(Request $request)
    {
        $user_id = Auth::user()->id;
        $buyer_id = $request->buyer_id;
        $message = $request->message;
        $chat_id = $request->chat_id;
        $uniq = $request->uniq;
        $product = Product::where("id", $request->pet_id)->first();

        //si el dueño del producto es el que esta en session envia la burbuja a la derecha
        if ($product->user_id != $user_id) {
            $type_message = 2;
        } else {
            $type_message = 1;
        }

        //Si el que envia el mensaje tiene una conversacion con el dueño del producto
        $user_topic = UserTopic::where("user_one", $product->user_id)->where("user_two", $buyer_id)->where("pet_id", $product->id)->first();

        if (!$user_topic && $chat_id == "") {

            $conversation_relation = new UserTopic();
            $conversation_relation->user_one = $product->user_id;
            $conversation_relation->user_two = $buyer_id;
            $conversation_relation->topic_id = uniqid();
            $conversation_relation->pet_id = $product->id;

            if ($conversation_relation->save()) {
                /* invita a las 2 personas al grupo */
                $tokenOwner = User::where("id", $product->user_id)->first();
                $tokenBuyer = User::where("id", $buyer_id)->first();
                $this->subscribeUser($tokenOwner->firebase_token, $conversation_relation->topic_id);
                $this->subscribeUser($tokenBuyer->firebase_token, $conversation_relation->topic_id);

                $chat = new Chat();
                $chat->chat_id = $conversation_relation->topic_id;
                $chat->message = $message;
                $chat->type = $type_message;
                $chat->pet_id = $product->id;
                $chat->uniq = $uniq;


                if ($chat->save()) {
                    $post_data = array(
                        'to' => "/topics/" . $conversation_relation->topic_id,
                        'data' => array(
                            'chat_id' => $conversation_relation->topic_id,
                            'message_id' => $chat->id,
                            'message' => $message,
                            'type' => $type_message,
                            'pet_id' => $product->id,
                            'category_id' => $product->categoriaId,
                            'uniq' => $uniq,
                            'title' => $product->nombre,
                            'user_id' => $user_id
                        )
                    );
                    Log::info('CHAT NUEVO:' . $conversation_relation->topic_id);
                    $this->sendNotification($post_data);
                    echo json_encode($chat);
                }
            }

        } else {
            //$conversation = UserTopic::where("user_one", $product->user_id)->orWhere("user_two", $product->user_id)->where("topic_id", $chat_id)->first();
            $conversation = UserTopic::where("topic_id", $chat_id)->first();

            $chat = new Chat();
            $chat->chat_id = $conversation->topic_id;
            $chat->message = $message;
            $chat->type = $type_message;
            $chat->pet_id = $product->id;
            $chat->uniq = $uniq;

            if ($chat->save()) {
                $post_data = array(
                    'to' => "/topics/" . $conversation->topic_id,
                    'data' => array(
                        'chat_id' => $conversation->topic_id,
                        'message_id' => $chat->id,
                        'message' => $message,
                        'type' => $type_message,
                        'pet_id' => $product->id,
                        'category_id' => $product->categoriaId,
                        'uniq' => $uniq,
                        'title' => $product->nombre,
                        'user_id' => $user_id
                    )
                );

                Log::info('CHAT EXISTENTE:' . $conversation->topic_id);

                $this->sendNotification($post_data);
                echo json_encode($chat);
            }
        }
    }

    public function deleteMessage(Request $request)
    {
        $topic = UserTopic::where("topic_id", $request->topic_id)->where("pet_id", $request->pet_id)->first();
        $topic->delete();
    }

    private function sendNotification($post_data)
    {
        return Curl::to('https://fcm.googleapis.com/fcm/send')
            ->withData($post_data)
            ->asJson()
            ->withHeader('Content-Type: application/json')
            ->withHeader('Authorization: Key=AAAAEZ0_Zqc:APA91bFYBorBc7GJdzyj-Cp_3tY_UV4gklGUEJtnf0zp6J9KFDupcTohK81CzqOK6SfRpKVBp9ctpZx8Da0ibuBkJrfO7MKHcQzRLSdkzoy88TyVfnmVHc6z41AQ1jMFuMBYgURoMBrb')
            ->post();
    }

    private function subscribeUser($user_token, $conversation_uuid)
    {
        $response = Curl::to('https://iid.googleapis.com/iid/v1/' . $user_token . '/rel/topics/' . $conversation_uuid)
            ->asJson()
            ->withHeader('Content-Type: application/json')
            ->withHeader('Authorization: Key=AAAAEZ0_Zqc:APA91bFYBorBc7GJdzyj-Cp_3tY_UV4gklGUEJtnf0zp6J9KFDupcTohK81CzqOK6SfRpKVBp9ctpZx8Da0ibuBkJrfO7MKHcQzRLSdkzoy88TyVfnmVHc6z41AQ1jMFuMBYgURoMBrb')
            ->post();
    }
}
