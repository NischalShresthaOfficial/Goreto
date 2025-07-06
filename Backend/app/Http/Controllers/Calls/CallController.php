<?php

namespace App\Http\Controllers\Calls;

use App\Events\CallAnswered;
use App\Events\CallEnded;
use App\Events\CallInitiated;
use App\Events\IceCandidateReceived;
use App\Events\OfferSent;
use App\Http\Controllers\Controller;
use App\Models\Call;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CallController extends Controller
{
    protected $activeCalls = [];

    public function initiate(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id|different:'.Auth::id(),
            'type' => 'required|in:audio,video',
        ]);

        $call = Call::create([
            'caller_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'type' => $request->type,
            'started_at' => now(),
            'status' => null,
        ]);

        broadcast(new CallInitiated(
            Auth::id(),
            $request->receiver_id,
            $request->type
        ))->toOthers();

        return response()->json([
            'message' => 'Call initiated',
            'call_id' => $call->id,
        ]);
    }

    public function sendOffer(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'sdp' => 'required|string',
        ]);

        broadcast(new OfferSent(
            Auth::id(),
            $request->receiver_id,
            $request->sdp
        ))->toOthers();

        return response()->json(['message' => 'Offer sent']);
    }

    public function sendAnswer(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'sdp' => 'required|string',
        ]);

        broadcast(new CallAnswered(
            Auth::id(),
            $request->receiver_id,
            $request->sdp
        ))->toOthers();

        return response()->json(['message' => 'Answer sent']);
    }

    public function sendIceCandidate(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'candidate' => 'required|array',
        ]);

        broadcast(new IceCandidateReceived(
            Auth::id(),
            $request->receiver_id,
            $request->candidate
        ))->toOthers();

        return response()->json(['message' => 'ICE candidate sent']);
    }

    public function end(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'call_id' => 'nullable|exists:calls,id',
            'status' => 'nullable|in:completed,missed,rejected',
        ]);

        if ($request->filled('call_id')) {
            $call = Call::find($request->call_id);
            if ($call) {
                $call->ended_at = now();
                $call->status = $request->status ?? 'completed';
                $call->save();
            }
        }

        broadcast(new CallEnded(
            Auth::id(),
            $request->receiver_id
        ))->toOthers();

        return response()->json(['message' => 'Call ended']);
    }
}
