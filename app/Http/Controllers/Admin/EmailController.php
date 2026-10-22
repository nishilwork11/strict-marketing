<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TestEmail;
use App\Support\MailCheck;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class EmailController extends Controller
{
    /**
     * The mail settings the site is using, anything wrong with them, and a test to send.
     */
    public function show(MailCheck $check): View
    {
        return view('admin.email', [
            'settings' => $check->settings(),
            'problems' => $check->problems(),
            'sends' => $check->sends(),
        ]);
    }

    /**
     * Send a test email now, and come back with how it went or the mail server's own error.
     */
    public function send(Request $request, MailCheck $check): RedirectResponse
    {
        $to = $request->input('to');
        // The session is stored as JSON, which can't hold bytes that aren't UTF-8
        $to = is_string($to) ? mb_substr(trim(mb_scrub($to, 'UTF-8')), 0, 200) : '';

        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return redirect()->route('admin.email')
                ->withErrors(['to' => 'Enter an email address, like name@company.co.uk.'])
                ->withInput(['to' => $to]);
        }

        $started = microtime(true);

        try {
            $sent = Mail::to($to)->send(new TestEmail);
            $result = ['sent' => true, 'seconds' => round(microtime(true) - $started, 1), 'reply' => $check->serverReply($sent)];
        } catch (Throwable $e) {
            Log::warning('[email] The test email failed', ['exception' => $e]);

            $result = ['sent' => false, 'error' => mb_scrub(mb_substr($e->getMessage(), 0, 2000), 'UTF-8'), 'hint' => $check->explain($e)];
        }

        return redirect()->route('admin.email')
            ->withInput(['to' => $to])
            ->with('test', ['to' => $to] + $result);
    }
}
