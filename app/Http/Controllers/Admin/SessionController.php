<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Sleep;
use Illuminate\View\View;

class SessionController extends Controller
{
    /**
     * Show the login page, or go straight to the enquiries when already signed in.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if (AdminSession::check($request)) {
            return redirect()->route('admin.enquiries.index');
        }

        return view('admin.login');
    }

    /**
     * Sign in with the admin password.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! AdminSession::passwordIsSet()) {
            return back()->withErrors([
                'password' => 'The admin panel is switched off. Set ADMIN_PASSWORD on the server to switch it on.',
            ]);
        }

        if (! AdminSession::isPassword((string) $request->input('password', ''))) {
            // A pause after every wrong password makes guessing it far too slow to be worth trying
            Sleep::for(1)->second();

            return back()->withErrors(['password' => "That password isn't right."]);
        }

        return redirect()->route('admin.enquiries.index')->withCookie(AdminSession::cookie());
    }

    /**
     * Sign out.
     */
    public function destroy(): RedirectResponse
    {
        return redirect()->route('admin.login')->withCookie(AdminSession::forget());
    }
}
