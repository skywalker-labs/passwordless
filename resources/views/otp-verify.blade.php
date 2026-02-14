<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .card { background: white; padding: 2rem; border-radius: 0.75rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); width: 100%; max-width: 28rem; }
        .btn-primary { background-color: #3b82f6; color: white; font-weight: 600; padding: 0.5rem 1rem; border-radius: 0.375rem; width: 100%; transition: background-color 0.2s; }
        .btn-primary:hover { background-color: #1d4ed8; }
        .input-field { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; text-align: center; font-size: 1.25rem; letter-spacing: 0.1em; }
        .input-field:focus { outline: none; ring: 2px; ring-color: #3b82f6; border-color: #3b82f6; }
    </style>
</head>
<body class="h-screen flex items-center justify-center">

    <div class="card">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Two-Factor Authentication</h2>
        <p class="text-center text-gray-600 mb-6">We've sent a One Time Password (OTP) to your registered email/phone. Please enter it below.</p>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('otp.verify.submit') }}" method="POST">
            @csrf
            <div class="mb-6">
                <label for="otp" class="block text-gray-700 text-sm font-bold mb-2">OTP Code</label>
                <input type="text" name="otp" id="otp" maxlength="6" required autofocus
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline text-center text-xl tracking-widest"
                    placeholder="123456">
                @error('otp')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Verify OTP
                </button>
            </div>
        </form>
        
        <form action="{{ route('otp.resend') }}" method="POST" class="mt-4 text-center">
            @csrf
            <button type="submit" class="text-sm text-blue-500 hover:text-blue-800">Resend OTP</button>
        </form>

        <div class="mt-6 text-center">
             <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-gray-500 hover:text-gray-800 text-sm">Logout</button>
            </form>
        </div>
    </div>

</body>
</html>
