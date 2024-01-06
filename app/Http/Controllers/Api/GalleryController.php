<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index()
    {
        $galleries = Gallery::all();

        return response()->json(['galleries' => $galleries], 200);
    }
    public function show($id)
    {
        $gallery = Gallery::find($id);

        if (!$gallery) {
            return response()->json(['message' => 'Gallery not found'], 404);
        }

        return response()->json(['gallery' => $gallery], 200);
    }


    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama' => 'required',
            'video' => 'required|mimes:mp4,mov,avi,wmv,mkv', // Ubah sesuai format video yang diizinkan
            'location_id' => 'required|exists:locations,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Upload video
        $videoPath = $request->file('video')->store('videos', 'public');

        // Simpan data galeri
        $gallery = Gallery::create([
            'nama' => $request->input('nama'),
            'video' => $videoPath,
            'location_id' => $request->input('location_id'),
        ]);

        return response()->json(['gallery' => $gallery], 201);
    }

    public function update(Request $request, $id)
{
    // Validasi input
    $validator = Validator::make($request->all(), [
        'nama' => 'required',
        'location_id' => 'required|exists:locations,id',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    $gallery = Gallery::find($id);

    if (!$gallery) {
        return response()->json(['message' => 'Gallery not found'], 404);
    }

    // Update data galeri
    $gallery->nama = $request->input('nama');
    $gallery->location_id = $request->input('location_id');

    // Cek apakah ada permintaan untuk mengubah video
    if ($request->hasFile('video')) {
        // Hapus video lama jika ada
        if ($gallery->video) {
            Storage::disk('public')->delete($gallery->video);
        }

        // Upload video baru
        $videoPath = $request->file('video')->store('videos', 'public');
        $gallery->video = $videoPath;
    }

    $gallery->save();

    return response()->json(['gallery' => $gallery], 200);
}

    public function destroy($id)
    {
        $gallery = Gallery::find($id);

        if (!$gallery) {
            return response()->json(['message' => 'Gallery tidak ada'], 404);
        }

        // Hapus video dari penyimpanan
        Storage::disk('public')->delete($gallery->video);

        // Hapus data galeri dari database
        $gallery->delete();

        return response()->json(['message' => 'Gallery telah dihapus'], 200);
    }
}



