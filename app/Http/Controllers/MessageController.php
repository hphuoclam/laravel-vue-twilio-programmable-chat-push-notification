<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Twilio\Rest\Client;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $users = User::where('id', '<>', $request->user()->id)->get();

        return view('messages.index', compact('users'));
    }

    public function chat(Request $request, $ids)
    {
        $authUser = $request->user();

        $ids_arr = explode('-', $ids);

        $reverse_ids = $ids_arr[1] .'-'. $ids_arr[0];

        $otherUser = User::find(explode('-', $ids)[1]);
        $users = User::where('id', '<>', $authUser->id)->get();

        $twilio = new Client(env('TWILIO_AUTH_SID'), env('TWILIO_AUTH_TOKEN'));

        // Fetch channel or create a new one if it doesn't exist
        try {
            $channel = $twilio->chat->v2->services(env('TWILIO_SERVICE_SID'))
                ->channels($ids)
                ->fetch();
        } catch (\Twilio\Exceptions\RestException $e) {
            try {
                $channel = $twilio->chat->v2->services(env('TWILIO_SERVICE_SID'))
                    ->channels($reverse_ids)
                    ->fetch();
            } catch (\Twilio\Exceptions\RestException $e) {
                $channel = $twilio->chat->v2->services(env('TWILIO_SERVICE_SID'))
                    ->channels
                    ->create([
                        'uniqueName' => $ids,
                        'type' => 'private',
                    ]);
                $twilio->chat->v2->services(env('TWILIO_SERVICE_SID'))
                    ->channels($channel->sid)
                    ->members
                    ->create($authUser->email);
                $twilio->chat->v2->services(env('TWILIO_SERVICE_SID'))
                    ->channels($channel->sid)
                    ->members
                    ->create($otherUser->email);
            }
        }

        return view('messages.chat', compact('users', 'otherUser'));
    }
}
