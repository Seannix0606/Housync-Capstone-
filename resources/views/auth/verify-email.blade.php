<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Email - Housync</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900 antialiased font-sans">
    <div class="min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-md">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Verify Your Email Address
                </h2>
                <p class="mt-4 text-center text-sm text-gray-600">
                    Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
                </p>
            </div>

            @if (session('message'))
                <div class="mb-4 font-medium text-sm text-green-600 text-center">
                    {{ session('message') }}
                </div>
            @endif

            <div class="mt-4 flex items-center justify-between">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <div>
                        <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Resend Verification Email
                        </button>
                    </div>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900">
                        Log Out
                    </button>
                </form>
            </div>
            
            <div class="mt-6 text-center">
                <a href="{{ route('dashboard') }}" class="font-medium text-blue-600 hover:text-blue-500">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>
