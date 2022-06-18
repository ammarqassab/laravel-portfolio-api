<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Image;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;
use App\Traits\ImagesTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use File;
class ProjectsController extends BaseController
{     
    use ImagesTrait;
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
            'images'=>'required',
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
        if($request->file('images'))
        {
            $this->storeImages($project,$request->images);
        }
        $project= Project::with('images')->find($project->id);
        return $this->sendResponse($project,'Added Project');
    }

    public function index()
    {
        $Project= Project::with('images')->get();
        if (is_null($Project)) {
            return $this->sendError('Project not found');
        }
        
        return $this->sendResponse($Project,'aLL project');
    }

    //Show ProjectID
    public function show($id)
    {
        $Project= Project::with('images')->find($id);
        if (is_null($Project)) {
            return $this->sendError('Project not found');
        }  
        return $this->sendResponse($Project,'projectID');
    }
    
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
        

                $project->update([
                    'number'=>$request->number,
                    'name'=>$request->name,
                    'type'=>$request->type,
                    'link'=>$request->link,
                    'description'=>$request->description,
                ]);
                if($request->file('images'))
                {
                    $this->updateImages($project,$request->images);
                }
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
        $project = Project::find($id);

        if ($project == null) {
            return $this->sendError('the project does not exist', $errorMessage);
        }
        $this-> deleteImages($project);
        $project->delete();
        return $this->sendResponse(true, 'project delete successfully');
    }
}
