<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function dashboard()
    {
        if (Auth::check()) {
            return view('dashboard');
        } else {
            return redirect()->route('login');
        }
    }

    public function register(Request $req)
    {
        $data = $req->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed',
            'admincode' => 'nullable|string',
        ]);

        $isAdmin = false;

        if (!empty($data['admincode'])) {
            if ($data['admincode'] !== env('ADMIN_SECRET_CODE')) {
                return back()->withErrors(['admincode' => 'Invalid admin code'])->withInput();
            }
            $isAdmin = true;
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'is_admin' => $isAdmin,
        ]);

        if ($user) {
            return redirect()->route('login');
        }
    }

    public function login(Request $req)
    {
        $credintials = $req->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credintials)) {
            return view('dashboard');
        } else {
            return redirect()->route('login');
        }
    }

    public function innerPage()
    {
        if (Auth::check()) {
            return redirect()->route('notes.table.data');
        } else {
            return redirect()->route('login');
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
