<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BorrowedBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class BookController extends Controller
{

    function __construct()

    {
        $this->middleware('permission:manage-books', ['only' => ['store', 'update', 'destroy', 'getBooks']]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'category' => 'required|string|max:50',
            'author' => 'required|string|max:50',
            'publisher' => 'required|string|max:50',
            'isbn' => 'required|string|max:20|unique:books,isbn'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()], 422);
        }

        $book = Book::create($request->all());

        return response()->json(['status' => 'success', 'message' => 'Book stored successfully'], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $book = Book::find($id);

            if (!$book) {
                return response()->json(['status' => 'error', 'message' => 'Book not found'], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:100',
                'category' => 'sometimes|required|string|max:50',
                'author' => 'sometimes|required|string|max:50',
                'publisher' => 'sometimes|required|string|max:50',
                'isbn' => 'sometimes|required|string|max:20|unique:books,isbn,' . $id . ',id'
            ]);


            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()], 422);
            }

            $book->update($request->all());

            return response()->json(['status' => 'success', 'message' => 'Book updated successfully', 'book' => $book], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $book = Book::find($id);

            if (!$book) {
                return response()->json(['status' => 'error', 'message' => 'Book not found'], 404);
            }
            BorrowedBook::where('book_id', $book->id)->delete();
            $book->delete();
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Book deleted successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getBooks(Request $request)
    {
        try {
            $books = Book::all();

            $booksWithStatus = $books->map(function ($book) {
                $borrowedBook = BorrowedBook::where('book_id', $book->id)
                    ->whereNull('returned_at')
                    ->first();
                $book->status = $book->status;
                if ($borrowedBook) {
                    $book->borrowed_by = $borrowedBook->user->name;
                } else {
                    $book->borrowed_by = null;
                }

                return $book;
            });

            return response()->json([
                'status' => 'success',
                'data' => $booksWithStatus
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
