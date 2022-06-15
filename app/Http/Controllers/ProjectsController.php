<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Image;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use File;
class ProjectsController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    //Show All Projects
    public function index()
    {
        $Project= Project::with('images')->get();
        if (is_null($Project)) {
            return $this->sendError('Project not found');
        }
        return $this->sendResponse($Project,'aLL project');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

     //ADD PROJECT
    public function store(Request $request)
    {

        $input=$request->all();
        
        $validator=Validator::make($input,
        [
            'number'=>'required',
            'name'=>'required',
            'type'=>'required',
            'link'=>'required',
            'description'=>'required',
        ]);
        
        if ($validator->fails()) {
            return $this->sendError('validate Error', $validator->errors());
        }
        $project=Project::create([
            'number'=>$request->number,
            'name'=>$request->name,
            'type'=>$request->type,
            'link'=>$request->link,
            'description'=>$request->description,
        ]);

        if ($request->hasFile('images'))
         {
        $images = $request->file('images');
        foreach ($images as $image) {
        $image_name='project_image-'.time().'.'.$request->images;
        $request->images->move(public_path('/upload/project_images'),$image_name);

        Image::create([
            'project_id'=>$project->id,
            'path' =>$image_name,
        ]);
        }
        return $this->sendResponse($project,'Added Project');

}
    }
                

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\cr  $cr
     * @return \Illuminate\Http\Response
     */

    //Show ProjectID
    public function show($id)
    {
        $Project= Project::with('images')->find($id);
        if (is_null($Project)) {
            return $this->sendError('Project not found');
        }  
        return $this->sendResponse($Project,'projectID');
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\cr  $cr
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $project=Project::find($id);
        if($project)
        {
            
            $validator=Validator::make($request->all(),
            [
            'number'=>'required',
            'name'=>'required',
            'type'=>'required',
            'link'=>'required',
            'description'=>'required',
            ]);
            if ($validator->fails()) {
                return $this->sendError('validation error', $validator->errors());
            }
            /*
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                foreach ($images as $image) {
                    if ($image->isValid())
                     {
                        $old_path=public_path().'/upload/public/images'. $project->images->name;
                if(File::exists($old_path))
                {
                    File::delete($old_path);
                }
                        $image_name='project_image-'.time().'.'.$request->images->extension();
                        $request->images->move_uploaded_file(public_path('/upload/project_images'),$image_name);
                        $image=Image::create([
                            'project_id'=>$project->id,
                            'path' =>$image_name,
                        ]);
                    }}}
                    */
                $project->update([
                    'number'=>$request->number,
                    'name'=>$request->name,
                    'type'=>$request->type,
                    'link'=>$request->link,
                    'description'=>$request->description,
                ]);
                $project->save();
                $project= Project::with('images')->find($id);
                    
            }

        
        else
        {
            return $this->sendError('Project not found');
        }

        return $this->sendResponse($project,'project Update Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\cr  $cr
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $errorMessage = [];
        $projects = Project::find($id);

        if ($projects == null) {
            return $this->sendError('the project does not exist', $errorMessage);
        }
        $projects->delete();
        return $this->sendResponse(true, 'project delete successfully');
    }
}
