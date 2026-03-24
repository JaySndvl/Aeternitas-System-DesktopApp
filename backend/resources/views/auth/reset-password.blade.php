@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-600 to-blue-800 flex items-center justify-center p-4">
    <!-- Background Elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-white rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-float"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl opacity-20 animate-float" style="animation-delay: 2s;"></div>
        <div class="absolute top-40 left-40 w-80 h-80 bg-gray-300 rounded-full mix-blend-multiply filter blur-xl opacity-15 animate-float" style="animation-delay: 4s;"></div>
    </div>

    <div class="relative z-10 w-full max-w-md">
        <!-- Logo Section -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full shadow-lg mb-4">
                <i class="fas fa-lock text-3xl text-blue-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Reset Password</h1>
            <p class="text-blue-100">Enter your new password below</p>
        </div>

        <!-- Reset Form -->
        <div class="bg-white/95 backdrop-blur-xl border border-blue-200/20 rounded-2xl p-8 shadow-2xl">
            
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                    @foreach($errors->all() as $error)
                        <p><i class="fas fa-exclamation-circle mr-2"></i>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
                @csrf
                
                <input type="hidden" name="token" value="{{ $token }}">
                
                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-blue-800 mb-2">
                        <i class="fas fa-info-circle mr-2"></i>
                        Reset password for:
                    </p>
                    <p class="text-sm font-semibold text-blue-900">{{ $email }}</p>
                </div>

                <!-- New Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-blue-600"></i>New Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="w-full px-4 py-3 pl-12 pr-12 bg-white border border-blue-300 rounded-lg text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300 {{ $errors->has('password') ? 'border-red-500' : '' }}"
                            placeholder="Enter new password (min. 8 characters)"
                            required
                            autofocus
                        >
                        <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-blue-600"></i>
                        <button 
                            type="button" 
                            onclick="togglePassword('password')"
                            class="absolute right-4 top-1/2 transform -translate-y-1/2 text-blue-600 hover:text-blue-700 transition-colors"
                        >
                            <i class="fas fa-eye" id="toggleIcon-password"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-500 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Confirm Password Field -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-blue-600"></i>Confirm Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            class="w-full px-4 py-3 pl-12 pr-12 bg-white border border-blue-300 rounded-lg text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300 {{ $errors->has('password_confirmation') ? 'border-red-500' : '' }}"
                            placeholder="Confirm your new password"
                            required
                        >
                        <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-blue-600"></i>
                        <button 
                            type="button" 
                            onclick="togglePassword('password_confirmation')"
                            class="absolute right-4 top-1/2 transform -translate-y-1/2 text-blue-600 hover:text-blue-700 transition-colors"
                        >
                            <i class="fas fa-eye" id="toggleIcon-password_confirmation"></i>
                        </button>
                    </div>
                </div>

                <!-- Password Requirements -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <p class="text-xs font-medium text-gray-700 mb-2">Password Requirements:</p>
                    <ul class="text-xs text-gray-600 space-y-1">
                        <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-2"></i>Minimum 8 characters</li>
                        <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-2"></i>Use a mix of letters and numbers</li>
                        <li class="flex items-center"><i class="fas fa-check-circle text-green-500 mr-2"></i>Use special characters for better security</li>
                    </ul>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 transform hover:scale-105 shadow-lg"
                >
                    <i class="fas fa-check-circle mr-2"></i>Reset Password
                </button>

                <!-- Back to Login -->
                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-700 transition-colors inline-flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Login
                    </a>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-blue-100 text-sm">
                Remember your password? 
                <a href="{{ route('login') }}" class="text-white font-semibold hover:underline">Sign In</a>
            </p>
        </div>
    </div>
</div>

<script>
function togglePassword(id) {
    const passwordInput = document.getElementById(id);
    const toggleIcon = document.getElementById('toggleIcon-' + id);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}
.animate-float {
    animation: float 6s ease-in-out infinite;
}
</script>
@endsection

