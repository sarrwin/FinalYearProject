<!DOCTYPE html>
<html>
<head>
    <title>Verify Your Email Address</title>
</head>
<body>
    <h1>Verify Your Email Address</h1>
    <p>A verification link has been sent to your email address. Please check your inbox and click the link to verify your email.</p>
    @if (session('resent'))
        <div>
            A new verification link has been sent to your email address.
        </div>
    @endif
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit">Resend Verification Email</button>
    </form>
</body>
</html>
