<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;

class MvvmCourseSeeder extends Seeder
{
    public function run()
    {
        // Obtain a teacher/admin user (e.g. user ID 1 or first available)
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin Teacher',
                'email' => 'admin@pairsync.com',
                'password' => bcrypt('password123'),
                'role' => 'teacher',
            ]);
        }

        // Create the Course
        $course = Course::create([
            'user_id' => $user->id,
            'icon' => '🏛️',
            'title' => 'Arquitectura MVVM en Android',
            'description' => 'Domina el patrón Modelo-Vista-ViewModel (MVVM) utilizando Jetpack Compose. Aprende a separar la lógica de negocio de la interfaz de usuario para crear aplicaciones escalables y mantenibles.',
            'level' => 'intermediate',
            'color' => '#10b981', // Emerald green
        ]);

        // Lesson 1: Fundamentos teóricos
        Lesson::create([
            'course_id' => $course->id,
            'title' => 'Fundamentos de MVVM',
            'description' => '¿Qué es MVVM y por qué es el estándar recomendado por Google para aplicaciones Android modernas?',
            'duration' => '15 min',
            'icon' => '📖',
            'order' => 1,
            'available' => true,
            'content' => [
                'sections' => [
                    [
                        'type' => 'intro',
                        'title' => 'Introducción a MVVM',
                        'body' => 'El patrón Model-View-ViewModel (MVVM) ayuda a separar limpiamente la interfaz de usuario de la lógica de negocio y de los datos. Esta separación permite que el código sea mucho más fácil de testear, mantener y escalar en equipos grandes.'
                    ],
                    [
                        'type' => 'concept',
                        'title' => 'Componentes de la Arquitectura',
                        'body' => 'MVVM consta de tres componentes principales que se comunican de forma unidireccional:',
                        'bullets' => [
                            '**Model (Modelo):** Representa la capa de datos. Es el responsable de obtener los datos de una API, base de datos local (Room) o caché. No sabe nada de la interfaz visual.',
                            '**ViewModel:** Actúa como puente entre el Modelo y la Vista. Contiene la lógica de presentación. Expone los datos utilizando flujos observables (`StateFlow` o `LiveData`) para que la Vista reaccione a los cambios.',
                            '**View (Vista):** Es tu interfaz gráfica (Actividades, Fragmentos o Composables en Jetpack Compose). Su única responsabilidad es pintar en pantalla los datos que el ViewModel le proporciona y notificar al ViewModel sobre acciones del usuario (clicks, texto introducido, etc).'
                        ]
                    ],
                    [
                        'type' => 'concept',
                        'title' => 'La Regla de Oro',
                        'body' => 'En MVVM, la comunicación debe fluir de arriba hacia abajo, y los eventos de abajo hacia arriba:',
                        'bullets' => [
                            'La **Vista** conoce y observa al **ViewModel**.',
                            'El **ViewModel** conoce al **Modelo**, pero *nunca* tiene una referencia directa a la Vista (esto previene fugas de memoria).',
                            'El **Modelo** no conoce a nadie, solo devuelve datos.'
                        ]
                    ],
                    [
                        'type' => 'summary',
                        'title' => 'Puntos Clave',
                        'bullets' => [
                            'MVVM separa la UI de la lógica de negocio.',
                            'ViewModel sobrevive a la rotación de pantalla.',
                            'Mejora drásticamente la capacidad de testear el código (Testing).'
                        ]
                    ]
                ]
            ]
        ]);

        // Lesson 2: MVVM con Jetpack Compose + Video
        Lesson::create([
            'course_id' => $course->id,
            'title' => 'MVVM en Jetpack Compose',
            'description' => 'Cómo integrar ViewModels en Compose usando State Hoisting y StateFlow.',
            'duration' => '25 min',
            'icon' => '🎥',
            'order' => 2,
            'available' => true,
            'content' => [
                'sections' => [
                    [
                        'type' => 'intro',
                        'title' => 'El matrimonio perfecto: Compose + MVVM',
                        'body' => 'Jetpack Compose es un framework declarativo. Esto significa que la UI se redibuja automáticamente (recomposición) cuando el estado cambia. El ViewModel es el lugar ideal para almacenar ese estado.'
                    ],
                    [
                        'type' => 'video',
                        'title' => 'Explicación Práctica',
                        'body' => 'Mira este video para entender cómo la interfaz reacciona a los cambios en el StateFlow del ViewModel.',
                        'url' => 'https://www.youtube.com/embed/5aXQz9tAyd4' // Example video URL (can be changed)
                    ],
                    [
                        'type' => 'concept',
                        'title' => 'State Hoisting (Elevación de Estado)',
                        'body' => 'Es un patrón en Compose donde mueves el estado a un componente padre (o al ViewModel) para hacer que el componente actual sea "stateless" (sin estado interno).',
                        'bullets' => [
                            'En lugar de que un botón cambie su propio color internamente, el botón notifica al ViewModel que fue clickeado.',
                            'El ViewModel actualiza la variable de color en su `StateFlow`.',
                            'Compose detecta el cambio en el `StateFlow` y redibuja el botón con el nuevo color.'
                        ]
                    ],
                    [
                        'type' => 'code',
                        'title' => 'Ejemplo Básico de ViewModel',
                        'body' => 'Así se ve un ViewModel exponiendo estado a Jetpack Compose:',
                        'language' => 'kotlin',
                        'code' => "class CounterViewModel : ViewModel() {\n    // Estado interno inmutable desde fuera\n    private val _count = MutableStateFlow(0)\n    // Estado público observable por Compose\n    val count: StateFlow<Int> = _count.asStateFlow()\n\n    // Acción invocada por la Vista\n    fun increment() {\n        _count.value++\n    }\n}",
                        'note' => 'Nota cómo se usa el guión bajo `_count` para proteger la variable interna, exponiendo solo una versión de solo lectura `count` a la UI.'
                    ]
                ]
            ]
        ]);

        // Lesson 3: Reto de Programación
        Lesson::create([
            'course_id' => $course->id,
            'title' => 'Reto: Implementando MVVM',
            'description' => 'Pon a prueba tus conocimientos creando tu primera interfaz con estado manejado por un ViewModel.',
            'duration' => '45 min',
            'icon' => '🎯',
            'order' => 3,
            'available' => true,
            'content' => [
                'sections' => [
                    [
                        'type' => 'intro',
                        'title' => 'El Reto del Modo Oscuro',
                        'body' => 'Es momento de codificar. Usaremos el simulador interactivo de PairSync para resolver este reto. Deberás implementar un ViewModel que controle tanto un contador como un interruptor de "Modo Oscuro".'
                    ],
                    [
                        'type' => 'exercise',
                        'title' => 'Misión',
                        'body' => 'Completa el código Kotlin proporcionado para hacer que los componentes reaccionen correctamente al ViewModel.',
                        'tasks' => [
                            [
                                'title' => '1. Conecta el Contador',
                                'description' => 'Dentro de la función `CounterApp()`, haz que el botón de incrementar llame a la función `increment()` del ViewModel.',
                                'hint' => 'Asigna `onClick = { viewModel.increment() }` dentro del botón.'
                            ],
                            [
                                'title' => '2. Implementa el Switch',
                                'description' => 'El Switch (interruptor) debe controlar el estado `isDarkMode`. Notifica al ViewModel cada vez que el Switch cambie su valor.',
                                'hint' => 'Usa `onCheckedChange = { viewModel.toggleDarkMode(it) }`.'
                            ]
                        ]
                    ],
                    [
                        'type' => 'code',
                        'title' => 'Código Inicial (Starter Code)',
                        'language' => 'kotlin',
                        'code' => "class AppViewModel {\n    var count = 0\n    var isDarkMode = false\n\n    fun increment() {\n        count++\n    }\n\n    fun toggleDarkMode(enabled: Boolean) {\n        isDarkMode = enabled\n    }\n}\n\n@Composable\nfun CounterApp() {\n    val viewModel = AppViewModel()\n\n    Scaffold(\n        topBar = { TopAppBar(title = { Text(\"Reto MVVM\") }) }\n    ) {\n        Column {\n            Card {\n                Text(\"Contador: \${viewModel.count}\")\n                Button(onClick = { /* TODO 1 */ }) {\n                    Text(\"Sumar +1\")\n                }\n            }\n            \n            Row {\n                Text(\"Modo Oscuro\")\n                Switch(\n                    checked = viewModel.isDarkMode,\n                    onCheckedChange = { /* TODO 2 */ }\n                )\n            }\n        }\n    }\n}",
                        'note' => 'Copia este código y pégalo en la sala colaborativa (PairSync) para probarlo en el simulador en tiempo real.'
                    ]
                ]
            ]
        ]);
    }
}
