<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FaceEmbedding;

class FaceController extends Controller
{
    // Store embeddings
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'embeddings' => 'required|array',
        ]);

        FaceEmbedding::updateOrCreate(
            ['user_id' => $request->user_id],
            ['embeddings' => $request->embeddings]
        );

        return response()->json(['message' => 'Embeddings stored successfully']);
    }

    // Compare new embedding to DB
    public function recognize(Request $request)
    {
        $request->validate([
            'embedding' => 'required|array'
        ]);

        $newEmbedding = $request->embedding;
        $threshold = 0.6; // tune based on accuracy

        $users = FaceEmbedding::all();
        $bestMatch = null;
        $bestDistance = INF;

        foreach ($users as $user) {
            $stored = $user->embeddings;
            $distance = $this->euclideanDistance($newEmbedding, $stored);

            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestMatch = $user->user_id;
            }
        }

        if ($bestMatch && $bestDistance < $threshold) {
            return response()->json(['recognized_as' => $bestMatch]);
        }

        return response()->json(['recognized_as' => null]);
    }

    private function euclideanDistance($a, $b)
    {
        $sum = 0;
        for ($i = 0; $i < count($a); $i++) {
            $sum += pow($a[$i] - $b[$i], 2);
        }
        return sqrt($sum);
    }
}
