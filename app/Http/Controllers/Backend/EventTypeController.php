<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventTypeController extends Controller
{
    public function index()
{
    $eventTypes = EventType::latest()->get();

    return view('admin.event_type.index', compact('eventTypes'));
}



public function store(Request $request)
{
    $validator = Validator::make($request->all(), [

        'name' => [
            'required',
            'string',
            'max:255',
            'unique:event_types,name'
        ],

        'color' => [
            'required',
            'string',
            'max:20'
        ],

        'is_off_day' => [
            'nullable',
            'boolean'
        ],

        'status' => [
            'required',
            'boolean'
        ],

    ]);


    if ($validator->fails()) {

        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ], 422);

    }



    $data = $validator->validated();


    $data['status'] = $request->boolean('status');
    $data['is_off_day'] = $request->boolean('is_off_day'); // unchecked checkbox request mein nahi aata


    EventType::create($data);



    return response()->json([

        'status' => true,

        'message' => 'Event type add ho gaya.'

    ]);

}




public function update(Request $request, EventType $eventType)
{

    $validator = Validator::make($request->all(), [

        'name' => [
            'required',
            'string',
            'max:255',
            'unique:event_types,name,' . $eventType->id
        ],

        'color' => [
            'required',
            'string',
            'max:20'
        ],

        'is_off_day' => [
            'nullable',
            'boolean'
        ],

        'status' => [
            'required',
            'boolean'
        ],

    ]);



    if ($validator->fails()) {

        return response()->json([

            'status' => false,

            'errors' => $validator->errors()

        ],422);

    }



    $data = $validator->validated();


    $data['status'] = $request->boolean('status');
    $data['is_off_day'] = $request->boolean('is_off_day'); // unchecked checkbox request mein nahi aata



    $eventType->update($data);



    return response()->json([

        'status' => true,

        'message' => 'Event type update ho gaya.'

    ]);

}




public function destroy(EventType $eventType)
{
    if ($eventType->academicCalendars()->exists()) {
        return response()->json([
            'status' => false,
            'message' => 'This event type is currently in use.'
        ], 400);
    }

    $eventType->delete();

    // Sirf ye line rahegi
    return response()->json([
        'status' => true,
        'message' => 'Event type deleted successfully.'
    ], 200);
}
}