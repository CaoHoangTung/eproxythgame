<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSentEvent;

class MessageController extends Controller{
    public function store(Request $request)
    {
        $user = Auth::user();
        // $user = "";

        $message = $request->message;
        event(new MessageSentEvent($user,$message));
        
        return response()->json([
            'status' => 1,
        ]);
    }
}