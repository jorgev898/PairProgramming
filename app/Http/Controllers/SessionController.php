<?php

namespace App\Http\Controllers;

use App\Models\PairSession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    public function index()
    {
        return view('pair.index');
    }

    public function create(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:30', 'regex:/^[a-zA-Z0-9_\- ]+$/'],
        ]);

        $code = strtoupper(Str::random(6));

        PairSession::create([
            'code'      => $code,
            'driver'    => $request->input('username'),
            'navigator' => null,
            'status'    => 'waiting',
        ]);

        session(['username_' . $code => $request->input('username')]);

        return redirect()->route('pair.room', ['code' => $code]);
    }

    public function join(Request $request)
    {
        $request->validate([
            'code'     => ['required', 'string', 'size:6'],
            'username' => ['required', 'string', 'max:30', 'regex:/^[a-zA-Z0-9_\- ]+$/'],
        ]);

        $code    = strtoupper($request->input('code'));
        $session = PairSession::where('code', $code)->first();

        if (! $session) {
            return back()->withErrors(['code' => 'Session not found. Double-check the code.'])->withInput();
        }

        if ($session->status === 'active') {
            return back()->withErrors(['code' => 'This session is already full.'])->withInput();
        }

        $session->update([
            'navigator' => $request->input('username'),
            'status'    => 'active',
        ]);

        session(['username_' . $code => $request->input('username')]);

        return redirect()->route('pair.room', ['code' => $code]);
    }

    public function room(string $code)
    {
        $code    = strtoupper($code);
        $session = PairSession::where('code', $code)->firstOrFail();

        $myName = session('username_' . $code);
        $myRole = $this->resolveRole($session, $myName);

        return view('pair.room', compact('session', 'myRole', 'myName'));
    }

    public function swap(string $code)
    {
        $code    = strtoupper($code);
        $session = PairSession::where('code', $code)->firstOrFail();

        [$session->driver, $session->navigator] = [$session->navigator, $session->driver];
        $session->save();

        return redirect()->route('pair.room', ['code' => $code]);
    }

    /** Polling de estado de la sesión (roles en tiempo real) */
    public function poll(Request $request, string $code)
    {
        $code    = strtoupper($code);
        $session = PairSession::where('code', $code)->firstOrFail();

        $myName = $request->query('name', '');
        $myRole = $this->resolveRole($session, $myName);

        return response()->json([
            'driver'      => $session->driver,
            'navigator'   => $session->navigator,
            'status'      => $session->status,
            'myRole'      => $myRole,
            'thread_id'   => $session->thread_id,
            'chat_history'=> $session->chat_history ?? [],
        ]);
    }

    /**
     * Guarda el thread_id de LangGraph en la BD para que ambos
     * dispositivos compartan el mismo hilo de conversación.
     */
    public function saveThread(Request $request, string $code)
    {
        $request->validate(['thread_id' => ['required', 'string', 'max:100']]);

        $code    = strtoupper($code);
        $session = PairSession::where('code', $code)->firstOrFail();

        // Solo guardar si todavía no tiene uno (el primero que llega gana)
        if (! $session->thread_id) {
            $session->update(['thread_id' => $request->input('thread_id')]);
        }

        return response()->json(['thread_id' => $session->thread_id]);
    }

    /**
     * Persiste el historial de mensajes del chat en la BD.
     * Se llama después de cada mensaje para que ambos dispositivos
     * vean el mismo historial al recargar.
     */
    public function saveChat(Request $request, string $code)
    {
        $request->validate(['history' => ['required', 'array']]);

        $code    = strtoupper($code);
        $session = PairSession::where('code', $code)->firstOrFail();

        $session->update(['chat_history' => $request->input('history')]);

        return response()->json(['ok' => true]);
    }

    /**
     * Devuelve el historial de mensajes guardado.
     * Lo llama cada dispositivo al entrar a la sala.
     */
    public function loadChat(string $code)
    {
        $code    = strtoupper($code);
        $session = PairSession::where('code', $code)->firstOrFail();

        return response()->json([
            'history'   => $session->chat_history ?? [],
            'thread_id' => $session->thread_id,
        ]);
    }

    // ── helpers ──────────────────────────────────────────────────────

    private function resolveRole(PairSession $session, ?string $name): ?string
    {
        if (! $name) return null;
        if ($session->driver    === $name) return 'driver';
        if ($session->navigator === $name) return 'navigator';
        return null;
    }
}