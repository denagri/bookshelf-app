<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BookRequest;
use App\Http\Resources\Api\V1\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if ($request->filled('q')) {
            $keyword = $request->input('q');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('genre_id')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genres.id', $request->input('genre_id'));
            });
        }

        $perPage = $request->integer('per_page', 20);
        $books = $query->paginate($perPage);

        return BookResource::collection($books);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BookRequest $request): JsonResponse
    {
        $data = array_merge($request->validated(), [
            'user_id' => auth()->id(),
        ]);

        $book = Book::create($data);

        return response()->json([
            'id'           => $book->id,
            'title'        => $book->title,
            'author'       => $book->author,
            'description'  => $book->description,
            'created_at'   => $book->created_at?->toIso8601String(),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book): BookResource
    {
        $book->load(['genres', 'reviews']);
        $book->loadAvg('reviews', 'rating');
        $book->loadCount('reviews');

        return new BookResource($book);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BookRequest $request, Book $book): JsonResponse
    {
        $book->update($request->validated());

        return response()->json([
            'id'           => $book->id,
            'title'        => $book->title,
            'author'       => $book->author,
            'description'  => $book->description,
            'updated_at'   => $book->updated_at?->toIso8601String(),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book): JsonResponse
    {
        $book->delete();
        return response()->json(null, 204);
    }
}
