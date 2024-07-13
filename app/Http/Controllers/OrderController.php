<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends ApiController
{

    public static function create($request,$mounts,$token){


        DB::beginTransaction();

        $order=Order::create([
            'user_id'=>$request->user_id,
            'total_amount'=>$mounts['totalAmount'],
            'delivery_amount'=>$mounts['deliveryAmount'],
            'paying_amount'=>$mounts['payingAmount'],
        ]);


        foreach ($request->order_items as $orderItem) {

            $product = Product::findOrFail($orderItem['product_id']);

            OrderItem::create([
                'order_id'=>$order->id,
                'product_id'=>$product->id,
                'price'=>$product->price,
                'quantity'=>$orderItem['quantity'],
                'subtotal'=>($product->price*$orderItem['quantity'])
            ]);

        }

        Transaction::create([
            'user_id'=>$request->user_id,
        	'order_id'=>$order->id,
        	'amount'=>$mounts['payingAmount'],
            'token'=>$token,
        	'request_from'=>$request->request_from
        ]);

        DB::commit();

    }

    public static function update($token,$transId){

        DB::beginTransaction();
        $teransaction=Transaction::where('token',$token)->firstOrFail();

        $teransaction->update([
            'status'=>1,
            'trans_id'=>$transId
        ]);

        $order=Order::findOrFail($teransaction->order_id);

        $order->update([
            'status'=>1,
            'payment_status'=>1,
        ]);


        foreach(OrderItem::where('order_id',$order->id)->get() as $item){


            $product=Product::find( $item->product_id);

            $product->update([
                'quantity'=> ( $product->quantity -  $item->quantity)
            ]);

        }

        DB::commit();
    }
}
