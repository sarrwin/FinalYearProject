<x-guest-layout>
    
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Role -->
        <div class="mt-4">
            <x-input-label for="role" :value="__('Role')" />
            <select name="role" id="role" class="block mt-1 w-full" required>
                <option value="" selected disabled>Select Role</option>
                <option value="student">Student</option>
                <option value="supervisor">Supervisor</option>
                <option value="coordinator">Coordinator</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <!-- Matric Number (hidden by default) -->
        <div class="mt-4" id="matric_number_field" style="display: none;">
            <x-input-label for="matric_number" :value="__('Matric Number')" />
            <x-text-input id="matric_number" class="block mt-1 w-full" type="text" name="matric_number" :value="old('matric_number')" autocomplete="matric_number" />
            <x-input-error :messages="$errors->get('matric_number')" class="mt-2" />
        </div>

        <!-- Staff ID (hidden by default) -->
        <div class="mt-4" id="staff_id_field" style="display: none;">
            <x-input-label for="staff_id" :value="__('Staff ID')" />
            <x-text-input id="staff_id" class="block mt-1 w-full" type="text" name="staff_id" :value="old('staff_id')" autocomplete="staff_id" />
            <x-input-error :messages="$errors->get('staff_id')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>

<script>
    document.getElementById('role').addEventListener('change', function () {
        var role = this.value;
        var matricNumberField = document.getElementById('matric_number_field');
        var staffIdField = document.getElementById('staff_id_field');

        if (role === 'student') {
            matricNumberField.style.display = 'block';
            staffIdField.style.display = 'none';
        } else if (role === 'supervisor' || role === 'coordinator') {
            matricNumberField.style.display = 'none';
            staffIdField.style.display = 'block';
        } else {
            matricNumberField.style.display = 'none';
            staffIdField.style.display = 'none';
        }
    });
</script>
