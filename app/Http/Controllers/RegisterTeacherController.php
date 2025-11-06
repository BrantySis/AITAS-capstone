<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FaceEmbedding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;

class RegisterTeacherController extends Controller
{
    /**
     * Show registration view (with face scan integration)
     */
    public function create()
    {
        return view('register', [
            'fastapiUrl'   => env('FASTAPI_URL', 'https://aitas-capstone.test:8001'),
            'laravelApiUrl'=> env('LARAVEL_URL', 'https://aitas-capstone.test'),
        ]);
    }

    /**
     * 1️⃣ Create a temporary user before face scan
     */
    public function storeTemp(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'face_registered' => 0,
        ]);

        return response()->json([
            'user_id' => $user->id,
            'message' => 'Temporary user created. Proceed with face scan.'
        ]);
    }

    /**
     * 2️⃣ Cleanup temporary user if face scan fails
     */
    public function cleanupTemp(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'Temporary user deleted successfully.'
        ]);
    }

    /**
     * 3️⃣ Final registration: save embeddings & trigger email verification
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id_hidden' => 'required|integer|exists:users,id',
            'face_embeddings_hidden' => 'required|string',
            'face_registered' => 'required|in:0,1',
        ]);

        $user = User::findOrFail($validated['user_id_hidden']);

        DB::beginTransaction();

        try {
            // ✅ Mark user as face registered
            $user->update(['face_registered' => 1]);

            // ✅ Decode and validate embeddings
            $embeddingsArray = json_decode($validated['face_embeddings_hidden'], true);
            if (!is_array($embeddingsArray)) {
                throw new \Exception("Invalid face embeddings format.");
            }

            // ✅ Save face embeddings
            FaceEmbedding::updateOrCreate(
                ['user_id' => $user->id],
                ['embedding' => json_encode($embeddingsArray)]
            );

            // ✅ Trigger email verification event
            event(new Registered($user));

            // ✅ Auto-login the user temporarily
            Auth::login($user);

            DB::commit();

            return response()->json([
                'success' => true,
                'redirect' => route('verification.notice'),
                'message' => 'Registration complete! A verification email has been sent to your Gmail. Please verify to continue.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
