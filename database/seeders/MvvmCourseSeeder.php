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
        $course = Course::updateOrCreate(
            ['title' => 'Arquitectura MVVM en Android'],
            [
                'user_id' => $user->id,
                'icon' => '🏛️',
                'description' => 'Domina el patrón Modelo-Vista-ViewModel (MVVM) utilizando Jetpack Compose. Aprende a separar la lógica de negocio de la interfaz de usuario para crear aplicaciones escalables y mantenibles.',
                'level' => 'intermediate',
                'color' => '#10b981', // Emerald green
            ]
        );

        // Lesson 1: Fundamentos teóricos
        Lesson::updateOrCreate(
            [
                'course_id' => $course->id,
                'order' => 1,
            ],
            [
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
                        'body' => 'El patrón Model-View-ViewModel (MVVM) ayuda a organizar nuestro código para llevar un mejor control. Separa la app en tres módulos distintos, facilitando el mantenimiento y testeo.'
                    ],
                    [
                        'type' => 'concept',
                        'title' => 'Componentes de la Arquitectura',
                        'body' => 'MVVM consta de tres componentes principales que se comunican de forma unidireccional:',
                        'bullets' => [
                            '**Model (Modelo):** Representa la capa de datos. Es decir, cuando recuperamos de una base de datos o de un servicio web, toda esa información la almacenaremos en modelos de datos.',
                            '**ViewModel:** Es la conexión entre el modelo y la vista. Las vistas se suscriben a sus respectivos ViewModels y estos, al percatarse de que el modelo ha sido modificado, lo notificarán a la vista.',
                            '**View (Vista):** Es la parte de la UI (Composables, XML, Activities). Actuarán ejecutando acciones (ej. al pulsar un botón) pero no realizarán la lógica, se suscribirán al ViewModel y este les dirá cuándo y cómo pintar.'
                        ]
                    ],
                    [
                        'type' => 'concept',
                        'title' => 'La Regla de Oro',
                        'body' => 'En MVVM, la comunicación fluye hacia abajo y los eventos hacia arriba:',
                        'bullets' => [
                            'La **Vista** conoce y observa al **ViewModel**.',
                            'El **ViewModel** conoce al **Modelo**, pero *nunca* tiene una referencia directa a la Vista.',
                            'El **Modelo** no conoce a nadie, solo provee datos.'
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
            ]
        );

        // Lesson 2: MVVM con Jetpack Compose + Video
        Lesson::updateOrCreate(
            [
                'course_id' => $course->id,
                'order' => 2,
            ],
            [
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
                        'body' => 'Mira este video oficial de Android Developers para entender a fondo cómo funciona el Estado y la recomposición en Jetpack Compose.',
                        'url' => 'https://www.youtube.com/embed/V-s4z8njlsQ' // Example video URL (can be changed)
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
                    ],
                    [
                        'type' => 'code',
                        'title' => 'Conectando la Vista (Composable)',
                        'body' => 'Para que la interfaz reaccione a los cambios del `StateFlow`, debemos "observarlo" usando `collectAsState()`. Esto convierte el flujo en un Estado que Compose entiende.',
                        'language' => 'kotlin',
                        'code' => "@Composable\nfun CounterScreen() {\n    // 1. Instanciar el ViewModel\n    val viewModel = CounterViewModel()\n    \n    // 2. Observar el estado\n    val count by viewModel.count.collectAsState()\n\n    Column {\n        Text(\"Contador: \$count\")\n        // 3. Ejecutar acción\n        Button(onClick = { viewModel.increment() }) {\n            Text(\"Sumar +1\")\n        }\n    }\n}",
                        'note' => 'Sin `collectAsState()`, la pantalla nunca se actualizaría. Es la magia que une a Compose con MVVM.'
                    ]
                ]
            ]
            ]
        );

        // Lesson 3: Reto de Programación
        Lesson::updateOrCreate(
            [
                'course_id' => $course->id,
                'order' => 3,
            ],
            [
                'title' => 'Reto: App de Citas Célebres',
                'description' => 'Pon a prueba tus conocimientos creando la clásica app de Citas (Quotes App) con estado manejado por un ViewModel.',
                'duration' => '45 min',
            'icon' => '🎯',
            'order' => 3,
            'available' => true,
            'content' => [
                'sections' => [
                    [
                        'type' => 'intro',
                        'title' => 'El Reto de las Citas Célebres',
                        'body' => 'Es momento de codificar. Usaremos el simulador interactivo de PairSync. Deberás implementar un ViewModel que provea una cita aleatoria y hacer que la interfaz de Compose se actualice al pulsar un botón.'
                    ],
                    [
                        'type' => 'exercise',
                        'title' => 'Misión',
                        'body' => 'Completa el código Kotlin proporcionado para conectar la Vista con el ViewModel usando StateFlow.',
                        'tasks' => [
                            [
                                'title' => '1. Observa el Estado',
                                'description' => 'Dentro de la función `QuotesApp()`, observa el `StateFlow` del ViewModel usando `collectAsState()`.',
                                'hint' => 'Usa `val quote by viewModel.quote.collectAsState()`'
                            ],
                            [
                                'title' => '2. Conecta el Botón',
                                'description' => 'Haz que el botón llame a la función `randomQuote()` del ViewModel para actualizar la interfaz.',
                                'hint' => 'Asigna `onClick = { viewModel.randomQuote() }` dentro del botón.'
                            ]
                        ]
                    ],
                    [
                        'type' => 'code',
                        'title' => 'Código Inicial (Starter Code)',
                        'language' => 'kotlin',
                        'code' => "data class Quote(val text: String, val author: String)\n\nclass QuotesViewModel : ViewModel() {\n    private val quotes = listOf(\n        Quote(\"Talk is cheap. Show me the code.\", \"Linus Torvalds\"),\n        Quote(\"A user interface is like a joke. If you have to explain it, it’s not that good.\", \"Anonymous\"),\n        Quote(\"Measuring programming progress by lines of code is like measuring aircraft building progress by weight.\", \"Bill Gates\")\n    )\n\n    private val _quote = MutableStateFlow(quotes.first())\n    val quote: StateFlow<Quote> = _quote.asStateFlow()\n\n    fun randomQuote() {\n        _quote.value = quotes.random()\n    }\n}\n\n@Composable\nfun QuotesApp() {\n    val viewModel = QuotesViewModel()\n    // TODO 1: Observa el estado aquí\n    // val currentQuote = ...\n\n    Scaffold(\n        topBar = { TopAppBar(title = { Text(\"Citas Célebres MVVM\") }) }\n    ) {\n        Column(modifier = Modifier.fillMaxSize().padding(16.dp), verticalArrangement = Arrangement.Center) {\n            Card(modifier = Modifier.fillMaxWidth().padding(16.dp)) {\n                Column(modifier = Modifier.padding(16.dp)) {\n                    Text(text = \"\\\"\${/* currentQuote.text */}\\\"\", style = MaterialTheme.typography.h6)\n                    Spacer(modifier = Modifier.height(8.dp))\n                    Text(text = \"- \${/* currentQuote.author */}\", style = MaterialTheme.typography.body2)\n                }\n            }\n            \n            Button(onClick = { /* TODO 2 */ }, modifier = Modifier.align(Alignment.CenterHorizontally)) {\n                Text(\"Siguiente Cita\")\n            }\n        }\n    }\n}",
                        'note' => 'Copia este código y pégalo en la sala colaborativa (PairSync) para probarlo en el simulador en tiempo real.'
                    ]
                ]
            ]
            ]
        );
    }
}
