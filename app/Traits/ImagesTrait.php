<?php 
namespace App\Traits;
use App\Models\Image;
use App\Model\Project;

Trait ImagesTrait 
{
    public function storeImages($pro,$request)     
    {
       foreach($request as $img)
       {
        $image_name=$image_name='project_image-'.time().'.'.$img;
        $img->move(public_path('/upload/project_images'),$image_name);
        $image=Image::create(['path'=>$image_name,'project_id'=>$pro->id]);
        $image->save();
       }
    }
       public function updateImages($pro,$request)     
    {

       foreach($pro->images as $img)
       {
          $img->delete();
       }
       foreach($request as $img)
       {
        $image_name=$image_name='project_image-'.time().'.'.$img;
        $img->move(public_path('/upload/project_images'),$image_name);
        $image=Image::create(['path'=>$image_name,'project_id'=>$pro->id]);
        $image->save();

       }
    }
       public function deleteImages($pro)     
    {
        foreach($pro->images as $img)
       {
          $img->delete();
       }
}

}


