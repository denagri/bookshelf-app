<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BookRequest;
use App\Http\Resources\Api\V1\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
        public function index(Request $request): AnonymousResourceCollection
    {
        $query = Book::with('genres')->withAvg('reviews', 'rating')->withCount('reviews');

        if ($request->filled('q')) {
            $keyword = $request->input('q');
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")->orWhere('author', 'like', "%{$keyword}%");
            });
        }
        
        if ($request->filled('genre_id')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('genres.id', $request->input('genre_id'));
            });
        }

        switch ($request->input('sort')) {
            case 'published_date_desc':
                $query->orderBy('published_date', 'desc');
                break;
            case 'rating_desc':
                $query->orderByRaw('reviews_avg_rating IS NULL ASC')
                      ->orderBy('reviews_avg_rating', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $books = $query->paginate($request->integer('per_page', 20));
        return BookResource::collection($books);
    }


    public function store(BookRequest $request): JsonResponse
    {
        if (is_null(auth()->id())) return response()->json(['message' => 'Unauthenticated.'], 401);
        $book = Book::create(array_merge($request->validated(), ['user_id' => auth()->id()]));
        return response()->json(['id' => $book->id, 'title' => $book->title, 'author' => $book->author, 'description' => $book->description, 'created_at' => $book->created_at?->toIso8601String()], 201);
    }

    public function show(Book $book): BookResource
    {
        $book->load(['genres', 'reviews'])->loadAvg('reviews', 'rating')->loadCount('reviews');
        return new BookResource($book);
    }

    public function update(BookRequest $request, Book $book): JsonResponse
    {
        if (is_null(auth()->id())) return response()->json(['message' => 'Unauthenticated.'], 401);
        $this->authorize('update', $book);
        $book->update($request->validated());
        return response()->json(['id' => $book->id, 'title' => $book->title, 'author' => $book->author, 'description' => $book->description, 'updated_at' => $book->updated_at?->toIso8601String()], 200);
    }

    public function destroy(Book $book): JsonResponse
    {
        if (is_null(auth()->id())) return response()->json(['message' => 'Unauthenticated.'], 401);
        $this->authorize('delete', $book);
        $book->delete();
        return response()->json(null, 204);
    }

    public function fetchByIsbn($isbn): JsonResponse
    {
        if (!is_string($isbn) || !preg_match('/^\d{13}$/', $isbn)) {
            return response()->json(['message' => 'ISBNは13桁で入力してください。'], 400);
        }

        $apiKey = config('services.google_books.key') ?: env('GOOGLE_BOOKS_API_KEY');
        
        $url = "https://www.googleapis.com/books/v1/volumes";

        $queryParams = ['q' => "isbn:{$isbn}"];
        if (!empty($apiKey)) {
            $queryParams['key'] = $apiKey;
        }

        try {
            $response = Http::timeout(5)->get($url, $queryParams);
            
            if ($response->status() === 429 || $response->status() === 403) {
                return response()->json(['message' => 'Google Books API のクォータを超過しました。.env に GOOGLE_BOOKS_API_KEY を設定してください。'], 429);
            }

            if ($response->failed()) {
                Log::error('Google Books API Error: ' . $response->body());
                return response()->json(['message' => 'API通信エラーが発生しました。'], 500);
            }

            $data = $response->json();

            if (empty($data['items']) || !is_array($data['items']) || !isset($data['items'][0]['volumeInfo'])) {
                return response()->json(['message' => '書籍が見つかりませんでした。'], 404);
            }
            $volumeInfo = $data['items'][0]['volumeInfo'];

            return response()->json([
                'title'       => $volumeInfo['title'] ?? '',
                'author'      => isset($volumeInfo['authors']) && is_array($volumeInfo['authors'])
                                    ? implode(', ', $volumeInfo['authors']) 
                                    : '',
                'description' => $volumeInfo['description'] ?? '',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Google Books API Connection Error: ' . $e->getMessage());
            return response()->json(['message' => 'API通信エラーが発生しました。'], 500);
        }
    }
}
