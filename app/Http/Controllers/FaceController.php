<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FaceEmbedding; 
use App\Models\User;

class FaceController extends Controller
{
    /**
     * Store embeddings from FastAPI
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'embeddings' => 'required|array',
        ]);

        // Save embeddings into DB (column 'embedding')
        $face = FaceEmbedding::updateOrCreate(
            ['user_id' => $request->user_id],
            ['embedding' => $request->embeddings] // left side = DB column, right side = FastAPI array
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Embeddings saved',
            'id' => $face->id
        ]);
    }

    /**
     * Recognize embedding
     */
    public function recognize(Request $request)
    {
        $request->validate([
            'embedding' => 'required|array',
        ]);

        $inputEmbedding = $request->embedding;

        // Fetch all embeddings from DB
        $allEmbeddings = FaceEmbedding::all();

        $matchedUser = null;
        $threshold = 0.5; // distance threshold for match (tune as needed)

        foreach ($allEmbeddings as $face) {
            $dbEmbedding = $face->embedding;

            // Compute cosine similarity or Euclidean distance
            $distance = $this->cosineSimilarity($inputEmbedding, $dbEmbedding);

            if ($distance < $threshold) {
                $matchedUser = $face->user_id;
                break;
            }
        }

        return response()->json([
            'status' => 'success',
            'match' => $matchedUser // null if no match
        ]);
    }

    /**
     * Simple Euclidean distance between two vectors
     */
    private function cosineSimilarity(array $a, array $b): float
{
    $dot = 0;
    $normA = 0;
    $normB = 0;
    foreach ($a as $i => $val) {
        $dot += $val * $b[$i];
        $normA += $val * $val;
        $normB += $b[$i] * $b[$i];
    }
    return $dot / (sqrt($normA) * sqrt($normB));
}

}
