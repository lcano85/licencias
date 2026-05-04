<h1>Welcome, {{ $user->name }}</h1>

<p>{{ __('Your account has been created successfully.') }}</p>

<p><strong>Email:</strong> {{ $user->email }}</p>
<p><strong>{{ __('Password:') }}</strong> {{ $plainPassword }}</p>

<p>{{ __('You can now log in using the above credentials.') }}</p>