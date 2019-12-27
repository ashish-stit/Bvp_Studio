<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User; 
use App\User_FolderModel;
use App\MediaModal;
use Illuminate\Support\Facades\Auth; 
use Validator;
use File;
use DB;
use Masih\YoutubeDownloader\YoutubeDownloader;


class UploadFileController extends Controller 
{
    public function savefolder(Request $request) 
    {
       
        $data=json_decode(file_get_contents('php://input'),true);
        $user_folder = new User_FolderModel();
        $user_folder->user_id=$data['user_id'];
        $user_folder->folder_name = $request->folder_name;
        $user_folder->parent_id ='0';
        $path = public_path().'/user/'.$user_folder->folder_name.'_'.$data['user_id'] ;
        File::makeDirectory($path,0777,true);
        $user_folder->save();
        return response()->json($user_folder);
    }
    public function getfolder(Request $request)
    {
        $rows = DB::select('select * from user_project where user_id='.$request->user_id);
        
        $folders = $this->folder_structure($this->objectToArray($rows));    
        $path = public_path().'/user/';
        $response = array('folders'=>array(),'path'=>'');
        $path = public_path().'/user/';
        $response['folders'] = $folders;
        $response['path'] = $path;     
        return response()->json($response);
    }
    function objectToArray($d)
    {
        if (is_object($d)) {
            $d = get_object_vars($d);
        }
        if (is_array($d)) {
            return array_map(function ($value) {
                return (array)$value;
            }, $d);
        } else {
            return $d;
        }
    }
    function folder_structure(array $elements, $parentId = 0)
    {
        $returnarray = array();

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->folder_structure($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $returnarray[] = $element;
            }
        }
        return $returnarray;
    }
    
     public function uploadvideo(Request $request)
    {
      if ($file = $request->file('video')) {
            $path = public_path() . "/video";
             $priv = 0777;
            if (!file_exists($path)) {
                mkdir($path, $priv) ? true : false;
            }
            $name = uniqid($file->getClientOriginalName());
            $file->move($path, $name);
            return response()->json(compact('path'));
        }
    }
     public function uploadtextfile(Request $request)
    {
      if ($file = $request->file('file')) {
            $path = public_path() . "/text";
             $priv = 0777;
            if (!file_exists($path)) {
                mkdir($path, $priv) ? true : false;
            }
            $name = uniqid($file->getClientOriginalName());
            $file->move($path, $name);
            return response()->json(compact('path'));
        }
    }
   public function uploadedfile(Request $res)
    {

        $file = $res->file('media');
            $ext = explode('.',$file->getClientOriginalName());
            $image = array( 'gif', 'jpg','jpeg','png');
            $VidExt = explode('.',$file->getClientOriginalName());
            $video = array('mp4','video/avi');
            $TxtExt = explode('.',$file->getClientOriginalName());
            $txtfile = array('doc','docx','pptx','pps','pdf');
                    if (in_array($ext[1], $image))
                    {
                    if(!$res->hasFile('media')) 
                          {
                            return response()->json(['file not found'], 400);
                          }
                          if(!$file->isValid()) 
                          {
                            return response()->json(['invalid file'], 400);
                          }
                      $path = public_path() . '/img/';
                      $file->move($path, $file->getClientOriginalName());
                      return response()->json(compact('path'));
                    }
                    elseif(in_array($VidExt[1], $video))
                    { 
                      $path = public_path() . '/img';
                      $priv = 0777;
                      if (!file_exists($path)) 
                      {
                        
                        mkdir($path, $priv) ? true : false;
                      }
                      $name = $file->getClientOriginalName();
                      $file->move($path, $name);
                      return response()->json(compact('path'));
                 
                  }
                  elseif(in_array($TxtExt[1], $txtfile))
                  {
                      $path = public_path() . "/img";
                      $priv = 0777;
                      if (!file_exists($path)) 
                      {
                        mkdir($path, $priv) ? true : false;
                      }
                      $name = uniqid($file->getClientOriginalName());
                      $file->move($path, $name);
                      return response()->json(compact('path'));
                     
                    }
            }




   public function editfolder($id)
    {
        $data = User_FolderModel::where('id', $id);
      return response()->json(compact('data'));
    }
    public function updatefolder(Request $request)

    { 
        $user_id=$request->user_id;
        $folder_name=$request->folder_name;
        $data=User_FolderModel::find($user_id);
        $data->folder_name=$folder_name;
        if($data->save())
        {
          return response()->json(compact('data'));
        }
    }
    public function imagelist(Request $request)
   {

      $youtubelink=$request->link;
      $youtube = new YoutubeDownloader($youtubelink);
      $result = $youtube->getInfo();
      $youtube->download();
   }
   public function showdata(Post $post)
{
    $post->with(['comments' => function ($query) {
        $query->orderBy('id', 'desc')
    }]);
 )

    return view('view_post', compact('post'));
}
 
}