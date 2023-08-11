<?php

namespace App\Http\Controllers\Meetings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Meeting\MeetingRequest;
use Illuminate\Http\Request;
use App\Models\Meeting;
use Carbon\Carbon;
use Illuminate\Http\Response;

class MeetingsController extends Controller
{
    /**
     * Schedule a meeting validating conflicts
     *
     * @param MeetingRequest $request
     * @return mixed
     */
    public function scheduleMeeting(MeetingRequest $request)
    {
        $validated = $request->validated();

        $meetingCollision = Meeting::find($validated['user_ids'])
                                    ->where('start_time', '>=', Carbon::createFromFormat('Y-m-d H:i:s', $validated['start_time']))
                                    ->where('end_time', '<=', Carbon::createFromFormat('Y-m-d H:i:s', $validated['end_time']))
                                    ->first();
        if ($meetingCollision) {
            return response()->json([
                'result' => "User {$meetingCollision->user_id} has a conflicting meeting: {$meetingCollision->meeting_name}",
            ], Response::HTTP_BAD_REQUEST);
        }

        foreach($validated['user_ids'] as $user_id) {
            Meeting::create([
                'user_id' => $user_id,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'meeting_name' => $validated['meeting_name']
            ]);
        }

        return response()->json([
            'result' => 'The meeting has been successfully booked.',
        ], Response::HTTP_CREATED);
    }
}
