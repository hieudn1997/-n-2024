<?php

namespace App\Http\Controllers;

use App\Events\SendPersonalNotification;
use App\Http\Requests\SendNotificationRequest;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     * @QAparam page nullable [0-9]+
     * @QAparam per_page nullable [0-9]+
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = 15;

        if ($request->has('per_page') && is_numeric($request->input('per_page'))) {
            $perPage = $request->input('per_page');
        }

        return Notification::where('user_id', '=', Auth::id())->orderBy('created_at', 'DESC')->paginate($perPage);
    }

    public function create($userId, SendNotificationRequest $request)
    {
        try {
            Notification::create([
                'user_id' => $userId,
                'message' => $request->message,
                'related_url' => $request->related_url,
                'status' => Notification::STATUS_UNREAD
            ]);

            broadcast(new SendPersonalNotification($userId, $request->message, $request->related_url));

            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * @QAparam page nullable [0-9]+
     * @QAparam per_page nullable [0-9]+
     * @QAparam order_by string nullable 'id', 'created_at', 'updated_at', 'message', 'related_url', 'status'
     * @QAparam sort_by string nullable 'desc'|'asc'
     * @QAparam message string nullable
     * @QAparam related_url string nullable
     */
    public function get($userId, Request $request)
    {   
        logger($userId);
        $perPage = 15;
        $orderBy = 'created_at'; // $fields
        $sortBy = 'desc'; // $orders
        $orders = ['desc', 'asc'];
        $fields = ['id', 'created_at', 'updated_at', 'message', 'related_url', 'status'];

        if ($request->has('per_page') && is_numeric($request->input('per_page'))) {
            $perPage = $request->input('per_page');
        }

        if ($request->has('order_by') && in_array($request->input('order_by'), $fields)) {
            $orderBy = $request->input('order_by');
        }

        if ($request->has('sort_by') && in_array($request->input('sort_by'), $orders)) {
            $sortBy = $request->input('sort_by');
        }

        $query = Notification::where('user_id', '=', $userId)->orderBy($orderBy, $sortBy);

        if ($request->has('message') && $request->input('message')) {
            $query = $query->whereRaw("message ILIKE '%" . $request->input('message') . "%' ");
        }

        if ($request->has('related_url') && $request->input('related_url')) {
            $query = $query->whereRaw("related_url ILIKE '%" . $request->input('related_url') . "%' ");
        }

        return $query->paginate($perPage);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function maskAllAsRead()
    {
        $updatedRecords = Notification::where([
            'user_id' => Auth::id(),
            'status' => Notification::STATUS_UNREAD
        ])
            ->update([
                'status' => Notification::STATUS_READ
            ]);

        return ['updated' => $updatedRecords];
    }

    public function maskAsRead($id)
    {
        $updatedRecords = Notification::where([
            'user_id' => Auth::id(),
            'status' => Notification::STATUS_UNREAD,
            'id' => $id
        ])
            ->update([
                'status' => Notification::STATUS_READ
            ]);

        return ['updated' => $updatedRecords];
    }

    /**
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            $notification = Notification::where([
                'id' => $id,
                'user_id' => Auth::id()
            ])->firstOrFail();

            $notification->delete();

            return 'success';
        } catch (\Throwable $th) {
            return 'failure';
        }
    }

    public function deleteAll()
    {
        try {
            $deletedRecords = Notification::where([
                'user_id' => Auth::id(),
            ])->delete();

            return ['deleted' => $deletedRecords];
        } catch (\Throwable $th) {
            return 'failure';
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
