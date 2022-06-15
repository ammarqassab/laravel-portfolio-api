<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Recipient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\http\Controllers\BaseController as BaseController;
use Throwable;

class ChatController extends BaseController
{
    //SEND MESSAGE 
    public function sentMessage(Request $request)
    {
        $user = Auth::user(); //sender
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

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                
                $message = [
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mimetype' => $file->getMimeType(),
                    'file_path' => $file->store('attachments', [
                        'disk' => 'public'
                    ]),
                ];
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
            $query->select(['id','username','profile_image','role_as']);
        }]);
        $message->load(['recipients'=>function($query)
        {
            $query->select(['profile_image']);
        }]);
            

          //  event(new MessageCreated($message));

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
                $builder->select(['id','username','profile_image','role_as']);
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
        $ConversationID=$Conv->id; //error : Property [id] does not exist on the Eloquent builder instance  solve : first  = not get


        $conversation = $user->conversations()
            ->with(['participants' => function($builder) use ($user) {
            $builder->where('id', '<>', $user->id);
            $builder->select(['id','username','profile_image','role_as']);
        }])
        ->findOrFail($ConversationID);
         
        $messages = $conversation->messages()->with(['recipients'=>function($query)
        {
            $query->select(['user_id','read_at']);
        }])
            ->with(['user'=>function($query)
            {
                $query->select(['id','username','profile_image','role_as']);
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
    



}

