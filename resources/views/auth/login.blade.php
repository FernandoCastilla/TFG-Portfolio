<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - UGR Portfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script>if (localStorage.getItem('tema') === 'oscuro') { document.documentElement.classList.add('dark'); }</script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-sans antialiased min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 transition-colors duration-200">

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="mx-auto h-16 w-16 bg-teal-700 rounded-xl flex items-center justify-center shadow-lg">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">Acceso al Panel</h2>
        <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">UGR Portfolio - Trabajo de Fin de Grado</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow-xl sm:rounded-2xl sm:px-10 border border-gray-100 dark:border-gray-700">

            @if ($errors->any())
                <div class="mb-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 rounded-md">
                    <p class="text-sm text-red-700 dark:text-red-400 font-medium">Las credenciales no coinciden con nuestros registros.</p>
                </div>
            @endif

            <form class="space-y-6" action="#" method="POST">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Correo Electrónico</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="text" autocomplete="email" required
                            class="appearance-none block w-full px-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-teal-500 focus:border-teal-600 sm:text-sm">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contraseña</label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="appearance-none block w-full px-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-teal-500 focus:border-teal-600 sm:text-sm">
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-teal-700 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-colors">
                        Iniciar Sesión
                    </button>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <p class="text-sm text-center text-gray-500 dark:text-gray-400 mb-3">¿Aún no tienes acceso al UGR Portfolio?</p>
                    <a href="{{ route('registro') }}"
                        class="block w-full text-center py-2.5 px-4 border-2 border-teal-700 text-teal-700 dark:text-teal-400 dark:border-teal-500 font-bold rounded-xl hover:bg-teal-50 dark:hover:bg-teal-900/30 transition-colors">
                        Crear una cuenta nueva
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>