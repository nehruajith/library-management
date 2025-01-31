<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BorrowedBook;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BorrowedBookController extends Controller
{
    function __construct()

    {
        $this->middleware('permission:borrow-books', ['only' => ['borrowedBook']]);
        $this->middleware('permission:return-books', ['only' => ['returnBook']]);
    }
    public function borrowedBook(Request $request, $book_id)
    {
        try {
            DB::beginTransaction();
            $book = Book::findOrFail($book_id);

            if ($book->status === 'borrowed') {
                return response()->json(['status' => 'error', 'message' => 'Book already borrowed'], 422);
            }

            $borrow = BorrowedBook::create([
                'user_id' => auth()->id(),
                'book_id' => $book_id,
                'borrowed_at' => Carbon::now(),
                'due_date' => Carbon::now()->addDays(14),
            ]);

            $book->update(['status' => 'borrowed']);

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Book borrowed successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function returnBook(Request $request, $book_id)
    {
        try {
            DB::beginTransaction();
            $borrow = BorrowedBook::where('book_id', $book_id)->whereNull('returned_at')->first();
            $fine = 0;
            if (Carbon::now()->gt($borrow->due_date)) {
                $fine = Carbon::now()->diffInDays($borrow->due_date) * 5;
            }

            $borrow->update(['returned_at' => Carbon::now(), 'fine' => $fine]);

            Book::find($book_id)->update(['status' => 'available']);

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Book returned', 'fine' => $fine],);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
