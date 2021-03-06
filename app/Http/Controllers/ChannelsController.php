<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Thread;

class ChannelsController extends Controller
{

    /**
     * Show channel threads
     * @param Channel $channel
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Channel $channel)
    {
        return view('threads.index',
            [
                'title'   => "{$channel->name} Threads",
                'threads' => $channel->threads()->paginate(20),
                'trending' => (new \App\Thread)->getTrending(),
                'latestThreads' => Thread::latest()->take(5)->get()
            ]);
    }

}
