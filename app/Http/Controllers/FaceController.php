<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FaceEmbedding;
use App\Models\User;

class FaceController extends Controller
{
    /**
     * Store embeddings from FastAPI
     * * NOTE: This method is now DEPRECATED because the embedding saving 
     * logic has moved to RegisterTeacherController@store for atomic 
     * saving (user + embedding) during final registration.
     * * You should comment it out or delete the method entirely. 
     */
    /*
    public function store(Request $request)
    {
        // ... (This logic is moved) ...
    }
    */

    /**
     * Recognize embedding
     * Route: POST /api/recognize-embedding
     */
    public function recognize(Request $request)
    {
        $request->validate([
            // Note: The FastAPI app sends the embedding as a list of floats, 
            // but Laravel receives it as an array from the JSON body.
            'embedding' => 'required|array', 
        ]);

        $inputEmbedding = $request->embedding;

        // Fetch all embeddings from DB
        $allEmbeddings = FaceEmbedding::all();

        $matchedUser = null;
        $bestScore = -1; // higher is better
        $threshold = 0.55; // tune between 0.4 - 0.6

        foreach ($allEmbeddings as $face) {
            // IMPORTANT: Ensure the embedding column is cast as JSON 
            // in the FaceEmbedding model to retrieve it as an array here.
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
                'status' => 'fail', // Changed from 'error' to 'fail' for clearer logic
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

        // Check if vectors have the same length (a basic check for robustness)
        if (count($a) !== count($b)) {
             // Handle error if vectors are not the same dimension
             return 0.0;
        }

        foreach ($a as $i => $val) {
            $dot += $val * $b[$i];
            $normA += $val * $val;
            $normB += $b[$i] * $b[$i];
        }

        $denominator = sqrt($normA) * sqrt($normB);

        // Prevent division by zero if a vector is zero
        if ($denominator == 0) {
             return 0.0;
        }

        return $dot / $denominator;
    }
}