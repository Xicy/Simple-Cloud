<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\Admin\StoreVideosRequest;
use App\Http\Requests\Admin\UpdateVideosRequest;
use App\Models\User;
use App\Models\Video;
use Spatie\MediaLibrary\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class FilesController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of Video.
     *
     * @return Response
     */
    public function index()
    {
        if (!Gate::allows('video_access')) {
            return abort(401);
        }

        if (request('show_deleted') == 1) {
            if (!Gate::allows('video_delete')) {
                return abort(401);
            }
            $videos = Video::where('user_id', auth()->user()->id)->onlyTrashed()->get();
        } else {
            $videos = Video::where('user_id', auth()->user()->id)->get();
        }

        return view('admin.videos.index', compact('videos'));
    }

    /**
     * Show the form for creating new Video.
     *
     * @return Response
     */
    public function create()
    {
        if (!Gate::allows('video_create')) {
            return abort(401);
        }
        return view('admin.videos.create');
    }

    /**
     * Store a newly created Video in storage.
     *
     * @param StoreVideosRequest $request
     * @return Response
     */
    public function store(StoreVideosRequest $request)
    {
        if (!Gate::allows('video_create')) {
            return abort(401);
        }
        $request = $this->saveFiles($request);
        $video = Video::create($request->all() + ['user_id' => auth()->user()->id]);
        foreach ($request->input('file_id', []) as $index => $id) {
            $model = config('medialibrary.media_model');
            $file = $model::find($id);
            $file->model_id = $video->id;
            $file->save();
        }

        return redirect()->route('admin.files.index');
    }

    /**
     * Show the form for editing Video.
     *
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        if (!Gate::allows('video_edit')) {
            return abort(401);
        }
        $video = Video::findOrFail($id);

        return view('admin.videos.edit', compact('video'));
    }

    /**
     * Update Video in storage.
     *
     * @param UpdateVideosRequest $request
     * @param int $id
     * @return Response
     */
    public function update(UpdateVideosRequest $request, $id)
    {
        if (!Gate::allows('video_edit')) {
            return abort(401);
        }
        $request = $this->saveFiles($request);
        $video = Video::findOrFail($id);
        $video->update($request->all());
        if ($request->video === true) {
            $media = [];
            foreach ($request->input('file_id[]') as $index => $id) {
                $model = config('laravel-medialibrary.media_model');
                $file = $model::find($id);
                $file->model_id = $video->id;
                $file->save();
                $media[] = $file->toArray();
            }
            $video->updateMedia($media, 'file');
        }
        return redirect()->route('admin.files.index');
    }

    /**
     * Display Video.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        if (!Gate::allows('video_view')) {
            return abort(401);
        }
        $video = Video::findOrFail($id);
        return view('admin.videos.show', compact('video'));
    }

    /**
     * Remove Video from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('video_delete')) {
            return abort(401);
        }
        $video = Video::findOrFail($id);
        $video->deletePreservingMedia();
        if (Storage::disk('public')->exists($video->file_name)) {
            $video->delete();
        }

        return redirect()->route('admin.files.index');
    }

    /**
     * Delete all selected Video at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (!Gate::allows('video_delete')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = Video::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->deletePreservingMedia();
            }
        }
    }

    /**
     * Restore Video from storage.
     *
     * @param int $id
     * @return Response
     */
    public function restore($id)
    {
        if (!Gate::allows('video_delete')) {
            return abort(401);
        }
        $video = Video::onlyTrashed()->findOrFail($id);
        $video->restore();

        return redirect()->route('admin.files.index');
    }

    /**
     * Permanently delete Video from storage.
     *
     * @param int $id
     * @return Response
     */
    public function perma_del($id)
    {
        if (!Gate::allows('video_delete')) {
            return abort(401);
        }
        $video = Video::onlyTrashed()->findOrFail($id);
        $video->forceDelete();

        return redirect()->route('admin.files.index');
    }

    public function download($filename)
    {
        /** @var User */
        $user = Auth::user();

        $mediaId = explode('/', $filename);
        $model = \Spatie\MediaLibrary\Models\Media::findOrFail($mediaId[0]);
        if (!$model->model)
            abort(404);

        if ($user->id != $model->model->user_id) {
            $AmountPerMbFileSize = $model->size / (1024 * 1024);
            if ($user->balance < $AmountPerMbFileSize)
                abort(402, "Payment Required");

            $vid = new $model->model_type(array_except($model->model->toArray(), ["id", "user_id"]) + ["user_id" => $user->id]);
            $vid->save();
            $med = \Spatie\MediaLibrary\Models\Media::create(array_except($model->toArray(), ["id", "model", "model_id"]) + ["model_id" => $vid->id]);
            mkdir(storage_path("app/public/" . $med->id), 0777, true);
            symlink(storage_path("app/public/" . $model->getPath()), storage_path("app/public/" . $med->getPath()));
            $user->wallets->first()->transactions()->create(["status" => "completed", "amount" => $AmountPerMbFileSize, "type" => "withdraw", "data" => ["address" => "", "exchange" => true]]);
            $model->model->user->wallets->first()->transactions()->create(["status" => "completed", "amount" => $AmountPerMbFileSize / 10, "type" => "deposit", "data" => ["address" => "", "exchange" => true]]);

            return redirect($med->getUrl());
        }

        return response(null)
            ->header('Content-Disposition', 'attachment; filename="' . $model->file_name . '"')
            ->header('X-Accel-Redirect', "/storage/app/public/$filename")
            ->header('X-Sendfile', base_path("/storage/app/public/$filename"));
    }
}
