<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ContactController extends Controller
{
    const EMAIL = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/u';

    const PHONE = '/^[+\d\s()-]{7,20}$/';

    /**
     * Check an enquiry from the contact form and save it for the admin panel.
     *
     * The form posts here with fetch and shows the answer in place. Without
     * JavaScript it posts normally and comes back to the form with the same messages.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $contact = config('site.contact');
        $budget = $this->text($request, 'budget', 40);

        $values = [
            'name' => $this->text($request, 'name', 100),
            'email' => $this->text($request, 'email', 200),
            'phone' => $this->text($request, 'phone', 20),
            'company' => $this->text($request, 'company', 120),
            'services' => array_values(array_filter(
                (array) $request->input('services', []),
                fn ($service) => in_array($service, $contact['services'], true),
            )),
            'budget' => in_array($budget, $contact['budgets'], true) ? $budget : '',
            'message' => $this->text($request, 'message', 5000),
        ];

        // Hidden field that people never see; bots fill it in. Pretend it worked.
        if ($this->text($request, 'website', 200) !== '') {
            return $this->sent($request, null);
        }

        $errors = array_filter([
            'name' => mb_strlen($values['name']) < 2 ? 'Enter your name.' : null,
            'email' => ! preg_match(self::EMAIL, $values['email']) ? 'Enter an email address, like name@company.co.uk.' : null,
            'phone' => ! preg_match(self::PHONE, $values['phone']) ? 'Enter a phone number, like 07700 900123.' : null,
            'message' => mb_strlen($values['message']) < 20 ? 'Tell us a bit more about the project (at least 20 characters).' : null,
        ]);

        if ($errors) {
            return $this->failed($request, $values, 'Check the highlighted fields and send again.', $errors, 422);
        }

        try {
            $this->deliver($values);
        } catch (Throwable $e) {
            Log::error('[contact] Delivery failed', ['exception' => $e, 'enquiry' => $values]);

            return $this->failed(
                $request,
                $values,
                'Your enquiry could not be sent. Try again, or email us at '.config('site.brand.email').'.',
                [],
                500,
            );
        }

        return $this->sent($request, 'Thanks, '.Str::before($values['name'], ' ').". We'll reply within one working day.");
    }

    /**
     * Where a valid enquiry goes: saved for the admin panel at /admin, and logged.
     *
     * To also have it emailed, set the MAIL_* values in .env and add, for example:
     *
     *   Mail::raw(json_encode($enquiry, JSON_PRETTY_PRINT), fn ($mail) => $mail
     *       ->to(config('site.brand.email'))
     *       ->replyTo($enquiry['email'])
     *       ->subject("New enquiry from {$enquiry['name']}"));
     *
     * Throwing here makes the form show its "could not be sent" message.
     *
     * @param  array<string, mixed>  $enquiry
     */
    protected function deliver(array $enquiry): void
    {
        Enquiry::create($enquiry);

        Log::info('[contact] New enquiry', $enquiry);
    }

    protected function sent(Request $request, ?string $message): JsonResponse|RedirectResponse
    {
        $message ??= "Thanks. We'll be in touch soon.";

        if ($request->expectsJson()) {
            return response()->json(['status' => 'success', 'message' => $message]);
        }

        return redirect()->to(url('/').'#contact')->with('enquiry_sent', $message);
    }

    /**
     * @param  array<string, mixed>  $values
     * @param  array<string, string>  $errors
     */
    protected function failed(Request $request, array $values, string $message, array $errors, int $status): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['status' => 'error', 'message' => $message, 'errors' => (object) $errors], $status);
        }

        // Back to the form, which keeps what the visitor typed
        return redirect()->to(url('/').'#contact')
            ->withErrors($errors)
            ->withInput($values)
            ->with('enquiry_error', $message);
    }

    protected function text(Request $request, string $key, int $max): string
    {
        $value = $request->input($key);

        // Bytes that aren't UTF-8 are swapped out, since the session (stored as JSON) can't hold them
        return is_string($value) ? mb_substr(trim(mb_scrub($value, 'UTF-8')), 0, $max) : '';
    }
}
