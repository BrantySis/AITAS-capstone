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
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'face_registered' => ['required', 'boolean'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'face_registered' => $request->face_registered,
        ]);

        // Generate verification token
        $user->email_verification_token = bin2hex(random_bytes(32));
        $user->save();

        // Send verification email via Brevo
        $this->sendVerificationEmail($user);

        event(new Registered($user));
        Auth::login($user);

        // Redirect to custom verification page
        return redirect()->route('email.verify')
            ->with('status', 'Registration successful! Please check your email to verify.');
    }

    /**
     * Send verification email via Brevo.
     */
    public function sendVerificationEmail(User $user): bool
    {
        $verificationLink = route('email.verify.token', ['token' => $user->email_verification_token]);

        return $this->sendBrevoEmail(
            $user->email,
            $user->name,
            'Verify Your Email',
            "<p>Hi {$user->name},</p>
            <p>Thank you for registering! Please click the link below to verify your email:</p>
            <p><a href='{$verificationLink}'>Verify Email</a></p>"
        );
    }

    /**
     * Send email via Brevo API.
     */
    private function sendBrevoEmail(string $toEmail, string $toName, string $subject, string $htmlContent): bool
    {
        $apiKey = config('services.brevo.key');

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

        if (!$response->successful()) {
            logger('Brevo Email Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return false;
        }

        return true;
    }

    /**
     * Verify email via token.
     */
    public function verifyEmail(Request $request): RedirectResponse
    {
        $token = $request->query('token');

        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return redirect()->route('login')
                ->with('status', 'Invalid or expired verification link.');
        }

        $user->email_verified_at = now();
        $user->email_verification_token = null;
        $user->save();

        return redirect()->route('login')
            ->with('status', 'Email verified successfully! You can now log in.');
    }

    /**
     * Resend verification email for authenticated user.
     */
    public function resendVerificationEmail(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('email.verify')
                ->with('status', 'Your email is already verified.');
        }

        $user->email_verification_token = bin2hex(random_bytes(32));
        $user->save();

        $emailSent = $this->sendVerificationEmail($user);

        if (!$emailSent) {
            return redirect()->back()->with('status', 'Unable to resend verification email. Contact support.');
        }

        return redirect()->back()->with('status', 'Verification email resent! Check your inbox.');
    }
}
