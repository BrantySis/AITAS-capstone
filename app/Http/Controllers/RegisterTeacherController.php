<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FaceEmbedding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\RegisteredUserController;

class RegisterTeacherController extends Controller
{
    protected RegisteredUserController $registeredUserController;

    public function __construct()
    {
        // Inject RegisteredUserController to send Brevo emails
        $this->registeredUserController = app(RegisteredUserController::class);
    }

    /**
     * Show registration view (with face scan integration)
     */
    public function create()
    {
        return view('register', [
            'fastapiUrl'    => env('FASTAPI_URL', 'https://aitas-capstone.test:8001'),
            'laravelApiUrl' => env('LARAVEL_URL', 'https://aitas-capstone.test'),
        ]);
    }

    /**
     * STEP 1️⃣: Create temporary user before face scan
     */
    public function storeTemp(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'password'        => Hash::make($validated['password']),
            'face_registered' => 0,
        ]);

        return response()->json([
            'user_id' => $user->id,
            'message' => 'Temporary user created. Proceed with face scan.'
        ]);
    }

    /**
     * STEP 2️⃣: Cleanup temporary user if face scan fails
     */
    public function cleanupTemp(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'Temporary user deleted successfully.'
        ]);
    }

    /**
     * STEP 3️⃣: Final registration - save face embeddings and trigger email verification
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id_hidden'         => 'required|integer|exists:users,id',
            'face_embeddings_hidden' => 'required|string',
            'face_registered'        => 'required|in:0,1',
        ]);

        $user = User::findOrFail($validated['user_id_hidden']);

        DB::beginTransaction();

        try {
            // ✅ Mark face as registered
            $user->update(['face_registered' => 1]);

            // ✅ Decode and validate face embeddings
            $embeddingsArray = json_decode($validated['face_embeddings_hidden'], true);
            if (!is_array($embeddingsArray)) {
                throw new \Exception("Invalid face embeddings format.");
            }

            // ✅ Save or update face embeddings
            FaceEmbedding::updateOrCreate(
                ['user_id' => $user->id],
                ['embedding' => json_encode($embeddingsArray)]
            );

            // ✅ Generate a new verification token
            $user->email_verification_token = bin2hex(random_bytes(32));
            $user->save();

            // ✅ Send Brevo verification email via RegisteredUserController
            $this->registeredUserController->sendVerificationEmail($user);

            // ✅ Auto-login the user temporarily
            Auth::login($user);

            DB::commit();

            
            // ✅ Return JSON response for frontend
            return response()->json([
                'success'           => true,
                'message'           => 'Registration complete! A verification email has been sent. Please verify to continue.',
                'redirect'          => route('email.verify'), // page telling user to check inbox
                'verification_url'  => route('email.verify.token', ['token' => $user->email_verification_token]) // full token link
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'error'   => true,
                'message' => 'Registration failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
