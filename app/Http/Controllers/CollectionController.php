<?php

namespace App\Http\Controllers;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Http\Resources\ChatResource;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\DocumentResource;
use App\Jobs\ProcessFileJob;
use App\Models\Collection;
use App\Models\Document;
use Illuminate\Support\Facades\Log;

class CollectionController extends Controller
{
    public function index()
    {

        return inertia('Collection/Index', [
            'collections' => CollectionResource::collection(Collection::query()
                ->withCount('documents')
                ->where('team_id', auth()->user()->current_team_id)
                ->get()),

        ]);
    }

    public function store()
    {

        $validated = request()->validate([
            'name' => 'required',
            'description' => 'required',
            'driver' => 'required',
            'embedding_driver' => 'required',
        ]);

        $validated['team_id'] = auth()->user()->current_team_id;

        $collection = Collection::create($validated);
        /**
         * Make and then reditect to the view page
         */
        request()->session()->flash('flash.banner', 'Collection created successfully!');

        return to_route('collections.show', $collection);
    }

    public function update(Collection $collection)
    {

        $validated = request()->validate([
            'name' => 'required',
            'description' => 'required',
            'driver' => 'required',
            'embedding_driver' => 'required',
        ]);

        $collection->update($validated);
        /**
         * Make and then reditect to the view page
         */
        request()->session()->flash('flash.banner', 'Collection updated successfully!');

        return to_route('collections.show', $collection);
    }

    public function show(Collection $collection)
    {
        $chatResource = $collection->chats()->where('user_id', auth()->user()->id)
            ->latest('id')
            ->first();

        if ($chatResource?->id) {
            $chatResource = new ChatResource($chatResource);
        }

        return inertia('Collection/Show', [
            'chat' => $chatResource,
            'collection' => new CollectionResource($collection),
            'documents' => DocumentResource::collection(Document::query()
                ->where('collection_id', $collection->id)
                ->latest('id')
                ->get()),
        ]);
    }

    public function filesUpload(Collection $collection)
    {
        $validated = request()->validate([
            'files' => 'required',
        ]);

        foreach ($validated['files'] as $file) {
            $mimetype = $file->getMimeType();

            //if pptx
            Log::info($mimetype);

            if ($mimetype === 'application/vnd.openxmlformats-officedocument.presentationml.presentation') {
                $document = Document::create([
                    'collection_id' => $collection->id,
                    'file_path' => $file->getClientOriginalName(),
                    'type' => TypesEnum::Pptx,
                ]);
            } else {
                $document = Document::create([
                    'collection_id' => $collection->id,
                    'file_path' => $file->getClientOriginalName(),
                    'type' => TypesEnum::PDF,
                ]);
            }

            $file->storeAs(
                path: $collection->id,
                name: $file->getClientOriginalName(),
                options: ['disk' => 'collections']
            );

            ProcessFileJob::dispatch($document);
        }

        request()->session()->flash('flash.banner', 'Files uploaded successfully!');

        return back();
    }

    public function resetCollectionDocument(Collection $collection, Document $document)
    {
        $document->document_chunks()->delete();
        $document->status = StatusEnum::Running;
        $document->document_chunk_count = 0;
        $document->update();

        ProcessFileJob::dispatch($document);

        request()->session()->flash('flash.banner', 'Document reset process running!');

        return back();
    }
}
