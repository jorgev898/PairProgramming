<?php

namespace App\Http\Controllers;

use App\Models\PairSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
            'lesson_id' => ['nullable', 'exists:lessons,id'],
        ]);

        $code = strtoupper(Str::random(6));

        PairSession::create([
            'code' => $code,
            'lesson_id' => $request->input('lesson_id'),
            'driver' => $request->input('username'),
            'navigator' => null,
            'status' => 'waiting',
        ]);

        session(['username_' . $code => $request->input('username')]);

        return redirect()->route('pair.room', ['code' => $code]);
    }

    public function join(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
            'username' => ['required', 'string', 'max:30', 'regex:/^[a-zA-Z0-9_\- ]+$/'],
        ]);

        $code = strtoupper($request->input('code'));
        $session = PairSession::where('code', $code)->first();

        if (!$session) {
            return back()->withErrors(['code' => __('Session not found. Double-check the code.')])->withInput();
        }

        if ($session->status === 'active') {
            return back()->withErrors(['code' => __('This session is already full.')])->withInput();
        }

        $session->update([
            'navigator' => $request->input('username'),
            'status' => 'active',
        ]);

        session(['username_' . $code => $request->input('username')]);

        return redirect()->route('pair.room', ['code' => $code]);
    }

    public function room(string $code)
    {
        $code = strtoupper($code);
        $session = PairSession::with('lesson.course')->where('code', $code)->firstOrFail();

        $myName = session('username_' . $code);
        $myRole = $this->resolveRole($session, $myName);

        $lgConfig = [
            'url' => config('services.langraph.url', env('LANGRAPH_API_URL')),
            'key' => config('services.langraph.key', env('LANGRAPH_API_KEY')),
            'agent_id' => config('services.langraph.agent_id', env('LANGRAPH_AGENT_ID')),
        ];

        return view('pair.room', compact('session', 'myRole', 'myName', 'lgConfig'));
    }

    public function swap(string $code)
    {
        $code = strtoupper($code);
        $session = PairSession::where('code', $code)->firstOrFail();

        [$session->driver, $session->navigator] = [$session->navigator, $session->driver];
        $session->save();

        return redirect()->route('pair.room', ['code' => $code]);
    }

    /** Fast polling only for cursor positions (lightweight) */
    public function pollCursors(string $code)
    {
        $code = strtoupper($code);
        $session = PairSession::where('code', $code)->first(['cursors']);
        return response()->json(['cursors' => $session?->cursors ?? []]);
    }

    /** Polling de estado de la sesión (roles en tiempo real) */
    public function poll(Request $request, string $code)
    {
        $code = strtoupper($code);
        $session = PairSession::where('code', $code)->firstOrFail();

        $myName = $request->query('name', '');
        $myRole = $this->resolveRole($session, $myName);

        return response()->json([
            'driver' => $session->driver,
            'navigator' => $session->navigator,
            'status' => $session->status,
            'myRole' => $myRole,
            'thread_id' => $session->thread_id,
            'chat_history' => $session->chat_history ?? [],
            'participant_chat' => $session->participant_chat ?? [],
            'code_content' => $session->code_content,
            'cursors' => $session->cursors ?? [],
        ]);
    }

    /**
     * Guarda el contenido del editor de código.
     * Solo el driver debería llamar a este endpoint.
     */
    public function saveCode(Request $request, string $code)
    {
        $request->validate(['content' => ['required', 'string']]);

        $code = strtoupper($code);
        $session = PairSession::where('code', $code)->firstOrFail();

        $session->update(['code_content' => $request->input('content')]);

        return response()->json(['ok' => true]);
    }

    /**
     * Guarda la posición del cursor de un usuario.
     */
    public function saveCursor(Request $request, string $code)
    {
        $request->validate([
            'line' => ['sometimes', 'integer', 'min:1'],
            'column' => ['sometimes', 'integer', 'min:1'],
            'mouse_x' => ['sometimes', 'numeric'],
            'mouse_y' => ['sometimes', 'numeric'],
        ]);

        $code = strtoupper($code);
        $session = PairSession::where('code', $code)->firstOrFail();

        $myName = session('username_' . $code);
        $myRole = $this->resolveRole($session, $myName);
        if (!$myRole) return response()->json(['error' => 'Unknown role'], 403);

        $cursors = $session->cursors ?? [];
        $existing = $cursors[$myRole] ?? ['name' => $myName];
        $existing['name'] = $myName;

        if ($request->has('line'))    $existing['line'] = $request->input('line');
        if ($request->has('column'))  $existing['column'] = $request->input('column');
        if ($request->has('mouse_x')) $existing['mouse_x'] = $request->input('mouse_x');
        if ($request->has('mouse_y')) $existing['mouse_y'] = $request->input('mouse_y');

        $cursors[$myRole] = $existing;
        $session->update(['cursors' => $cursors]);

        return response()->json(['ok' => true]);
    }

    /**
     * Ejecuta código usando la API de Paiza.io.
     * Proxy server-side para no exponer APIs externas al cliente.
     */
    public function executeCode(Request $request, string $code)
    {
        $request->validate([
            'code' => ['required', 'string'],
            'language' => ['required', 'string', 'in:kotlin,python,java,javascript'],
        ]);

        try {
            // 1. Create execution job
            $createResponse = Http::timeout(15)->post('https://api.paiza.io/runners/create', [
                'source_code' => $request->input('code'),
                'language' => $request->input('language'),
                'api_key' => 'guest',
            ]);

            if ($createResponse->failed()) {
                return response()->json([
                    'error' => 'Code execution service unavailable. Please try again.',
                ], 502);
            }

            $jobId = $createResponse->json('id');

            // 2. Poll for result (max ~20 seconds)
            for ($i = 0; $i < 10; $i++) {
                usleep($i < 2 ? 1500000 : 2000000); // 1.5s first 2, then 2s

                $detailResponse = Http::timeout(10)->get('https://api.paiza.io/runners/get_details', [
                    'id' => $jobId,
                    'api_key' => 'guest',
                ]);

                if ($detailResponse->failed()) continue;

                $detail = $detailResponse->json();

                if ($detail['status'] === 'completed') {
                    return response()->json([
                        'run' => [
                            'stdout' => $detail['stdout'] ?? '',
                            'stderr' => $detail['stderr'] ?? '',
                            'code' => $detail['exit_code'] ?? 0,
                        ],
                        'compile' => [
                            'stderr' => $detail['build_stderr'] ?? '',
                        ],
                    ]);
                }
            }

            return response()->json([
                'error' => 'Code execution timed out. Please try again.',
            ], 504);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Code execution failed: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Guarda el thread_id de LangGraph en la BD para que ambos
     * dispositivos compartan el mismo hilo de conversación.
     */
    public function saveThread(Request $request, string $code)
    {
        $request->validate(['thread_id' => ['required', 'string', 'max:100']]);

        $code = strtoupper($code);
        $session = PairSession::where('code', $code)->firstOrFail();

        // Solo guardar si todavía no tiene uno (el primero que llega gana)
        if (!$session->thread_id) {
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

        $code = strtoupper($code);
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
        $code = strtoupper($code);
        $session = PairSession::where('code', $code)->firstOrFail();

        return response()->json([
            'history' => $session->chat_history ?? [],
            'thread_id' => $session->thread_id,
        ]);
    }

    /**
     * Guarda un mensaje en el chat de participantes.
     */
    public function saveParticipantChat(Request $request, string $code)
    {
        $request->validate(['text' => ['required', 'string', 'max:1000']]);

        $code = strtoupper($code);
        $session = PairSession::where('code', $code)->firstOrFail();

        $myName = session('username_' . $code);
        if (!$myName) return response()->json(['error' => 'Not authenticated in room'], 403);

        $chat = $session->participant_chat ?? [];
        $chat[] = [
            'sender' => $myName,
            'text' => $request->input('text')
        ];

        // Limitar a los últimos 100 mensajes para evitar que el JSON crezca demasiado
        if (count($chat) > 100) {
            $chat = array_slice($chat, -100);
        }

        $session->update(['participant_chat' => $chat]);

        return response()->json(['ok' => true]);
    }

    /**
     * Preview: compila Kotlin→JS via api.kotlinlang.org
     * Si detecta @Composable, retorna mode='compose' para el simulador client-side
     */
    public function previewCode(Request $request, string $code)
    {
        $request->validate(['code' => ['required', 'string']]);

        $kotlinCode = $request->input('code');

        // Detect Compose patterns
        $isCompose = preg_match('/@Composable|Scaffold|TopAppBar|MaterialTheme|Column\s*\(|Row\s*\(|LazyColumn|BottomNavigation/i', $kotlinCode);

        if ($isCompose) {
            return response()->json([
                'mode' => 'compose',
                'kotlinCode' => $kotlinCode,
                'errors' => [],
            ]);
        }

        // Console mode: compile Kotlin→JS via kotlinlang.org
        try {
            $payload = [
                'args' => '',
                'files' => [
                    [
                        'name' => 'File.kt',
                        'text' => $kotlinCode,
                        'publicId' => '',
                    ]
                ],
                'confType' => 'js',
            ];

            $response = Http::timeout(15)->post(
                'https://api.kotlinlang.org/api/compiler/translate',
                $payload
            );

            if ($response->failed()) {
                return response()->json([
                    'mode' => 'console',
                    'jsCode' => '',
                    'errors' => ['Kotlin compiler service unavailable. Try again.'],
                ], 502);
            }

            $data = $response->json();
            $errors = [];

            if (!empty($data['errors'])) {
                foreach ($data['errors'] as $err) {
                    $errors[] = $err['message'] ?? (is_string($err) ? $err : json_encode($err));
                }
            }

            return response()->json([
                'mode' => 'console',
                'jsCode' => $data['jsCode'] ?? '',
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'mode' => 'console',
                'jsCode' => '',
                'errors' => ['Preview failed: ' . $e->getMessage()],
            ], 500);
        }
    }

    // ── helpers ──────────────────────────────────────────────────────

    private function resolveRole(PairSession $session, ?string $name): ?string
    {
        if (!$name)
            return null;
        if ($session->driver === $name)
            return 'driver';
        if ($session->navigator === $name)
            return 'navigator';
        return null;
    }
}