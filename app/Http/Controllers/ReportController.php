<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Enums\ReadingPlanStatus;

class ReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. 基本統計の集計
        $totalReviews = $user->reviews()->count();
        $booksRead = $user->reviews()->distinct('book_id')->count('book_id');
        $averageRating = $user->reviews()->avg('rating') ?? 0;

        // 2. 評価分布の集計
        $rawDistribution = $user->reviews()
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating');

        $ratingDistribution = collect([
            4 => $rawDistribution->get(5, 0), // ★5
            3 => $rawDistribution->get(4, 0), // ★4
            2 => $rawDistribution->get(3, 0), // ★3
            1 => $rawDistribution->get(2, 0), // ★2
            0 => $rawDistribution->get(1, 0), // ★1
        ]);

        // 3. 高評価書籍 TOP5
        $topRatedBooks = $user->reviews()
            ->with('book')
            ->where('rating', '>=', 4)
            ->orderBy('rating', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($review) {
                if (!$review->book) {
                    return null;
                }
                
                $book = $review->book;
                $book->rating = $review->rating; 
                
                return $book;
            })->filter()->values();

        // 4. ジャンル別評価傾向 TOP5
        $genreRatings = DB::table('reviews')
            ->join('books', 'reviews.book_id', '=', 'books.id')
            ->join('book_genre', 'books.id', '=', 'book_genre.book_id')
            ->join('genres', 'book_genre.genre_id', '=', 'genres.id')
            ->where('reviews.user_id', $user->id)
            ->select(
                'genres.id',
                'genres.name',
                DB::raw('count(reviews.id) as count'),
                DB::raw('avg(reviews.rating) as average_rating')
            )
            ->groupBy('genres.id', 'genres.name')
            ->orderBy('average_rating', 'desc')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'count' => $item->count,
                    'average_rating' => $item->average_rating,
                ];
            });

        // 5.  「読書計画」のカウント集計を統合
        $planCounts = \App\Models\ReadingPlan::where('user_id', $user->id)
            ->selectRaw("status, count(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status');

        $overdueOrUrgentCount = \App\Models\ReadingPlan::where('user_id', $user->id)
            ->whereIn('status', [ReadingPlanStatus::PLANNED->value, ReadingPlanStatus::READING->value])
            ->where('target_date', '<=', now()->addDays(3))
            ->count();

        $stats = [
            'summary' => [
                'total_reviews' => $totalReviews,
                'books_read' => $booksRead,
                'average_rating' => $averageRating,
            ],
            'rating_distribution' => $ratingDistribution,
            'top_rated_books' => $topRatedBooks,
            'genre_ratings' => $genreRatings,
            'plans' => [
                'planned' => $planCounts[ReadingPlanStatus::PLANNED->value] ?? 0,
                'reading' => $planCounts[ReadingPlanStatus::READING->value] ?? 0,
                'urgent'  => $overdueOrUrgentCount,
            ],
        ];

        return view('reports.index', compact('stats'));
    }

    /**
     * ★ 通知一覧の取得 (GET /notifications) 
     * 【出し分け対応】PCのfetch通信ならJSON、スマホの通常画面遷移ならHTMLビューを返す
     */
    public function notifications(Request $request)
    {
        // 1. PCベルドロップダウン（fetchによる非同期要求）の場合、JSONデータを返却
        if ($request->expectsJson() || $request->ajax()) {
            $notifications = $request->user()->notifications()->take(10)->get();

            $formattedNotifications = $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at_human' => $notification->created_at->diffForHumans(),
                ];
            });

            return response()->json($formattedNotifications);
        }

        // 2. スマホ・通常アクセスの場合、ご提示いただいた既存の Blade 画面を表示
        $notifications = $request->user()->notifications;
        return view('notifications.index', compact('notifications'));
    }

    /**
     * ★ 特定の通知を既読化 (POST /notifications/{id}/read)
     * 【出し分け対応】認証＋所有者チェック付き
     */
    public function readNotification(Request $request, $id)
    {
        // ログインユーザー(認証)が所有するすべての通知からIDを検索【所有者チェック】
        $notification = $request->user()->notifications()->findOrFail($id);

        if ($notification->unread()) {
            $notification->markAsRead();
        }

        // PCドロップダウン（fetch通信）の場合はJSONを返す
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        // 既存のindex.blade.php（スマホ等の通常フォーム送信）の場合はセッションメッセージ付きでリダイレクト
        return back()->with('success', '通知を既読にしました。');
    }
}
