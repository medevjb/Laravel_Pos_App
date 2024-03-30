<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Mail\PromotionalMail;
use App\Models\InvoiceProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller {

    public function createInvoice( Request $request ) {

        DB::beginTransaction();

        try {

            $user_id = $request->header( 'id' );
            $user_email = $request->header( 'email' );
            $total = $request->input( 'total' );
            $discount = $request->input( 'discount' );
            $vat = $request->input( 'vat' );
            $customer_id = $request->input( 'customer_id' );
            $payable = $request->input( 'payable' );

            // return $user_id;

            $invoice = Invoice::create( [
                'total'       => $total,
                'discount'    => $discount,
                'user_id'     => $user_id,
                'vat'         => $vat,
                'customer_id' => $customer_id,
                'payable' => $payable,
            ] );

            $invoice_id = $invoice->id;

            $products = $request->input( 'products' );
            foreach ( $products as $product ) {
                $invoiceProduct = InvoiceProduct::create( [
                    'invoice_id' => $invoice_id,
                    'product_id' => $product['product_id'],
                    'qty'        => $product['qty'],
                    'sale_price' => $product['sale_price'],
                ] );
            }

            Mail::to( $user_email )->send( new PromotionalMail( "Your Purces Invoice", "Your Invoice Details<br>" . $invoice, "Your Poduct Details" . $invoiceProduct ) );

            DB::commit();
            return 1;

        } catch ( Exception $e ) {

            DB::rollBack();
            return 0;

        }

    }

    function invoiceSelect( Request $request ) {
        $user_id = $request->header( 'id' );
        return Invoice::where( 'user_id', $user_id )->with( 'customer' )->get();
        // return Invoice::where( 'user_id', $user_id )->with( ['customer', 'invoiceProducts'] )->get();   // With relational all data
    }

    function invoiceDetails( Request $request ) {
        // $user_id = $request->header( 'id' );
        // $customer_id = $request->input( 'cus_id' );
        // $invoice_id = $request->input( 'inv_id' );
        // return Invoice::where( 'user_id', $user_id )
        //     ->where( 'customer_id', '=', $customer_id )
        //     ->where( 'id', '=', $invoice_id )
        //     ->with( ['customer', 'invoiceProducts'] )
        //     ->get();

        // ALternative
        $user_id = $request->header( 'id' );
        $customerDetails = Customer::where( 'user_id', $user_id )->where( 'id', $request->input( 'cus_id' ) )->first();
        $invoiceTotal = Invoice::where( 'user_id', $user_id )->where( 'id', $request->input( 'inv_id' ) )->first();
        $invoiceProduct = InvoiceProduct::with('product')->where( 'invoice_id', $request->input( 'inv_id' ) )->get();

        return array(
            'customer' => $customerDetails,
            'invoice'  => $invoiceTotal,
            'product'  => $invoiceProduct,
        );
    }

    function invoiceDelete( Request $request ) {
        DB::beginTransaction();

        try {

            $user_id = $request->header( 'id' );
            InvoiceProduct::where( 'invoice_id', $request->input( 'inv_id' ) )->delete();
            Invoice::where( 'id', '=', $request->input( 'inv_id' ) )
                ->where( 'user_id', '=', $user_id )
                ->delete();

            DB::commit();
            return 1;

        } catch ( Exception $e ) {
            DB::rollBack();
            return $e;
        }
    }


    public function salePage(){
        return view('Frontend.pages.dashboard.sale');
    }
    public function invoicePage(){
        return view('Frontend.pages.dashboard.invoice');
    }

    



   

}
