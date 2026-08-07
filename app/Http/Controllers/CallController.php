<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Models\CallSignal;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CallController extends Controller
{
    public function incoming(): JsonResponse
    {
        $call = Call::where('callee_id', Auth::id())
            ->where('status', Call::STATUS_RINGING)
            ->where('created_at', '>=', now()->subSeconds(25))
            ->with('caller')
            ->latest()
            ->first();

        return response()->json([
            'call' => $call ? ['id' => $call->id, 'caller_name' => $call->caller->name] : null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['callee_id' => ['required', 'integer', 'exists:users,id']]);

        abort_if($data['callee_id'] === Auth::id(), 422, 'Нельзя позвонить самому себе.');

        $callee = User::findOrFail($data['callee_id']);

        $call = Call::create([
            'caller_id' => Auth::id(),
            'callee_id' => $callee->id,
            'status' => Call::STATUS_RINGING,
        ]);

        return response()->json(['id' => $call->id]);
    }

    public function show(Call $call): JsonResponse
    {
        abort_unless(in_array(Auth::id(), [$call->caller_id, $call->callee_id], true), 403);

        return response()->json([
            'status' => $call->status,
            'offer_sdp' => $call->offer_sdp,
            'answer_sdp' => $call->answer_sdp,
        ]);
    }

    public function offer(Request $request, Call $call): JsonResponse
    {
        abort_unless($call->caller_id === Auth::id(), 403);

        $data = $request->validate(['sdp' => ['required', 'string']]);

        $call->update(['offer_sdp' => $data['sdp']]);

        return response()->json(['ok' => true]);
    }

    public function answer(Request $request, Call $call): JsonResponse
    {
        abort_unless($call->callee_id === Auth::id(), 403);

        $data = $request->validate(['sdp' => ['required', 'string']]);

        $call->update([
            'answer_sdp' => $data['sdp'],
            'status' => Call::STATUS_ACCEPTED,
            'started_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function decline(Call $call): JsonResponse
    {
        abort_unless($call->callee_id === Auth::id(), 403);

        if ($call->status === Call::STATUS_RINGING) {
            $call->update(['status' => Call::STATUS_DECLINED, 'ended_at' => now()]);
        }

        return response()->json(['status' => $call->status]);
    }

    public function end(Call $call): JsonResponse
    {
        abort_unless(in_array(Auth::id(), [$call->caller_id, $call->callee_id], true), 403);

        if (in_array($call->status, [Call::STATUS_ENDED, Call::STATUS_DECLINED, Call::STATUS_MISSED], true)) {
            return response()->json(['status' => $call->status]);
        }

        $call->update([
            'status' => $call->status === Call::STATUS_RINGING ? Call::STATUS_MISSED : Call::STATUS_ENDED,
            'ended_at' => now(),
        ]);

        return response()->json(['status' => $call->status]);
    }

    public function storeCandidate(Request $request, Call $call): JsonResponse
    {
        abort_unless(in_array(Auth::id(), [$call->caller_id, $call->callee_id], true), 403);

        $data = $request->validate(['candidate' => ['required', 'string']]);

        CallSignal::create([
            'call_id' => $call->id,
            'sender_id' => Auth::id(),
            'candidate' => $data['candidate'],
        ]);

        return response()->json(['ok' => true]);
    }

    public function candidates(Request $request, Call $call): JsonResponse
    {
        abort_unless(in_array(Auth::id(), [$call->caller_id, $call->callee_id], true), 403);

        $since = (int) $request->query('since', 0);

        $signals = CallSignal::where('call_id', $call->id)
            ->where('sender_id', '!=', Auth::id())
            ->where('id', '>', $since)
            ->orderBy('id')
            ->get(['id', 'candidate']);

        return response()->json(['candidates' => $signals]);
    }
}
