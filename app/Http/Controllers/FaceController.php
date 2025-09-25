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
            ['embedding' => $request->embeddings] // Store as JSON
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
        $bestScore = -1; // higher is better
        $threshold = 0.5; // tune between 0.4 - 0.6

        foreach ($allEmbeddings as $face) {
            $dbEmbedding = $face->embedding;

            // Compute cosine similarity
            $similarity = $this->cosineSimilarity($inputEmbedding, $dbEmbedding);

            if ($similarity > $bestScore) {
                $bestScore = $similarity;
                $matchedUser = $face->user_id;
            }
        }

        if ($bestScore >= $threshold) {
            return response()->json([
                'status' => 'success',
                'match' => $matchedUser,
                'similarity' => $bestScore
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'No matching face found',
                'similarity' => $bestScore
            ], 401);
        }
    }

    /**
     * Cosine Similarity between two vectors
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
