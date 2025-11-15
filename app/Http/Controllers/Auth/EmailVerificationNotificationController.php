<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // ✅ If email is already verified, redirect to dashboard
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($this->getDashboardRoute($user));
        }

        // ✅ Generate and send Brevo email verification link
        $token = bin2hex(random_bytes(32));
        $user->email_verification_token = $token;
        $user->save();

        $verificationLink = url("/verify-email?token={$token}");
        $this->sendBrevoEmail(
            $user->email,
            $user->name,
            "Verify Your Email",
            "<p>Hi {$user->name},</p>
            <p>Please click the link below to verify your email:</p>
            <p><a href='{$verificationLink}'>Verify Email</a></p>"
        );

        return back()->with('status', 'Verification link sent! Check your email.');
    }

    /**
     * Determine the dashboard route based on user role.
     */
    private function getDashboardRoute($user): string
    {
        return match($user->role_id) {
            1 => route('admin.dashboard'),   // Admin
            2 => route('teacher.dashboard'), // Teacher
            3 => route('dean.dashboard'),    // Dean
            default => route('login'),
        };
    }

    /**
     * Send email via Brevo API
     */
    private function sendBrevoEmail($toEmail, $toName, $subject, $htmlContent)
    {
        $apiKey = env('BREVO_API_KEY');

        $response = Http::withHeaders([
            'api-key' => $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => 'UCLM Portal',
                'email' => 'no-reply@uclm.edu.ph',
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName]
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ]);

        return $response->successful();
    }
}
