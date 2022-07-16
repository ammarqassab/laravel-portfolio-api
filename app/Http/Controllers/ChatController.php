<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Events\MessageCreated;
use App\Http\Controllers\BaseController as BaseController;
use Throwable;
class ChatController extends BaseController
{
    public function welcome($user)
    {

        $admin=1;
        $conversation = Conversation::create([
            'user_id' =>$user,  //owner conversation
        ]);
       //add sender and receiver into participants
       // insert in many to many use attack
        $conversation->participants()->attach([
            $admin => ['joined_at' => now()],
            $user => ['joined_at' => now()],
        ]);
        $type = 'text';
        $message ='welcome, how can we help you?';
        $message = $conversation->messages()->create([
            'user_id' =>$admin,
            'type' => $type,
            'body' => $message,
        ]);
        DB::statement('
                INSERT INTO recipients (user_id, message_id)
                SELECT user_id, ? FROM participants
                WHERE conversation_id = ?
                AND user_id <> ? ',
                [$message->id, $conversation->id,$admin]);
                $conversation->update([
                'last_message_id' => $message->id,
            ]);

            return response()->json([
                'message'=>'register successfully and sent message welcome',],200);
    }
    //SEND MESSAGE  request:  user_id /message or attachment   response :
    public function sentMessage(Request $request)
    {

        $user = Auth::user();
        $request->validate([
            'conversation_id' => [
                Rule::requiredIf(function() use ($request) {
                    return !$request->input('user_id');
                }),
                'int',
                'exists:conversations,id',
            ],
            'user_id' => [
                Rule::requiredIf(function() use ($request) {
                    return !$request->input('conversation_id');
                }),
                'int',
                'exists:users,id',
            ],
        ]);


        $conversation_id = $request->post('conversation_id');
        $user_id = $request->post('user_id');  //Receive

        DB::beginTransaction();
        try {
                // by conversationID
            if ($conversation_id)
            {
                $conversation = $user->conversations()->findOrFail($conversation_id);

            }
            else
            {
                // BY USERID
                //check if u=sender and receive have conv
                //where has  relationship  condition on this relationship
                $conversation = Conversation::whereHas('participants', function ($builder) use ($user_id, $user)
             {
                 $builder->join('participants as participants2','participants2.conversation_id', '=', 'participants.conversation_id')
                         ->where('participants.user_id', '=', $user_id)
                         ->where('participants2.user_id', '=', $user->id);

             })->first();
                 // not found conv with user
                if (!$conversation)
                {
                    $conversation = Conversation::create([
                        'user_id' => $user->id,  //owner conversation
                    ]);
                   //add sender and receiver into participants
                   // insert in many to many use attack
                    $conversation->participants()->attach([
                        $user->id => ['joined_at' => now()],
                        $user_id => ['joined_at' => now()],
                    ]);
                }

            }
            $type = 'text';
            $message = $request->post('message');

            if ($request->hasFile('message')) {
                $file = $request->file('message');

                $message = [
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mimetype' => $file->getMimeType(),
                    'file_path' =>md5(microtime()).'_'.$user->username.'.'.$file->extension(),
                ];
                $file->move(public_path('/Chat_images'),$message['file_path']);
                $type = 'attachment';
            }
            //add message
            $message = $conversation->messages()->create([
                'user_id' => $user->id,
                'type' => $type,
                'body' => $message,
            ]);
            //add receipents to message

            DB::statement('
                INSERT INTO recipients (user_id, message_id)
                SELECT user_id, ? FROM participants
                WHERE conversation_id = ?
                AND user_id <> ?
            ', [$message->id, $conversation->id, $user->id]);

            $conversation->update([
                'last_message_id' => $message->id,
            ]);

            DB::commit();

            //$message->load('user');
            $message->load(['user'=>function($query)
        {
            $query->select(['id','username','email']);
        }]);
        $message->load(['recipients'=>function($query)
        {
            $query->select(['id','username','email']);
        }]);


       event(new MessageCreated($message));

        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }
      // return $message;

       return $this->sendResponse($message, 'done send message Successfully!');
    }


    //show AllConv
    public function shoWAllConv()
    {
        $user = Auth::user();
        $conversation= $user->conversations()->with([
            'lastMessage', 'participants' => function($builder) use ($user)
             {
                $builder->where('id', '<>', $user->id);
                $builder->select(['id','username']);
            },
            ])->withCount([
                'recipients as new_messages' => function($builder) use ($user) {
                    $builder->where('recipients.user_id', '=', $user->id)
                        ->whereNull('read_at');
                }
            ])->get();
            return $this->sendResponse($conversation, 'All Conversations ');

    }


    //get messages for conversationID
    public function allMssageConvID($id)
    {
        $user=Auth::user();
        $Conv=Conversation::where('user_id','=',$id)->first();
        $ConversationID=$Conv->id;
         //error : Property [id] does not exist on the Eloquent builder instance  solve : first  = not get


        $conversation = $user->conversations()
            ->with(['participants' => function($builder) use ($user) {
            $builder->where('id', '<>', $user->id);
            $builder->select(['id','username']);
        }])
        ->findOrFail($ConversationID);

        $messages = $conversation->messages()->with(['recipients'=>function($query)
        {
            $query->select(['user_id','read_at']);
        }])
            ->with(['user'=>function($query)
            {
                $query->select(['id','username']);
            }])
            ->where(function($query) use ($user) {
                $query
                    ->where(function($query) use ($user) {
                        $query->where('user_id', $user->id)
                            ->whereNull('deleted_at');
                    })
                    ->orWhereRaw('id IN (
                        SELECT message_id FROM recipients
                        WHERE recipients.message_id = messages.id
                        AND recipients.user_id = ?
                        AND recipients.deleted_at IS NULL
                    )', [$user->id]);
            })
            ->latest()
            ->get();

        return [
            'conversation' => $conversation,
            'messages' => $messages,
        ];
    }

     //read at in table  Recipient
     public function markAsRead($id)
     {
        $Conv=Conversation::where('user_id','=',$id)->first();
        $ConversationID=$Conv->id;
        Recipient::where('user_id', '=', Auth::id())
             ->whereNull('read_at')
             ->whereRaw('message_id IN (
                 SELECT id FROM messages WHERE conversation_id = ?
             )', [$ConversationID])
             ->update([
                 'read_at' => Carbon::now(),
             ]);
             return [
                 'message'=>'all messages read'
             ];

     }
     public function showImageChat($image_name)
     {
        $path=public_path().'/Chat_images/'.$image_name;
        return Response::download($path);
     }
     public function unread($id)
     {
        //token admin and id user  get message sented by this user and unread by admin
        if(auth()->user()->tokenCan('server:admin'))
        {
            $user=Auth::User();

         $messages= Message::where('user_id', '=',$id)
            ->whereRaw('id IN (
                SELECT message_id FROM recipients where read_at is null AND user_id=1)')->with(['recipients'=>function($query)
                {
                    $query->select(['user_id','read_at']);
                }])->with(['user'=>function($query)
                {
                    $query->select(['id','username']);
                }])->get();
                $conversation = $user->conversations()
            ->with(['participants' => function($builder) use ($user) {
            $builder->where('id', '<>', $user->id);
            $builder->select(['id','username']);
        }])
        ->findOrFail($messages->pluck('conversation_id'));
                return [
                    'conversation' => $conversation,
                    'messages' => $messages,
                ];

    }
        //token user get message sent by admin  and unread by this user
        if(auth()->user()->tokenCan('server:user'))
        {
            $user=Auth::User();

            $messages=  Message::where('user_id', '=','1')
            ->whereRaw('id IN (
                SELECT message_id FROM recipients where read_at is null AND user_id=?)',[$id])->with(['recipients'=>function($query)
                {
                    $query->select(['user_id','read_at']);
                }])->with(['user'=>function($query)
                {
                    $query->select(['id','username']);
                }])->get();
              //  return $messages->pluck('conversation_id');     //get a   [ { 'a':b}]
                $conversation = $user->conversations()
                ->with(['participants' => function($builder) use ($user) {
                $builder->where('id', '<>', $user->id);
                $builder->select(['id','username']);
            }])
            ->findOrFail($messages->pluck('conversation_id'));
                return [
                    'conversation' => $conversation,
                    'messages' => $messages,
                ];
        }
      /*  return response()->json([
            'message'=>'NO MESSAGES',],200);
            */
}


     }

