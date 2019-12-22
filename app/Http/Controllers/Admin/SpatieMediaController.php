<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpatieMediaController extends Controller
{

    /**
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        if (!$request->has('model_name') && !$request->has('file_key') && !$request->has('bucket')) {
            return abort(500);
        }

        $user = Auth::user();
        $AmountPerMbFileSize = $request->header("content-length", 0) / (1024 * 1024);
        if ($user->balance < $AmountPerMbFileSize)
            abort(402, "Payment Required");
            
        $user->wallets->first()->transactions()->create(["status" => "completed", "amount" => $AmountPerMbFileSize, "type" => "withdraw", "data" => ["address" => "", "exchange" => true]]);

        $model = 'App\\Models\\' . $request->input('model_name');
        try {
            $model = new $model();
        } catch (ModelNotFoundException $e) {
            abort(500, 'Model not found');
        }
        $files = $request->file($request->input('file_key')); // file_key
        $addedFiles = [];
        foreach ($files as $file) {
            //try {
            $model->exists = true;
            $media = $model->addMedia($file)->toMediaCollection($request->input('bucket'), 'public');
            $addedFiles[] = $media;
            //} catch (Exception $e) {
            //    abort(500, 'Could not upload your file');
            //}
        }

        return response()->json([
            'files' => $addedFiles
        ]);
    }
}
