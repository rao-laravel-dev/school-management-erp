<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with('category')->latest()->get();
        $bookCategories = BookCategory::where('status', 1)->orderBy('name')->get();

        return view('admin.books.index', compact('books', 'bookCategories'));
    }

    public function show($id)
    {
        $book = Book::with('category')->findOrFail($id);

        // Image ka URL generate karna taake frontend par preview theek se dikhe
        $book->cover_url = $book->cover_image ? asset('storage/' . $book->cover_image) : null;

        return response()->json(['book' => $book]);
    }

    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'book_category_id' => 'required|exists:book_categories,id',
        'title'            => 'required|string|max:255',
        'author'           => 'nullable|string|max:255',
        'isbn'             => 'nullable|string|max:50|unique:books,isbn',
        'publisher'        => 'nullable|string|max:255',
        'edition'          => 'nullable|string|max:100',
        'total_copies'     => 'required|integer|min:1',
        'description'      => 'nullable|string',
        'cover_image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $data = $validator->validated();
    $data['available_copies'] = $data['total_copies'];
    $data['status'] = 1;

    if ($request->hasFile('cover_image')) {

        $image = $request->file('cover_image');

        $destinationPath = public_path('uploads/book_covers');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $fileName = time() . '_' . uniqid() . '.webp';
        $filePath = $destinationPath . '/' . $fileName;

        $img = imagecreatefromstring(file_get_contents($image->getRealPath()));

        if (!$img) {
            return response()->json([
                'message' => 'Invalid image file.'
            ], 422);
        }

        imagewebp($img, $filePath, 80);
        imagedestroy($img);

        // DB me sirf filename save hoga
        $data['cover_image'] = $fileName;
    }

    Book::create($data);

    return response()->json([
        'message' => 'Book added successfully.'
    ]);
}


    public function update(Request $request, $id)
{
    $book = Book::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'book_category_id' => 'required|exists:book_categories,id',
        'title'            => 'required|string|max:255',
        'author'           => 'nullable|string|max:255',
        'isbn'             => 'nullable|string|max:50|unique:books,isbn,' . $book->id,
        'publisher'        => 'nullable|string|max:255',
        'edition'          => 'nullable|string|max:100',
        'total_copies'     => 'required|integer|min:1',
        'description'      => 'nullable|string',
        'cover_image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $data = $validator->validated();

    // Keep available_copies in sync
    $issuedCount = $book->total_copies - $book->available_copies;
    $data['available_copies'] = max(0, $data['total_copies'] - $issuedCount);

    if ($request->hasFile('cover_image')) {

        // Delete old image
        if ($book->cover_image && file_exists(public_path('uploads/book_covers/' . $book->cover_image))) {
            unlink(public_path('uploads/book_covers/' . $book->cover_image));
        }

        $image = $request->file('cover_image');

        $destinationPath = public_path('uploads/book_covers');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $fileName = time() . '_' . uniqid() . '.webp';
        $filePath = $destinationPath . '/' . $fileName;

        $img = imagecreatefromstring(file_get_contents($image->getRealPath()));

        if (!$img) {
            return response()->json([
                'message' => 'Invalid image file.'
            ], 422);
        }

        imagewebp($img, $filePath, 80);
        imagedestroy($img);

        // DB me sirf filename save hoga
        $data['cover_image'] = $fileName;
    }

    $book->update($data);

    return response()->json([
        'message' => 'Book updated successfully.'
    ]);
}


    /**
     * Helper function to convert any image (jpg, png, etc.) to WebP format
     */
    private function convertToWebp($source, $destination, $extension)
    {
        $extension = strtolower($extension);

        if ($extension == 'jpeg' || $extension == 'jpg') {
            $img = imagecreatefromjpeg($source);
        } elseif ($extension == 'png') {
            $img = imagecreatefrompng($source);
            imagepalettetotruecolor($img);
            imagealphablending($img, true);
            imagesavealpha($img, true);
        } elseif ($extension == 'webp') {
            $img = imagecreatefromwebp($source);
        } else {
            $img = imagecreatefromstring(file_get_contents($source));
        }

        // WebP format mein save karna (80 quality rakhi hai taake size chota rahay)
        imagewebp($img, $destination, 80);
        imagedestroy($img);
    }

    public function destroy($id)
    {
        $book = Book::findOrFail($id);

        if ($book->available_copies < $book->total_copies) {
            return response()->json(['message' => 'Cannot delete — some copies of this book are currently issued.'], 422);
        }

        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }

        $book->delete();

        return response()->json(['message' => 'Book deleted successfully.']);
    }

    public function toggleStatus($id)
    {
        $book = Book::findOrFail($id);
        $book->update(['status' => !$book->status]);

        return response()->json([
            'message' => 'Status updated successfully.',
            'status'  => $book->status,
        ]);
    }
}
