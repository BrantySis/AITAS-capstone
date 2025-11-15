<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', [
        'fastapiUrl' => config('services.fastapi.url'),
        'laravelApiUrl' => config('services.laravel.url'),
    ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'face_registered' => ['required', 'boolean'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'face_registered' => $request->face_registered,
        ]);

        // Generate a simple verification token
        $token = bin2hex(random_bytes(32));
        $user->email_verification_token = $token;
        $user->save();

        // Send email via Brevo
        $verificationLink = url("/verify-email?token={$token}");
        $this->sendBrevoEmail(
            $user->email,
            $user->name,
            "Verify Your Email",
            "<p>Hi {$user->name},</p>
            <p>Thank you for registering! Please click the link below to verify your email:</p>
            <p><a href='{$verificationLink}'>Verify Email</a></p>"
        );

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('teacher.dashboard')->with('status', 'Registration successful! Check your email to verify.');
    }

    private function sendBrevoEmail($toEmail, $toName, $subject, $htmlContent)
    {
        $apiKey = env('BREVO_API_KEY');

        $response = Http::withHeaders([
            'api-key' => $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => 'UC Teachers Portal',
                'email' => 'admin@aitasportal.com',
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName]
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ]);

        return $response->successful();
    }

    public function verifyEmail(Request $request)
    {
        $token = $request->query('token'); // Get token from query string

        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return redirect()->route('login')->with('status', 'Invalid or expired verification link.');
        }

        $user->email_verified_at = now();
        $user->email_verification_token = null;
        $user->save();

        return redirect()->route('login')->with('status', 'Email verified successfully! You can now log in.');
    }
}
