<?php

namespace App\Http\Controllers;

use App\Models\Insert;
use App\Models\product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    
    public function show(){
        $show = product::paginate(3);
        return view('welcome',compact('show'));
    }

    public function create(Request $request){
        $validation = $request->validate([
            'pro_name' =>'required|string',
            'pro_price' => 'required|numeric',
            'pro_image' => 'required|mimes:png,jpg,jpeg'
        ],[
            'pro_name.required'=>'Product Name is Required',
            'pro_name.string' =>'Product Name Should Be String',
            'pro_price.required'=>'Product Price is Required',
            'pro_price.numeric'=>'Product Name should be Number',
            'pro_image.required' => 'Image is Required',
            'pro_image.mimes' => 'Image should be png,jpg,jpeg',
        ]);

        //check if image has

        if($request->hasFile('pro_image')){
            //generate a unique name
            $fileName = time().'.'.$request->pro_image->extension();

            //move the file to the folder
            $request->pro_image->move(public_path('file'),$fileName);

            //insert data to the database

            $create = product::create([
                'name' =>  $validation['pro_name'],
                'price' => $validation['pro_price'],
                'image' => $fileName
            ]);

            return response()->json([
                'status' => 'success'
            ]);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'Image upload failed'
        ]);
    }

    //update product

    public function updates(Request $request){

        $validations = $request->validate([
            'update_name' =>'nullable|string',
            'update_price' =>'nullable|numeric',
            'update_image' =>'nullable|mimes:png,jpg,jpeg'
        ],[
            'update_name.string' =>'Name should be String',
            'update_price.numeric' => 'Price should be Number',
           'update_image.mimes' => 'Image must be png,jpg or jpeg' 
        ]);

        $update_product = product::find($request->update_id);


        if($request->hasFile('update_image')){
            $image = $request->file('update_image');
            $image_name = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('file'),$image_name);
            $update_product->image = $image_name;
        }

        $update_product->name = $validations['update_name'];
        $update_product->price = $validations['update_price'];
        $update_product->save();

        return response()->json([
            'status' => 'success'
        ]);
    }

    //delete product
    public function delete(Request $request){
        $delete = product::find($request->delete_id);
        
        if($delete){
            //get the image
            $image_path = public_path('file/'.$delete->image);
            //delete image
            if(file_exists($image_path)){
                unlink( $image_path);
            }
            $delete->delete();
            return response()->json([
            'status' => 'success'
            ]);
        }
    }

    //pagination
    public function pagination(Request $request){
        $show = product::paginate(3);
        return view('pagination_products',compact('show'))->render();
    }
    //search
    public function search(Request $request){


        $show = product::where('name', 'like', '%'.$request->search.'%')->paginate(3);
        
        if($show->count() >= 1){
            return view('pagination_products',compact('show'))->render();
        }else{
            return response()->json([
                'status' => 'Nothing Is Found'
            ]);
        }
    }











    //code for delete multiple at a time using ajax

    public function data(){
        $all = Product::all();
        return view('dlt_multiple',compact('all'));
    }
    
    public function dlt_users(Request $request) {
        $ids = explode(",", $request->ids);
        $products = Product::whereIn('id', $ids)->get();
    
        foreach ($products as $product) {
            $imagePath = public_path('file/' . $product->image); // Adjust the path if needed
            if (file_exists($imagePath)) {
                unlink($imagePath); // Delete the image from the server
            }
        }
    
        Product::whereIn('id', $ids)->delete(); // Then, delete the records from the database
    
        return response()->json(['status' => true, 'message' => "Products successfully removed."]);
    }


    //insert checkbox data
    public function checkbox(){
        return view('insert_ck');
    }

    public function insert_box(Request $request){
        $lenguages = explode(',',$request->select_len);

        $insert = Insert::create([
            'name' => $request->name,
            'language' => json_encode($lenguages) 
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data save Successfully'
        ]);
    }

    public function show_all(){
        $show_all = Insert::all();
        return view('show_checkbox',compact('show_all'));
    }

    public function update_box(Request $request,$id){
        $data = Insert::where('id',$id)->first();
        return view('update_checkbox',compact('data'));
    }

    public function update_checkbox(Request $request){
        $Id = Insert::find($request->id);
        $lenguages = explode(',',$request->selected_len);

        $Id->name = $request->name;
        $Id->language = json_encode($lenguages);
        $Id->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Data save Successfully'
        ]);
    }
    
    
    












    
}
