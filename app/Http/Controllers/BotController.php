<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\User;
use App\Bot;
use App\Step;
use App\ChatLog;
use Log;


class BotController extends Controller
{

    public function test(Request $request)
    {
        Log::info('Telegram callback received', $request->all());
        return $request->all();
    }
    
    public function show(Request $request, $callback)
    {

        $bot = Bot::where('callback', $callback)->firstOrFail();
        
        if(!$bot->id || !count($bot->steps))
        {
            return response()->json([
                'error' => 'not found',
            ], 404);
        }
        
        Log::info('Telegram callback received', $request->all());
        
        $message = $request->message['text'] ?? $request->callback_query['data'] ?? '';
        $chat_id = $request->callback_query['from']['id'] ?? $request->message['chat']['id'] ?? '';
        $name = $request->callback_query['from']['first_name'] ?? $request->message['chat']['first_name'] ?? '';
        $username = $request->callback_query['from']['username'] ?? $request->message['chat']['username'] ?? '';

        if(!$message)
        {
            return response()->json([
                'error' => 'callback_query is not set',
            ], 400);
        }

        
        $user = User::firstOrCreate(
            ['telegram_id' => $chat_id, 'bot_id' => $bot->id],
            ['name' => $name, 'username' => $username]
        );

        if($user->banned) {
            return response()->json([
                'error' => 'user is banned',
            ], 200);
        }

  
        // upload all files if available
        if (isset($request->photo[1]['file_id']) || $request->document) 
        {
            $file_id = $request->photo[1]['file_id'] ?? $request->document['file_id'];

            $out = Http::post($bot->api . 'getFile', [
                'file_id' => $file_id,
            ]);
            $out = $response->json();

            $file_path = Storage::putFileAs(
                'public/' . $user->user_id,
                 file_get_contents(
                     'https://api.telegram.org/file/'.$bot->token.'/' 
                    . $out['result']['file_path']
                ),
                ($request->document['file_name'] ?? $request->photo[1]['file_id'] . '.jpg')
            );

            $message = $file_path;
        }

        
        

        if($message == '/start')
        {
            $step = Step::where([
                'step_order' => 1,
                'bot_id' => $user->bot_id,
            ])->first();
        } 
        else 
        {
            $step = Step::where([
                'step_order' => $user->step->step_order+1,
                'bot_id' => $user->bot_id,
            ])->first();
            $buttons[] = ['text' => __('Start over'), 'callback_data' => '/start'];
     
        }

        $user->step_id = $step->id ?? 1;

        $chat_log = new ChatLog([
            'response' => $message,
            'user_id' => $user->id,
            'step_id' => $user->step_id,
        ]);

        
        $buttons = [];
        $options = [];
       
        if($step->skippable)
        {
            $buttons[] = ['text' => __('Skip step'), 'callback_data' => 'skip_step'];
        }

        if(count($buttons))
        {
            $options = [
                'reply_markup' => json_encode([
                    'inline_keyboard' => array_chunk($buttons, 2),
                ])
            ];
        }

        if($step->payload)
        {
            $payload = array_merge($payload, $step->payload);
        }

        $payload = [
            'chat_id' => $user->telegram_id,
            'text' => $step->message,
            'disable_web_page_preview' => true
        ];

        $response = Http::post( $bot->api . 'sendMessage', [
                array_merge($payload, $options)
        ]);

        Log::info('Telegram callback sent to '. $bot->api . 'sendMessage', array_merge($payload, $options, $response->json()));

        return response()->json([
            'status' => 'ok',
        ], 200);

    }

}
