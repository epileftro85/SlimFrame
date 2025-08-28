<div class="flex flex-row min-h-screen justify-center items-center">
    <div class="bg-white shadow rounded-lg p-6">
        <h1 id="form-title" class="text-xl font-semibold text-gray-800">Sign in</h1>

        <form id="auth-form" method="POST" action="/login" class="space-y-4" novalidate>
            <input type="hidden" id="mode" name="mode" value="login" />
            <?php echo $csrf; ?>

            <div id="row-name" class="hidden">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">First name</label>
                    <input id="name" name="name" type="text" autocomplete="given-name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last name</label>
                    <input id="last_name" name="last_name" type="text" autocomplete="family-name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                </div>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" name="email" type="email" autocomplete="email" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
            </div>

            <div>
                <label for="remember" class="block text-sm font-medium text-gray-700">Remember me</label>
                <input id="remember" name="remember" type="checkbox"
                    class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
            </div>

            <button id="submit-btn"
                type="submit"
                class="w-full inline-flex justify-center items-center px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Sign in
            </button>

            <hr>

            <button id="toggle-mode"
                type="button"
                class="text-sm text-blue-600 hover:underline"
                aria-controls="auth-form"
                aria-expanded="false">
                New here? Register
            </button>
        </form>
    </div>
</div>