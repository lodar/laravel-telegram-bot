<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        
        $message = $request->callback_query['data'] ?? $request->message['text'] ?? '';
        $chat_id = $request->callback_query['from']['id'] ?? $request->message['chat']['id'] ?? '';
        $name = $request->callback_query['from']['first_name'] ?? $request->message['chat']['first_name'] ?? '';
        $username = $request->callback_query['from']['username'] ?? $request->message['chat']['username'] ?? '';
        $file_id = $request->message['photo'][1]['file_id'] ?? $request->message['document']['file_id'] ?? '';

        if(!$message && !$file_id)
        {
            return response()->json([
                'error' => 'callback_query is not set',
            ], 400);
        }

        $user = User::firstOrCreate(
            ['telegram_id' => $chat_id, 'bot_id' => $bot->id],
            ['name' => $name, 'username' => $username]
        );

        if($user->banned) 
        {
            return response()->json([
                'error' => 'user is banned',
            ], 200);
        }

        if ($file_id) 
        {
            $out = Http::post($bot->api . 'getFile', [
                'file_id' => $file_id,
            ]);
            $out = $out->json();
            $message = 'https://api.telegram.org/file/bot'.$bot->token.'/' . ($out['result']['file_path'] ?? '');
        }

        
        $buttons = [];
        $options = [];

        if($user->step_id)
        {
            $chat_log = new ChatLog([
                'response' => $message,
                'user_id' => $user->id,
                'step_id' => $user->step_id,
                'remember_token' => $user->remember_token,
            ]);
            $chat_log->save();
        }
        

        if($message == '/start')
        {
            $step = Step::where([
                'step_order' => 1,
                'bot_id' => $user->bot_id,
            ])->first();
            $user->remember_token = Str::uuid();
        }
        elseif($file_id)
        {
            $step = $user->step;
            $step->message = __('File uploaded') . '. ' . __('Upload next file or click Next step') . '.';
            $buttons[] = ['text' => __('Next step'), 'callback_data' => 'next_step'];
        }
        elseif($bot->steps->max('step_order') <= $user->step->step_order+1)
        {
            $step = Step::where([
                'step_order' => $user->step->step_order+1,
                'bot_id' => $user->bot_id,
            ])->first();
            $buttons[] = ['text' => __('Start over'), 'callback_data' => '/start'];
            $send_chat_logs = true;
        } 
        elseif($message == 'skip_step' || $message == 'next_step')
        {
            $step = Step::where([
                'step_order' => $user->step->step_order+1,
                'bot_id' => $user->bot_id,
            ])->first();
        }
        else
        {
            $step = Step::where([
                'step_order' => $user->step->step_order+1,
                'bot_id' => $user->bot_id,
            ])->first();
        }

        if(!isset($step->id))
        {
            return response()->json([
                'error' => 'next step is not set',
            ], 400);
        }

        $user->step_id = $step->id;
        $user->save();

        if(isset($send_chat_logs))
        {
            $text[] = __('Chat log') . " " . $user->remember_token;
            $text[] =  '@' . $user->username;
            $chat_logs = ChatLog::where('remember_token', $user->remember_token)->get();
            foreach ($chat_logs as $log) 
            {
                $text[] = $log->step->message;
                $text[] = $log->response;
            }
            $response = Http::post($bot->api . 'sendMessage', [
                'chat_id' => $bot->owner,
                'text' => implode("\n\n", $text),
            ]);
        }
        
    
        if($step->skippable)
        {
            $buttons[] = ['text' => __('Skip step'), 'callback_data' => 'skip_step'];
        }

        if(is_array($step->payload) && count($step->payload))
        {
            $buttons = $step->payload;
        }

        if(count($buttons))
        {
            $options = [
                'reply_markup' => json_encode([
                    'inline_keyboard' => array_chunk($buttons, 2),
                ])
            ];
        }

        $payload = [
            'chat_id' => $user->telegram_id,
            'text' => $step->message,
            'disable_web_page_preview' => true
        ];

        $payload = array_merge($payload, $options);
        
        $response = Http::post($bot->api . 'sendMessage', $payload);

        Log::info('Telegram callback sent to '. $bot->api . 'sendMessage', array_merge($payload, $response->json()));

        return response()->json([
            'status' => 'ok',
        ], 200);

    }

}
