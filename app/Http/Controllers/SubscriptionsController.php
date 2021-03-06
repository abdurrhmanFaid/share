<?php

namespace App\Http\Controllers;

use App\Thread;

class SubscriptionsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Authenticated user may subscribe to a thread
     * @param Thread $thread
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function subscribeToThread(Thread $thread){

        if(! $thread->isSubscribed){
            $thread->subscribe();
        }

        return response([], 204);

    }

    /**
     * A user may unfollow a thread that he was subscribed
     * @param Thread $thread
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function unsubscribeFromThread(Thread $thread){
        $thread->isSubscribed ? $thread->unsubscribe() : '';

        return response([], 204);
    }
}
